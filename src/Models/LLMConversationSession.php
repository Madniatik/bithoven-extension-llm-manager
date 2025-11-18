<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Illuminate\Support\Str;

class LLMConversationSession extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_conversation_sessions';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMConversationSessionFactory::new();
    }

    protected $fillable = [
        'session_id',
        'user_id',
        'extension_slug',
        'llm_configuration_id',
        'title',
        'metadata',
        'started_at',
        'last_activity_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (!$session->session_id) {
                $session->session_id = Str::uuid();
            }
            if (!$session->expires_at) {
                $ttl = config('llm-manager.conversations.session_ttl', 3600);
                $session->expires_at = now()->addSeconds($ttl);
            }
        });
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(LLMConfiguration::class, 'llm_configuration_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LLMConversationMessage::class, 'session_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LLMConversationLog::class, 'session_id');
    }

    public function toolExecutions(): HasMany
    {
        return $this->hasMany(LLMToolExecution::class, 'session_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeByExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeForExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Extend session expiration
     */
    public function extend(int $seconds = null): void
    {
        $seconds = $seconds ?? config('llm-manager.conversations.session_ttl', 3600);
        $this->expires_at = now()->addSeconds($seconds);
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Get message count
     */
    public function getMessageCountAttribute(): int
    {
        return $this->messages()->count();
    }

    /**
     * Get total tokens used
     */
    public function getTotalTokensAttribute(): int
    {
        return $this->messages()->sum('tokens') ?? 0;
    }

    /**
     * Get total tokens (method for backward compatibility)
     */
    public function totalTokens(): int
    {
        return $this->messages()->sum('tokens') ?? 0;
    }

    /**
     * End session
     */
    public function endSession(): void
    {
        $this->is_active = false;
        $this->save();
    }
}

