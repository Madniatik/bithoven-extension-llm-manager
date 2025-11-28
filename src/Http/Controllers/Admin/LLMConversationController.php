<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Services\Conversations\LLMConversationManager;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\LLMStreamLogger;
use Illuminate\Support\Facades\Response;

class LLMConversationController extends Controller
{
    public function __construct(
        private readonly LLMManager $llmManager,
        private readonly LLMStreamLogger $streamLogger,
        private readonly LLMConversationManager $conversationManager
    ) {}

    public function index()
    {
        $sessions = LLMConversationSession::with(['user', 'configuration'])
            ->latest()
            ->paginate(20);

        return view('llm-manager::admin.conversations.index', compact('sessions'));
    }

    public function show(int $id)
    {
        $conversation = LLMConversationSession::with(['user', 'configuration', 'messages', 'logs'])
            ->findOrFail($id);

        return view('llm-manager::admin.conversations.show', compact('conversation'));
    }

    public function destroy(int $id)
    {
        $session = LLMConversationSession::findOrFail($id);
        $session->delete();

        return redirect()
            ->route('admin.llm.conversations.index')
            ->with('success', 'Conversation deleted successfully');
    }

    public function export(int $id, LLMConversationManager $manager)
    {
        $session = LLMConversationSession::findOrFail($id);
        $export = $manager->export($session->session_id);

        return response()->json($export);
    }

    /**
     * Stream a reply to a conversation using Server-Sent Events
     */
    public function streamReply(int $id, Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4000',
        ]);

        $conversation = LLMConversationSession::with(['configuration', 'messages'])->findOrFail($id);

        // If conversation has ended or expired, don't allow streaming
        if ($conversation->ended_at || ($conversation->expires_at && $conversation->expires_at->isPast())) {
            return response()->json([
                'error' => 'Conversation has ended or expired',
            ], 422);
        }

        // Increase PHP execution time for streaming
        set_time_limit(300); // 5 minutes

        return Response::stream(function () use ($validated, $conversation) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            try {
                // 1. Save user message to database
                $userMessage = LLMConversationMessage::create([
                    'session_id' => $conversation->id,
                    'role' => 'user',
                    'content' => $validated['message'],
                    'token_count' => str_word_count($validated['message']), // Rough estimate
                ]);

                // 2. Build context from conversation history
                $context = [];
                foreach ($conversation->messages as $msg) {
                    $context[] = [
                        'role' => $msg->role,
                        'content' => $msg->content,
                    ];
                }
                // Add current user message
                $context[] = [
                    'role' => 'user',
                    'content' => $validated['message'],
                ];

                // 3. Start logging session
                $params = [
                    'temperature' => $validated['temperature'] ?? $conversation->configuration->temperature,
                    'max_tokens' => $validated['max_tokens'] ?? $conversation->configuration->max_tokens,
                ];

                $session = $this->streamLogger->startSession(
                    $conversation->configuration,
                    $validated['message'],
                    $params
                );

                // 4. Get provider instance
                $provider = $this->llmManager
                    ->config($conversation->configuration->id)
                    ->getProvider();

                // 5. Stream response chunks
                $fullResponse = '';
                $tokenCount = 0;

                $metrics = $provider->stream(
                    $validated['message'],
                    $context,
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

                // 6. Save usage log
                $usageLog = $this->streamLogger->endSession($session, $fullResponse, $metrics);

                // 7. Save assistant message to database
                $assistantMessage = LLMConversationMessage::create([
                    'session_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'token_count' => $metrics['usage']['completion_tokens'] ?? $tokenCount,
                ]);

                // 8. Update conversation totals
                $conversation->update([
                    'total_tokens' => $conversation->total_tokens + ($metrics['usage']['prompt_tokens'] ?? 0) + ($metrics['usage']['completion_tokens'] ?? 0),
                    'total_cost' => $conversation->total_cost + $usageLog->cost_usd,
                ]);

                // 9. Send completion event
                echo "data: " . json_encode([
                    'type' => 'done',
                    'message_id' => $assistantMessage->id,
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
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
