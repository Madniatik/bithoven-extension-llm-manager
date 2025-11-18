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

    protected $table = 'llm_manager_usage_logs';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMUsageLogFactory::new();
    }

    protected $fillable = [
        'llm_configuration_id',
        'user_id',
        'extension_slug',
        'prompt',
        'response',
        'parameters_used',
        'metadata', // Alias for parameters_used
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'currency',
        'cost_original',
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
        'currency' => 'string',
        'cost_original' => 'decimal:6',
        'execution_time_ms' => 'integer',
        'executed_at' => 'datetime',
    ];

    /**
     * Accessors
     */
    public function getExecutionTimeSecondsAttribute(): ?float
    {
        return $this->execution_time_ms ? $this->execution_time_ms / 1000 : null;
    }

    public function getMetadataAttribute(): array
    {
        return $this->parameters_used ?? [];
    }

    public function setMetadataAttribute($value): void
    {
        $this->attributes['parameters_used'] = is_array($value) ? json_encode($value) : $value;
    }

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

    /**
     * Set cost with automatic USD conversion if needed
     * 
     * @param float $amount Cost amount
     * @param string $currency Currency code (USD, EUR, GBP, etc.)
     * @param float|null $exchangeRate Exchange rate to USD (if null, uses 1.0 for USD)
     */
    public function setCost(float $amount, string $currency = 'USD', ?float $exchangeRate = null): void
    {
        $this->currency = strtoupper($currency);
        $this->cost_original = $amount;
        
        // If currency is USD, no conversion needed
        if ($currency === 'USD') {
            $this->cost_usd = $amount;
        } else {
            // Use provided exchange rate or fetch from config/API
            $rate = $exchangeRate ?? $this->getExchangeRate($currency);
            $this->cost_usd = $amount * $rate;
        }
    }

    /**
     * Get exchange rate for currency (placeholder - can be extended with API)
     */
    protected function getExchangeRate(string $currency): float
    {
        // TODO: Implement actual exchange rate API (e.g., exchangerate-api.com)
        // For now, return default rates from config
        $rates = config('llm-manager.exchange_rates', [
            'USD' => 1.0,
            'EUR' => 1.08,
            'GBP' => 1.25,
            'MXN' => 0.05,
            'CAD' => 0.73,
            'JPY' => 0.0067,
            'CNY' => 0.14,
            'INR' => 0.012,
            'BRL' => 0.20,
        ]);

        return $rates[$currency] ?? 1.0;
    }
}
