<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Illuminate\Support\Facades\Http;

class CustomProvider implements LLMProviderInterface
{
    public function __construct(protected LLMProviderConfiguration $configuration)
    {
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        $headers = [];
        if ($this->configuration->api_key) {
            $headers['Authorization'] = "Bearer {$this->configuration->api_key}";
        }

        $response = Http::withHeaders($headers)
            ->timeout(120)
            ->post($this->configuration->api_endpoint, [
                'model' => $this->configuration->model,
                'prompt' => $prompt,
                'parameters' => $parameters,
            ]);

        if (!$response->successful()) {
            throw new \Exception("Custom LLM API error: {$response->body()}");
        }

        $data = $response->json();

        // Adapt response format - assumes standard format
        return [
            'response' => $data['response'] ?? $data['text'] ?? $data['content'] ?? '',
            'usage' => [
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                'total_tokens' => $data['usage']['total_tokens'] ?? 0,
            ],
        ];
    }

    public function embed(string|array $text): array
    {
        $headers = [];
        if ($this->configuration->api_key) {
            $headers['Authorization'] = "Bearer {$this->configuration->api_key}";
        }

        $endpoint = str_replace('/generate', '/embed', $this->configuration->api_endpoint);

        $response = Http::withHeaders($headers)->post($endpoint, [
            'text' => is_array($text) ? $text : [$text],
        ]);

        if (!$response->successful()) {
            throw new \Exception("Custom embedding error: {$response->body()}");
        }

        $embeddings = $response->json('embeddings', []);

        return is_array($text) ? $embeddings : ($embeddings[0] ?? []);
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
    {
        // Custom streaming implementation
        // This is a basic example - adapt to your custom API
        throw new \Exception('Streaming not implemented for custom provider. Override this method in your custom implementation.');
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? false,
            'vision' => $capabilities['vision'] ?? false,
            'function_calling' => $capabilities['function_calling'] ?? false,
            'json_mode' => $capabilities['json_mode'] ?? false,
            default => $capabilities[$feature] ?? false,
        };
    }
}
