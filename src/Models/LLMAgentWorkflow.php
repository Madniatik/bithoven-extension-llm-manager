<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMAgentWorkflow extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_agent_workflows';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMAgentWorkflowFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'extension_slug',
        'workflow_definition',
        'agents_config',
        'llm_configuration_id',
        'max_steps',
        'timeout_seconds',
        'is_active',
        'description',
    ];

    protected $casts = [
        'workflow_definition' => 'array',
        'agents_config' => 'array',
        'max_steps' => 'integer',
        'timeout_seconds' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(LLMConfiguration::class, 'llm_configuration_id');
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

    /**
     * Get initial state from workflow definition
     */
    public function getInitialStateAttribute(): ?string
    {
        return $this->workflow_definition['initial_state'] ?? null;
    }

    /**
     * Get all states from workflow definition
     */
    public function getStatesAttribute(): array
    {
        return $this->workflow_definition['states'] ?? [];
    }

    /**
     * Get transitions from workflow definition
     */
    public function getTransitionsAttribute(): array
    {
        return $this->workflow_definition['transitions'] ?? [];
    }
}
