<?php

namespace Bithoven\LLMManager\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\Tools\LLMToolExecutor;

class LLMToolController extends Controller
{
    public function execute(Request $request, LLMToolExecutor $executor)
    {
        $validated = $request->validate([
            'tool' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        try {
            $result = $executor->execute(
                $validated['tool'],
                $validated['parameters'] ?? []
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
            ], $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
