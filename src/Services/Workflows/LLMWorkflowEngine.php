<?php

namespace Bithoven\LLMManager\Services\Workflows;

use Bithoven\LLMManager\Models\LLMAgentWorkflow;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\RAG\LLMRAGService;

class LLMWorkflowEngine
{
    protected array $state = [];
    protected array $executionHistory = [];

    public function __construct(
        protected LLMManager $llmManager
    ) {
    }

    /**
     * Execute a workflow
     */
    public function execute(string $slug, array $input): array
    {
        $workflow = LLMAgentWorkflow::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $this->state = $input;
        $this->executionHistory = [];

        $startTime = microtime(true);

        try {
            $result = $this->runWorkflow($workflow);

            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            return [
                'success' => true,
                'result' => $result,
                'execution_time_ms' => $executionTime,
                'steps_executed' => count($this->executionHistory),
                'history' => $this->executionHistory,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'steps_executed' => count($this->executionHistory),
                'history' => $this->executionHistory,
            ];
        }
    }

    /**
     * Run workflow state machine
     */
    protected function runWorkflow(LLMAgentWorkflow $workflow): mixed
    {
        $currentState = $workflow->initial_state;
        $steps = 0;
        $maxSteps = $workflow->max_steps;

        while ($currentState !== 'end' && $steps < $maxSteps) {
            $stateDefinition = $workflow->states[$currentState] ?? null;

            if (!$stateDefinition) {
                throw new \Exception("State '{$currentState}' not found in workflow definition");
            }

            // Execute state
            $result = $this->executeState($stateDefinition, $workflow);

            // Store in history
            $this->executionHistory[] = [
                'state' => $currentState,
                'result' => $result,
                'timestamp' => now()->toISOString(),
            ];

            // Update state data
            $this->state[$currentState] = $result;

            // Find next state
            $currentState = $this->findNextState($currentState, $workflow, $result);

            $steps++;
        }

        if ($steps >= $maxSteps) {
            throw new \Exception("Workflow exceeded maximum steps ({$maxSteps})");
        }

        return $this->state;
    }

    /**
     * Execute a single state
     */
    protected function executeState(array $stateDefinition, LLMAgentWorkflow $workflow): mixed
    {
        $type = $stateDefinition['type'];

        return match ($type) {
            'llm' => $this->executeLLMState($stateDefinition, $workflow),
            'rag' => $this->executeRAGState($stateDefinition, $workflow),
            'condition' => $this->executeConditionState($stateDefinition),
            'transform' => $this->executeTransformState($stateDefinition),
            default => throw new \Exception("Unknown state type: {$type}"),
        };
    }

    /**
     * Execute LLM state
     */
    protected function executeLLMState(array $stateDefinition, LLMAgentWorkflow $workflow): array
    {
        $prompt = $this->interpolateVariables($stateDefinition['prompt']);

        // Use workflow's LLM configuration
        if ($workflow->llm_configuration_id) {
            $this->llmManager->config($workflow->configuration->slug);
        }

        return $this->llmManager->generate($prompt);
    }

    /**
     * Execute RAG state
     */
    protected function executeRAGState(array $stateDefinition, LLMAgentWorkflow $workflow): array
    {
        $query = $this->interpolateVariables($stateDefinition['query_from'] ?? $stateDefinition['query']);

        $ragService = app(LLMRAGService::class);

        return $ragService->search($query, $workflow->extension_slug);
    }

    /**
     * Execute condition state
     */
    protected function executeConditionState(array $stateDefinition): bool
    {
        $condition = $stateDefinition['condition'];

        // Simple condition evaluation
        return $this->evaluateCondition($condition);
    }

    /**
     * Execute transform state
     */
    protected function executeTransformState(array $stateDefinition): mixed
    {
        $input = $this->state[$stateDefinition['input']] ?? null;
        $transform = $stateDefinition['transform'];

        // Apply transformation
        return match ($transform) {
            'uppercase' => strtoupper($input),
            'lowercase' => strtolower($input),
            'json_decode' => json_decode($input, true),
            'json_encode' => json_encode($input),
            default => $input,
        };
    }

    /**
     * Find next state based on transitions
     */
    protected function findNextState(string $currentState, LLMAgentWorkflow $workflow, mixed $result): string
    {
        $transitions = $workflow->transitions;

        foreach ($transitions as $transition) {
            if ($transition['from'] === $currentState) {
                $condition = $transition['condition'] ?? 'always';

                if ($condition === 'always' || $this->evaluateCondition($condition)) {
                    return $transition['to'];
                }
            }
        }

        // Default next state from state definition
        $stateDefinition = $workflow->states[$currentState];

        return $stateDefinition['next'] ?? 'end';
    }

    /**
     * Interpolate variables in strings
     */
    protected function interpolateVariables(string $text): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) {
            $path = $matches[1];
            return $this->getNestedValue($this->state, $path) ?? $matches[0];
        }, $text);
    }

    /**
     * Get nested value from array
     */
    protected function getNestedValue(array $array, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Evaluate condition
     */
    protected function evaluateCondition(string $condition): bool
    {
        // Simple condition evaluation (can be extended)
        if ($condition === 'always') {
            return true;
        }

        // Example: "category != null"
        if (preg_match('/(\w+(?:\.\w+)*)\s*(==|!=|>|<|>=|<=)\s*(.+)/', $condition, $matches)) {
            $left = $this->getNestedValue($this->state, $matches[1]);
            $operator = $matches[2];
            $right = $matches[3] === 'null' ? null : trim($matches[3], '"\'');

            return match ($operator) {
                '==' => $left == $right,
                '!=' => $left != $right,
                '>' => $left > $right,
                '<' => $left < $right,
                '>=' => $left >= $right,
                '<=' => $left <= $right,
                default => false,
            };
        }

        return false;
    }

    /**
     * Get execution state
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * Get execution history
     */
    public function getHistory(): array
    {
        return $this->executionHistory;
    }
}
