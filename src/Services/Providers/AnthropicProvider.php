<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Illuminate\Support\Facades\Http;

class AnthropicProvider implements LLMProviderInterface
{
    public function __construct(protected LLMProviderConfiguration $configuration)
    {
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->configuration->api_key,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(120)->post($this->configuration->api_endpoint, [
            'model' => $this->configuration->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $parameters['max_tokens'] ?? 4096,
            'temperature' => $parameters['temperature'] ?? 1.0,
            'top_p' => $parameters['top_p'] ?? 1,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Anthropic API error: {$response->body()}");
        }

        $data = $response->json();

        return [
            'response' => $data['content'][0]['text'] ?? '',
            'usage' => [
                'prompt_tokens' => $data['usage']['input_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['output_tokens'] ?? 0,
                'total_tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
            ],
            // Anthropic-specific metadata
            'stop_reason' => $data['stop_reason'] ?? null,
            'stop_sequence' => $data['stop_sequence'] ?? null,
            // Complete raw response for debugging and analysis
            'raw_response' => $data,
        ];
    }

    public function embed(string|array $text): array
    {
        // Anthropic doesn't provide embeddings API
        // Use OpenAI or another service for embeddings
        throw new \Exception('Anthropic does not support embeddings. Use OpenAI or configure a separate embedding service.');
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
    {
        throw new \Exception('Streaming not yet implemented for Anthropic provider');
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? false, // Disabled until implemented
            'vision' => $capabilities['vision'] ?? true,
            'function_calling' => $capabilities['function_calling'] ?? true,
            'json_mode' => $capabilities['json_mode'] ?? false,
            default => false,
        };
    }
}
