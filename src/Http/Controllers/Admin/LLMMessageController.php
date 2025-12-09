<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LLMMessageController extends Controller
{
    /**
     * Delete a conversation message
     * 
     * Optionally deletes related usage logs if requested
     * 
     * @param int $id Message ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id, Request $request)
    {
        $validated = $request->validate([
            'delete_logs' => 'nullable|boolean',
        ]);

        $message = LLMConversationMessage::findOrFail($id);

        // Verify permissions: user can only delete their own messages
        if ($message->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only delete your own messages',
            ], 403);
        }

        // Delete message
        $message->delete();

        // Optionally delete related usage logs
        $logsDeleted = 0;
        if ($validated['delete_logs'] ?? false) {
            $logsDeleted = LLMUsageLog::where('message_id', $id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully',
            'logs_deleted' => $logsDeleted,
        ]);
    }
}
