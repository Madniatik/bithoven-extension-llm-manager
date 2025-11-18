<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMUsageLogFactory extends Factory
{
    protected $model = LLMUsageLog::class;

    public function definition(): array
    {
        $promptTokens = fake()->numberBetween(100, 2000);
        $completionTokens = fake()->numberBetween(50, 1000);
        $executedAt = now()->subMinutes(fake()->numberBetween(1, 60));
        
        return [
            'llm_configuration_id' => LLMConfiguration::factory(),
            'user_id' => fake()->numberBetween(1, 100),
            'extension_slug' => fake()->randomElement(['llm-manager', 'tickets', 'dummy', null]),
            'prompt' => fake()->paragraph(),
            'response' => fake()->paragraphs(3, true),
            'parameters_used' => [
                'temperature' => 0.7,
                'max_tokens' => 2048,
            ],
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_usd' => fake()->randomFloat(6, 0.0001, 0.05),
            'execution_time_ms' => fake()->numberBetween(500, 5000),
            'status' => 'success',
            'error_message' => null,
            'executed_at' => $executedAt,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'error_message' => fake()->sentence(),
            'response' => null,
            'completion_tokens' => 0,
            'cost_usd' => 0,
        ]);
    }

    public function withCurrency(string $currency, float $rate): static
    {
        // Mantenemos por compatibilidad pero no hace nada porque currency se movió a otra tabla
        return $this->state(fn (array $attributes) => []);
    }
}
