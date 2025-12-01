<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMConversationSession;

class LLMQuickChatController extends Controller
{
    /**
     * Display the Quick Chat interface.
     * 
     * @param int|null $sessionId Optional conversation session ID to display
     * @return \Illuminate\View\View
     */
    public function index($sessionId = null)
    {
        // Load active LLM configurations
        $configurations = LLMConfiguration::active()->get();
        
        // Default to session 4 for testing/demo purposes
        $sessionId = $sessionId ?? 4;
        
        // Load conversation session with messages, user, and configuration
        $session = LLMConversationSession::with([
            'messages' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'user',
            'configuration'
        ])->find($sessionId);
        
        return view('llm-manager::admin.quick-chat.index', compact('configurations', 'session'));
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
