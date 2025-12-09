<?php

namespace Bithoven\LLMManager\Tests\Feature;

use Bithoven\LLMManager\Tests\TestCase;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

/**
 * Streaming Feature Tests
 * 
 * Tests for Server-Sent Events (SSE) streaming functionality
 * covering Quick Chat streaming endpoint, chunked responses,
 * error handling, and concurrent streams.
 */
class StreamingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected LLMConfiguration $config;
    protected LLMConversationSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create active LLM configuration (Ollama provider for testing)
        $this->config = LLMConfiguration::create([
            'name' => 'Test Ollama Provider',
            'slug' => 'test-ollama-provider',
            'provider' => 'ollama',
            'model' => 'llama2',
            'api_endpoint' => 'http://localhost:11434',
            'is_active' => true,
            'system_instructions' => 'You are a helpful assistant.',
            'parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ],
        ]);

        // Create conversation session
        $this->session = LLMConversationSession::create([
            'session_id' => 'test_session_' . uniqid(),
            'title' => 'Test Quick Chat Session',
            'user_id' => $this->user->id,
            'llm_configuration_id' => $this->config->id,
            'extension_slug' => null, // Quick Chat
        ]);
    }

    /**
     * Test basic streaming endpoint responds with SSE headers
     */
    public function test_streaming_endpoint_returns_sse_headers(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Hello, how are you?',
            'configuration_id' => $this->config->id,
        ]);

        $response->assertOk();
        
        // Verify SSE headers
        $response->assertHeader('Content-Type', 'text/event-stream');
        $response->assertHeader('Cache-Control', 'no-cache');
        $response->assertHeader('X-Accel-Buffering', 'no');
    }

    /**
     * Test SSE events format (data: JSON format)
     */
    public function test_sse_events_have_correct_format(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Test prompt',
            'configuration_id' => $this->config->id,
        ]);

        $content = $response->getContent();
        
        // SSE format: "data: {json}\n\n"
        $this->assertStringContainsString('data: {', $content);
        
        // Extract first data line
        preg_match('/data: ({.+?})\n/s', $content, $matches);
        $this->assertNotEmpty($matches, 'No SSE data events found in response');
        
        $jsonData = $matches[1];
        $decoded = json_decode($jsonData, true);
        
        $this->assertNotNull($decoded, 'SSE data is not valid JSON');
        $this->assertArrayHasKey('type', $decoded, 'SSE event missing type field');
    }

    /**
     * Test metadata event is sent before streaming
     */
    public function test_metadata_event_sent_before_chunks(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Metadata test',
            'configuration_id' => $this->config->id,
        ]);

        $content = $response->getContent();
        
        // Find metadata event
        preg_match('/data: ({[^}]*"type"\s*:\s*"metadata"[^}]*})/s', $content, $matches);
        $this->assertNotEmpty($matches, 'Metadata event not found');
        
        $metadata = json_decode($matches[1], true);
        
        $this->assertEquals('metadata', $metadata['type']);
        $this->assertArrayHasKey('user_message_id', $metadata);
        $this->assertArrayHasKey('user_prompt', $metadata);
        $this->assertArrayHasKey('input_tokens', $metadata);
        $this->assertArrayHasKey('context_size', $metadata);
    }

    /**
     * Test request_data event is emitted for Request Inspector
     */
    public function test_request_data_event_emitted(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Request data test',
            'configuration_id' => $this->config->id,
            'temperature' => 0.8,
            'max_tokens' => 1500,
        ]);

        $content = $response->getContent();
        
        // Find request_data event
        $this->assertStringContainsString('event: request_data', $content);
        
        preg_match('/event: request_data\ndata: ({.+?})\n/s', $content, $matches);
        $this->assertNotEmpty($matches, 'request_data event not found');
        
        $requestData = json_decode($matches[1], true);
        
        $this->assertArrayHasKey('metadata', $requestData);
        $this->assertArrayHasKey('parameters', $requestData);
        $this->assertArrayHasKey('current_prompt', $requestData);
        
        // Verify parameters match request
        $this->assertEquals(0.8, $requestData['parameters']['temperature']);
        $this->assertEquals(1500, $requestData['parameters']['max_tokens']);
        $this->assertEquals('Request data test', $requestData['current_prompt']);
    }

    /**
     * Test streaming sends chunked response
     */
    public function test_streaming_sends_chunks(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Generate chunks',
            'configuration_id' => $this->config->id,
        ]);

        $content = $response->getContent();
        
        // Mock provider sends multiple chunks
        preg_match_all('/data: ({[^}]*"type"\s*:\s*"chunk"[^}]*})/s', $content, $matches);
        
        $this->assertGreaterThan(0, count($matches[0]), 'No chunk events found');
        
        // Verify chunk structure
        $firstChunk = json_decode($matches[1][0], true);
        $this->assertEquals('chunk', $firstChunk['type']);
        $this->assertArrayHasKey('content', $firstChunk);
    }

    /**
     * Test done event is sent after streaming completes
     */
    public function test_done_event_sent_after_completion(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Complete stream test',
            'configuration_id' => $this->config->id,
        ]);

        $content = $response->getContent();
        
        // Find done event
        preg_match('/data: ({[^}]*"type"\s*:\s*"done"[^}]*})/s', $content, $matches);
        $this->assertNotEmpty($matches, 'Done event not found');
        
        $doneEvent = json_decode($matches[1], true);
        
        $this->assertEquals('done', $doneEvent['type']);
        $this->assertArrayHasKey('assistant_message_id', $doneEvent);
        $this->assertArrayHasKey('full_response', $doneEvent);
        $this->assertArrayHasKey('metrics', $doneEvent);
    }

    /**
     * Test streaming saves messages to database
     */
    public function test_streaming_saves_messages_to_database(): void
    {
        $initialCount = LLMConversationMessage::count();

        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Save to DB test',
            'configuration_id' => $this->config->id,
        ]);

        $response->assertOk();

        // Should create 2 messages: user + assistant
        $this->assertEquals($initialCount + 2, LLMConversationMessage::count());

        $userMessage = LLMConversationMessage::where('role', 'user')
            ->where('session_id', $this->session->id)
            ->latest()
            ->first();

        $assistantMessage = LLMConversationMessage::where('role', 'assistant')
            ->where('session_id', $this->session->id)
            ->latest()
            ->first();

        $this->assertNotNull($userMessage);
        $this->assertNotNull($assistantMessage);
        $this->assertEquals('Save to DB test', $userMessage->content);
        $this->assertNotEmpty($assistantMessage->content);
    }

    /**
     * Test error handling when model is offline/unavailable
     */
    public function test_error_handling_when_provider_fails(): void
    {
        // Create invalid configuration (nonexistent provider)
        $invalidConfig = LLMConfiguration::create([
            'name' => 'Invalid Provider',
            'slug' => 'invalid-provider',
            'provider' => 'nonexistent-provider',
            'model' => 'invalid-model',
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Error test',
            'configuration_id' => $invalidConfig->id,
        ]);

        $content = $response->getContent();
        
        // Should contain error event
        preg_match('/data: ({[^}]*"type"\s*:\s*"error"[^}]*})/s', $content, $matches);
        $this->assertNotEmpty($matches, 'Error event not found');
        
        $errorEvent = json_decode($matches[1], true);
        
        $this->assertEquals('error', $errorEvent['type']);
        $this->assertArrayHasKey('message', $errorEvent);
    }

    /**
     * Test context limit parameter
     */
    public function test_context_limit_parameter_works(): void
    {
        // Create 5 previous messages
        for ($i = 1; $i <= 5; $i++) {
            LLMConversationMessage::create([
                'session_id' => $this->session->id,
                'role' => $i % 2 === 0 ? 'assistant' : 'user',
                'content' => "Message $i",
                'tokens' => 10,
            ]);
        }

        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Context limit test',
            'configuration_id' => $this->config->id,
            'context_limit' => 3, // Only last 3 messages
        ]);

        $content = $response->getContent();
        
        // Extract request_data event
        preg_match('/event: request_data\ndata: ({.+?})\n/s', $content, $matches);
        $requestData = json_decode($matches[1], true);
        
        // Should only include 3 context messages (excluding current prompt)
        $this->assertEquals(3, $requestData['parameters']['actual_context_size']);
    }

    /**
     * Test validation errors are returned properly
     */
    public function test_validation_errors_returned(): void
    {
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            // Missing prompt
            'configuration_id' => $this->config->id,
        ]);

        $response->assertSessionHasErrors(['prompt']);
    }

    /**
     * Test unauthorized access is blocked
     */
    public function test_unauthorized_access_blocked(): void
    {
        // Create another user's session
        $otherUser = User::factory()->create();
        $otherSession = LLMConversationSession::create([
            'session_id' => 'other_session_' . uniqid(),
            'title' => 'Other User Session',
            'user_id' => $otherUser->id,
            'llm_configuration_id' => $this->config->id,
        ]);

        // Try to access other user's session
        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $otherSession->id,
            'prompt' => 'Unauthorized test',
            'configuration_id' => $this->config->id,
        ]);

        // Should fail validation (session doesn't exist for this user)
        $response->assertSessionHasErrors(['session_id']);
    }

    /**
     * Test stop streaming functionality
     */
    public function test_stop_streaming_endpoint(): void
    {
        // Create a user message
        $userMessage = LLMConversationMessage::create([
            'session_id' => $this->session->id,
            'role' => 'user',
            'content' => 'Test stop',
            'tokens' => 2,
        ]);

        $response = $this->post(route('admin.llm.quick-chat.stop'), [
            'session_id' => $this->session->id,
            'user_message_id' => $userMessage->id,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // User message should be deleted
        $this->assertDatabaseMissing('llm_manager_conversation_messages', [
            'id' => $userMessage->id,
        ]);
    }

    /**
     * Test concurrent streams are handled correctly
     * 
     * Note: This is a simplified test. Real concurrent streams
     * would require async testing or browser automation.
     */
    public function test_multiple_sessions_dont_interfere(): void
    {
        // Create second session
        $session2 = LLMConversationSession::create([
            'session_id' => 'test_session_2_' . uniqid(),
            'title' => 'Test Session 2',
            'user_id' => $this->user->id,
            'llm_configuration_id' => $this->config->id,
        ]);

        // Stream to session 1
        $response1 = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Session 1 message',
            'configuration_id' => $this->config->id,
        ]);

        // Stream to session 2
        $response2 = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $session2->id,
            'prompt' => 'Session 2 message',
            'configuration_id' => $this->config->id,
        ]);

        $response1->assertOk();
        $response2->assertOk();

        // Both sessions should have their own messages
        $session1Messages = LLMConversationMessage::where('session_id', $this->session->id)->count();
        $session2Messages = LLMConversationMessage::where('session_id', $session2->id)->count();

        $this->assertEquals(2, $session1Messages); // user + assistant
        $this->assertEquals(2, $session2Messages); // user + assistant
    }

    /**
     * Test session last_activity_at is updated on stream
     */
    public function test_session_activity_updated_on_stream(): void
    {
        $originalActivity = $this->session->last_activity_at;
        
        sleep(1); // Ensure time difference

        $response = $this->post(route('admin.llm.quick-chat.stream'), [
            'session_id' => $this->session->id,
            'prompt' => 'Activity test',
            'configuration_id' => $this->config->id,
        ]);

        $this->session->refresh();
        
        $this->assertGreaterThan(
            $originalActivity->timestamp,
            $this->session->last_activity_at->timestamp
        );
    }
}
