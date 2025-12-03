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
                    'temperature' => $configuration->temperature,
                    'max_tokens' => $configuration->default_parameters['max_tokens'] ?? 8000,
                ];

                $logSession = $this->streamLogger->startSession($configuration, $validated['prompt'], $params);
                
                $provider = $this->llmManager->config($configuration->id)->getProvider();
                
                // Build context from previous messages
                $context = $session->messages()
                    ->orderBy('id')
                    ->get()
                    ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                    ->toArray();

                $fullResponse = '';
                $tokenCount = 0;

                $metrics = $provider->stream(
                    $validated['prompt'],
                    $context,
                    $params,
                    function ($chunk) use (&$fullResponse, &$tokenCount) {
                        $fullResponse .= $chunk;
                        $tokenCount++;

                        echo "data: " . json_encode([
                            'type' => 'chunk',
                            'content' => $chunk,
                            'tokens' => $tokenCount,
                        ]) . "\n\n";

                        if (ob_get_level()) ob_flush();
                        flush();
                    }
                );

                // Save assistant message to DB
                $assistantMessage = LLMConversationMessage::create([
                    'session_id' => $session->id,
                    'user_id' => auth()->id(),
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens' => $metrics['usage']['total_tokens'] ?? $tokenCount,
                    'metadata' => [
                        'model' => $configuration->model,
                        'provider' => $configuration->provider,
                        'max_tokens' => $params['max_tokens'],
                        'temperature' => $params['temperature'],
                        'chunks_count' => $tokenCount,
                        'finish_reason' => $metrics['finish_reason'] ?? 'unknown',
                        'output_tokens' => $metrics['usage']['completion_tokens'] ?? 0,
                        'input_tokens' => $metrics['usage']['prompt_tokens'] ?? 0,
                        'context_size' => count($context),
                        'is_streaming' => true,
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
