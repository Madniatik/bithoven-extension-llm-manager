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
}
