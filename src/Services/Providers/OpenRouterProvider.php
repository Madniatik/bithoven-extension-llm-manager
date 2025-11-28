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

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
    {
        // Build messages array with context
        $messages = [];
        
        // Add context messages if provided
        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }
        
        // Add current user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Storage for last response (contains usage metrics)
        $lastResponse = null;

        try {
            // Ensure parameters are of correct types
            $temperature = (float) ($parameters['temperature'] ?? $this->configuration->default_parameters['temperature'] ?? 0.7);
            $maxTokens = (int) ($parameters['max_tokens'] ?? $this->configuration->default_parameters['max_tokens'] ?? 4096);
            $topP = (float) ($parameters['top_p'] ?? $this->configuration->default_parameters['top_p'] ?? 1);

            // Stream using OpenAI-compatible API
            $stream = $this->client->chat()->createStreamed([
                'model' => $this->configuration->model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'top_p' => $topP,
            ]);

            foreach ($stream as $response) {
                $lastResponse = $response;
                if (isset($response->choices[0]->delta->content)) {
                    $callback($response->choices[0]->delta->content);
                }
            }
        } catch (\ErrorException $e) {
            // OpenRouter doesn't send completion_tokens_details fields that OpenAI SDK expects
            // This causes "Undefined array key 'accepted_prediction_tokens'" errors
            // These are cosmetic - streaming already completed successfully
            if (!str_contains($e->getMessage(), 'accepted_prediction_tokens') && 
                !str_contains($e->getMessage(), 'rejected_prediction_tokens') &&
                !str_contains($e->getMessage(), 'reasoning_tokens')) {
                // If it's a different error, re-throw it
                throw $e;
            }
            // Otherwise, silently ignore the cosmetic error
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
        ];
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? true, // Now implemented
            'vision' => $capabilities['vision'] ?? true,
            'function_calling' => $capabilities['function_calling'] ?? false,
            'json_mode' => $capabilities['json_mode'] ?? true,
            default => false,
        };
    }
}
