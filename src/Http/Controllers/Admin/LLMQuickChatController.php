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
     * Creates a new conversation in DB and shows the Quick Chat UI.
     */
    public function index()
    {
        $configurations = LLMConfiguration::active()->get();
        $defaultConfig = $configurations->first();
        
        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }
        
        // Create conversation in DB (auto-save approach)
        $session = LLMConversationSession::create([
            'session_id' => 'quick_chat_' . uniqid(),
            'title' => 'Quick Chat - ' . now()->format('Y-m-d H:i'),
            'user_id' => auth()->id(),
            'llm_configuration_id' => $defaultConfig->id,
            'extension_slug' => null,
            'total_tokens' => 0,
            'total_cost' => 0,
        ]);
        
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

        set_time_limit(300);

        return Response::stream(function () use ($validated, $session, $configuration) {
            if (ob_get_level()) ob_end_clean();

            try {
                // Save user message to DB
                $userMessage = LLMConversationMessage::create([
                    'session_id' => $session->id,
                    'role' => 'user',
                    'content' => $validated['prompt'],
                    'tokens' => 0,
                ]);

                $params = [
                    'temperature' => $configuration->temperature,
                    'max_tokens' => $configuration->max_tokens,
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
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'tokens' => $metrics['usage']['total_tokens'] ?? $tokenCount,
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
        return redirect()->route('admin.llm.quick-chat');
    }
}
