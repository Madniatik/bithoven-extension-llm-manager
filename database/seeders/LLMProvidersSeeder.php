<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMProvider;

class LLMProvidersSeeder extends Seeder
{
    /**
     * Seed base LLM providers
     */
    public function run(): void
    {
        $providers = [
            [
                'slug' => 'ollama',
                'name' => 'Ollama',
                'package' => 'bithoven/llm-provider-ollama',
                'version' => null, // Set by import command
                'api_endpoint' => 'http://localhost:11434',
                'capabilities' => [
                    'vision' => false,
                    'function_calling' => false,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_installed' => false, // Will be true after first import
                'metadata' => [
                    'type' => 'local',
                    'requires_api_key' => false,
                ],
            ],
            [
                'slug' => 'openai',
                'name' => 'OpenAI',
                'package' => 'bithoven/llm-provider-openai',
                'version' => null,
                'api_endpoint' => 'https://api.openai.com/v1',
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_installed' => false,
                'metadata' => [
                    'type' => 'cloud',
                    'requires_api_key' => true,
                ],
            ],
            [
                'slug' => 'anthropic',
                'name' => 'Anthropic',
                'package' => 'bithoven/llm-provider-anthropic',
                'version' => null,
                'api_endpoint' => 'https://api.anthropic.com/v1',
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => false,
                ],
                'is_active' => true,
                'is_installed' => false,
                'metadata' => [
                    'type' => 'cloud',
                    'requires_api_key' => true,
                ],
            ],
            [
                'slug' => 'openrouter',
                'name' => 'OpenRouter',
                'package' => 'bithoven/llm-provider-openrouter',
                'version' => null,
                'api_endpoint' => 'https://openrouter.ai/api/v1',
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_installed' => false,
                'metadata' => [
                    'type' => 'cloud',
                    'requires_api_key' => true,
                ],
            ],
            [
                'slug' => 'google',
                'name' => 'Google',
                'package' => 'bithoven/llm-provider-google',
                'version' => null,
                'api_endpoint' => 'https://generativelanguage.googleapis.com/v1',
                'capabilities' => [
                    'vision' => true,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => true,
                ],
                'is_active' => true,
                'is_installed' => false,
                'metadata' => [
                    'type' => 'cloud',
                    'requires_api_key' => true,
                ],
            ],
            [
                'slug' => 'cohere',
                'name' => 'Cohere',
                'package' => 'bithoven/llm-provider-cohere',
                'version' => null,
                'api_endpoint' => 'https://api.cohere.ai/v1',
                'capabilities' => [
                    'vision' => false,
                    'function_calling' => true,
                    'streaming' => true,
                    'json_mode' => false,
                ],
                'is_active' => true,
                'is_installed' => false,
                'metadata' => [
                    'type' => 'cloud',
                    'requires_api_key' => true,
                ],
            ],
            [
                'slug' => 'custom',
                'name' => 'Custom',
                'package' => null,
                'version' => null,
                'api_endpoint' => null,
                'capabilities' => [],
                'is_active' => true,
                'is_installed' => true, // Always available
                'metadata' => [
                    'type' => 'custom',
                    'requires_api_key' => false,
                ],
            ],
        ];

        foreach ($providers as $provider) {
            LLMProvider::updateOrCreate(
                ['slug' => $provider['slug']],
                $provider
            );
        }

        $this->command->info('✅ LLM Providers seeded (7 providers: ollama, openai, anthropic, openrouter, google, cohere, custom)');
    }
}
