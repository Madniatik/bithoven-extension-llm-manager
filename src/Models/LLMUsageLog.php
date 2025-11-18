<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class LLMUsageLog extends Model
{
    use HasFactory;

    protected $table = 'llm_usage_logs';

    protected $fillable = [
        'llm_configuration_id',
        'user_id',
        'extension_slug',
        'prompt',
        'response',
        'parameters_used',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'execution_time_ms',
        'status',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'parameters_used' => 'array',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost_usd' => 'decimal:6',
        'execution_time_ms' => 'integer',
        'executed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(LLMConfiguration::class, 'llm_configuration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customMetrics(): HasMany
    {
        return $this->hasMany(LLMCustomMetric::class, 'usage_log_id');
    }

    public function toolExecutions(): HasMany
    {
        return $this->hasMany(LLMToolExecution::class, 'usage_log_id');
    }

    /**
     * Scopes
     */
    public function scopeByExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('executed_at', [$startDate, $endDate]);
    }

    /**
     * Get total cost for a period
     */
    public static function getTotalCost($extensionSlug = null, $startDate = null, $endDate = null): float
    {
        return static::query()
            ->when($extensionSlug, fn($q) => $q->byExtension($extensionSlug))
            ->when($startDate && $endDate, fn($q) => $q->inPeriod($startDate, $endDate))
            ->sum('cost_usd');
    }
}
