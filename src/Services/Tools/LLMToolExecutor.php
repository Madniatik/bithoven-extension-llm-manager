<?php

namespace Bithoven\LLMManager\Services\Tools;

use Bithoven\LLMManager\Models\LLMToolDefinition;
use Bithoven\LLMManager\Services\MCP\LLMMCPConnectorManager;
use Illuminate\Support\Facades\Process;

class LLMToolExecutor
{
    public function __construct(
        protected LLMToolService $toolService,
        protected LLMMCPConnectorManager $mcpManager
    ) {
    }

    /**
     * Execute a tool
     */
    public function execute(string $slug, array $parameters = []): array
    {
        $tool = $this->toolService->get($slug);

        if (!$tool) {
            throw new \Exception("Tool '{$slug}' not found");
        }

        // Validate parameters
        $this->toolService->validateParameters($slug, $parameters);

        $startTime = microtime(true);

        try {
            $result = $this->executeTool($tool, $parameters);

            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            return [
                'success' => true,
                'result' => $result,
                'execution_time_ms' => $executionTime,
            ];

        } catch (\Exception $e) {
            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            \Log::error("Tool execution failed: {$slug}", [
                'error' => $e->getMessage(),
                'parameters' => $parameters,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime,
            ];
        }
    }

    /**
     * Execute tool based on type
     */
    protected function executeTool(LLMToolDefinition $tool, array $parameters): mixed
    {
        return match ($tool->tool_type) {
            'native' => $this->executeNativeTool($tool, $parameters),
            'mcp' => $this->executeMCPTool($tool, $parameters),
            'custom' => $this->executeCustomTool($tool, $parameters),
            default => throw new \Exception("Unknown tool type: {$tool->tool_type}"),
        };
    }

    /**
     * Execute native PHP function/method
     */
    protected function executeNativeTool(LLMToolDefinition $tool, array $parameters): mixed
    {
        $implementation = $tool->implementation;

        // Format: "ClassName@method" or "function_name"
        if (str_contains($implementation, '@')) {
            [$class, $method] = explode('@', $implementation);

            if (!class_exists($class)) {
                throw new \Exception("Class '{$class}' not found");
            }

            $instance = app($class);

            if (!method_exists($instance, $method)) {
                throw new \Exception("Method '{$method}' not found in class '{$class}'");
            }

            return $instance->$method($parameters);
        }

        // Function call
        if (function_exists($implementation)) {
            return $implementation($parameters);
        }

        throw new \Exception("Implementation '{$implementation}' not found");
    }

    /**
     * Execute MCP (Model Context Protocol) tool
     */
    protected function executeMCPTool(LLMToolDefinition $tool, array $parameters): mixed
    {
        // Implementation format: "server_name:tool_name"
        [$serverName, $toolName] = explode(':', $tool->implementation);

        return $this->mcpManager->executeTool($serverName, $toolName, $parameters);
    }

    /**
     * Execute custom script/command
     */
    protected function executeCustomTool(LLMToolDefinition $tool, array $parameters): mixed
    {
        $implementation = $tool->implementation;

        // Security: Only allow whitelisted scripts
        $allowedPaths = config('llm-manager.tools.allowed_script_paths', []);
        $isAllowed = false;

        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($implementation, $allowedPath)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            throw new \Exception("Script '{$implementation}' is not in allowed paths");
        }

        if (!file_exists($implementation)) {
            throw new \Exception("Script '{$implementation}' not found");
        }

        // Build command with parameters
        $command = $implementation;
        foreach ($parameters as $key => $value) {
            $command .= " --{$key}=" . escapeshellarg($value);
        }

        // Execute with timeout
        $timeout = config('llm-manager.tools.execution_timeout', 30);

        $result = Process::timeout($timeout)->run($command);

        if ($result->failed()) {
            throw new \Exception("Script execution failed: {$result->errorOutput()}");
        }

        return $result->output();
    }

    /**
     * Execute multiple tools in sequence
     */
    public function executeMultiple(array $toolCalls): array
    {
        $results = [];

        foreach ($toolCalls as $toolCall) {
            $slug = $toolCall['slug'] ?? $toolCall['name'];
            $parameters = $toolCall['parameters'] ?? [];

            $results[] = $this->execute($slug, $parameters);
        }

        return $results;
    }

    /**
     * Execute tools in parallel (async)
     */
    public function executeParallel(array $toolCalls): array
    {
        // For now, execute sequentially
        // Can be enhanced with async/promises
        return $this->executeMultiple($toolCalls);
    }
}
