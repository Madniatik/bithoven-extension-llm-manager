<?php

namespace Bithoven\LLMManager\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\LLMRAGService;

class LLMRAGController extends Controller
{
    public function search(Request $request, LLMRAGService $ragService)
    {
        $validated = $request->validate([
            'query' => 'required|string',
            'extension' => 'nullable|string',
            'top_k' => 'nullable|integer|min:1|max:20',
        ]);

        try {
            $results = $ragService->search(
                $validated['query'],
                $validated['extension'] ?? null,
                $validated['top_k'] ?? 5
            );

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generate(Request $request, LLMRAGService $ragService)
    {
        $validated = $request->validate([
            'query' => 'required|string',
            'extension' => 'nullable|string',
        ]);

        try {
            $result = $ragService->generateAnswer(
                $validated['query'],
                $validated['extension'] ?? null
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
}
