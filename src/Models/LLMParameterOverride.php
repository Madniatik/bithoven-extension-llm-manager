<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMParameterOverride extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_parameter_overrides';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMParameterOverrideFactory::new();
    }

    protected $fillable = [
        'extension_slug',
        'llm_provider_configuration_id',
        'context',
        'override_parameters',
        'merge_strategy',
        'is_active',
        'priority',
        'description',
    ];

    protected $casts = [
        'override_parameters' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Relationships
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(LLMProviderConfiguration::class, 'llm_provider_configuration_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority');
    }
}
