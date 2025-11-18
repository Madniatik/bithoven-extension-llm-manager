<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Services\Conversations\LLMConversationManager;

class LLMConversationController extends Controller
{
    public function index()
    {
        $sessions = LLMConversationSession::with(['user', 'configuration'])
            ->latest()
            ->paginate(20);

        return view('llm-manager::admin.conversations.index', compact('sessions'));
    }

    public function show(string $sessionId, LLMConversationManager $manager)
    {
        $session = $manager->getSession($sessionId);
        $session->load(['user', 'configuration', 'messages']);

        return view('llm-manager::admin.conversations.show', compact('session'));
    }

    public function destroy(string $sessionId, LLMConversationManager $manager)
    {
        $session = $manager->getSession($sessionId);
        $session->delete();

        return redirect()
            ->route('admin.llm.conversations.index')
            ->with('success', 'Conversation deleted successfully');
    }

    public function export(string $sessionId, LLMConversationManager $manager)
    {
        $export = $manager->export($sessionId);

        return response()->json($export);
    }
}
