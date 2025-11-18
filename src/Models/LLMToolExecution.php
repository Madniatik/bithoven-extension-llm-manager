<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMToolExecution extends Model
{
    use HasFactory;

    protected $table = 'llm_tool_executions';

    protected $fillable = [
        'tool_definition_id',
        'usage_log_id',
        'session_id',
        'input_parameters',
        'output_result',
        'status',
        'error_message',
        'execution_time_ms',
        'executed_at',
    ];

    protected $casts = [
        'input_parameters' => 'array',
        'execution_time_ms' => 'integer',
        'executed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function toolDefinition(): BelongsTo
    {
        return $this->belongsTo(LLMToolDefinition::class, 'tool_definition_id');
    }

    public function usageLog(): BelongsTo
    {
        return $this->belongsTo(LLMUsageLog::class, 'usage_log_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LLMConversationSession::class, 'session_id');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('executed_at', '>=', now()->subHours($hours));
    }

    /**
     * Mark as running
     */
    public function markAsRunning(): void
    {
        $this->status = 'running';
        $this->save();
    }

    /**
     * Mark as successful
     */
    public function markAsSuccessful(string $output, int $executionTimeMs): void
    {
        $this->status = 'success';
        $this->output_result = $output;
        $this->execution_time_ms = $executionTimeMs;
        $this->save();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage, int $executionTimeMs = null): void
    {
        $this->status = 'error';
        $this->error_message = $errorMessage;
        if ($executionTimeMs) {
            $this->execution_time_ms = $executionTimeMs;
        }
        $this->save();
    }

    /**
     * Get parsed output
     */
    public function getParsedOutputAttribute()
    {
        return json_decode($this->output_result, true) ?? $this->output_result;
    }
}
