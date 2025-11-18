<?php

namespace Bithoven\LLMManager\Services\Tools;

use Bithoven\LLMManager\Services\LLMManager;

class LLMFunctionCallingAdapter
{
    public function __construct(
        protected LLMManager $llmManager,
        protected LLMToolService $toolService,
        protected LLMToolExecutor $toolExecutor
    ) {
    }

    /**
     * Generate with function calling support
     */
    public function generate(
        string $prompt,
        ?string $extensionSlug = null,
        ?array $tools = null
    ): array {
        // Get available tools
        $tools = $tools ?? $this->toolService->getFormatted($extensionSlug);

        if (empty($tools)) {
            // No tools available, regular generation
            return $this->llmManager->generate($prompt);
        }

        // First request with tools
        $response = $this->llmManager->parameters([
            'tools' => $tools,
            'tool_choice' => 'auto',
        ])->generate($prompt);

        // Check if LLM wants to call tools
        $toolCalls = $this->extractToolCalls($response);

        if (empty($toolCalls)) {
            // No tool calls, return response
            return $response;
        }

        // Execute tool calls
        $toolResults = [];
        foreach ($toolCalls as $toolCall) {
            $result = $this->toolExecutor->execute(
                $toolCall['name'],
                $toolCall['arguments']
            );

            $toolResults[] = [
                'tool_call_id' => $toolCall['id'] ?? uniqid(),
                'role' => 'tool',
                'name' => $toolCall['name'],
                'content' => json_encode($result),
            ];
        }

        // Second request with tool results
        $finalResponse = $this->llmManager->parameters([
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
                ['role' => 'assistant', 'content' => $response['response'], 'tool_calls' => $toolCalls],
                ...$toolResults,
            ],
        ])->generate('Continue with the tool results');

        return [
            'response' => $finalResponse['response'],
            'tool_calls' => $toolCalls,
            'tool_results' => $toolResults,
            'usage' => array_merge_recursive($response['usage'] ?? [], $finalResponse['usage'] ?? []),
        ];
    }

    /**
     * Extract tool calls from response
     */
    protected function extractToolCalls(array $response): array
    {
        $toolCalls = [];

        // OpenAI format
        if (isset($response['tool_calls'])) {
            foreach ($response['tool_calls'] as $call) {
                $toolCalls[] = [
                    'id' => $call['id'] ?? uniqid(),
                    'name' => $call['function']['name'],
                    'arguments' => json_decode($call['function']['arguments'], true),
                ];
            }
        }

        // Check response text for function call patterns
        if (isset($response['response']) && str_contains($response['response'], 'function_call')) {
            // Parse JSON function calls from text
            if (preg_match('/\{[^}]*"function_call"[^}]*\}/', $response['response'], $matches)) {
                $data = json_decode($matches[0], true);
                if ($data && isset($data['function_call'])) {
                    $toolCalls[] = [
                        'id' => uniqid(),
                        'name' => $data['function_call']['name'],
                        'arguments' => json_decode($data['function_call']['arguments'], true),
                    ];
                }
            }
        }

        return $toolCalls;
    }

    /**
     * Chat with function calling
     */
    public function chat(
        string $sessionId,
        string $message,
        ?string $extensionSlug = null,
        ?array $tools = null
    ): array {
        // Get available tools
        $tools = $tools ?? $this->toolService->getFormatted($extensionSlug);

        // Use conversation manager with tools
        $conversationManager = app(\Bithoven\LLMManager\Services\Conversations\LLMConversationManager::class);

        // Send message (simplified - would need to add tools to conversation)
        $response = $conversationManager->sendMessage($sessionId, $message);

        // Check for tool calls
        $toolCalls = $this->extractToolCalls($response);

        if (!empty($toolCalls)) {
            // Execute tools and continue conversation
            foreach ($toolCalls as $toolCall) {
                $result = $this->toolExecutor->execute(
                    $toolCall['name'],
                    $toolCall['arguments']
                );

                // Add tool result to conversation
                $toolMessage = "Tool '{$toolCall['name']}' result: " . json_encode($result);
                $response = $conversationManager->sendMessage($sessionId, $toolMessage);
            }
        }

        return $response;
    }
}
