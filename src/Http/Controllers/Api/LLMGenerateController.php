<?php

namespace Bithoven\LLMManager\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\LLMManager;

class LLMGenerateController extends Controller
{
    public function __invoke(Request $request, LLMManager $llm)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'config' => 'nullable|string',
            'parameters' => 'nullable|array',
            'extension' => 'nullable|string',
            'context' => 'nullable|string',
        ]);

        try {
            if (isset($validated['config'])) {
                $llm->config($validated['config']);
            }

            if (isset($validated['parameters'])) {
                $llm->parameters($validated['parameters']);
            }

            if (isset($validated['extension'])) {
                $llm->extension($validated['extension']);
            }

            if (isset($validated['context'])) {
                $llm->context($validated['context']);
            }

            $result = $llm->generate($validated['prompt']);

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
