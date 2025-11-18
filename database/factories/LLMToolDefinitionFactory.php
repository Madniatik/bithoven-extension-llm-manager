<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMToolDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMToolDefinitionFactory extends Factory
{
    protected $model = LLMToolDefinition::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['function_calling', 'mcp']);
        
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'type' => $type,
            'mcp_connector_id' => $type === 'mcp' ? null : null, // Se puede agregar factory de MCP después
            'function_schema' => [
                'name' => fake()->word(),
                'description' => fake()->sentence(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'input' => [
                            'type' => 'string',
                            'description' => 'Input parameter',
                        ],
                    ],
                    'required' => ['input'],
                ],
            ],
            'handler_class' => $type === 'function_calling' ? 'App\\Tools\\' . fake()->word() . 'Tool' : null,
            'handler_method' => $type === 'function_calling' ? 'handle' : null,
            'validation_rules' => [
                'input' => 'required|string',
            ],
            'security_policy' => null,
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }

    public function functionCalling(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'function_calling',
            'handler_class' => 'App\\Tools\\CustomTool',
            'handler_method' => 'handle',
        ]);
    }

    public function native(): static
    {
        return $this->functionCalling(); // Alias para compatibilidad
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function mcp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mcp',
            'handler_class' => null,
            'handler_method' => null,
        ]);
    }
}
