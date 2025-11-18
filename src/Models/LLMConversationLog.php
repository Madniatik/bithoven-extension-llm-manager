<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMConversationLog extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_conversation_logs';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMConversationLogFactory::new();
    }

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'event_type',
        'event_data',
        'tokens_used',
        'cost_usd',
        'execution_time_ms',
        'created_at',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost_usd' => 'decimal:6',
        'execution_time_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (!$log->created_at) {
                $log->created_at = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(LLMConversationSession::class, 'session_id');
    }

    /**
     * Scopes
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeErrors($query)
    {
        return $query->where('event_type', 'error');
    }

    /**
     * Get parsed event data
     */
    public function getParsedEventDataAttribute()
    {
        return json_decode($this->event_data, true);
    }
}
