<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMPromptTemplate;
use Bithoven\LLMManager\Models\LLMParameterOverride;
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;
use Bithoven\LLMManager\Models\LLMAgentWorkflow;

class LLMDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed demo data for testing and documentation
     */
    public function run(): void
    {
        // IMPORTANT: Ensure core seeders run first
        if (\Bithoven\LLMManager\Models\LLMConfiguration::count() === 0) {
            $this->command->info('⚠️  No configurations found. Running core seeders first...');
            $this->call([
                LLMConfigurationSeeder::class,
                LLMToolDefinitionsSeeder::class,
                LLMMCPConnectorsSeeder::class,
            ]);
        }

        // Demo Prompt Templates
        $templates = [
            [
                'name' => 'Ticket Summary',
                'slug' => 'ticket-summary',
                'extension_slug' => 'tickets',
                'category' => 'summarization',
                'template' => 'Summarize the following support ticket in 2-3 sentences:\n\n{{ticket_content}}',
                'variables' => ['ticket_content'],
                'example_values' => [
                    'ticket_content' => 'My printer is not working. I tried restarting it but still getting error 0x0000001.',
                ],
                'default_parameters' => [
                    'temperature' => 0.3,
                    'max_tokens' => 150,
                ],
                'is_active' => true,
                'description' => 'Generate concise ticket summaries',
            ],
            [
                'name' => 'Code Review',
                'slug' => 'code-review',
                'extension_slug' => 'developer',
                'category' => 'analysis',
                'template' => 'Review this {{language}} code and provide feedback on:\n1. Code quality\n2. Potential bugs\n3. Performance improvements\n\n```{{language}}\n{{code}}\n```',
                'variables' => ['language', 'code'],
                'example_values' => [
                    'language' => 'php',
                    'code' => 'function getUserData($id) { return DB::table("users")->where("id", $id)->first(); }',
                ],
                'default_parameters' => [
                    'temperature' => 0.5,
                    'max_tokens' => 500,
                ],
                'is_active' => true,
                'description' => 'Automated code review assistant',
            ],
        ];

        foreach ($templates as $template) {
            LLMPromptTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        // Demo Parameter Overrides
        // Only create if we have configurations
        if (\Bithoven\LLMManager\Models\LLMConfiguration::where('id', 1)->exists()) {
            $overrides = [
                [
                    'extension_slug' => 'tickets',
                    'llm_configuration_id' => 1,
                    'context' => 'urgent_ticket',
                    'override_parameters' => [
                        'temperature' => 0.2, // More deterministic for urgent tickets
                        'max_tokens' => 300,
                    ],
                    'merge_strategy' => 'merge',
                    'is_active' => true,
                    'priority' => 10,
                    'description' => 'Lower temperature for urgent ticket processing',
                ],
            ];

            foreach ($overrides as $override) {
                LLMParameterOverride::create($override);
            }
        } else {
            $this->command->warn('⚠️  Skipping parameter overrides (no configuration with ID=1)');
        }

        // Demo Knowledge Base Documents
        $documents = [
            [
                'extension_slug' => 'llm',
                'document_type' => 'manual',
                'title' => 'LLM Manager Quick Start',
                'content' => "# LLM Manager Quick Start\n\nThis guide will help you get started with the LLM Manager extension.\n\n## Basic Usage\n\n```php\nuse LLM;\n\n\$result = LLM::generate('What is Laravel?');\necho \$result['response'];\n```\n\n## Using Configurations\n\n```php\n\$result = LLM::config('openai-gpt4o')\n    ->generate('Explain dependency injection');\n```\n\n## Custom Parameters\n\n```php\n\$result = LLM::parameters([\n    'temperature' => 0.8,\n    'max_tokens' => 500,\n])->generate('Write a poem');\n```",
                'content_chunks' => [],
                'embeddings' => null,
                'embedding_model' => null,
                'metadata' => [
                    'source' => 'documentation',
                    'version' => '3.0.0',
                    'author' => 'BITHOVEN Team',
                ],
                'is_indexed' => false,
                'indexed_at' => null,
            ],
        ];

        foreach ($documents as $document) {
            LLMDocumentKnowledgeBase::create($document);
        }

        // Demo Workflow
        if (\Bithoven\LLMManager\Models\LLMConfiguration::where('id', 1)->exists()) {
            $workflows = [
                [
                    'name' => 'Multi-Step Ticket Resolution',
                    'slug' => 'ticket-resolution-workflow',
                    'extension_slug' => 'tickets',
                    'workflow_definition' => [
                        'initial_state' => 'analyze',
                        'states' => [
                            'analyze' => [
                                'type' => 'llm',
                                'prompt' => 'Analyze this ticket and categorize it: {{ticket_content}}',
                                'next' => 'search_kb',
                            ],
                            'search_kb' => [
                                'type' => 'rag',
                                'query_from' => 'analyze.category',
                                'next' => 'generate_response',
                            ],
                            'generate_response' => [
                                'type' => 'llm',
                                'prompt' => 'Generate a response based on KB results: {{kb_results}}',
                                'next' => 'end',
                            ],
                        ],
                        'transitions' => [
                            ['from' => 'analyze', 'to' => 'search_kb', 'condition' => 'category != null'],
                            ['from' => 'search_kb', 'to' => 'generate_response', 'condition' => 'results.length > 0'],
                            ['from' => 'generate_response', 'to' => 'end', 'condition' => 'always'],
                        ],
                    ],
                    'agents_config' => [
                        'analyzer' => ['role' => 'categorize tickets', 'model' => 'ollama-llama32'],
                        'responder' => ['role' => 'generate responses', 'model' => 'openai-gpt4o'],
                    ],
                    'llm_configuration_id' => 1,
                    'max_steps' => 10,
                    'timeout_seconds' => 60,
                    'is_active' => true,
                    'description' => 'Automated ticket resolution using RAG and multi-agent workflow',
                ],
            ];

            foreach ($workflows as $workflow) {
                LLMAgentWorkflow::updateOrCreate(
                    ['slug' => $workflow['slug']],
                    $workflow
                );
            }
        } else {
            $this->command->warn('⚠️  Skipping workflows (no configuration with ID=1)');
        }

        $this->command->info('✅ LLM Demo Data seeded');
        
        // Seed demo conversations for testing
        $this->call(DemoConversationsSeeder::class);
        
        // Seed demo usage statistics for testing
        $this->call(DemoUsageStatsSeeder::class);
    }
}
