<?php

namespace Bithoven\LLMManager\Tests\Unit\Models;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMConversationSessionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_conversation_session()
    {
        $session = LLMConversationSession::factory()->create([
            'session_id' => 'test-session-123',
            'extension_slug' => 'test-extension',
        ]);

        $this->assertDatabaseHas('llm_manager_conversation_sessions', [
            'session_id' => 'test-session-123',
            'extension_slug' => 'test-extension',
        ]);
    }

    /** @test */
    public function it_has_configuration_relationship()
    {
        $config = LLMConfiguration::factory()->create();

        $session = LLMConversationSession::factory()->create([
            'llm_configuration_id' => $config->id,
        ]);

        $this->assertInstanceOf(LLMConfiguration::class, $session->configuration);
        $this->assertEquals($config->id, $session->configuration->id);
    }

    /** @test */
    public function it_has_messages_relationship()
    {
        $session = LLMConversationSession::factory()->create();

        LLMConversationMessage::factory()->count(2)->create([
            'session_id' => $session->id,
        ]);

        $this->assertCount(2, $session->messages);
    }

    /** @test */
    public function it_calculates_total_tokens()
    {
        $session = LLMConversationSession::factory()->create();

        LLMConversationMessage::factory()->create([
            'session_id' => $session->id,
            'tokens' => 100,
        ]);

        LLMConversationMessage::factory()->create([
            'session_id' => $session->id,
            'tokens' => 50,
        ]);

        $this->assertEquals(150, $session->totalTokens());
    }

    /** @test */
    public function scope_active_returns_only_active_sessions()
    {
        LLMConversationSession::factory()->create([
            'session_id' => 'active-session',
        ]);

        LLMConversationSession::factory()->create([
            'session_id' => 'inactive-session',
            'is_active' => false,
        ]);

        $activeSessions = LLMConversationSession::active()->get();

        $this->assertCount(1, $activeSessions);
        $this->assertEquals('active-session', $activeSessions->first()->session_id);
    }

    /** @test */
    public function scope_for_extension_filters_by_extension()
    {
        LLMConversationSession::factory()->create([
            'session_id' => 'ext-a-session',
            'extension_slug' => 'extension-a',
        ]);

        LLMConversationSession::factory()->create([
            'session_id' => 'ext-b-session',
            'extension_slug' => 'extension-b',
        ]);

        $extASessions = LLMConversationSession::forExtension('extension-a')->get();

        $this->assertCount(1, $extASessions);
        $this->assertEquals('extension-a', $extASessions->first()->extension_slug);
    }

    /** @test */
    public function it_ends_session_correctly()
    {
        $session = LLMConversationSession::factory()->create();

        $session->endSession();

        $this->assertFalse($session->fresh()->is_active);
    }
}
