<?php

namespace Bithoven\LLMManager\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\Conversations\LLMConversationManager;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMChatController extends Controller
{
    public function start(Request $request, LLMConversationManager $manager)
    {
        $validated = $request->validate([
            'config' => 'required|string',
            'extension' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        try {
            $config = LLMConfiguration::where('slug', $validated['config'])
                ->active()
                ->firstOrFail();

            $session = $manager->createSession(
                $config,
                $validated['extension'] ?? null,
                auth()->id(),
                $validated['title'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $session->session_id,
                    'title' => $session->title,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function send(Request $request, LLMConversationManager $manager)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $result = $manager->sendMessage(
                $validated['session_id'],
                $validated['message']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function end(Request $request, LLMConversationManager $manager)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $manager->endSession($validated['session_id']);

            return response()->json([
                'success' => true,
                'message' => 'Session ended successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
