<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMCustomMetric extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_custom_metrics';

    protected $fillable = [
        'usage_log_id',
        'extension_slug',
        'metric_key',
        'metric_value',
        'metric_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    public function usageLog(): BelongsTo
    {
        return $this->belongsTo(LLMUsageLog::class, 'usage_log_id');
    }

    /**
     * Scopes
     */
    public function scopeByExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeByMetricKey($query, string $metricKey)
    {
        return $query->where('metric_key', $metricKey);
    }

    /**
     * Accessors
     */
    public function getTypedValueAttribute()
    {
        return match ($this->metric_type) {
            'integer' => (int) $this->metric_value,
            'float' => (float) $this->metric_value,
            'boolean' => filter_var($this->metric_value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->metric_value, true),
            default => $this->metric_value,
        };
    }

    /**
     * Get metric statistics
     */
    public static function getStats(string $extensionSlug, string $metricKey): array
    {
        $metrics = static::byExtension($extensionSlug)
            ->byMetricKey($metricKey)
            ->get();

        if ($metrics->isEmpty()) {
            return [];
        }

        $firstMetric = $metrics->first();

        if (in_array($firstMetric->metric_type, ['integer', 'float'])) {
            $values = $metrics->pluck('metric_value')->map(fn($v) => (float) $v);
            return [
                'count' => $values->count(),
                'sum' => $values->sum(),
                'avg' => $values->avg(),
                'min' => $values->min(),
                'max' => $values->max(),
            ];
        }

        return [
            'count' => $metrics->count(),
            'distribution' => $metrics->groupBy('metric_value')
                ->map(fn($group) => $group->count())
                ->toArray(),
        ];
    }
}
