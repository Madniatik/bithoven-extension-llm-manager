<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Bithoven\LLMManager\Models\LLMProvider;

class LLMProviderConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * IMPORTANTE: Ejecutar LLMProvidersSeeder primero
     * IDs 1-5 reserved for Fix Extension compatibility
     */
    public function run(): void
    {
        // Ensure providers exist
        if (LLMProvider::count() === 0) {
            $this->command->warn('⚠️  No providers found. Running LLMProvidersSeeder first...');
            $this->call(LLMProvidersSeeder::class);
        }

        // Get provider IDs
        $ollama = LLMProvider::where('slug', 'ollama')->first();
        $openai = LLMProvider::where('slug', 'openai')->first();
        $anthropic = LLMProvider::where('slug', 'anthropic')->first();
        $custom = LLMProvider::where('slug', 'custom')->first();

        if (!$ollama || !$openai || !$anthropic || !$custom) {
            $this->command->error('❌ Required providers not found. Cannot seed configurations.');
            return;
        }

        $configurations = [
            [
                'id' => 1,
                'provider_id' => $ollama->id,
                'name' => 'Ollama Qwen 3',
                'slug' => 'ollama-qwen3',
                'model' => 'qwen3:4b',
                'api_key' => null,
                'default_parameters' => [
                    'temperature' => 0.7,
                    'max_tokens' => 8000,
                    'top_p' => 0.9,
                    'stream' => false,
                ],
                'capabilities' => [
                    'vision' => false,
                    'function_calling' => false,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_default' => true,
                'description' => 'Local Qwen 3 4B model via Ollama',
            ],
            [
                'id' => 2,
                'provider_id' => $ollama->id,
                'name' => 'Ollama DeepSeek Coder',
                'slug' => 'ollama-deepseek-coder',
                'model' => 'deepseek-coder:6.7b',
                'api_key' => null,
                'default_parameters' => [
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                    'top_p' => 0.9,
                    'stream' => false,
                ],
                'capabilities' => [
                    'vision' => false,
                    'function_calling' => false,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'description' => 'Local DeepSeek Coder 6.7B model via Ollama',
            ],
            [
                'id' => 3,
                'provider_id' => $openai->id,
                'name' => 'OpenAI GPT-4o',
                'slug' => 'openai-gpt4o',
                'model' => 'gpt-4o',
                'api_key' => env('OPENAI_API_KEY'),
                'default_parameters' => [
                    'temperature' => 0.7,
                    'max_tokens' => 4096,
                    'top_p' => 1,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0,
                ],
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => false,
                'is_default' => false,
                'description' => 'OpenAI GPT-4o with vision and function calling',
            ],
            [
                'id' => 4,
                'provider_id' => $anthropic->id,
                'name' => 'Anthropic Claude 3.5 Sonnet',
                'slug' => 'anthropic-claude35-sonnet',
                'model' => 'claude-3-5-sonnet-20241022',
                'api_key' => env('ANTHROPIC_API_KEY'),
                'default_parameters' => [
                    'temperature' => 1.0,
                    'max_tokens' => 4096,
                    'top_p' => 1,
                ],
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => false,
                ],
                'is_active' => false,
                'is_default' => false,
                'description' => 'Anthropic Claude 3.5 Sonnet with extended context',
            ],
            [
                'id' => 5,
                'provider_id' => $custom->id,
                'name' => 'Custom LLM',
                'slug' => 'custom-llm',
                'model' => 'custom-model',
                'api_key' => env('CUSTOM_LLM_API_KEY'),
                'default_parameters' => [
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ],
                'capabilities' => [
                    'vision' => false,
                    'function_calling' => false,
                    'streaming' => false,
                    'json_mode' => false,
                ],
                'is_active' => false,
                'is_default' => false,
                'description' => 'Custom LLM endpoint configuration',
            ],
        ];

        foreach ($configurations as $config) {
            LLMProviderConfiguration::updateOrCreate(
                ['id' => $config['id']],
                $config
            );
        }

        $this->command->info('✅ LLM Provider Configurations seeded (IDs 1-5 reserved for Fix Extension)');
    }
}
