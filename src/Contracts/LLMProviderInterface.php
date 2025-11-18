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
     * Stream response (optional)
     *
     * @param string $prompt
     * @param array $parameters
     * @param callable $callback
     * @return void
     */
    public function stream(string $prompt, array $parameters, callable $callback): void;

    /**
     * Check if provider supports feature
     *
     * @param string $feature
     * @return bool
     */
    public function supports(string $feature): bool;
}
