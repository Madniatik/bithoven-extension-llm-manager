<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMConversationMessage extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_conversation_messages';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'role',
        'content',
        'metadata',
        'tokens',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tokens' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (!$message->created_at) {
                $message->created_at = now();
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
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }

    /**
     * Get messages in OpenAI format
     */
    public static function formatForOpenAI(int $sessionId, int $limit = null): array
    {
        $query = static::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })->toArray();
    }
}
