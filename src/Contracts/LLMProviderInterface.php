<?php

namespace Bithoven\LLMManager\Contracts;

interface LLMProviderInterface
{
    /**
     * Generate text completion
     *
     * @param string $prompt
     * @param array $parameters
     * @return array ['response' => string, 'usage' => array]
     */
    public function generate(string $prompt, array $parameters = []): array;

    /**
     * Generate embeddings
     *
     * @param string|array $text
     * @return array
     */
    public function embed(string|array $text): array;

    /**
     * Stream response with optional conversation context
     *
     * @param string $prompt The user's message/prompt
     * @param array $context Previous conversation messages [{role: string, content: string}, ...]
     * @param array $parameters Generation parameters (temperature, max_tokens, etc.)
     * @param callable $callback Function to call for each chunk: function(string $chunk): void
     * @return array Usage metrics: ['usage' => ['prompt_tokens' => int, 'completion_tokens' => int, 'total_tokens' => int], 'model' => string, 'finish_reason' => string|null]
     */
    public function stream(string $prompt, array $context, array $parameters, callable $callback): array;

    /**
     * Check if provider supports feature
     *
     * @param string $feature
     * @return bool
     */
    public function supports(string $feature): bool;
}
