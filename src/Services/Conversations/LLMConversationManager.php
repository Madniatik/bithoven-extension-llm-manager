<?php

namespace Bithoven\LLMManager\Services\Conversations;

use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Services\LLMExecutor;
use Illuminate\Support\Facades\App;

class LLMConversationManager
{
    /**
     * Create a new conversation session
     */
    public function createSession(
        LLMProviderConfiguration $configuration,
        ?string $extensionSlug = null,
        ?int $userId = null,
        ?string $title = null
    ): LLMConversationSession {
        $session = LLMConversationSession::create([
            'user_id' => $userId,
            'extension_slug' => $extensionSlug,
            'llm_provider_configuration_id' => $configuration->id,
            'title' => $title ?? 'New Conversation',
            'is_active' => true,
        ]);

        // Log session started
        $this->logEvent($session, 'started');

        return $session;
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(
        string $sessionId,
        string $message,
        ?LLMProviderConfiguration $configuration = null
    ): array {
        $session = LLMConversationSession::where('session_id', $sessionId)
            ->active()
            ->firstOrFail();

        // Check if session is expired
        if ($session->isExpired()) {
            throw new \Exception('Conversation session has expired');
        }

        // Use session configuration if not provided
        $configuration = $configuration ?? $session->configuration;

        $startTime = microtime(true);

        // Add user message
        $this->addMessage($session, 'user', $message);

        // Get conversation history
        $messages = $this->getMessagesForContext($session);

        // Check max messages limit
        $maxMessages = config('llm-manager.conversations.max_messages', 50);
        if (count($messages) > $maxMessages) {
            $this->summarizeAndTrim($session);
            $messages = $this->getMessagesForContext($session);
        }

        // Execute LLM request with conversation context
        $executor = App::make(LLMExecutor::class);
        $executor->setConfiguration($configuration);
        $executor->setExtensionSlug($session->extension_slug);

        // Build prompt with context
        $prompt = $this->buildContextualPrompt($messages);

        try {
            $result = $executor->execute($prompt);

            // Add assistant response
            $this->addMessage($session, 'assistant', $result['response'], [
                'usage' => $result['usage'],
                'cost' => $result['cost'],
            ]);

            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            // Log response received
            $this->logEvent($session, 'response_received', json_encode([
                'tokens_used' => $result['usage']['total_tokens'] ?? 0,
                'cost_usd' => $result['cost'],
                'execution_time_ms' => $executionTime,
            ]), $result['usage']['total_tokens'] ?? 0, $result['cost'], $executionTime);

            // Extend session
            $session->extend();

            return [
                'session_id' => $session->session_id,
                'response' => $result['response'],
                'usage' => $result['usage'],
                'cost' => $result['cost'],
                'message_count' => $session->message_count,
            ];

        } catch (\Exception $e) {
            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            // Log error
            $this->logEvent($session, 'error', json_encode([
                'error' => $e->getMessage(),
            ]), 0, 0, $executionTime);

            throw $e;
        }
    }

    /**
     * Add a message to the conversation
     */
    protected function addMessage(
        LLMConversationSession $session,
        string $role,
        string $content,
        ?array $metadata = null
    ): LLMConversationMessage {
        // Estimate tokens (rough approximation: 1 token ≈ 4 chars)
        $tokens = (int) ceil(strlen($content) / 4);

        $message = LLMConversationMessage::create([
            'session_id' => $session->id,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
            'tokens' => $tokens,
        ]);

        // Log message sent
        $this->logEvent($session, 'message_sent', json_encode([
            'role' => $role,
            'tokens' => $tokens,
        ]), $tokens);

        return $message;
    }

    /**
     * Get messages formatted for LLM context
     */
    protected function getMessagesForContext(LLMConversationSession $session): array
    {
        return LLMConversationMessage::formatForOpenAI($session->id);
    }

    /**
     * Build contextual prompt from messages
     */
    protected function buildContextualPrompt(array $messages): string
    {
        // For providers that support chat format, return last message
        // The executor will handle full context
        return end($messages)['content'] ?? '';
    }

    /**
     * Summarize and trim old messages
     */
    protected function summarizeAndTrim(LLMConversationSession $session): void
    {
        $messages = $session->messages()->oldest()->take(10)->get();

        if ($messages->isEmpty()) {
            return;
        }

        // Create summary of old messages
        $summary = "Previous conversation summary:\n";
        foreach ($messages as $message) {
            $summary .= "{$message->role}: " . substr($message->content, 0, 100) . "...\n";
        }

        // Add system message with summary
        $this->addMessage($session, 'system', $summary);

        // Delete old messages
        $session->messages()->whereIn('id', $messages->pluck('id'))->delete();

        // Log summarization
        $this->logEvent($session, 'summarized', json_encode([
            'messages_removed' => $messages->count(),
        ]));
    }

    /**
     * Log conversation event (deprecated - now using usage_logs)
     */
    protected function logEvent(
        LLMConversationSession $session,
        string $eventType,
        ?string $eventData = null,
        ?int $tokensUsed = null,
        ?float $cost = null,
        ?int $executionTime = null
    ): void {
        // Event logging moved to usage_logs table
        // This method is deprecated but kept for backward compatibility
    }

    /**
     * Get session
     */
    public function getSession(string $sessionId): LLMConversationSession
    {
        return LLMConversationSession::where('session_id', $sessionId)->firstOrFail();
    }

    /**
     * End session
     */
    public function endSession(string $sessionId): void
    {
        $session = $this->getSession($sessionId);

        $session->is_active = false;
        $session->save();

        $this->logEvent($session, 'ended');
    }

    /**
     * Get conversation messages
     */
    public function getMessages(string $sessionId): \Illuminate\Database\Eloquent\Collection
    {
        $session = $this->getSession($sessionId);

        return $session->messages()->orderBy('created_at', 'asc')->get();
    }

    /**
     * Export conversation
     */
    public function export(string $sessionId): array
    {
        $session = $this->getSession($sessionId);
        $messages = $this->getMessages($sessionId);

        return [
            'session_id' => $session->session_id,
            'title' => $session->title,
            'started_at' => $session->started_at,
            'message_count' => $messages->count(),
            'total_tokens' => $session->total_tokens,
            'messages' => $messages->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at,
            ])->toArray(),
        ];
    }
}
