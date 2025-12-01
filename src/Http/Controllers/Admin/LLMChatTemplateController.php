<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMConversationSession;

class LLMChatTemplateController extends Controller
{
    /**
     * Display the Chat Template interface.
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
        
        return view('llm-manager::admin.quick-chat.chat-template', compact('configurations', 'session'));
    }
}
