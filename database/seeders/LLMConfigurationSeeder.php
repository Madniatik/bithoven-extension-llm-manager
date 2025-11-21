<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Support\Str;

class LLMConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * IDs 1-5 reserved for Fix Extension compatibility
     */
    public function run(): void
    {
        $configurations = [
            [
                'id' => 1,
                'name' => 'Ollama Llama 3.2',
                'slug' => 'ollama-llama32',
                'provider' => 'ollama',
                'model' => 'llama3.2',
                'api_endpoint' => 'http://localhost:11434',
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
                'is_default' => true,
                'description' => 'Local Llama 3.2 model via Ollama',
            ],
            [
                'id' => 2,
                'name' => 'Ollama Llama 3.2 Vision',
                'slug' => 'ollama-llama32-vision',
                'provider' => 'ollama',
                'model' => 'llama3.2-vision',
                'api_endpoint' => 'http://localhost:11434',
                'api_key' => null,
                'default_parameters' => [
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                    'top_p' => 0.9,
                    'stream' => false,
                ],
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => false,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'description' => 'Local Llama 3.2 Vision model via Ollama',
            ],
            [
                'id' => 3,
                'name' => 'OpenAI GPT-4o',
                'slug' => 'openai-gpt4o',
                'provider' => 'openai',
                'model' => 'gpt-4o',
                'api_endpoint' => 'https://api.openai.com/v1',
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
                'name' => 'Anthropic Claude 3.5 Sonnet',
                'slug' => 'anthropic-claude35-sonnet',
                'provider' => 'anthropic',
                'model' => 'claude-3-5-sonnet-20241022',
                'api_endpoint' => 'https://api.anthropic.com/v1',
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
                'name' => 'Custom LLM',
                'slug' => 'custom-llm',
                'provider' => 'custom',
                'model' => 'custom-model',
                'api_endpoint' => env('CUSTOM_LLM_ENDPOINT'),
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
            LLMConfiguration::updateOrCreate(
                ['id' => $config['id']],
                $config
            );
        }

        $this->command->info('✅ LLM Configurations seeded (IDs 1-5 reserved for Fix Extension)');
    }
}
