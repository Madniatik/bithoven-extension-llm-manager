<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMConversationMessage extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_conversation_messages';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMConversationMessageFactory::new();
    }

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'llm_provider_configuration_id',
        'model', // Snapshot of the actual model used (e.g., "gpt-4", "qwen3:4b")
        'role',
        'content',
        'metadata',
        'raw_response',
        'tokens',
        'response_time',
        'cost_usd',
        'created_at',
        'sent_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'raw_response' => 'array',
        'tokens' => 'integer',
        'response_time' => 'float',
        'cost_usd' => 'float',
        'created_at' => 'datetime',
        'sent_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function providerConfiguration(): BelongsTo
    {
        return $this->belongsTo(LLMProviderConfiguration::class, 'llm_provider_configuration_id');
    }
    
    // Alias for backward compatibility
    public function llmConfiguration(): BelongsTo
    {
        return $this->providerConfiguration();
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
     * Accessors & Helpers
     */
    
    /**
     * Get response time in seconds (null-safe)
     */
    public function getResponseTimeAttribute(): ?float
    {
        try {
            if (!$this->started_at || !$this->completed_at) {
                return null;
            }

            return $this->completed_at->diffInSeconds($this->started_at, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get total tokens (null-safe, from tokens field or metadata)
     */
    public function getTotalTokensAttribute(): int
    {
        try {
            // Prefer dedicated tokens field
            if ($this->tokens !== null && $this->tokens > 0) {
                return $this->tokens;
            }

            // Fallback to metadata
            if (is_array($this->metadata)) {
                $inputTokens = $this->metadata['input_tokens'] ?? 0;
                $outputTokens = $this->metadata['output_tokens'] ?? 0;
                
                return $inputTokens + $outputTokens;
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get provider (null-safe, from metadata or session configuration)
     */
    public function getProviderAttribute(): ?string
    {
        try {
            // First try metadata
            if (is_array($this->metadata) && isset($this->metadata['provider'])) {
                return $this->metadata['provider'];
            }

            // Fallback to session configuration (with null-safe checks)
            if ($this->relationLoaded('session') && 
                $this->session && 
                $this->session->relationLoaded('configuration') && 
                $this->session->configuration) {
                return $this->session->configuration->provider->slug;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get model (null-safe, from database field, metadata, or session configuration)
     */
    public function getModelAttribute($value): ?string
    {
        try {
            // First try database field (primary source)
            if ($value !== null) {
                return $value;
            }

            // Second try metadata
            if (is_array($this->attributes['metadata'] ?? null)) {
                $metadata = json_decode($this->attributes['metadata'], true);
                if (isset($metadata['model'])) {
                    return $metadata['model'];
                }
            }

            // Fallback to session configuration (with null-safe checks)
            if ($this->relationLoaded('session') && 
                $this->session && 
                $this->session->relationLoaded('configuration') && 
                $this->session->configuration) {
                return $this->session->configuration->model;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
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
