<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

/**
 * Provider Repository Validator
 * 
 * Validates provider configuration packages and JSON config files
 * according to bithoven LLM configuration schema.
 * 
 * @package Bithoven\LLMManager\Services
 * @version 0.4.0
 * @since 0.4.0
 */
class ProviderRepositoryValidator
{
    /**
     * JSON Schema version supported
     */
    private const SCHEMA_VERSION = '0.1.0';

    /**
     * Validate provider config JSON file structure
     * 
     * @param array $config Parsed JSON configuration
     * @return array Validation errors (empty if valid)
     */
    public function validate(array $config): array
    {
        $validator = Validator::make($config, [
            // Root level
            'version' => 'required|string',
            
            // Metadata
            'metadata' => 'required|array',
            'metadata.package' => 'required|string',
            'metadata.created_at' => 'required|date',
            'metadata.updated_at' => 'required|date',
            'metadata.author' => 'required|string',
            
            // Configuration (core)
            'configuration' => 'required|array',
            'configuration.name' => 'required|string|max:255',
            'configuration.slug' => 'required|string|regex:/^[a-z0-9\-]+$/|max:100',
            'configuration.provider' => 'required|string|max:50',
            'configuration.model_name' => 'required|string|max:100',
            'configuration.description' => 'nullable|string|max:1000',
            'configuration.api_endpoint' => 'required|url|max:500',
            
            // Default parameters
            'configuration.default_parameters' => 'required|array',
            'configuration.default_parameters.max_tokens' => 'required|integer|min:1|max:200000',
            'configuration.default_parameters.temperature' => 'required|numeric|min:0|max:2',
            'configuration.default_parameters.top_p' => 'nullable|numeric|min:0|max:1',
            'configuration.default_parameters.frequency_penalty' => 'nullable|numeric|min:-2|max:2',
            'configuration.default_parameters.presence_penalty' => 'nullable|numeric|min:-2|max:2',
            
            // Capabilities
            'configuration.capabilities' => 'nullable|array',
            'configuration.capabilities.*' => 'string',
            
            // Limits
            'configuration.limits' => 'nullable|array',
            'configuration.limits.context_window' => 'nullable|integer|min:1',
            'configuration.limits.max_output_tokens' => 'nullable|integer|min:1',
            'configuration.limits.requests_per_minute' => 'nullable|integer|min:1',
            'configuration.limits.tokens_per_minute' => 'nullable|integer|min:1',
            
            // Pricing
            'configuration.pricing' => 'nullable|array',
            'configuration.pricing.currency' => 'nullable|string|size:3',
            'configuration.pricing.input_per_1k_tokens' => 'nullable|numeric|min:0',
            'configuration.pricing.output_per_1k_tokens' => 'nullable|numeric|min:0',
            
            // Metadata
            'configuration.recommended_use_cases' => 'nullable|array',
            'configuration.recommended_use_cases.*' => 'string',
            'configuration.tags' => 'nullable|array',
            'configuration.tags.*' => 'string',
            
            // Status flags
            'configuration.is_active' => 'nullable|boolean',
            'configuration.is_default' => 'nullable|boolean',
        ]);

        return $validator->errors()->toArray();
    }

    /**
     * Check if package manifest exists and is valid
     * 
     * @param string $packagePath Absolute path to package directory
     * @return bool True if valid package structure
     */
    public function validatePackage(string $packagePath): bool
    {
        // Check configs directory exists
        if (!is_dir($packagePath . '/configs')) {
            return false;
        }

        // Check manifest exists
        $manifestPath = $packagePath . '/configs/manifest.json';
        if (!file_exists($manifestPath)) {
            return false;
        }

        // Validate manifest structure
        try {
            $manifestContent = File::get($manifestPath);
            $manifest = json_decode($manifestContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            // Required manifest fields
            $required = ['package_name', 'version', 'configurations'];
            foreach ($required as $field) {
                if (!isset($manifest[$field])) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get package manifest data
     * 
     * @param string $packagePath Absolute path to package directory
     * @return array|null Manifest data or null if invalid
     */
    public function getManifest(string $packagePath): ?array
    {
        $manifestPath = $packagePath . '/configs/manifest.json';
        
        if (!file_exists($manifestPath)) {
            return null;
        }

        try {
            $manifestContent = File::get($manifestPath);
            $manifest = json_decode($manifestContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $manifest;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate config file path and content
     * 
     * @param string $filePath Absolute path to config JSON file
     * @return array ['valid' => bool, 'data' => array|null, 'errors' => array]
     */
    public function validateFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [
                'valid' => false,
                'data' => null,
                'errors' => ['file' => ['File not found']],
            ];
        }

        if (!str_ends_with($filePath, '.json')) {
            return [
                'valid' => false,
                'data' => null,
                'errors' => ['file' => ['Must be a JSON file']],
            ];
        }

        try {
            $content = File::get($filePath);
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'valid' => false,
                    'data' => null,
                    'errors' => ['json' => [json_last_error_msg()]],
                ];
            }

            $errors = $this->validate($data);

            return [
                'valid' => empty($errors),
                'data' => $data,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]],
            ];
        }
    }

    /**
     * Check if schema version is compatible
     * 
     * @param string $version Version string from config
     * @return bool True if compatible
     */
    public function isCompatibleVersion(string $version): bool
    {
        // For now, simple exact match
        // Future: implement semver comparison
        return $version === self::SCHEMA_VERSION;
    }
}
