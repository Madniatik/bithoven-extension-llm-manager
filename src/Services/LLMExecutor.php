<?php

namespace Bithoven\LLMManager\Services;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Services\Providers\OllamaProvider;
use Bithoven\LLMManager\Services\Providers\OpenAIProvider;
use Bithoven\LLMManager\Services\Providers\AnthropicProvider;
use Bithoven\LLMManager\Services\Providers\CustomProvider;
use Bithoven\LLMManager\Events\LLMRequestStarted;
use Bithoven\LLMManager\Events\LLMRequestCompleted;
use Illuminate\Support\Facades\Event;

class LLMExecutor
{
    protected LLMConfiguration $configuration;
    protected ?string $extensionSlug = null;
    protected ?string $context = null;
    protected array $customParameters = [];

    public function __construct(protected LLMManager $manager)
    {
    }

    /**
     * Set configuration
     */
    public function setConfiguration(LLMConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * Set extension slug
     */
    public function setExtensionSlug(?string $extensionSlug): void
    {
        $this->extensionSlug = $extensionSlug;
    }

    /**
     * Set context
     */
    public function setContext(?string $context): void
    {
        $this->context = $context;
    }

    /**
     * Set custom parameters
     */
    public function setCustomParameters(array $parameters): void
    {
        $this->customParameters = $parameters;
    }

    /**
     * Execute LLM request
     */
    public function execute(string $prompt): array
    {
        $startTime = microtime(true);

        // Get effective parameters
        $parameters = $this->getEffectiveParameters();

        // Fire started event
        Event::dispatch(new LLMRequestStarted(
            $this->configuration,
            $prompt,
            $parameters
        ));

        try {
            // Get provider
            $provider = $this->getProvider();

            // Execute request
            $result = $provider->generate($prompt, $parameters);

            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            // Calculate cost
            $cost = $this->calculateCost(
                $result['usage']['prompt_tokens'] ?? 0,
                $result['usage']['completion_tokens'] ?? 0
            );

            // Log usage
            $usageLog = $this->logUsage(
                $prompt,
                $result['response'],
                $parameters,
                $result['usage'] ?? [],
                $cost,
                $executionTime,
                'success'
            );

            // Fire completed event
            Event::dispatch(new LLMRequestCompleted(
                $usageLog,
                $result
            ));

            // Check budget
            $this->checkBudget();

            return [
                'response' => $result['response'],
                'usage' => $result['usage'] ?? [],
                'cost' => $cost,
                'execution_time_ms' => $executionTime,
                'log_id' => $usageLog->id,
            ];

        } catch (\Exception $e) {
            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            // Log error
            $this->logUsage(
                $prompt,
                '',
                $parameters,
                [],
                0,
                $executionTime,
                'error',
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Get effective parameters (with overrides)
     */
    protected function getEffectiveParameters(): array
    {
        $baseParameters = $this->configuration->getEffectiveParameters(
            $this->extensionSlug,
            $this->context
        );

        return array_merge($baseParameters, $this->customParameters);
    }

    /**
     * Get provider instance
     */
    protected function getProvider(): LLMProviderInterface
    {
        return match ($this->configuration->provider) {
            'ollama' => new OllamaProvider($this->configuration),
            'openai' => new OpenAIProvider($this->configuration),
            'anthropic' => new AnthropicProvider($this->configuration),
            'custom' => new CustomProvider($this->configuration),
            default => throw new \Exception("Unsupported provider: {$this->configuration->provider}"),
        };
    }

    /**
     * Calculate cost based on token usage
     */
    protected function calculateCost(int $promptTokens, int $completionTokens): float
    {
        // Pricing per 1M tokens (example rates)
        $pricing = match ($this->configuration->provider) {
            'openai' => [
                'gpt-4o' => ['prompt' => 2.50, 'completion' => 10.00],
                'gpt-4o-mini' => ['prompt' => 0.15, 'completion' => 0.60],
            ],
            'anthropic' => [
                'claude-3-5-sonnet-20241022' => ['prompt' => 3.00, 'completion' => 15.00],
            ],
            'ollama' => ['prompt' => 0, 'completion' => 0], // Free (local)
            default => ['prompt' => 0, 'completion' => 0],
        };

        $modelPricing = $pricing[$this->configuration->model] ?? ['prompt' => 0, 'completion' => 0];

        $promptCost = ($promptTokens / 1000000) * $modelPricing['prompt'];
        $completionCost = ($completionTokens / 1000000) * $modelPricing['completion'];

        return round($promptCost + $completionCost, 6);
    }

    /**
     * Log usage
     */
    protected function logUsage(
        string $prompt,
        string $response,
        array $parameters,
        array $usage,
        float $cost,
        int $executionTime,
        string $status,
        ?string $errorMessage = null
    ): LLMUsageLog {
        return LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'user_id' => auth()->id(),
            'extension_slug' => $this->extensionSlug,
            'prompt' => $prompt,
            'response' => $response,
            'parameters_used' => $parameters,
            'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
            'completion_tokens' => $usage['completion_tokens'] ?? 0,
            'total_tokens' => $usage['total_tokens'] ?? 0,
            'cost_usd' => $cost,
            'execution_time_ms' => $executionTime,
            'status' => $status,
            'error_message' => $errorMessage,
            'executed_at' => now(),
        ]);
    }

    /**
     * Check budget and alert if needed
     */
    protected function checkBudget(): void
    {
        if (!config('llm-manager.budget.enabled', false)) {
            return;
        }

        $budgetManager = app(LLMBudgetManager::class);
        $budgetManager->checkAndAlert($this->extensionSlug);
    }
}
