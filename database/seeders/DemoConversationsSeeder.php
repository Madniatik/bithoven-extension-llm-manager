<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Models\LLMConversationLog;
use Bithoven\LLMManager\Models\LLMConfiguration;

class DemoConversationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first configuration (Ollama by default)
        $config = LLMConfiguration::first();
        
        if (!$config) {
            $this->command->warn('No LLM configurations found. Please run LLMConfigurationSeeder first.');
            return;
        }

        // Get first user for demo messages
        $demoUser = \App\Models\User::first();
        
        if (!$demoUser) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // Session 1: Laravel Framework Discussion
        $session1 = LLMConversationSession::create([
            'session_id' => 'demo-session-001',
            'extension_slug' => 'llm-manager',
            'llm_configuration_id' => $config->id,
            'title' => 'Laravel Framework Discussion',
            'started_at' => now()->subMinutes(15),
            'last_activity_at' => now()->subMinutes(10),
            'metadata' => [
                'avg_response_time' => 1.87,
                'default_provider' => $config->provider,
                'default_model' => $config->model,
                'total_errors' => 0,
                'total_retries' => 0,
            ],
        ]);

        // Message 1: User question
        $msg1Time = now()->subMinutes(15);
        LLMConversationMessage::create([
            'session_id' => $session1->id,
            'user_id' => $demoUser->id,
            'llm_configuration_id' => $config->id,
            'role' => 'user',
            'content' => 'What is Laravel?',
            'tokens' => 15,
            'created_at' => $msg1Time,
            'sent_at' => $msg1Time,
            'metadata' => [
                'input_tokens' => 15,
                'context_messages_count' => 0,
            ],
        ]);

        // Message 2: Assistant response
        $msg2Start = $msg1Time->copy()->addMilliseconds(120);
        $msg2End = $msg2Start->copy()->addMilliseconds(1850);
        LLMConversationMessage::create([
            'session_id' => $session1->id,
            'llm_configuration_id' => $config->id,
            'role' => 'assistant',
            'content' => 'Laravel is a popular PHP framework for web development. It provides elegant syntax and powerful tools for building modern web applications.',
            'tokens' => 85,
            'response_time' => 1.85,
            'created_at' => $msg2End,
            'started_at' => $msg2Start,
            'completed_at' => $msg2End,
            'metadata' => [
                'input_tokens' => 15,
                'output_tokens' => 70,
                'provider' => $config->provider,
                'model' => $config->model,
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'is_streaming' => true,
                'chunks_count' => 23,
                'time_to_first_chunk' => 0.18,
                'response_time' => 1.85,
                'finish_reason' => 'stop',
                'context_messages_count' => 1,
                'context_size' => 89,
            ],
        ]);

        // Message 3: User follow-up
        $msg3Time = $msg2End->copy()->addSeconds(5);
        LLMConversationMessage::create([
            'session_id' => $session1->id,
            'user_id' => $demoUser->id,
            'llm_configuration_id' => $config->id,
            'role' => 'user',
            'content' => 'What are its main features?',
            'tokens' => 20,
            'created_at' => $msg3Time,
            'sent_at' => $msg3Time,
            'metadata' => [
                'input_tokens' => 20,
                'context_messages_count' => 2,
            ],
        ]);

        // Message 4: Assistant detailed response
        $msg4Start = $msg3Time->copy()->addMilliseconds(145);
        $msg4End = $msg4Start->copy()->addMilliseconds(2100);
        LLMConversationMessage::create([
            'session_id' => $session1->id,
            'llm_configuration_id' => $config->id,
            'role' => 'assistant',
            'content' => 'Main Laravel features include: Eloquent ORM for database operations, Blade templating engine, built-in authentication, routing system, migrations for database version control, and Artisan CLI for common tasks.',
            'tokens' => 230,
            'response_time' => 2.10,
            'created_at' => $msg4End,
            'started_at' => $msg4Start,
            'completed_at' => $msg4End,
            'metadata' => [
                'input_tokens' => 125,
                'output_tokens' => 105,
                'provider' => $config->provider,
                'model' => $config->model,
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'is_streaming' => true,
                'chunks_count' => 35,
                'time_to_first_chunk' => 0.21,
                'response_time' => 2.10,
                'finish_reason' => 'stop',
                'context_messages_count' => 3,
                'context_size' => 567,
            ],
        ]);

        LLMConversationLog::insert([
            ['session_id' => $session1->id, 'event_type' => 'message_sent', 'event_data' => 'User asked: What is Laravel?', 'tokens_used' => 15, 'execution_time_ms' => 125, 'cost_usd' => 0.000050, 'created_at' => now()],
            ['session_id' => $session1->id, 'event_type' => 'response_received', 'event_data' => 'Assistant responded about Laravel framework', 'tokens_used' => 85, 'execution_time_ms' => 1850, 'cost_usd' => 0.000450, 'created_at' => now()],
            ['session_id' => $session1->id, 'event_type' => 'message_sent', 'event_data' => 'User asked: What are its main features?', 'tokens_used' => 20, 'execution_time_ms' => 145, 'cost_usd' => 0.000060, 'created_at' => now()],
            ['session_id' => $session1->id, 'event_type' => 'response_received', 'event_data' => 'Assistant explained Laravel features', 'tokens_used' => 230, 'execution_time_ms' => 2100, 'cost_usd' => 0.001000, 'created_at' => now()],
        ]);

        // Session 2: Machine Learning Basics
        $session2 = LLMConversationSession::create([
            'session_id' => 'demo-session-002',
            'extension_slug' => 'llm-manager',
            'llm_configuration_id' => $config->id,
            'title' => 'Machine Learning Basics',
            'started_at' => now()->subHours(2),
            'last_activity_at' => now()->subHours(1)->subMinutes(55),
            'metadata' => [
                'avg_response_time' => 2.80,
                'default_provider' => $config->provider,
                'default_model' => $config->model,
                'total_errors' => 0,
                'total_retries' => 0,
            ],
        ]);

        // ML Session - Message 1: User question
        $mlMsg1Time = now()->subHours(2);
        LLMConversationMessage::create([
            'session_id' => $session2->id,
            'user_id' => $demoUser->id,
            'llm_configuration_id' => $config->id,
            'role' => 'user',
            'content' => 'Explain machine learning in simple terms',
            'tokens' => 25,
            'created_at' => $mlMsg1Time,
            'sent_at' => $mlMsg1Time,
            'metadata' => [
                'input_tokens' => 25,
                'context_messages_count' => 0,
            ],
        ]);

        // ML Session - Message 2: Assistant response
        $mlMsg2Start = $mlMsg1Time->copy()->addMilliseconds(180);
        $mlMsg2End = $mlMsg2Start->copy()->addMilliseconds(2800);
        LLMConversationMessage::create([
            'session_id' => $session2->id,
            'llm_configuration_id' => $config->id,
            'role' => 'assistant',
            'content' => 'Machine learning is teaching computers to learn from data without explicit programming. Like how you learn from experience, ML algorithms improve their performance as they process more data.',
            'tokens' => 155,
            'response_time' => 2.80,
            'created_at' => $mlMsg2End,
            'started_at' => $mlMsg2Start,
            'completed_at' => $mlMsg2End,
            'metadata' => [
                'input_tokens' => 25,
                'output_tokens' => 130,
                'provider' => $config->provider,
                'model' => $config->model,
                'temperature' => 0.8,
                'max_tokens' => 2000,
                'top_p' => 0.9,
                'is_streaming' => true,
                'chunks_count' => 42,
                'time_to_first_chunk' => 0.25,
                'finish_reason' => 'stop',
                'context_messages_count' => 1,
                'context_size' => 156,
            ],
        ]);

        LLMConversationLog::insert([
            ['session_id' => $session2->id, 'event_type' => 'message_sent', 'event_data' => 'User asked about machine learning', 'tokens_used' => 25, 'execution_time_ms' => 180, 'cost_usd' => 0.000075, 'created_at' => now()],
            ['session_id' => $session2->id, 'event_type' => 'response_received', 'event_data' => 'Assistant explained ML concepts', 'tokens_used' => 155, 'execution_time_ms' => 2800, 'cost_usd' => 0.000725, 'created_at' => now()],
        ]);

        // Session 3: Web Development Best Practices
        $session3 = LLMConversationSession::create([
            'session_id' => 'demo-session-003',
            'extension_slug' => 'llm-manager',
            'llm_configuration_id' => $config->id,
            'title' => 'Web Development Best Practices',
            'started_at' => now()->subDay(),
            'last_activity_at' => now()->subDay()->addMinutes(12),
            'metadata' => [
                'avg_response_time' => 2.18,
                'default_provider' => $config->provider,
                'default_model' => $config->model,
                'total_errors' => 0,
                'total_retries' => 0,
            ],
        ]);

        // Web Dev Session - Message 1: User asks about REST
        $webMsg1Time = now()->subDay();
        LLMConversationMessage::create([
            'session_id' => $session3->id,
            'user_id' => $demoUser->id,
            'llm_configuration_id' => $config->id,
            'role' => 'user',
            'content' => 'What are RESTful API best practices?',
            'tokens' => 30,
            'created_at' => $webMsg1Time,
            'sent_at' => $webMsg1Time,
            'metadata' => [
                'input_tokens' => 30,
                'context_messages_count' => 0,
            ],
        ]);

        // Web Dev Session - Message 2: Assistant REST response
        $webMsg2Start = $webMsg1Time->copy()->addMilliseconds(165);
        $webMsg2End = $webMsg2Start->copy()->addMilliseconds(2200);
        LLMConversationMessage::create([
            'session_id' => $session3->id,
            'llm_configuration_id' => $config->id,
            'role' => 'assistant',
            'content' => 'RESTful API best practices: Use proper HTTP methods (GET, POST, PUT, DELETE), implement versioning, use meaningful URLs, return appropriate status codes, and provide consistent response formats.',
            'tokens' => 140,
            'response_time' => 2.20,
            'created_at' => $webMsg2End,
            'started_at' => $webMsg2Start,
            'completed_at' => $webMsg2End,
            'metadata' => [
                'input_tokens' => 30,
                'output_tokens' => 110,
                'provider' => $config->provider,
                'model' => $config->model,
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'is_streaming' => true,
                'chunks_count' => 38,
                'time_to_first_chunk' => 0.19,
                'finish_reason' => 'stop',
                'context_messages_count' => 1,
                'context_size' => 234,
            ],
        ]);

        // Web Dev Session - Message 3: User asks about auth
        $webMsg3Time = $webMsg2End->copy()->addSeconds(8);
        LLMConversationMessage::create([
            'session_id' => $session3->id,
            'user_id' => $demoUser->id,
            'llm_configuration_id' => $config->id,
            'role' => 'user',
            'content' => 'How to handle authentication?',
            'tokens' => 25,
            'created_at' => $webMsg3Time,
            'sent_at' => $webMsg3Time,
            'metadata' => [
                'input_tokens' => 25,
                'context_messages_count' => 2,
            ],
        ]);

        // Web Dev Session - Message 4: Assistant auth response
        $webMsg4Start = $webMsg3Time->copy()->addMilliseconds(145);
        $webMsg4End = $webMsg4Start->copy()->addMilliseconds(1900);
        LLMConversationMessage::create([
            'session_id' => $session3->id,
            'llm_configuration_id' => $config->id,
            'role' => 'assistant',
            'content' => 'Common authentication methods: JWT tokens for stateless auth, OAuth2 for third-party integration, API keys for simple cases, and session-based auth for traditional web apps.',
            'tokens' => 120,
            'response_time' => 1.90,
            'created_at' => $webMsg4End,
            'started_at' => $webMsg4Start,
            'completed_at' => $webMsg4End,
            'metadata' => [
                'input_tokens' => 195,
                'output_tokens' => 95,
                'provider' => $config->provider,
                'model' => $config->model,
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'is_streaming' => true,
                'chunks_count' => 30,
                'time_to_first_chunk' => 0.17,
                'finish_reason' => 'stop',
                'context_messages_count' => 3,
                'context_size' => 678,
            ],
        ]);

        // Web Dev Session - Message 5: User asks about security
        $webMsg5Time = $webMsg4End->copy()->addSeconds(6);
        LLMConversationMessage::create([
            'session_id' => $session3->id,
            'user_id' => $demoUser->id,
            'llm_configuration_id' => $config->id,
            'role' => 'user',
            'content' => 'What about security?',
            'tokens' => 18,
            'created_at' => $webMsg5Time,
            'sent_at' => $webMsg5Time,
            'metadata' => [
                'input_tokens' => 18,
                'context_messages_count' => 4,
            ],
        ]);

        // Web Dev Session - Message 6: Assistant security response
        $webMsg6Start = $webMsg5Time->copy()->addMilliseconds(190);
        $webMsg6End = $webMsg6Start->copy()->addMilliseconds(2450);
        LLMConversationMessage::create([
            'session_id' => $session3->id,
            'llm_configuration_id' => $config->id,
            'role' => 'assistant',
            'content' => 'Security essentials: Always validate input, use HTTPS, implement rate limiting, sanitize output to prevent XSS, use parameterized queries to prevent SQL injection, and keep dependencies updated.',
            'tokens' => 187,
            'response_time' => 2.45,
            'created_at' => $webMsg6End,
            'started_at' => $webMsg6Start,
            'completed_at' => $webMsg6End,
            'metadata' => [
                'input_tokens' => 333,
                'output_tokens' => 154,
                'provider' => $config->provider,
                'model' => $config->model,
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'is_streaming' => true,
                'chunks_count' => 51,
                'time_to_first_chunk' => 0.22,
                'finish_reason' => 'stop',
                'context_messages_count' => 5,
                'context_size' => 1234,
            ],
        ]);

        LLMConversationLog::insert([
            ['session_id' => $session3->id, 'event_type' => 'message_sent', 'event_data' => 'User asked about RESTful API practices', 'tokens_used' => 30, 'execution_time_ms' => 165, 'cost_usd' => 0.000090, 'created_at' => now()],
            ['session_id' => $session3->id, 'event_type' => 'response_received', 'event_data' => 'Assistant explained REST best practices', 'tokens_used' => 140, 'execution_time_ms' => 2200, 'cost_usd' => 0.000650, 'created_at' => now()],
            ['session_id' => $session3->id, 'event_type' => 'message_sent', 'event_data' => 'User asked about authentication', 'tokens_used' => 25, 'execution_time_ms' => 145, 'cost_usd' => 0.000075, 'created_at' => now()],
            ['session_id' => $session3->id, 'event_type' => 'response_received', 'event_data' => 'Assistant explained auth methods', 'tokens_used' => 120, 'execution_time_ms' => 1900, 'cost_usd' => 0.000550, 'created_at' => now()],
            ['session_id' => $session3->id, 'event_type' => 'message_sent', 'event_data' => 'User asked about security', 'tokens_used' => 18, 'execution_time_ms' => 190, 'cost_usd' => 0.000055, 'created_at' => now()],
            ['session_id' => $session3->id, 'event_type' => 'response_received', 'event_data' => 'Assistant explained security essentials', 'tokens_used' => 187, 'execution_time_ms' => 2450, 'cost_usd' => 0.000880, 'created_at' => now()],
        ]);

        $this->command->info('✅ Created 3 demo conversation sessions');
        $this->command->info('   - Session 1: Laravel Framework Discussion (4 messages)');
        $this->command->info('   - Session 2: Machine Learning Basics (2 messages)');
        $this->command->info('   - Session 3: Web Development Best Practices (6 messages)');
    }
}
