<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Bithoven\LLMManager\Services\LLMManager;

class LLMStreamController extends Controller
{
    public function __construct(
        private readonly LLMManager $llmManager
    ) {}

    /**
     * Show streaming test page
     */
    public function test()
    {
        $configurations = LLMConfiguration::active()
            ->whereIn('provider', ['ollama', 'openai'])
            ->get();

        return view('llm-manager::admin.stream.test', compact('configurations'));
    }

    /**
     * Stream LLM response using Server-Sent Events (SSE)
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'configuration_id' => 'required|exists:llm_manager_configurations,id',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4000',
        ]);

        $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);

        // Verify provider supports streaming
        if (!in_array($configuration->provider, ['ollama', 'openai'])) {
            return response()->json([
                'error' => 'Provider does not support streaming'
            ], 400);
        }

        // Set up SSE headers
        return Response::stream(function () use ($validated, $configuration) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Get provider instance
            $provider = $this->llmManager
                ->config($configuration->id)
                ->getProvider();

            try {
                // Parameters for streaming request
                $params = [
                    'temperature' => $validated['temperature'] ?? $configuration->temperature,
                    'max_tokens' => $validated['max_tokens'] ?? $configuration->max_tokens,
                ];

                // Stream chunks to client
                $fullResponse = '';
                $tokenCount = 0;

                $provider->stream(
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

                // Send completion event
                echo "data: " . json_encode([
                    'type' => 'done',
                    'total_tokens' => $tokenCount,
                    'full_response' => $fullResponse,
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

            } catch (\Exception $e) {
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

            $provider = $this->llmManager
                ->config($configuration->id)
                ->getProvider();

            try {
                $fullResponse = '';
                $tokenCount = 0;

                $provider->stream(
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

                // Save messages after streaming completes
                $session->messages()->create([
                    'role' => 'user',
                    'content' => $validated['message'],
                ]);

                $session->messages()->create([
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens_used' => $tokenCount,
                ]);

                echo "data: " . json_encode([
                    'type' => 'done',
                    'total_tokens' => $tokenCount,
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

            } catch (\Exception $e) {
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
