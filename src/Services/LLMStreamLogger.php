<?php

namespace Bithoven\LLMManager\Services;

use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Illuminate\Support\Str;

class LLMStreamLogger
{
    /**
     * Start a streaming session and return session data
     *
     * @param LLMProviderConfiguration $configuration
     * @param string $prompt
     * @param array $parameters
     * @param int|null $sessionId DB session_id for conversation tracking (optional)
     * @param int|null $requestMessageId DB request_message_id (user message) for tracking (optional)
     * @return array Session data with session_id and start_time
     */
    public function startSession(
        LLMProviderConfiguration $configuration,
        string $prompt,
        array $parameters,
        ?int $sessionId = null,
        ?int $requestMessageId = null
    ): array {
        return [
            'session_id' => Str::uuid()->toString(), // Temporary UUID for logger tracking
            'db_session_id' => $sessionId, // Real DB session_id
            'db_request_message_id' => $requestMessageId, // Real DB request_message_id (user message)
            'start_time' => microtime(true),
            'configuration' => $configuration,
            'prompt' => $prompt,
            'parameters' => $parameters,
        ];
    }

    /**
     * End streaming session and save to database
     *
     * @param array $session Session data from startSession()
     * @param string $response Full response text
     * @param array $metrics Metrics from provider: ['usage' => [...], 'model' => '...', 'finish_reason' => '...']
     * @param int|null $responseMessageId DB response_message_id (assistant message) created after streaming
     * @return LLMUsageLog
     */
    public function endSession(array $session, string $response, array $metrics, ?int $responseMessageId = null): LLMUsageLog
    {
        $executionTimeMs = (int) ((microtime(true) - $session['start_time']) * 1000);

        $usage = $metrics['usage'] ?? [];
        
        // Use provider-calculated cost if available (e.g., OpenRouter), otherwise calculate
        $cost = $metrics['cost'] ?? $this->calculateCost(
            $session['configuration']->provider->slug,
            $metrics['model'] ?? $session['configuration']->model,
            $usage
        );

        return LLMUsageLog::create([
            'llm_provider_configuration_id' => $session['configuration']->id,
            'user_id' => auth()->id(),
            'session_id' => $session['db_session_id'] ?? null, // Real DB session_id (if provided)
            'request_message_id' => $session['db_request_message_id'] ?? null, // User message (request)
            'response_message_id' => $responseMessageId, // Assistant message (response)
            'extension_slug' => 'llm-manager', // Can be overridden
            'prompt' => $session['prompt'],
            'response' => $response,
            'parameters_used' => $session['parameters'],
            'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
            'completion_tokens' => $usage['completion_tokens'] ?? 0,
            'total_tokens' => $usage['total_tokens'] ?? 0,
            'cost_usd' => $cost,
            'execution_time_ms' => $executionTimeMs,
            'status' => 'success',
            'executed_at' => now(),
        ]);
    }

    /**
     * Calculate cost based on provider, model and token usage
     *
     * @param string $provider
     * @param string $model
     * @param array $usage
     * @return float Cost in USD
     */
    public function calculateCost(string $provider, string $model, array $usage): float
    {
        // Load pricing from config
        $pricing = config('llm-manager.pricing', []);

        // Ollama and local models are free
        if (in_array($provider, ['ollama', 'local', 'custom'])) {
            return 0.0;
        }

        // Get model-specific pricing or provider default
        $modelPricing = $pricing[$provider][$model] ?? $pricing[$provider]['default'] ?? null;

        if (!$modelPricing) {
            // Fallback pricing if not configured
            return 0.0;
        }

        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;

        // Pricing is per 1M tokens
        $promptCost = ($promptTokens / 1_000_000) * ($modelPricing['prompt'] ?? 0);
        $completionCost = ($completionTokens / 1_000_000) * ($modelPricing['completion'] ?? 0);

        return round($promptCost + $completionCost, 6);
    }

    /**
     * Log an error during streaming
     *
     * @param array $session
     * @param string $errorMessage
     * @return LLMUsageLog
     */
    public function logError(array $session, string $errorMessage): LLMUsageLog
    {
        $executionTimeMs = (int) ((microtime(true) - $session['start_time']) * 1000);

        return LLMUsageLog::create([
            'llm_provider_configuration_id' => $session['configuration']->id,
            'user_id' => auth()->id(),
            'session_id' => $session['db_session_id'] ?? null, // Real DB session_id (if provided)
            'request_message_id' => $session['db_request_message_id'] ?? null, // User message (request)
            'response_message_id' => null, // No response on error
            'extension_slug' => 'llm-manager',
            'prompt' => $session['prompt'],
            'response' => '',
            'parameters_used' => $session['parameters'],
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'cost_usd' => 0,
            'execution_time_ms' => $executionTimeMs,
            'status' => 'error',
            'error_message' => $errorMessage,
            'executed_at' => now(),
        ]);
    }
}
