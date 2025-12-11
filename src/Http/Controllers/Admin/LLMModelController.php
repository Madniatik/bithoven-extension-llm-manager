<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Services\LLMConfigurationService;

class LLMModelController extends Controller
{
    public function __construct(
        private readonly LLMConfigurationService $configService
    ) {}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:llm_manager_configurations,name',
            'provider' => 'required|string|in:ollama,openai,anthropic,openrouter,local,custom',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = false; // Inactive until configured
        $validated['model'] = 'pending-configuration'; // Placeholder until user configures

        $configuration = $this->configService->create($validated);

        // Return JSON for AJAX requests
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration created successfully. Please configure model and API key.',
                'redirect' => route('admin.llm.models.show', $configuration)
            ]);
        }

        return redirect()
            ->route('admin.llm.models.show', $configuration)
            ->with('success', 'Configuration created successfully. Please configure model and API key.');
    }
    
    public function show(LLMConfiguration $model)
    {
        $model->loadCount('usageLogs');
        
        // Calculate statistics
        $stats = (object) [
            'total_requests' => $model->usageLogs()->count(),
            'total_cost' => $model->usageLogs()->sum('cost_usd'),
            'total_tokens' => $model->usageLogs()->sum('total_tokens'),
            'avg_execution_time' => $model->usageLogs()->avg('execution_time_ms') ?? 0,
        ];
        
        // Get recent usage logs
        $recentLogs = $model->usageLogs()->latest()->limit(10)->get();
        
        // Get provider config
        $providers = config('llm-manager.providers', []);
        $providerConfig = $providers[$model->provider] ?? [];
        
        return view('llm-manager::admin.models.show', compact('model', 'stats', 'recentLogs', 'providerConfig', 'providers'));
    }
    
    public function update(Request $request, LLMConfiguration $model)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:llm_manager_configurations,name,' . $model->id,
            'model' => 'required|string',
            'api_key' => 'nullable|string',
            'parameters' => 'nullable|array',
            'max_tokens' => 'nullable|integer|min:1',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'is_active' => 'boolean',
        ]);

        $model->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Model updated successfully'
        ]);
    }
    
    public function updateAdvanced(Request $request, LLMConfiguration $model)
    {
        $validated = $request->validate([
            'api_endpoint' => 'nullable|url',
            'endpoint_chat' => 'nullable|string',
            'endpoint_embeddings' => 'nullable|string',
            'endpoint_models' => 'nullable|string',
            'custom_headers' => 'nullable|array',
            'timeout' => 'nullable|integer|min:5|max:300',
            'retry_attempts' => 'nullable|integer|min:0|max:10',
        ]);

        // Only update non-null values (allow empty string to clear)
        $updateData = [];
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $updateData[$key] = $value;
            }
        }
        
        // Convert custom_headers to JSON string if present
        if (isset($updateData['custom_headers'])) {
            $updateData['custom_headers'] = json_encode($updateData['custom_headers']);
        }

        $model->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Advanced settings updated successfully'
        ]);
    }
}
