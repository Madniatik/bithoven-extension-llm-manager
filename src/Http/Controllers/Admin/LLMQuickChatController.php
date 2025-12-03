<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\LLMStreamLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LLMQuickChatController extends Controller
{
    public function __construct(
        private readonly LLMManager $llmManager,
        private readonly LLMStreamLogger $streamLogger
    ) {}
    
    /**
     * Display the Quick Chat interface.
     * 
     * Quick Chat uses temporary in-memory sessions by default (not saved to DB).
     * User can optionally save the chat to persist it as a Conversation.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Load active LLM configurations
        $configurations = LLMConfiguration::active()->get();
        
        // Get default configuration (first active or first available)
        $defaultConfig = $configurations->first();
        
        // Create temporary session object (NOT saved to DB)
        $session = (object) [
            'id' => null, // NULL = temporary session
            'session_id' => 'temp_' . uniqid(),
            'title' => 'Quick Chat - ' . now()->format('Y-m-d H:i'),
            'configuration_id' => $defaultConfig?->id,
            'configuration' => $defaultConfig,
            'user_id' => auth()->id(),
            'messages' => collect([]), // Empty collection
            'total_tokens' => 0,
            'total_cost' => 0,
        ];
        
        return view('llm-manager::admin.quick-chat.index', compact('configurations', 'session'));
    }
    
    /**
     * Save temporary Quick Chat session to database as a Conversation.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'configuration_id' => 'required|exists:llm_manager_configurations,id',
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|in:user,assistant,system',
            'messages.*.content' => 'required|string',
            'messages.*.tokens' => 'nullable|integer',
        ]);
        
        $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);
        
        // Create persistent conversation session
        $session = LLMConversationSession::create([
            'session_id' => 'quick_chat_' . uniqid(),
            'title' => $validated['title'] ?? 'Quick Chat - ' . now()->format('Y-m-d H:i'),
            'user_id' => auth()->id(),
            'configuration_id' => $configuration->id,
            'extension_slug' => null, // Quick chats don't belong to an extension
            'total_tokens' => 0,
            'total_cost' => 0,
        ]);
        
        // Save all messages
        $totalTokens = 0;
        foreach ($validated['messages'] as $messageData) {
            LLMConversationMessage::create([
                'session_id' => $session->id,
                'role' => $messageData['role'],
                'content' => $messageData['content'],
                'token_count' => $messageData['tokens'] ?? 0,
            ]);
            
            $totalTokens += $messageData['tokens'] ?? 0;
        }
        
        // Update session totals
        $session->update(['total_tokens' => $totalTokens]);
        
        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'redirect_url' => route('admin.llm.conversations.show', $session->id),
        ]);
    }
    
    /**
     * Start a new Quick Chat (reload page).
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newChat()
    {
        return redirect()->route('admin.llm.quick-chat');
    }
    
    /**
     * Stream LLM response for Quick Chat (no DB persistence by default).
     * 
     * This endpoint handles Server-Sent Events (SSE) streaming for Quick Chat.
     * Messages are NOT automatically saved to database (temporary chat).
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'configuration_id' => 'required|integer|exists:llm_manager_configurations,id',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4000',
            'context' => 'nullable|array', // Optional: previous messages for context
            'context.*.role' => 'required_with:context|in:user,assistant,system',
            'context.*.content' => 'required_with:context|string',
        ]);

        $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);

        // Increase PHP execution time for streaming
        set_time_limit(300); // 5 minutes

        return Response::stream(function () use ($validated, $configuration) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            try {
                // Prepare parameters
                $params = [
                    'temperature' => (float) ($validated['temperature'] ?? $configuration->temperature),
                    'max_tokens' => (int) ($validated['max_tokens'] ?? $configuration->max_tokens),
                ];

                // Start logging session
                $session = $this->streamLogger->startSession(
                    $configuration,
                    $validated['prompt'],
                    $params
                );

                // Get provider instance
                $provider = $this->llmManager
                    ->config($configuration->id)
                    ->getProvider();

                // Build context from request (if provided)
                $context = $validated['context'] ?? [];

                // Stream response chunks
                $fullResponse = '';
                $tokenCount = 0;

                $metrics = $provider->stream(
                    $validated['prompt'],
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

                // Save usage log to database (for metrics/analytics)
                $usageLog = $this->streamLogger->endSession($session, $fullResponse, $metrics);

                // Send completion event with real metrics
                echo "data: " . json_encode([
                    'type' => 'done',
                    'full_response' => $fullResponse, // Include full response for client-side storage
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
                if (isset($session)) {
                    $this->streamLogger->logError($session, $e->getMessage());
                }

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
     * Get raw message data as JSON.
     *
     * @param int $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRawMessage($messageId)
    {
        $message = \Bithoven\LLMManager\Models\LLMConversationMessage::with(['session.configuration', 'session.user'])
            ->findOrFail($messageId);
        
        return response()->json([
            'id' => $message->id,
            'session_id' => $message->session_id,
            'role' => $message->role,
            'content' => $message->content,
            'metadata' => $message->metadata,
            'tokens' => $message->tokens,
            'sent_at' => $message->sent_at,
            'started_at' => $message->started_at,
            'completed_at' => $message->completed_at,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'session' => [
                'id' => $message->session->id,
                'session_id' => $message->session->session_id,
                'title' => $message->session->title,
                'user' => $message->session->user ? [
                    'id' => $message->session->user->id,
                    'name' => $message->session->user->name,
                    'email' => $message->session->user->email,
                ] : null,
                'configuration' => $message->session->configuration ? [
                    'id' => $message->session->configuration->id,
                    'name' => $message->session->configuration->name,
                    'provider' => $message->session->configuration->provider,
                    'model' => $message->session->configuration->model,
                    'temperature' => $message->session->configuration->temperature,
                    'max_tokens' => $message->session->configuration->max_tokens,
                ] : null,
            ],
            'computed' => [
                'response_time' => $message->response_time,
                'total_tokens' => $message->total_tokens,
                'provider' => $message->provider,
                'model' => $message->model,
            ]
        ]);
    }
}
