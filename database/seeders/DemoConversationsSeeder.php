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

        // Session 1: Laravel Framework Discussion
        $session1 = LLMConversationSession::create([
            'session_id' => 'demo-session-001',
            'extension_slug' => 'llm-manager',
            'llm_configuration_id' => $config->id,
            'title' => 'Laravel Framework Discussion',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        LLMConversationMessage::insert([
            ['session_id' => $session1->id, 'role' => 'user', 'content' => 'What is Laravel?', 'tokens' => 15, 'created_at' => now()],
            ['session_id' => $session1->id, 'role' => 'assistant', 'content' => 'Laravel is a popular PHP framework for web development. It provides elegant syntax and powerful tools for building modern web applications.', 'tokens' => 85, 'created_at' => now()],
            ['session_id' => $session1->id, 'role' => 'user', 'content' => 'What are its main features?', 'tokens' => 20, 'created_at' => now()],
            ['session_id' => $session1->id, 'role' => 'assistant', 'content' => 'Main Laravel features include: Eloquent ORM for database operations, Blade templating engine, built-in authentication, routing system, migrations for database version control, and Artisan CLI for common tasks.', 'tokens' => 230, 'created_at' => now()],
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
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        LLMConversationMessage::insert([
            ['session_id' => $session2->id, 'role' => 'user', 'content' => 'Explain machine learning in simple terms', 'tokens' => 25, 'created_at' => now()],
            ['session_id' => $session2->id, 'role' => 'assistant', 'content' => 'Machine learning is teaching computers to learn from data without explicit programming. Like how you learn from experience, ML algorithms improve their performance as they process more data.', 'tokens' => 155, 'created_at' => now()],
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
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        LLMConversationMessage::insert([
            ['session_id' => $session3->id, 'role' => 'user', 'content' => 'What are RESTful API best practices?', 'tokens' => 30, 'created_at' => now()],
            ['session_id' => $session3->id, 'role' => 'assistant', 'content' => 'RESTful API best practices: Use proper HTTP methods (GET, POST, PUT, DELETE), implement versioning, use meaningful URLs, return appropriate status codes, and provide consistent response formats.', 'tokens' => 140, 'created_at' => now()],
            ['session_id' => $session3->id, 'role' => 'user', 'content' => 'How to handle authentication?', 'tokens' => 25, 'created_at' => now()],
            ['session_id' => $session3->id, 'role' => 'assistant', 'content' => 'Common authentication methods: JWT tokens for stateless auth, OAuth2 for third-party integration, API keys for simple cases, and session-based auth for traditional web apps.', 'tokens' => 120, 'created_at' => now()],
            ['session_id' => $session3->id, 'role' => 'user', 'content' => 'What about security?', 'tokens' => 18, 'created_at' => now()],
            ['session_id' => $session3->id, 'role' => 'assistant', 'content' => 'Security essentials: Always validate input, use HTTPS, implement rate limiting, sanitize output to prevent XSS, use parameterized queries to prevent SQL injection, and keep dependencies updated.', 'tokens' => 187, 'created_at' => now()],
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
