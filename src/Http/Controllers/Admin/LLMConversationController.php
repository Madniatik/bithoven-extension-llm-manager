<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Services\Conversations\LLMConversationManager;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\LLMStreamLogger;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Illuminate\Support\Facades\Response;

class LLMConversationController extends Controller
{
    public function __construct(
        private readonly LLMManager $llmManager,
        private readonly LLMStreamLogger $streamLogger,
        private readonly LLMConversationManager $conversationManager,
        private readonly LLMConfigurationService $configService
    ) {}

    public function index()
    {
        $sessions = LLMConversationSession::with(['user', 'configuration'])
            ->latest()
            ->paginate(20);

        return view('llm-manager::admin.conversations.index', compact('sessions'));
    }

    public function create()
    {
        $configurations = $this->configService->getActive();

        return view('llm-manager::admin.conversations.create', compact('configurations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'configuration_id' => 'required|exists:llm_manager_configurations,id',
            'title' => 'nullable|string|max:255',
        ]);

        $configuration = $this->configService->findOrFail($validated['configuration_id']);
        
        $conversation = $this->conversationManager->createSession(
            $configuration,
            null, // extension_slug
            auth()->id(),
            $validated['title'] ?? 'New Conversation'
        );

        return redirect()
            ->route('admin.llm.conversations.show', $conversation->id)
            ->with('success', 'Conversation created successfully');
    }

    public function show(int $id)
    {
        // Eager load all necessary relationships to avoid N+1 queries
        $conversation = LLMConversationSession::with([
            'user',
            'configuration',
            'messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'logs'
        ])->findOrFail($id);
        
        // Get all active configurations for model selector
        $configurations = $this->configService->getActive();

        return view('llm-manager::admin.conversations.show', compact('conversation', 'configurations'));
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
        \Log::info('streamReply validation attempt', [
            'conversation_id' => $id,
            'params' => $request->all(),
        ]);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4000',
            'configuration_id' => 'nullable|integer|exists:llm_manager_configurations,id',
            'context_limit' => 'nullable|integer|min:0|max:100',
        ]);

        \Log::info('streamReply validation passed', ['validated' => $validated]);

        $conversation = LLMConversationSession::with(['configuration', 'messages'])->findOrFail($id);

        // Use provided configuration or fall back to conversation's configuration
        $configurationId = $validated['configuration_id'] ?? $conversation->configuration?->id;
        
        if (!$configurationId) {
            return response()->json([
                'error' => 'No LLM configuration available. Please select a configuration.',
            ], 422);
        }
        
        $configuration = $this->configService->findOrFail($configurationId);

        // If conversation has ended or expired, don't allow streaming
        if ($conversation->ended_at || ($conversation->expires_at && $conversation->expires_at->isPast())) {
            return response()->json([
                'error' => 'Conversation has ended or expired',
            ], 422);
        }

        // Increase PHP execution time for streaming
        set_time_limit(300); // 5 minutes

        return Response::stream(function () use ($validated, $conversation, $configuration) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            try {
                // 1. Save user message to database
                $userMessage = LLMConversationMessage::create([
                    'session_id' => $conversation->id,
                    'llm_configuration_id' => $configuration->id,
                    'role' => 'user',
                    'content' => $validated['message'],
                    'tokens' => str_word_count($validated['message']), // Rough estimate
                ]);

                // 2. Build context from conversation history (use context_limit from request)
                $contextLimit = (int) ($validated['context_limit'] ?? 10);
                $context = [];
                
                if ($contextLimit > 0) {
                    $recentMessages = $conversation->messages()
                        ->orderBy('created_at', 'desc')
                        ->take($contextLimit)
                        ->get()
                        ->reverse();
                    
                    foreach ($recentMessages as $msg) {
                        // Skip empty messages
                        if (empty(trim($msg->content))) {
                            continue;
                        }
                        
                        $context[] = [
                            'role' => $msg->role,
                            'content' => $msg->content,
                        ];
                    }
                } else {
                    // context_limit = 0 means all messages
                    $allMessages = $conversation->messages()
                        ->orderBy('created_at', 'asc')
                        ->get();
                    
                    foreach ($allMessages as $msg) {
                        // Skip empty messages
                        if (empty(trim($msg->content))) {
                            continue;
                        }
                        
                        $context[] = [
                            'role' => $msg->role,
                            'content' => $msg->content,
                        ];
                    }
                }
                
                // Add current user message
                $context[] = [
                    'role' => 'user',
                    'content' => $validated['message'],
                ];

                // 3. Start logging session
                $params = [
                    'temperature' => (float) ($validated['temperature'] ?? $configuration->temperature),
                    'max_tokens' => (int) ($validated['max_tokens'] ?? $configuration->max_tokens),
                ];

                $session = $this->streamLogger->startSession(
                    $configuration,
                    $validated['message'],
                    $params
                );

                // 4. Get provider instance
                $provider = $this->llmManager
                    ->config($configuration->id)
                    ->getProvider();

                // Emit request_data event for Request Inspector tab
                $requestData = [
                    'metadata' => [
                        'provider' => $configuration->provider,
                        'model' => $configuration->model,
                        'endpoint' => $configuration->api_endpoint ?? 'N/A',
                        'timestamp' => now()->toIso8601String(),
                        'conversation_id' => $conversation->id,
                        'message_id' => $userMessage->id,
                    ],
                    'parameters' => [
                        'temperature' => $params['temperature'],
                        'max_tokens' => $params['max_tokens'],
                        'top_p' => $params['top_p'] ?? 1.0,
                        'context_limit' => $contextLimit,
                        'actual_context_size' => count($context),
                    ],
                    'system_instructions' => $configuration->system_instructions ?? null,
                    'context_messages' => collect($context)->map(function($m, $idx) use ($conversation) {
                        // Get original message from DB to get metadata
                        $originalMsg = $conversation->messages()->skip($idx)->first();
                        return [
                            'id' => $originalMsg->id ?? $idx,
                            'role' => $m['role'],
                            'content' => \Illuminate\Support\Str::limit($m['content'], 200),
                            'tokens' => $originalMsg->tokens ?? 0,
                            'created_at' => $originalMsg->created_at?->toIso8601String() ?? now()->toIso8601String(),
                        ];
                    })->toArray(),
                    'current_prompt' => $validated['message'],
                    'full_request_body' => [
                        'model' => $configuration->model,
                        'messages' => array_merge(
                            $configuration->system_instructions ? [['role' => 'system', 'content' => $configuration->system_instructions]] : [],
                            $context
                        ),
                        'temperature' => $params['temperature'],
                        'max_tokens' => $params['max_tokens'],
                        'stream' => true,
                    ],
                ];

                echo "event: request_data\n";
                echo "data: " . json_encode($requestData) . "\n\n";
                
                if (ob_get_level()) ob_flush();
                flush();

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

                \Log::info('Stream completed', [
                    'fullResponse_length' => strlen($fullResponse),
                    'fullResponse_preview' => substr($fullResponse, 0, 100),
                    'metrics' => $metrics,
                ]);

                // 6. Save assistant message to database FIRST
                $assistantMessage = LLMConversationMessage::create([
                    'session_id' => $conversation->id,
                    'llm_configuration_id' => $configuration->id,
                    'model' => $metrics['model'] ?? $configuration->model, // Snapshot (prefer from provider response)
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens' => $metrics['usage']['completion_tokens'] ?? $tokenCount,
                ]);

                // 7. Save usage log with response_message_id
                $usageLog = $this->streamLogger->endSession($session, $fullResponse, $metrics, $assistantMessage->id);

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
                \Log::error('streamReply exception', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

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
