<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LLMToolDefinition extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_tool_definitions';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMToolDefinitionFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'type',
        'extension_slug',
        'tool_type',
        'mcp_connector_id',
        'function_schema',
        'parameters_schema',
        'handler_class',
        'handler_method',
        'implementation',
        'validation_rules',
        'security_policy',
        'metadata',
        'is_active',
        'description',
    ];

    protected $casts = [
        'function_schema' => 'array',
        'parameters_schema' => 'array',
        'validation_rules' => 'array',
        'security_policy' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function mcpConnector(): BelongsTo
    {
        return $this->belongsTo(LLMMCPConnector::class, 'mcp_connector_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(LLMToolExecution::class, 'tool_definition_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFunctionCalling($query)
    {
        return $query->where('type', 'function_calling');
    }

    public function scopeMCP($query)
    {
        return $query->where('type', 'mcp');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if tool uses function calling
     */
    public function isFunctionCalling(): bool
    {
        return $this->type === 'function_calling';
    }

    /**
     * Check if tool uses MCP
     */
    public function isMCP(): bool
    {
        return $this->type === 'mcp';
    }

    /**
     * Validate input parameters
     */
    public function validateInput(array $input): array
    {
        if (!$this->validation_rules) {
            return ['valid' => true, 'errors' => []];
        }

        $validator = validator($input, $this->validation_rules);

        return [
            'valid' => !$validator->fails(),
            'errors' => $validator->errors()->toArray(),
        ];
    }

    /**
     * Check security policy
     */
    public function checkSecurityPolicy(array $input): bool
    {
        if (!$this->security_policy) {
            return true;
        }

        // Check whitelisted paths
        if (isset($this->security_policy['allowed_paths']) && isset($input['path'])) {
            $allowed = false;
            foreach ($this->security_policy['allowed_paths'] as $allowedPath) {
                if (str_starts_with($input['path'], $allowedPath)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                return false;
            }
        }

        // Check allowed extensions
        if (isset($this->security_policy['allowed_extensions']) && isset($input['path'])) {
            $extension = pathinfo($input['path'], PATHINFO_EXTENSION);
            if (!in_array($extension, $this->security_policy['allowed_extensions'])) {
                return false;
            }
        }

        // Check max file size
        if (isset($this->security_policy['max_file_size']) && isset($input['size'])) {
            if ($input['size'] > $this->security_policy['max_file_size']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate parameters against function schema
     */
    public function validateParameters(array $parameters): bool
    {
        // Support both schema formats:
        // 1. {"parameters": {"required": [...]}} (standard)
        // 2. {"param1": {"required": true}, ...} (legacy)
        
        $schema = $this->function_schema;
        
        if (isset($schema['parameters']['required'])) {
            // Standard format
            $required = $schema['parameters']['required'];
            foreach ($required as $requiredParam) {
                if (!isset($parameters[$requiredParam])) {
                    return false;
                }
            }
        } else {
            // Legacy format - check each param
            foreach ($schema as $paramName => $paramSchema) {
                if (isset($paramSchema['required']) && $paramSchema['required'] === true) {
                    if (!isset($parameters[$paramName])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Format tool for function calling
     */
    public function toFunctionCallingFormat(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description ?? '',
            'parameters' => $this->function_schema['parameters'] ?? [
                'type' => 'object',
                'properties' => [],
            ],
        ];
    }
}

