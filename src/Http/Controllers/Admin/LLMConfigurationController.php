<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMConfigurationController extends Controller
{
    public function index()
    {
        $configurations = LLMConfiguration::withCount('usageLogs')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('llm-manager::admin.configurations.index', compact('configurations'));
    }

    public function create()
    {
        $providers = config('llm-manager.providers', []);
        
        return view('llm-manager::admin.configurations.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string',
            'model' => 'required|string',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|url',
            'parameters' => 'nullable|array',
            'max_tokens' => 'nullable|integer|min:1',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $configuration = LLMConfiguration::create($validated);

        return redirect()
            ->route('admin.llm.configurations.show', $configuration)
            ->with('success', 'Configuration created successfully');
    }

    public function show(LLMConfiguration $configuration)
    {
        $configuration->loadCount('usageLogs');
        $configuration->load(['usageLogs' => function($q) {
            $q->latest()->limit(50);
        }]);

        // Calculate statistics from usage logs
        $stats = (object) [
            'total_requests' => $configuration->usageLogs()->count(),
            'total_cost' => $configuration->usageLogs()->sum('cost_usd'),
            'total_tokens' => $configuration->usageLogs()->sum('total_tokens'),
            'avg_execution_time' => $configuration->usageLogs()->avg('execution_time_ms') ?? 0,
        ];

        return view('llm-manager::admin.configurations.show', compact('configuration', 'stats'));
    }

    public function edit(LLMConfiguration $configuration)
    {
        $providers = config('llm-manager.providers', []);
        
        return view('llm-manager::admin.configurations.edit', compact('configuration', 'providers'));
    }

    public function update(Request $request, LLMConfiguration $configuration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|url',
            'parameters' => 'nullable|array',
            'max_tokens' => 'nullable|integer|min:1',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'is_active' => 'boolean',
        ]);

        $configuration->update($validated);

        return redirect()
            ->route('admin.llm.configurations.show', $configuration)
            ->with('success', 'Configuration updated successfully');
    }

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

        return back()->with('success', 'Configuration status updated');
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'api_endpoint' => 'nullable|url',
            'api_key' => 'nullable|string',
        ]);

        try {
            // Basic connectivity test based on provider
            $provider = $validated['provider'];
            $endpoint = $validated['api_endpoint'] ?? config("llm-manager.providers.{$provider}.endpoint");
            
            if (!$endpoint) {
                return response()->json([
                    'success' => false,
                    'message' => 'No endpoint configured for this provider'
                ]);
            }

            // Simple HTTP check
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Accept 200-499 as reachable (401/403 means endpoint exists but needs auth)
            if ($httpCode >= 200 && $httpCode < 500) {
                return response()->json([
                    'success' => true,
                    'message' => "Endpoint reachable (HTTP {$httpCode})"
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Endpoint returned HTTP {$httpCode}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
