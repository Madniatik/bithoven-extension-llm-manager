<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMProviderConfiguration extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_provider_configurations';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMProviderConfigurationFactory::new();
    }

    protected $fillable = [
        'provider_id',
        'name',
        'slug',
        'model',
        'api_key',
        'default_parameters',
        'capabilities',
        'cost_per_1k_input_tokens',
        'cost_per_1k_output_tokens',
        'currency',
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
    public function provider(): BelongsTo
    {
        return $this->belongsTo(LLMProvider::class, 'provider_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(LLMUsageLog::class, 'llm_provider_configuration_id');
    }

    public function parameterOverrides(): HasMany
    {
        return $this->hasMany(LLMParameterOverride::class, 'llm_provider_configuration_id');
    }

    public function conversationSessions(): HasMany
    {
        return $this->hasMany(LLMConversationSession::class, 'llm_provider_configuration_id');
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(LLMAgentWorkflow::class, 'llm_provider_configuration_id');
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
        return $query->whereHas('provider', function ($q) use ($provider) {
            $q->where('slug', $provider);
        });
    }

    public function scopeForProvider($query, string $provider)
    {
        return $query->whereHas('provider', function ($q) use ($provider) {
            $q->where('slug', $provider);
        });
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
     * Magic accessor for default_parameters fields
     * Allows accessing $config->temperature, $config->max_tokens, etc.
     */
    public function __get($key)
    {
        if (in_array($key, ['temperature', 'max_tokens', 'top_p', 'frequency_penalty', 'presence_penalty'])) {
            return $this->default_parameters[$key] ?? null;
        }
        return parent::__get($key);
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
