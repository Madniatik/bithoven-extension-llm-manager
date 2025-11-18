<?php

namespace Bithoven\LLMManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * LLM Facade
 * 
 * @method static array generate(string $prompt, array $parameters = [])
 * @method static \Bithoven\LLMManager\Services\LLMManager config(string $slug)
 * @method static \Bithoven\LLMManager\Services\LLMManager parameters(array $parameters)
 * @method static \Bithoven\LLMManager\Services\LLMManager extension(string $extensionSlug)
 * @method static \Bithoven\LLMManager\Services\LLMManager context(string $context)
 * @method static array embed(string|array $text)
 * @method static array chat(string $sessionId, string $message)
 * @method static string conversation(string $sessionId = null)
 * @method static array template(string $slug, array $variables)
 * @method static array workflow(string $slug, array $input)
 * @method static array rag(string $query, string $extensionSlug = null)
 * @method static array tool(string $slug, array $parameters)
 * @method static void recordMetric(string $usageLogId, string $key, mixed $value, string $type = 'string')
 * 
 * @see \Bithoven\LLMManager\Services\LLMManager
 */
class LLM extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'llm';
    }
}
