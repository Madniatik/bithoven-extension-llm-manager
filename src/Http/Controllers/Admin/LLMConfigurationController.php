<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMConfigurationController extends Controller
{
    /**
     * Display a listing of configurations.
     * Used by /admin/llm/configurations (index page)
     */
    public function index()
    {
        $configurations = LLMConfiguration::withCount('usageLogs')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

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
            $startTime = microtime(true);
            
            $provider = $validated['provider'];
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

            // Use provided endpoint or fallback to config
            $baseEndpoint = $validated['api_endpoint'] ?? $providerConfig['endpoint'];
            $testEndpoint = $testConfig['endpoint'];
            $fullUrl = rtrim($baseEndpoint, '/') . $testEndpoint;

            // Use provided API key or empty for local providers
            $apiKey = $validated['api_key'] ?? '';

            // Prepare headers
            $headers = [];
            foreach ($testConfig['headers'] as $key => $value) {
                // Replace {api_key} placeholder
                $value = str_replace('{api_key}', $apiKey, $value);
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
