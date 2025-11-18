<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMConfigurationFactory extends Factory
{
    protected $model = LLMConfiguration::class;

    public function definition(): array
    {
        $provider = fake()->randomElement(['ollama', 'openai', 'anthropic', 'local', 'custom']);
        
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'provider' => $provider,
            'model' => $this->getModelName($provider),
            'api_endpoint' => $this->getApiUrl($provider),
            'api_key' => in_array($provider, ['ollama', 'local']) ? null : fake()->uuid(),
            'default_parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 2048,
            ],
            'cost_per_1k_input_tokens' => fake()->randomFloat(4, 0, 0.01),
            'cost_per_1k_output_tokens' => fake()->randomFloat(4, 0, 0.03),
            'currency' => 'USD',
            'is_default' => false,
            'is_active' => true,
        ];
    }

    protected function getModelName(string $provider): string
    {
        return match($provider) {
            'ollama' => 'llama3.2',
            'openai' => 'gpt-4o-mini',
            'anthropic' => 'claude-3-sonnet',
            'local' => 'local-llama-7b',
            'custom' => 'custom-model',
            default => 'default-model',
        };
    }

    protected function getApiUrl(string $provider): string
    {
        return match($provider) {
            'ollama' => 'http://localhost:11434',
            'openai' => 'https://api.openai.com/v1',
            'anthropic' => 'https://api.anthropic.com/v1',
            'local' => 'http://localhost:5000',
            default => 'http://localhost:8000/api',
        };
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function ollama(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'ollama',
            'model' => 'llama3.2',
            'api_endpoint' => 'http://localhost:11434',
            'api_key' => null,
        ]);
    }

    public function openai(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'api_endpoint' => 'https://api.openai.com/v1',
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'local',
            'model' => 'local-llama-7b',
            'api_endpoint' => 'http://localhost:5000',
            'api_key' => null,
        ]);
    }
}
