<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LLMProvider extends Model
{
    protected $table = 'llm_manager_providers';

    protected $fillable = [
        'slug',
        'name',
        'package',
        'version',
        'api_endpoint',
        'capabilities',
        'is_active',
        'is_installed',
        'archived_at',
        'metadata',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Get all provider configurations for this provider
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(LLMProviderConfiguration::class, 'provider_id');
    }

    /**
     * Get active configurations only
     */
    public function activeConfigurations(): HasMany
    {
        return $this->configurations()->where('is_active', true);
    }

    /**
     * Get default configuration for this provider
     */
    public function defaultConfiguration()
    {
        return $this->configurations()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Scope: Only active providers
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where('is_installed', true)
                    ->whereNull('archived_at');
    }

    /**
     * Scope: Include archived providers
     */
    public function scopeWithArchived(Builder $query): Builder
    {
        return $query; // Returns all, including archived
    }

    /**
     * Scope: Only archived providers
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at')
                    ->orWhere('is_installed', false);
    }

    /**
     * Check if provider is archived
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null || !$this->is_installed;
    }

    /**
     * Archive this provider (soft deletion)
     */
    public function archive(string $reason = 'manual'): void
    {
        $this->update([
            'is_active' => false,
            'is_installed' => false,
            'archived_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'archived_reason' => $reason,
                'archived_by' => auth()->id() ?? 'system',
                'last_active_at' => $this->updated_at,
            ]),
        ]);

        // Deactivate all configurations
        $this->configurations()->update(['is_active' => false]);
    }

    /**
     * Restore archived provider
     */
    public function restore(): void
    {
        $this->update([
            'is_active' => true,
            'is_installed' => true,
            'archived_at' => null,
            'metadata' => array_merge($this->metadata ?? [], [
                'restored_at' => now(),
                'restored_by' => auth()->id() ?? 'system',
                'restore_count' => ($this->metadata['restore_count'] ?? 0) + 1,
            ]),
        ]);

        // Reactivate all configurations
        $this->configurations()->update(['is_active' => true]);
    }

    /**
     * Get provider statistics
     */
    public function getStatsAttribute(): array
    {
        return [
            'total_configurations' => $this->configurations()->count(),
            'active_configurations' => $this->activeConfigurations()->count(),
            'total_usage' => LLMUsageLog::whereHas('configuration', function ($q) {
                $q->where('provider_id', $this->id);
            })->count(),
        ];
    }
}
