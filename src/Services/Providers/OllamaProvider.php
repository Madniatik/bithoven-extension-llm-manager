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

        // Use native PHP streams for real streaming (Laravel Http waits for full response)
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 120,
            ],
        ]);

        $stream = @fopen($endpoint, 'r', false, $context);
        
        if (!$stream) {
            throw new \Exception("Failed to connect to Ollama at {$endpoint}");
        }

        // Storage for final metrics
        $finalData = null;

        // Read NDJSON stream line by line
        while (!feof($stream)) {
            $line = fgets($stream);
            if ($line === false) {
                continue;
            }

            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $data = json_decode($line, true);
            if (!$data) {
                continue;
            }

            // Send response chunk to callback
            // Some models (like qwen3) may use 'thinking' field instead of 'response'
            $chunk = $data['response'] ?? $data['thinking'] ?? null;
            if ($chunk !== null && $chunk !== '') {
                $callback($chunk);
            }

            // Check if done and capture final metrics
            if (isset($data['done']) && $data['done'] === true) {
                $finalData = $data;
                break;
            }
        }

        fclose($stream);

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
