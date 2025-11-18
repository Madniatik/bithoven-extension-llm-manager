<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMCustomMetric;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMCustomMetricFactory extends Factory
{
    protected $model = LLMCustomMetric::class;

    public function definition(): array
    {
        return [
            'llm_configuration_id' => LLMConfiguration::factory(),
            'metric_name' => fake()->randomElement(['response_quality', 'hallucination_rate', 'user_satisfaction']),
            'metric_value' => fake()->randomFloat(2, 0, 100),
            'measurement_period' => fake()->randomElement(['hourly', 'daily', 'weekly']),
            'metadata' => [
                'unit' => 'percentage',
                'threshold' => 80,
            ],
        ];
    }

    public function quality(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => 'response_quality',
            'metric_value' => fake()->randomFloat(2, 70, 100),
        ]);
    }

    public function cost(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => 'cost_per_request',
            'metric_value' => fake()->randomFloat(4, 0.001, 0.1),
            'metadata' => [
                'unit' => 'USD',
                'currency' => 'USD',
            ],
        ]);
    }
}
