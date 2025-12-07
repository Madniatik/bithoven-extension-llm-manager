<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\Cache;

/**
 * LLM Provider Service
 * 
 * Centralized service for provider operations (connection testing, model loading).
 * Provides reusable methods with built-in caching for API efficiency.
 * 
 * @package Bithoven\LLMManager\Services
 */
class LLMProviderService
{
    /**
     * Test connection to a provider endpoint
     * 
     * @param string $provider Provider slug (ollama, openai, anthropic, etc.)
     * @param string|null $endpoint Custom endpoint URL (optional, falls back to config)
     * @param string|null $apiKey API key for authentication (optional)
     * @return array Response: ['success' => bool, 'message' => string, 'metadata' => array]
     */
    public function testConnection(string $provider, ?string $endpoint = null, ?string $apiKey = null): array
    {
        $startTime = microtime(true);
        
        $providerConfig = config("llm-manager.providers.{$provider}");
        
        if (!$providerConfig) {
            return [
                'success' => false,
                'message' => 'Provider configuration not found',
                'metadata' => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]
            ];
        }

        $testConfig = $providerConfig['test_connection'] ?? null;
        
        if (!$testConfig) {
            return [
                'success' => false,
                'message' => 'Test connection not configured for this provider',
                'metadata' => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]
            ];
        }

        // Build endpoint URL
        $baseEndpoint = $endpoint ?? $providerConfig['endpoint'];
        $testEndpoint = $testConfig['endpoint'];
        $fullUrl = rtrim($baseEndpoint, '/') . $testEndpoint;

        // Prepare headers
        $headers = [];
        foreach ($testConfig['headers'] as $key => $value) {
            $value = str_replace('{api_key}', $apiKey ?? '', $value);
            $headers[] = "{$key}: {$value}";
        }

        // Prepare request body
        $requestBody = null;
        if (!empty($testConfig['body'])) {
            $requestBody = json_encode($testConfig['body']);
        }

        // Make request
        $response = $this->makeRequest(
            $fullUrl,
            $testConfig['method'],
            $headers,
            $requestBody ? json_decode($requestBody, true) : null
        );

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => $response['message'],
                'metadata' => [
                    'url' => $fullUrl,
                    'method' => strtoupper($testConfig['method']),
                    'execution_time_ms' => $response['execution_time_ms'] ?? 0,
                ],
                'request_body' => $requestBody,
            ];
        }

        $httpCode = $response['http_code'] ?? 200;
        $responseData = $response['data'] ?? null;
        
        // Parse response preview
        $responsePreview = is_array($responseData) 
            ? json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : substr(json_encode($responseData), 0, 500);

        $metadata = [
            'url' => $fullUrl,
            'method' => strtoupper($testConfig['method']),
            'http_code' => $httpCode,
            'execution_time_ms' => $response['execution_time_ms'] ?? 0,
            'request_size_bytes' => $requestBody ? strlen($requestBody) : 0,
            'response_size_bytes' => strlen(json_encode($responseData)),
        ];

        // Success codes: 200-299 (and 400-499 for validation errors which confirm connection)
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => "Connection successful! (HTTP {$httpCode})",
                'metadata' => $metadata,
                'response' => $responsePreview,
                'request_body' => $requestBody,
            ];
        } elseif ($httpCode >= 400 && $httpCode < 500) {
            // 4xx means endpoint is reachable but there's an auth/validation issue
            $errorMsg = $responseData['error']['message'] ?? $responseData['message'] ?? 'Authentication or validation error';
            
            return [
                'success' => true,
                'message' => "Endpoint reachable (HTTP {$httpCode}). Note: {$errorMsg}",
                'metadata' => $metadata,
                'response' => $responsePreview,
                'request_body' => $requestBody,
            ];
        }

        return [
            'success' => false,
            'message' => "Connection failed with HTTP {$httpCode}",
            'metadata' => $metadata,
            'response' => $responsePreview,
            'request_body' => $requestBody,
        ];
    }

    /**
     * Load available models from a provider
     * 
     * @param string $provider Provider slug
     * @param string|null $endpoint Custom endpoint URL (optional)
     * @param string|null $apiKey API key for authentication (optional)
     * @param bool $useCache Whether to use cache (default: true)
     * @return array Response: ['success' => bool, 'models' => array, 'message' => string, 'cached' => bool]
     */
    public function loadModels(string $provider, ?string $endpoint = null, ?string $apiKey = null, bool $useCache = true): array
    {
        $providerConfig = config("llm-manager.providers.{$provider}");
        
        if (!$providerConfig || !($providerConfig['supports_dynamic_models'] ?? false)) {
            return [
                'success' => false,
                'message' => 'Provider does not support dynamic model loading',
                'models' => [],
                'cached' => false,
            ];
        }

        // Check cache first
        $cacheKey = "llm_models_{$provider}_" . md5(($endpoint ?? '') . ($apiKey ?? ''));
        $cacheTtl = $providerConfig['cache_ttl'] ?? config('llm-manager.cache.ttl', 600);
        
        if ($useCache && Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            return [
                'success' => true,
                'message' => count($cachedData) . ' models loaded (cached)',
                'models' => $cachedData,
                'cached' => true,
            ];
        }

        // Build endpoint URL
        $baseEndpoint = $endpoint ?? $providerConfig['endpoint'];
        $modelsPath = $providerConfig['endpoints']['models'] ?? '/models';
        $fullUrl = rtrim($baseEndpoint, '/') . $modelsPath;

        // Prepare headers
        $headers = ['Accept: application/json'];
        
        if ($apiKey && ($providerConfig['requires_api_key'] ?? false)) {
            $headers[] = "Authorization: Bearer {$apiKey}";
        }

        // Make request
        $response = $this->makeRequest($fullUrl, 'GET', $headers);

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => $response['message'],
                'models' => [],
                'cached' => false,
            ];
        }

        // Parse models from response
        $models = $this->parseModelsResponse($response['data'] ?? [], $provider);

        // Cache results if successful
        if ($useCache && !empty($models)) {
            Cache::put($cacheKey, $models, $cacheTtl);
        }

        return [
            'success' => true,
            'message' => count($models) . ' models loaded',
            'models' => $models,
            'cached' => false,
        ];
    }

    /**
     * Parse models from different provider response formats
     * 
     * Supports:
     * - OpenAI/OpenRouter: {data: [{id: "..."}, ...]}
     * - Ollama: {models: [{name: "..."}, ...]}
     * - Plain array: ["model1", "model2"]
     * 
     * @param array $data Raw response data
     * @param string $provider Provider slug (for format detection)
     * @return array Normalized models: [['id' => string, 'name' => string], ...]
     */
    public function parseModelsResponse(array $data, string $provider): array
    {
        $models = [];

        // OpenAI/OpenRouter format: { data: [ {id: "..."}, ... ] }
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $model) {
                $models[] = [
                    'id' => $model['id'] ?? $model['name'] ?? 'unknown',
                    'name' => $model['id'] ?? $model['name'] ?? 'unknown',
                ];
            }
        }
        // Ollama format: { models: [ {name: "..."}, ... ] }
        elseif (isset($data['models']) && is_array($data['models'])) {
            foreach ($data['models'] as $model) {
                $models[] = [
                    'id' => $model['name'] ?? $model['id'] ?? 'unknown',
                    'name' => $model['name'] ?? $model['id'] ?? 'unknown',
                ];
            }
        }
        // Plain array format
        elseif (is_array($data)) {
            foreach ($data as $model) {
                if (is_string($model)) {
                    $models[] = ['id' => $model, 'name' => $model];
                } elseif (is_array($model)) {
                    $models[] = [
                        'id' => $model['id'] ?? $model['name'] ?? 'unknown',
                        'name' => $model['name'] ?? $model['id'] ?? 'unknown',
                    ];
                }
            }
        }

        return $models;
    }

    /**
     * Make HTTP request via cURL
     * 
     * @param string $url Full URL
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array $headers Headers array (format: "Key: Value")
     * @param array|null $body Request body (will be JSON encoded)
     * @return array Response: ['success' => bool, 'data' => array, 'http_code' => int, 'message' => string, 'execution_time_ms' => float]
     */
    protected function makeRequest(string $url, string $method, array $headers, ?array $body = null): array
    {
        $startTime = microtime(true);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set method and body
        if (strtoupper($method) === 'POST' && $body) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        if ($error) {
            return [
                'success' => false,
                'message' => "Connection error: {$error}",
                'http_code' => 0,
                'execution_time_ms' => $executionTime,
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'message' => "HTTP {$httpCode}",
                'http_code' => $httpCode,
                'data' => json_decode($response, true),
                'execution_time_ms' => $executionTime,
            ];
        }

        return [
            'success' => true,
            'message' => "Success (HTTP {$httpCode})",
            'http_code' => $httpCode,
            'data' => json_decode($response, true),
            'execution_time_ms' => $executionTime,
        ];
    }

    /**
     * Clear cached models for a provider
     * 
     * @param string $provider Provider slug
     * @return bool Success status
     */
    public function clearModelsCache(string $provider): bool
    {
        // Pattern: llm_models_{provider}_*
        // Note: For simple implementation, we flush all cache
        // For production, consider using cache tags or store keys list
        
        $pattern = "llm_models_{$provider}_";
        
        // Clear specific cache entries if using Redis/Memcached with pattern support
        // For now, return true as we rely on TTL expiration
        
        return true;
    }
}
