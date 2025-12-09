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
     * Display the Quick Chat interface with ChatWorkspace component.
     * 
     * @param int|null $sessionId Optional session ID to load specific session
     * 
     * Behavior:
     * - If $sessionId provided: Load that specific session (if user owns it)
     * - If no $sessionId: Reuse last active session or create new one
     */
    public function index($sessionId = null)
    {
        $configurations = LLMConfiguration::active()->get();
        $defaultConfig = $configurations->first();
        
        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }
        
        // If session ID provided, load that specific session
        if ($sessionId) {
            $session = LLMConversationSession::where('id', $sessionId)
                ->where('user_id', auth()->id()) // Security: only user's own sessions
                ->where('extension_slug', null) // Only Quick Chat sessions
                ->first();
            
            if (!$session) {
                return redirect()->route('admin.llm.quick-chat')
                    ->with('error', 'Session not found or access denied.');
            }
        } else {
            // Reutilizar última sesión activa del usuario (Quick Chat behavior)
            $session = LLMConversationSession::where('user_id', auth()->id())
                ->where('extension_slug', null) // Quick Chat sessions
                ->where('is_active', true)
                ->latest('last_activity_at')
                ->first();
            
            // Si no hay sesión activa, crear una nueva
            if (!$session) {
                $session = LLMConversationSession::create([
                    'session_id' => 'quick_chat_' . uniqid(),
                    'title' => 'Quick Chat - ' . now()->format('Y-m-d H:i'),
                    'user_id' => auth()->id(),
                    'llm_configuration_id' => $defaultConfig->id,
                    'extension_slug' => null,
                ]);
            }
        }
        
        // Actualizar last_activity_at
        $session->touch('last_activity_at');
        
        return view('llm-manager::admin.quick-chat.index', compact('configurations', 'session'));
    }
    
    /**
     * Stream LLM response and auto-save to DB.
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:llm_manager_conversation_sessions,id',
            'prompt' => 'required|string|max:5000',
            'configuration_id' => 'required|exists:llm_manager_configurations,id',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:128000',
            'context_limit' => 'nullable|integer|min:0|max:100',
        ]);

        $session = LLMConversationSession::findOrFail($validated['session_id']);
        $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);
        
        // Force fresh load to get latest parameters
        $configuration->refresh();

        set_time_limit(300);

        return Response::stream(function () use ($validated, $session, $configuration) {
            if (ob_get_level()) ob_end_clean();

            try {
                // Estimate prompt tokens (before sending to provider)
                // Rule of thumb: ~1 token per 4 characters for English text
                $promptTokensEstimate = (int) ceil(mb_strlen($validated['prompt']) / 4);
                
                // Save user message to DB
                $userMessage = LLMConversationMessage::create([
                    'session_id' => $session->id,
                    'user_id' => auth()->id(),
                    'llm_configuration_id' => $configuration->id,
                    'role' => 'user',
                    'content' => $validated['prompt'],
                    'tokens' => $promptTokensEstimate, // Estimated tokens of prompt only
                    'metadata' => [
                        'input_tokens' => $promptTokensEstimate, // Prompt tokens only (estimated)
                        'context_messages_count' => $session->messages()->count(),
                    ],
                ]);

                $params = [
                    'temperature' => $validated['temperature'] ?? $configuration->temperature,
                    'max_tokens' => $validated['max_tokens'] ?? $configuration->default_parameters['max_tokens'] ?? 8000,
                ];

                // Pass real DB session_id and message_id for usage tracking
                $logSession = $this->streamLogger->startSession(
                    $configuration,
                    $validated['prompt'],
                    $params,
                    $session->id, // DB session_id
                    $userMessage->id // DB message_id (user message)
                );
                
                $provider = $this->llmManager->config($configuration->id)->getProvider();
                
                // Build context from previous messages (apply context_limit)
                // Skip error messages from context (is_error flag in metadata)
                $contextLimit = $validated['context_limit'] ?? 10;
                
                // Get all messages first (to support 'All messages' option)
                $allMessages = $session->messages()
                    ->orderBy('id')
                    ->get()
                    ->filter(function($m) {
                        // Skip messages marked as errors
                        return !($m->metadata['is_error'] ?? false);
                    });
                
                // If context_limit > 0, take LAST N messages (most recent)
                // If context_limit = 0, use ALL messages
                $contextMessages = $contextLimit > 0 
                    ? $allMessages->slice(-$contextLimit)->values() // Negative slice = take last N
                    : $allMessages->values();
                
                $context = $contextMessages
                    ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                    ->toArray();

                $fullResponse = '';
                $tokenCount = 0;
                $startTime = microtime(true);
                $firstChunkTime = null;

                // Send metadata event BEFORE streaming starts (with estimated input_tokens)
                // This allows UI to show token count during "Thinking..." phase
                $estimatedInputTokens = str_word_count($validated['prompt']) + 
                                       array_sum(array_map(fn($m) => str_word_count($m['content']), $context));
                
                echo "data: " . json_encode([
                    'type' => 'metadata',
                    'user_message_id' => $userMessage->id, // For deletion if user stops before first chunk
                    'user_prompt' => $validated['prompt'], // For restoration to input if stopped early
                    'input_tokens' => $estimatedInputTokens,
                    'context_size' => count($context),
                ]) . "\n\n";
                
                if (ob_get_level()) ob_flush();
                flush();

                // Emit request_data event for Request Inspector tab
                $requestData = [
                    'metadata' => [
                        'provider' => $configuration->provider,
                        'model' => $configuration->model,
                        'endpoint' => $configuration->api_endpoint ?? 'N/A',
                        'timestamp' => now()->toIso8601String(),
                        'session_id' => $session->id,
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
                    'context_messages' => $contextMessages->map(function($m) {
                        return [
                            'id' => $m->id,
                            'role' => $m->role,
                            'content' => \Illuminate\Support\Str::limit($m->content, 200),
                            'tokens' => $m->tokens ?? 0,
                            'created_at' => $m->created_at?->toIso8601String() ?? now()->toIso8601String(),
                        ];
                    })->toArray(),
                    'current_prompt' => $validated['prompt'],
                    'full_request_body' => [
                        'model' => $configuration->model,
                        'messages' => array_merge(
                            $configuration->system_instructions ? [['role' => 'system', 'content' => $configuration->system_instructions]] : [],
                            $context,
                            [['role' => 'user', 'content' => $validated['prompt']]]
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

                $metrics = $provider->stream(
                    $validated['prompt'],
                    $context,
                    $params,
                    function ($chunk) use (&$fullResponse, &$tokenCount, &$firstChunkTime, $startTime) {
                        $fullResponse .= $chunk;
                        $tokenCount++;
                        
                        // Capture time to first chunk
                        if ($tokenCount === 1) {
                            $firstChunkTime = microtime(true);
                        }

                        echo "data: " . json_encode([
                            'type' => 'chunk',
                            'content' => $chunk,
                            'tokens' => $tokenCount,
                        ]) . "\n\n";

                        if (ob_get_level()) ob_flush();
                        flush();
                    }
                );
                
                $endTime = microtime(true);
                $responseTime = round($endTime - $startTime, 3);
                $ttft = $firstChunkTime ? round($firstChunkTime - $startTime, 3) : null;

                // Determine error type and create appropriate message
                $finishReason = $metrics['finish_reason'] ?? 'unknown';
                $isEmptyResponse = empty($fullResponse) || trim($fullResponse) === '';
                $errorMessage = null;
                
                if ($isEmptyResponse) {
                    // Generate error explanation based on finish_reason
                    switch ($finishReason) {
                        case 'length':
                            $errorMessage = "⚠️ **Response Generation Failed**\n\n" .
                                           "The model could not generate a useful response within the token limit.\n\n" .
                                           "**Settings:**\n" .
                                           "- Max Tokens: {$params['max_tokens']}\n" .
                                           "- Model: {$configuration->model}\n\n" .
                                           "**Suggestion:** Increase `max_tokens` to allow longer responses (try 500-2000).";
                            break;
                        case 'stop':
                            $errorMessage = "⚠️ **Empty Response**\n\n" .
                                           "The model stopped without generating content.\n\n" .
                                           "This may indicate an issue with the prompt or model configuration.";
                            break;
                        default:
                            $errorMessage = "⚠️ **Streaming Error**\n\n" .
                                           "No content was received from the model.\n\n" .
                                           "**Details:**\n" .
                                           "- Finish Reason: {$finishReason}\n" .
                                           "- Max Tokens: {$params['max_tokens']}\n" .
                                           "- Response Time: {$responseTime}s";
                    }
                    
                    $fullResponse = $errorMessage;
                    
                    \Log::warning('LLMQuickChat: Empty response - saving error message', [
                        'session_id' => $session->id,
                        'max_tokens' => $params['max_tokens'],
                        'finish_reason' => $finishReason,
                        'token_count' => $tokenCount,
                    ]);
                }

                // Save assistant message to DB (always save, even errors)
                $assistantMessage = LLMConversationMessage::create([
                    'session_id' => $session->id,
                    'user_id' => auth()->id(),
                    'llm_configuration_id' => $configuration->id,
                    'model' => $metrics['model'] ?? $configuration->model, // Snapshot (prefer from provider response)
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens' => $metrics['usage']['total_tokens'] ?? $tokenCount,
                    'response_time' => $responseTime,
                    'cost_usd' => null, // Will be updated after usageLog creation
                    'raw_response' => $metrics['raw_response'] ?? null,
                    'metadata' => [
                        'model' => $configuration->model,
                        'provider' => $configuration->provider,
                        'max_tokens' => $params['max_tokens'],
                        'temperature' => $params['temperature'],
                        'chunks_count' => $tokenCount,
                        'is_error' => $isEmptyResponse, // Flag error messages
                        'finish_reason' => $metrics['finish_reason'] ?? 'unknown',
                        'output_tokens' => $metrics['usage']['completion_tokens'] ?? 0,
                        'input_tokens' => $metrics['usage']['prompt_tokens'] ?? 0,
                        'context_size' => count($context),
                        'is_streaming' => true,
                        'time_to_first_chunk' => $ttft,
                        'response_time' => $responseTime,
                        // OpenRouter-specific metadata
                        'generation_id' => $metrics['generation_id'] ?? null,
                        'native_tokens_prompt' => $metrics['usage']['native_tokens_prompt'] ?? null,
                        'native_tokens_completion' => $metrics['usage']['native_tokens_completion'] ?? null,
                        'system_fingerprint' => $metrics['system_fingerprint'] ?? null,
                        // Provider-calculated cost (e.g., OpenRouter)
                        'provider_cost' => $metrics['cost'] ?? null,
                    ],
                ]);

                $usageLog = $this->streamLogger->endSession($logSession, $fullResponse, $metrics);

                // Update message with cost from usage log
                $assistantMessage->update(['cost_usd' => $usageLog->cost_usd]);

                // Note: Session totals (tokens/cost) can be calculated from messages and usage_logs
                // No need to store redundant data in session table

                echo "data: " . json_encode([
                    'type' => 'done',
                    'usage' => $metrics['usage'],
                    'cost' => $usageLog->cost_usd,
                    'message_id' => $assistantMessage->id,
                    'response_time' => $responseTime,
                    'ttft' => $ttft,
                    'content' => $fullResponse, // Include content for error messages (when no chunks sent)
                    'metadata' => [
                        'provider' => $configuration->provider,
                        'model' => $configuration->model,
                        'is_error' => $isEmptyResponse, // Flag error messages
                    ],
                ]) . "\n\n";

                if (ob_get_level()) ob_flush();
                flush();

            } catch (\Exception $e) {
                if (isset($logSession)) {
                    $this->streamLogger->logError($logSession, $e->getMessage());
                }

                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]) . "\n\n";

                if (ob_get_level()) ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
    
    public function newChat(Request $request)
    {
        // Marcar sesión actual como inactiva
        LLMConversationSession::where('user_id', auth()->id())
            ->where('extension_slug', null)
            ->where('is_active', true)
            ->update(['is_active' => false]);
        
        // Get custom title from query param or use default
        $customTitle = $request->query('title');
        $title = $customTitle ?: ('Quick Chat - ' . now()->format('Y-m-d H:i'));
        
        // Create new session with custom title
        $defaultConfig = LLMConfiguration::active()->first();
        
        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }
        
        $newSession = LLMConversationSession::create([
            'session_id' => 'quick_chat_' . uniqid(),
            'title' => $title,
            'user_id' => auth()->id(),
            'llm_configuration_id' => $defaultConfig->id,
            'extension_slug' => null,
        ]);
        
        return redirect()->route('admin.llm.quick-chat.session', ['sessionId' => $newSession->id])
            ->with('success', 'New chat created: ' . $title);
    }
    
    /**
     * Delete a Quick Chat session and all its messages
     */
    public function deleteSession($sessionId)
    {
        $session = LLMConversationSession::where('id', $sessionId)
            ->where('user_id', auth()->id()) // Security: only user's own sessions
            ->where('extension_slug', null) // Only Quick Chat sessions
            ->first();
        
        if (!$session) {
            return redirect()->route('admin.llm.quick-chat')
                ->with('error', 'Session not found or access denied.');
        }
        
        $title = $session->title;
        
        // Delete all messages in this session
        $session->messages()->delete();
        
        // Delete the session itself
        $session->delete();
        
        return redirect()->route('admin.llm.quick-chat')
            ->with('success', 'Chat deleted: ' . $title);
    }
    
    /**
     * Get raw message data for debugging/inspection
     */
    public function getRawMessage($messageId)
    {
        // Find message with session relation
        $message = LLMConversationMessage::with('session')->find($messageId);
        
        if (!$message) {
            return response()->json([
                'error' => 'Message not found'
            ], 404);
        }
        
        // Ensure user can only access their own messages
        if (!$message->session || $message->session->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized access to message'
            ], 403);
        }
        
        return response()->json([
            'id' => $message->id,
            'session_id' => $message->session_id,
            'role' => $message->role,
            'content' => $message->content,
            'metadata' => $message->metadata,
            'raw_response' => $message->raw_response, // Complete provider response
            'tokens' => $message->tokens,
            'created_at' => $message->created_at?->toIso8601String() ?? null,
            'updated_at' => $message->updated_at?->toIso8601String() ?? null,
            'session' => [
                'id' => $message->session->id,
                'session_id' => $message->session->session_id,
                'title' => $message->session->title,
                'llm_configuration_id' => $message->session->llm_configuration_id,
            ],
        ]);
    }
    
    /**
     * Get message data for retry functionality
     */
    public function getMessage($messageId)
    {
        $message = LLMConversationMessage::with('session')->find($messageId);
        
        if (!$message) {
            return response()->json([
                'error' => 'Message not found'
            ], 404);
        }
        
        // Ensure user can only access their own messages
        if (!$message->session || $message->session->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized access to message'
            ], 403);
        }
        
        // Find previous user message in same session
        $previousUserMessage = LLMConversationMessage::where('session_id', $message->session_id)
            ->where('role', 'user')
            ->where('id', '<', $message->id)
            ->orderBy('id', 'desc')
            ->first();
        
        return response()->json([
            'id' => $message->id,
            'content' => $message->content,
            'metadata' => $message->metadata,
            'is_error' => $message->metadata['is_error'] ?? false,
            'finish_reason' => $message->metadata['finish_reason'] ?? null,
            'max_tokens' => $message->metadata['max_tokens'] ?? null,
            'previous_user_message' => $previousUserMessage ? [
                'id' => $previousUserMessage->id,
                'content' => $previousUserMessage->content,
            ] : null,
        ]);
    }
    
    /**
     * Delete user message when streaming is stopped before first chunk
     * This prevents orphaned user messages in DB when user cancels during "Thinking..."
     */
    public function deleteUserMessage($messageId)
    {
        $message = LLMConversationMessage::with('session')->find($messageId);
        
        if (!$message) {
            return response()->json(['success' => false, 'error' => 'Message not found'], 404);
        }
        
        // Security: Ensure user owns this message
        if ($message->session && $message->session->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        // Security: Only allow deletion of user role messages (not assistant responses)
        if ($message->role !== 'user') {
            return response()->json(['success' => false, 'error' => 'Can only delete user messages'], 400);
        }
        
        $message->delete();
        
        return response()->json(['success' => true]);
    }
}

