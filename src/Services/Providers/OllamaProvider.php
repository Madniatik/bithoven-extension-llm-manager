<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements LLMProviderInterface
{
    public function __construct(protected LLMConfiguration $configuration)
    {
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        $endpoint = rtrim($this->configuration->api_endpoint, '/') . '/api/generate';
        
        $response = Http::timeout(120)
            ->post($endpoint, [
                'model' => $this->configuration->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => $parameters['temperature'] ?? 0.7,
                    'top_p' => $parameters['top_p'] ?? 0.9,
                    'num_predict' => $parameters['max_tokens'] ?? 2000,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("Ollama API error: {$response->body()}");
        }

        $data = $response->json();

        return [
            'response' => $data['response'] ?? '',
            'usage' => [
                'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                'completion_tokens' => $data['eval_count'] ?? 0,
                'total_tokens' => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
            ],
        ];
    }

    public function embed(string|array $text): array
    {
        $endpoint = rtrim($this->configuration->api_endpoint, '/') . '/api/embeddings';

        $response = Http::post($endpoint, [
            'model' => $this->configuration->model,
            'prompt' => is_array($text) ? $text[0] : $text,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Ollama embeddings error: {$response->body()}");
        }

        return $response->json('embedding', []);
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
    {
        // Ollama streaming endpoint
        $endpoint = rtrim($this->configuration->api_endpoint, '/') . '/api/generate';

        // Build context if provided (for multi-turn conversations)
        $systemPrompt = '';
        if (!empty($context)) {
            $contextText = collect($context)
                ->map(fn($msg) => "{$msg['role']}: {$msg['content']}")
                ->join("\n");
            $systemPrompt = "Previous conversation:\n{$contextText}\n\n";
        }

        $fullPrompt = $systemPrompt . "user: {$prompt}";

        // Prepare request payload
        $payload = json_encode([
            'model' => $this->configuration->model,
            'prompt' => $fullPrompt,
            'stream' => true,
            'options' => [
                'temperature' => $parameters['temperature'] ?? $this->configuration->default_parameters['temperature'] ?? 0.7,
                'num_predict' => $parameters['max_tokens'] ?? $this->configuration->default_parameters['max_tokens'] ?? 2000,
                'top_p' => $parameters['top_p'] ?? $this->configuration->default_parameters['top_p'] ?? 0.9,
            ],
        ]);

        \Log::info('OllamaProvider: Payload', [
            'payload' => $payload,
            'payload_length' => strlen($payload),
            'context_count' => count($context),
        ]);

        // Use cURL for real streaming (fopen doesn't work well with POST)
        $finalData = null;
        $buffer = '';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_WRITEFUNCTION => function($curl, $data) use ($callback, &$finalData, &$buffer) {
                $buffer .= $data;
                $lines = explode("\n", $buffer);
                
                // Keep last incomplete line in buffer
                $buffer = array_pop($lines);
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) {
                        continue;
                    }

                    $json = json_decode($line, true);
                    if (!$json) {
                        continue;
                    }

                    // Send response chunk to callback
                    // Some models (like qwen3) may use 'thinking' field instead of 'response'
                    $chunk = $json['response'] ?? $json['thinking'] ?? null;
                    if ($chunk !== null && $chunk !== '') {
                        $callback($chunk);
                    }

                    // Check if done and capture final metrics
                    if (isset($json['done']) && $json['done'] === true) {
                        $finalData = $json;
                    }
                }
                
                return strlen($data);
            },
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        \Log::info('OllamaProvider: Starting cURL request', [
            'endpoint' => $endpoint,
            'model' => $this->configuration->model,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        \Log::info('OllamaProvider: cURL completed', [
            'result' => $result === false ? 'FALSE' : 'TRUE',
            'http_code' => $httpCode,
            'error' => $error,
            'errno' => $errno,
            'finalData_isset' => isset($finalData),
        ]);
        
        if ($result === false) {
            throw new \Exception("Failed to connect to Ollama at {$endpoint}: {$error}");
        }

        // Extract usage metrics from final response
        return [
            'usage' => [
                'prompt_tokens' => $finalData['prompt_eval_count'] ?? 0,
                'completion_tokens' => $finalData['eval_count'] ?? 0,
                'total_tokens' => ($finalData['prompt_eval_count'] ?? 0) + ($finalData['eval_count'] ?? 0),
            ],
            'model' => $this->configuration->model,
            'finish_reason' => $finalData['done_reason'] ?? 'stop',
        ];
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? true,
            'vision' => $capabilities['vision'] ?? false,
            'function_calling' => $capabilities['function_calling'] ?? false,
            'json_mode' => $capabilities['json_mode'] ?? true,
            default => false,
        };
    }
}
