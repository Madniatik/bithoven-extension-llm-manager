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

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
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

        // Storage for last response (contains usage metrics)
        $lastResponse = null;

        try {
            // Ensure parameters are of correct types
            $temperature = (float) ($parameters['temperature'] ?? $this->configuration->default_parameters['temperature'] ?? 0.7);
            $maxTokens = (int) ($parameters['max_tokens'] ?? $this->configuration->default_parameters['max_tokens'] ?? 4096);
            $topP = (float) ($parameters['top_p'] ?? $this->configuration->default_parameters['top_p'] ?? 1);
            $frequencyPenalty = (float) ($parameters['frequency_penalty'] ?? $this->configuration->default_parameters['frequency_penalty'] ?? 0);
            $presencePenalty = (float) ($parameters['presence_penalty'] ?? $this->configuration->default_parameters['presence_penalty'] ?? 0);

            // Create streaming request
            $stream = $this->client->chat()->createStreamed([
                'model' => $this->configuration->model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'top_p' => $topP,
                'frequency_penalty' => $frequencyPenalty,
                'presence_penalty' => $presencePenalty,
            ]);

            // Process stream chunks
            foreach ($stream as $response) {
                $lastResponse = $response;
                if (isset($response->choices[0]->delta->content)) {
                    $callback($response->choices[0]->delta->content);
                }
            }
        } catch (\ErrorException $e) {
            // Some OpenAI-compatible providers don't send all token detail fields
            // Silently ignore these cosmetic errors if streaming completed
            if (!str_contains($e->getMessage(), 'accepted_prediction_tokens') && 
                !str_contains($e->getMessage(), 'rejected_prediction_tokens') &&
                !str_contains($e->getMessage(), 'reasoning_tokens')) {
                throw $e;
            }
        }

        // Extract usage metrics from last response
        return [
            'usage' => [
                'prompt_tokens' => $lastResponse->usage->promptTokens ?? 0,
                'completion_tokens' => $lastResponse->usage->completionTokens ?? 0,
                'total_tokens' => $lastResponse->usage->totalTokens ?? 0,
            ],
            'model' => $lastResponse->model ?? $this->configuration->model,
            'finish_reason' => $lastResponse->choices[0]->finishReason ?? 'stop',
            // OpenAI-specific metadata
            'system_fingerprint' => $lastResponse->systemFingerprint ?? null,
            'created' => $lastResponse->created ?? null,
            // Complete raw response for debugging and analysis
            'raw_response' => $lastResponse->toArray(),
        ];
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
