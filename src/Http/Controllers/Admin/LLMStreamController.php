<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\LLMStreamLogger;

class LLMStreamController extends Controller
{
    public function __construct(
        private readonly LLMManager $llmManager,
        private readonly LLMStreamLogger $streamLogger
    ) {}

    /**
     * Show streaming test page
     */
    public function test()
    {
        // Get all active configurations
        $configurations = LLMConfiguration::active()->get();

        return view('llm-manager::admin.stream.test', compact('configurations'));
    }

    /**
     * Stream LLM response using Server-Sent Events (SSE)
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'configuration_id' => 'required|integer|exists:llm_manager_configurations,id',
            'temperature' => 'nullable|string',
            'max_tokens' => 'nullable|string',
        ]);
        
        // Convert string values to appropriate types
        $validated['temperature'] = isset($validated['temperature']) ? (float) $validated['temperature'] : null;
        $validated['max_tokens'] = isset($validated['max_tokens']) ? (int) $validated['max_tokens'] : null;

        $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);

        // Increase PHP execution time for streaming (can take a while)
        set_time_limit(300); // 5 minutes
        
        // Set up SSE headers
        return Response::stream(function () use ($validated, $configuration) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Start logging session
            $params = [
                'temperature' => $validated['temperature'] ?? $configuration->temperature,
                'max_tokens' => $validated['max_tokens'] ?? $configuration->max_tokens,
            ];
            
            $session = $this->streamLogger->startSession(
                $configuration,
                $validated['prompt'],
                $params
            );

            // Get provider instance
            $provider = $this->llmManager
                ->config($configuration->id)
                ->getProvider();

            try {
                // Stream chunks to client
                $fullResponse = '';
                $tokenCount = 0;

                $metrics = $provider->stream(
                    $validated['prompt'],
                    [],
                    $params,
                    function ($chunk) use (&$fullResponse, &$tokenCount) {
                        $fullResponse .= $chunk;
                        $tokenCount++;

                        // Send chunk to client
                        echo "data: " . json_encode([
                            'type' => 'chunk',
                            'content' => $chunk,
                            'tokens' => $tokenCount,
                        ]) . "\n\n";

                        // Flush output buffer
                        if (ob_get_level()) {
                            ob_flush();
                        }
                        flush();
                    }
                );

                // Save usage log to database
                $usageLog = $this->streamLogger->endSession($session, $fullResponse, $metrics);

                // Send completion event with real metrics
                echo "data: " . json_encode([
                    'type' => 'done',
                    'usage' => $metrics['usage'],
                    'cost' => $usageLog->cost_usd,
                    'execution_time_ms' => $usageLog->execution_time_ms,
                    'log_id' => $usageLog->id,
                    'model' => $metrics['model'],
                    'finish_reason' => $metrics['finish_reason'],
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

            } catch (\Exception $e) {
                // Log error to database
                $this->streamLogger->logError($session, $e->getMessage());

                // Send error event
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Stream for conversation (with context)
     */
    public function conversationStream(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:llm_conversation_sessions,id',
            'message' => 'required|string|max:5000',
        ]);

        $session = \Bithoven\LLMManager\Models\LLMConversationSession::findOrFail($validated['session_id']);
        $configuration = $session->configuration;

        // Increase PHP execution time for streaming
        set_time_limit(300); // 5 minutes
        
        // Verify provider supports streaming
        if (!in_array($configuration->provider, ['ollama', 'openai'])) {
            return response()->json([
                'error' => 'Provider does not support streaming'
            ], 400);
        }

        // Get conversation context
        $context = $session->messages()
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        return Response::stream(function () use ($validated, $configuration, $context, $session) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Start logging session
            $params = [];
            $logSession = $this->streamLogger->startSession(
                $configuration,
                $validated['message'],
                $params,
                $session->id, // DB session_id
                null // No message_id yet (message created after stream completes)
            );

            $provider = $this->llmManager
                ->config($configuration->id)
                ->getProvider();

            try {
                $fullResponse = '';
                $tokenCount = 0;

                $metrics = $provider->stream(
                    $validated['message'],
                    $context,
                    [],
                    function ($chunk) use (&$fullResponse, &$tokenCount) {
                        $fullResponse .= $chunk;
                        $tokenCount++;

                        echo "data: " . json_encode([
                            'type' => 'chunk',
                            'content' => $chunk,
                        ]) . "\n\n";

                        if (ob_get_level()) {
                            ob_flush();
                        }
                        flush();
                    }
                );

                // Save usage log
                $usageLog = $this->streamLogger->endSession($logSession, $fullResponse, $metrics);

                // Save messages after streaming completes
                $session->messages()->create([
                    'role' => 'user',
                    'content' => $validated['message'],
                ]);

                $session->messages()->create([
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens_used' => $metrics['usage']['completion_tokens'] ?? 0,
                ]);

                echo "data: " . json_encode([
                    'type' => 'done',
                    'usage' => $metrics['usage'],
                    'cost' => $usageLog->cost_usd,
                    'execution_time_ms' => $usageLog->execution_time_ms,
                    'log_id' => $usageLog->id,
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

            } catch (\Exception $e) {
                // Log error
                $this->streamLogger->logError($logSession, $e->getMessage());

                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}
