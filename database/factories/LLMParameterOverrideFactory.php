<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMParameterOverride;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMParameterOverrideFactory extends Factory
{
    protected $model = LLMParameterOverride::class;

    public function definition(): array
    {
        return [
            'llm_configuration_id' => LLMConfiguration::factory(),
            'extension_slug' => fake()->randomElement(['llm-manager', 'tickets', null]),
            'context' => fake()->randomElement(['chat', 'analysis', 'generation']),
            'overrides' => [
                'temperature' => fake()->randomFloat(1, 0, 1),
                'max_tokens' => fake()->numberBetween(512, 4096),
            ],
            'is_active' => true,
        ];
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension_slug' => null,
            'context' => null,
        ]);
    }

    public function forContext(string $context): static
    {
        return $this->state(fn (array $attributes) => [
            'context' => $context,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
