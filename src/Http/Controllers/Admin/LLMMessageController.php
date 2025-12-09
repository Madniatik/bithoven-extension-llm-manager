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
     * Usage logs are preserved (no FK constraint on message_id)
     * 
     * @param int $id Message ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $message = LLMConversationMessage::findOrFail($id);

        // Verify permissions: user can only delete their own messages
        if ($message->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only delete your own messages',
            ], 403);
        }

        // Delete message (logs are preserved - no FK constraint)
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully',
        ]);
    }
}
