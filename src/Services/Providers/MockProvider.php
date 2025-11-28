<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;

/**
 * Mock Provider for testing streaming functionality without a real LLM
 */
class MockProvider implements LLMProviderInterface
{
    private string $mockResponse = "This is a mock response from the Mock Provider. " .
        "It simulates real-time streaming by sending chunks at a controlled pace. " .
        "Each word is sent as a separate chunk to demonstrate the streaming capability. " .
        "This provider is useful for testing and development without needing a real LLM service.";

    public function __construct(protected LLMConfiguration $configuration)
    {
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        return [
            'response' => $this->mockResponse,
            'usage' => [
                'prompt_tokens' => str_word_count($prompt),
                'completion_tokens' => str_word_count($this->mockResponse),
                'total_tokens' => str_word_count($prompt) + str_word_count($this->mockResponse),
            ],
        ];
    }

    public function embed(string|array $text): array
    {
        // Return a fake embedding vector (768-dimensional, like many models)
        return array_fill(0, 768, 0.1);
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
    {
        // Simulate streaming by sending each word separately with a small delay
        $words = explode(' ', $this->mockResponse);
        
        foreach ($words as $word) {
            // Send word + space
            call_user_func($callback, $word . ' ');
            
            // Simulate processing time (very fast, just 10ms per word)
            usleep(10000); // 10ms delay
        }

        return [
            'response' => $this->mockResponse,
            'usage' => [
                'prompt_tokens' => str_word_count($prompt),
                'completion_tokens' => str_word_count($this->mockResponse),
                'total_tokens' => str_word_count($prompt) + str_word_count($this->mockResponse),
            ],
            'model' => 'mock-model',
            'finish_reason' => 'stop',
        ];
    }
}
