<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMParameterOverride;
use Bithoven\LLMManager\Models\LLMAgentWorkflow;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;

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
        if (LLMProviderConfiguration::count() === 0) {
            $this->command->info('⚠️  No configurations found. Running core seeders first...');
            $this->call([
                LLMProvidersSeeder::class,
                LLMProviderConfigurationSeeder::class,
                LLMToolDefinitionsSeeder::class,
                LLMMCPConnectorsSeeder::class,
            ]);
        }

        // Demo Parameter Overrides
        // Only create if we have configurations
        if (LLMProviderConfiguration::where('id', 1)->exists()) {
            $overrides = [
                [
                    'extension_slug' => 'tickets',
                    'llm_provider_configuration_id' => 1,
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

        // Demo Workflow
        if (LLMProviderConfiguration::where('id', 1)->exists()) {
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
                    'llm_provider_configuration_id' => 1,
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

        $this->command->info('✅ Demo data seeded:');
        $this->command->info('   - Parameter overrides (context-specific)');
        $this->command->info('   - Agent workflows (example orchestration)');
        
        // Seed demo conversations for testing
        $this->call(DemoConversationsSeeder::class);
        
        // Seed demo usage statistics for testing
        $this->call(DemoUsageStatsSeeder::class);
    }
}
