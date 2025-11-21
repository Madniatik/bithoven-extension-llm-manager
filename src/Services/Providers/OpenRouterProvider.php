<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;
use OpenAI;

class OpenRouterProvider implements LLMProviderInterface
{
    protected $client;

    public function __construct(protected LLMConfiguration $configuration)
    {
        // OpenRouter uses OpenAI-compatible API
        $this->client = OpenAI::factory()
            ->withApiKey($configuration->api_key)
            ->withBaseUri('https://openrouter.ai/api/v1')
            ->make();
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        $response = $this->client->chat()->create([
            'model' => $this->configuration->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $parameters['temperature'] ?? 0.7,
            'max_tokens' => $parameters['max_tokens'] ?? 4096,
            'top_p' => $parameters['top_p'] ?? 1,
        ]);

        return [
            'response' => $response->choices[0]->message->content,
            'usage' => [
                'prompt_tokens' => $response->usage->promptTokens,
                'completion_tokens' => $response->usage->completionTokens,
                'total_tokens' => $response->usage->totalTokens,
            ],
        ];
    }

    public function embed(string|array $text): array
    {
        // OpenRouter doesn't support embeddings
        throw new \Exception('OpenRouter does not support embeddings. Use OpenAI or local provider.');
    }

    public function stream(string $prompt, array $parameters, callable $callback): void
    {
        $stream = $this->client->chat()->createStreamed([
            'model' => $this->configuration->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $parameters['temperature'] ?? 0.7,
            'max_tokens' => $parameters['max_tokens'] ?? 4096,
        ]);

        foreach ($stream as $response) {
            if (isset($response->choices[0]->delta->content)) {
                $callback($response->choices[0]->delta->content);
            }
        }
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): void
    {
        throw new \Exception('Streaming not yet implemented for OpenRouter provider');
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? false, // Disabled until implemented
            'vision' => $capabilities['vision'] ?? true,
            'function_calling' => $capabilities['function_calling'] ?? false,
            'json_mode' => $capabilities['json_mode'] ?? true,
            default => false,
        };
    }
}
