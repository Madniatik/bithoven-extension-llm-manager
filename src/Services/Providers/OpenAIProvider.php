<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;
use OpenAI;

class OpenAIProvider implements LLMProviderInterface
{
    protected $client;

    public function __construct(protected LLMConfiguration $configuration)
    {
        $this->client = OpenAI::client($configuration->api_key);
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
            'frequency_penalty' => $parameters['frequency_penalty'] ?? 0,
            'presence_penalty' => $parameters['presence_penalty'] ?? 0,
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
        $model = config('llm-manager.rag.embedding_model', 'text-embedding-3-small');

        $response = $this->client->embeddings()->create([
            'model' => $model,
            'input' => is_array($text) ? $text : [$text],
        ]);

        if (is_array($text)) {
            return array_map(fn($item) => $item->embedding, $response->embeddings);
        }

        return $response->embeddings[0]->embedding;
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): void
    {
        // Build messages array with context
        $messages = [];

        // Add conversation context if provided
        if (!empty($context)) {
            foreach ($context as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        // Create streaming request
        $stream = $this->client->chat()->createStreamed([
            'model' => $this->configuration->model,
            'messages' => $messages,
            'temperature' => $parameters['temperature'] ?? $this->configuration->default_parameters['temperature'] ?? 0.7,
            'max_tokens' => $parameters['max_tokens'] ?? $this->configuration->default_parameters['max_tokens'] ?? 4096,
            'top_p' => $parameters['top_p'] ?? $this->configuration->default_parameters['top_p'] ?? 1,
            'frequency_penalty' => $parameters['frequency_penalty'] ?? $this->configuration->default_parameters['frequency_penalty'] ?? 0,
            'presence_penalty' => $parameters['presence_penalty'] ?? $this->configuration->default_parameters['presence_penalty'] ?? 0,
        ]);

        // Process stream chunks
        foreach ($stream as $response) {
            if (isset($response->choices[0]->delta->content)) {
                $callback($response->choices[0]->delta->content);
            }
        }
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? true,
            'vision' => $capabilities['vision'] ?? true,
            'function_calling' => $capabilities['function_calling'] ?? true,
            'json_mode' => $capabilities['json_mode'] ?? true,
            default => false,
        };
    }
}
