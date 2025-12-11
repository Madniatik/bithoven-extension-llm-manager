<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Services\LLMProviderService;
use Bithoven\LLMManager\Services\LLMConfigurationService;

class LLMConfigurationController extends Controller
{
    public function __construct(
        private readonly LLMProviderService $providerService,
        private readonly LLMConfigurationService $configService
    ) {}

    /**
     * Display a listing of configurations.
     * Used by /admin/llm/configurations (index page)
     */
    public function index()
    {
        $configurations = $this->configService->getAll();

        return view('llm-manager::admin.configurations.index', compact('configurations'));
    }

    /**
     * Remove the specified configuration.
     */
    public function destroy(LLMConfiguration $configuration)
    {
        $configuration->delete();

        return redirect()
            ->route('admin.llm.configurations.index')
            ->with('success', 'Configuration deleted successfully');
    }

    public function toggleActive(LLMConfiguration $configuration)
    {
        $configuration->is_active = !$configuration->is_active;
        $configuration->save();

        // Return JSON for AJAX requests
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration status updated successfully',
                'is_active' => $configuration->is_active
            ]);
        }

        return back()->with('success', 'Configuration status updated');
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'api_endpoint' => 'nullable|string',
            'api_key' => 'nullable|string',
        ]);

        try {
            $result = $this->providerService->testConnection(
                $validated['provider'],
                $validated['api_endpoint'] ?? null,
                $validated['api_key'] ?? null
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: {$e->getMessage()}",
            ]);
        }
    }

    /**
     * Load dynamic models from provider
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadModels(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'api_endpoint' => 'nullable|string',
            'api_key' => 'nullable|string',
            'use_cache' => 'nullable|boolean',
        ]);

        try {
            $result = $this->providerService->loadModels(
                $validated['provider'],
                $validated['api_endpoint'] ?? null,
                $validated['api_key'] ?? null,
                $validated['use_cache'] ?? true
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: {$e->getMessage()}",
                'models' => [],
                'cached' => false,
            ]);
        }
    }
}
