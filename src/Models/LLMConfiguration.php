<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LLMConfiguration extends Model
{
    use HasFactory;

    protected $table = 'llm_configurations';

    protected $fillable = [
        'name',
        'slug',
        'provider',
        'model',
        'api_endpoint',
        'api_key',
        'default_parameters',
        'capabilities',
        'is_active',
        'is_default',
        'description',
    ];

    protected $casts = [
        'default_parameters' => 'array',
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Relationships
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(LLMUsageLog::class);
    }

    public function parameterOverrides(): HasMany
    {
        return $this->hasMany(LLMParameterOverride::class);
    }

    public function conversationSessions(): HasMany
    {
        return $this->hasMany(LLMConversationSession::class);
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(LLMAgentWorkflow::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Accessors & Mutators
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = $value ? encrypt($value) : null;
    }

    public function getApiKeyAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Get effective parameters by merging with overrides
     */
    public function getEffectiveParameters(string $extensionSlug = null, string $context = null): array
    {
        $params = $this->default_parameters ?? [];

        if ($extensionSlug || $context) {
            $overrides = $this->parameterOverrides()
                ->where('is_active', true)
                ->when($extensionSlug, fn($q) => $q->where('extension_slug', $extensionSlug))
                ->when($context, fn($q) => $q->where('context', $context))
                ->orderBy('priority')
                ->get();

            foreach ($overrides as $override) {
                $params = match ($override->merge_strategy) {
                    'replace' => $override->override_parameters,
                    'merge' => array_merge($params, $override->override_parameters),
                    'deep_merge' => array_merge_recursive($params, $override->override_parameters),
                    default => array_merge($params, $override->override_parameters),
                };
            }
        }

        return $params;
    }
}
