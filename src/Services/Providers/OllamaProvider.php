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
        $response = Http::timeout(120)
            ->post($this->configuration->api_endpoint, [
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
        $endpoint = str_replace('/api/generate', '/api/embeddings', $this->configuration->api_endpoint);

        $response = Http::post($endpoint, [
            'model' => $this->configuration->model,
            'prompt' => is_array($text) ? $text[0] : $text,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Ollama embeddings error: {$response->body()}");
        }

        return $response->json('embedding', []);
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): void
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

        // Create streaming request
        $response = Http::timeout(120)
            ->withBody(json_encode([
                'model' => $this->configuration->model,
                'prompt' => $fullPrompt,
                'stream' => true,
                'options' => [
                    'temperature' => $parameters['temperature'] ?? $this->configuration->default_parameters['temperature'] ?? 0.7,
                    'num_predict' => $parameters['max_tokens'] ?? $this->configuration->default_parameters['max_tokens'] ?? 2000,
                    'top_p' => $parameters['top_p'] ?? $this->configuration->default_parameters['top_p'] ?? 0.9,
                ],
            ]), 'application/json')
            ->post($endpoint);

        if (!$response->successful()) {
            throw new \Exception("Ollama streaming error: {$response->status()} - {$response->body()}");
        }

        // Process NDJSON stream (one JSON object per line)
        $body = $response->body();
        $lines = explode("\n", $body);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $data = json_decode($line, true);
            if (!$data) {
                continue;
            }

            // Send response chunk to callback
            if (isset($data['response']) && !empty($data['response'])) {
                $callback($data['response']);
            }

            // Check if done
            if (isset($data['done']) && $data['done'] === true) {
                break;
            }
        }
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
