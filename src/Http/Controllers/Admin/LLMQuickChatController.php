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
     * Reuses last active session or creates a new one.
     */
    public function index()
    {
        $configurations = LLMConfiguration::active()->get();
        $defaultConfig = $configurations->first();
        
        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }
        
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
                // Save user message to DB
                $userMessage = LLMConversationMessage::create([
                    'session_id' => $session->id,
                    'user_id' => auth()->id(),
                    'role' => 'user',
                    'content' => $validated['prompt'],
                    'tokens' => 0,
                    'metadata' => [
                        'input_tokens' => 0,
                        'context_messages_count' => $session->messages()->count(),
                    ],
                ]);

                $params = [
                    'temperature' => $validated['temperature'] ?? $configuration->temperature,
                    'max_tokens' => $validated['max_tokens'] ?? $configuration->default_parameters['max_tokens'] ?? 8000,
                ];

                $logSession = $this->streamLogger->startSession($configuration, $validated['prompt'], $params);
                
                $provider = $this->llmManager->config($configuration->id)->getProvider();
                
                // Build context from previous messages (apply context_limit)
                $contextLimit = $validated['context_limit'] ?? 10;
                $query = $session->messages()->orderBy('id');
                
                // If context_limit is 0, use all messages; otherwise take last N
                if ($contextLimit > 0) {
                    $query->take($contextLimit);
                }
                
                $context = $query->get()
                    ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                    ->toArray();

                $fullResponse = '';
                $tokenCount = 0;
                $startTime = microtime(true);
                $firstChunkTime = null;

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
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens' => $metrics['usage']['total_tokens'] ?? $tokenCount,
                    'response_time' => $responseTime,
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
                    ],
                ]);

                $usageLog = $this->streamLogger->endSession($logSession, $fullResponse, $metrics);

                // Note: Session totals (tokens/cost) can be calculated from messages and usage_logs
                // No need to store redundant data in session table

                echo "data: " . json_encode([
                    'type' => 'done',
                    'usage' => $metrics['usage'],
                    'cost' => $usageLog->cost_usd,
                    'message_id' => $assistantMessage->id,
                    'response_time' => $responseTime,
                    'ttft' => $ttft,
                    'metadata' => [
                        'provider' => $configuration->provider,
                        'model' => $configuration->model,
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
    
    public function newChat()
    {
        // Marcar sesión actual como inactiva
        LLMConversationSession::where('user_id', auth()->id())
            ->where('extension_slug', null)
            ->where('is_active', true)
            ->update(['is_active' => false]);
        
        return redirect()->route('admin.llm.quick-chat');
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
}
