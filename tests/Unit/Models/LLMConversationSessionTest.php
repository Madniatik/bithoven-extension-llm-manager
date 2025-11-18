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
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        $session = LLMConversationSession::create([
            'session_id' => 'test-session-123',
            'configuration_id' => $config->id,
            'extension_slug' => 'test-extension',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('llm_conversation_sessions', [
            'session_id' => 'test-session-123',
            'extension_slug' => 'test-extension',
        ]);
    }

    /** @test */
    public function it_has_configuration_relationship()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        $session = LLMConversationSession::create([
            'session_id' => 'test-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(LLMConfiguration::class, $session->configuration);
        $this->assertEquals($config->id, $session->configuration->id);
    }

    /** @test */
    public function it_has_messages_relationship()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        $session = LLMConversationSession::create([
            'session_id' => 'test-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => true,
        ]);

        LLMConversationMessage::create([
            'session_id' => $session->session_id,
            'role' => 'user',
            'content' => 'Hello',
            'tokens' => 5,
        ]);

        LLMConversationMessage::create([
            'session_id' => $session->session_id,
            'role' => 'assistant',
            'content' => 'Hi there!',
            'tokens' => 10,
        ]);

        $this->assertCount(2, $session->messages);
    }

    /** @test */
    public function it_calculates_total_tokens()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        $session = LLMConversationSession::create([
            'session_id' => 'test-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => true,
        ]);

        LLMConversationMessage::create([
            'session_id' => $session->session_id,
            'role' => 'user',
            'content' => 'Hello',
            'tokens' => 100,
        ]);

        LLMConversationMessage::create([
            'session_id' => $session->session_id,
            'role' => 'assistant',
            'content' => 'Hi!',
            'tokens' => 50,
        ]);

        $this->assertEquals(150, $session->totalTokens());
    }

    /** @test */
    public function it_calculates_total_cost()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        $session = LLMConversationSession::create([
            'session_id' => 'test-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => true,
        ]);

        LLMConversationMessage::create([
            'session_id' => $session->session_id,
            'role' => 'user',
            'content' => 'Hello',
            'tokens' => 100,
            'cost' => 0.002,
        ]);

        LLMConversationMessage::create([
            'session_id' => $session->session_id,
            'role' => 'assistant',
            'content' => 'Hi!',
            'tokens' => 50,
            'cost' => 0.001,
        ]);

        $this->assertEquals(0.003, $session->totalCost());
    }

    /** @test */
    public function scope_active_returns_only_active_sessions()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConversationSession::create([
            'session_id' => 'active-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => true,
        ]);

        LLMConversationSession::create([
            'session_id' => 'inactive-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => false,
        ]);

        $activeSessions = LLMConversationSession::active()->get();

        $this->assertCount(1, $activeSessions);
        $this->assertEquals('active-session', $activeSessions->first()->session_id);
    }

    /** @test */
    public function scope_for_extension_filters_by_extension()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConversationSession::create([
            'session_id' => 'ext-a-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'extension-a',
            'is_active' => true,
        ]);

        LLMConversationSession::create([
            'session_id' => 'ext-b-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'extension-b',
            'is_active' => true,
        ]);

        $extASessions = LLMConversationSession::forExtension('extension-a')->get();

        $this->assertCount(1, $extASessions);
        $this->assertEquals('extension-a', $extASessions->first()->extension_slug);
    }

    /** @test */
    public function it_ends_session_correctly()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        $session = LLMConversationSession::create([
            'session_id' => 'test-session',
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'is_active' => true,
        ]);

        $session->endSession();

        $this->assertFalse($session->fresh()->is_active);
        $this->assertNotNull($session->fresh()->ended_at);
    }
}
