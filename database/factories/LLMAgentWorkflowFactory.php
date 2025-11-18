<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMAgentWorkflow;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMAgentWorkflowFactory extends Factory
{
    protected $model = LLMAgentWorkflow::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'llm_configuration_id' => LLMConfiguration::factory(),
            'extension_slug' => 'llm-manager',
            'description' => fake()->sentence(),
            'steps' => [
                [
                    'name' => 'analyze',
                    'action' => 'llm_call',
                    'parameters' => ['prompt' => 'Analyze this'],
                ],
                [
                    'name' => 'generate',
                    'action' => 'llm_call',
                    'parameters' => ['prompt' => 'Generate response'],
                ],
            ],
            'metadata' => [
                'version' => '1.0',
                'author' => fake()->name(),
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function simple(): static
    {
        return $this->state(fn (array $attributes) => [
            'steps' => [
                [
                    'name' => 'execute',
                    'action' => 'llm_call',
                    'parameters' => [],
                ],
            ],
        ]);
    }
}
