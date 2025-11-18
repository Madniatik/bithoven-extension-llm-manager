<?php

namespace Bithoven\LLMManager\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\Workflows\LLMWorkflowEngine;

class LLMWorkflowController extends Controller
{
    public function execute(Request $request, LLMWorkflowEngine $engine)
    {
        $validated = $request->validate([
            'workflow' => 'required|string',
            'input' => 'required|array',
        ]);

        try {
            $result = $engine->execute(
                $validated['workflow'],
                $validated['input']
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
