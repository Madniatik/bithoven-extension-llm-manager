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
            'configuration_id' => 'required|exists:ai_llm_configurations,id',
        ]);

        try {
            $startTime = microtime(true);
            
            $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);
            $provider = $configuration->provider;
            $providerConfig = config("llm-manager.providers.{$provider}");
            
            if (!$providerConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider configuration not found',
                    'metadata' => [
                        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    ]
                ]);
            }

            $testConfig = $providerConfig['test_connection'] ?? null;
            
            if (!$testConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test connection not configured for this provider',
                    'metadata' => [
                        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    ]
                ]);
            }

            // Build full URL
            $baseEndpoint = $configuration->api_endpoint ?? $providerConfig['endpoint'];
            $testEndpoint = $testConfig['endpoint'];
            $fullUrl = rtrim($baseEndpoint, '/') . $testEndpoint;

            // Prepare headers
            $headers = [];
            foreach ($testConfig['headers'] as $key => $value) {
                // Replace {api_key} placeholder
                $value = str_replace('{api_key}', $configuration->api_key, $value);
                $headers[] = "{$key}: {$value}";
            }

            // Prepare request body
            $requestBody = null;
            if (!empty($testConfig['body'])) {
                $requestBody = json_encode($testConfig['body']);
            }

            // Initialize cURL
            $curlStartTime = microtime(true);
            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Set method and body
            if (strtoupper($testConfig['method']) === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($requestBody) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
                }
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlInfo = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            $curlExecutionTime = round((microtime(true) - $curlStartTime) * 1000, 2);

            // Parse response
            $responseData = json_decode($response, true);
            $responsePreview = is_array($responseData) 
                ? json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                : substr($response, 0, 500);

            $metadata = [
                'url' => $fullUrl,
                'method' => strtoupper($testConfig['method']),
                'http_code' => $httpCode,
                'request_time_ms' => $curlExecutionTime,
                'total_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'request_size_bytes' => $requestBody ? strlen($requestBody) : 0,
                'response_size_bytes' => strlen($response),
            ];

            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => "Connection error: {$error}",
                    'metadata' => $metadata,
                    'response' => null,
                    'request_body' => $requestBody,
                ]);
            }

            // Success codes: 200-299 (and 400-499 for Anthropic validation errors which confirm connection)
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json([
                    'success' => true,
                    'message' => "Connection successful! (HTTP {$httpCode})",
                    'metadata' => $metadata,
                    'response' => $responsePreview,
                    'request_body' => $requestBody,
                ]);
            } elseif ($httpCode >= 400 && $httpCode < 500) {
                // 4xx means endpoint is reachable but there's an auth/validation issue
                $errorMsg = $responseData['error']['message'] ?? $responseData['message'] ?? 'Authentication or validation error';
                
                return response()->json([
                    'success' => true,
                    'message' => "Endpoint reachable (HTTP {$httpCode}). Note: {$errorMsg}",
                    'metadata' => $metadata,
                    'response' => $responsePreview,
                    'request_body' => $requestBody,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Connection failed with HTTP {$httpCode}",
                'metadata' => $metadata,
                'response' => $responsePreview,
                'request_body' => $requestBody,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: {$e->getMessage()}",
                'metadata' => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ],
            ]);
        }
    }
}
