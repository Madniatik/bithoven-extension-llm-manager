<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LLMMCPConnector extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_mcp_connectors';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMMCPConnectorFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'type',
        'server_path',
        'server_url',
        'protocol',
        'capabilities',
        'configuration',
        'is_active',
        'auto_start',
        'priority',
        'description',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'configuration' => 'array',
        'is_active' => 'boolean',
        'auto_start' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Relationships
     */
    public function toolDefinitions(): HasMany
    {
        return $this->hasMany(LLMToolDefinition::class, 'mcp_connector_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBundled($query)
    {
        return $query->where('type', 'bundled');
    }

    public function scopeExternal($query)
    {
        return $query->where('type', 'external');
    }

    public function scopeAutoStart($query)
    {
        return $query->where('auto_start', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Check if connector is bundled
     */
    public function isBundled(): bool
    {
        return $this->type === 'bundled';
    }

    /**
     * Get full server path
     */
    public function getFullServerPathAttribute(): ?string
    {
        if ($this->isBundled() && $this->server_path) {
            return base_path($this->server_path);
        }

        return $this->server_path;
    }
}
