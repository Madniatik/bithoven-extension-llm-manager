<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMPromptTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMPromptTemplateFactory extends Factory
{
    protected $model = LLMPromptTemplate::class;

    public function definition(): array
    {
        $variables = ['user_name', 'content'];
        
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'extension_slug' => 'llm-manager',
            'category' => fake()->randomElement(['analysis', 'generation', 'summarization', 'code-review']),
            'template' => 'Hello {{user_name}}, analyze this: {{content}}',
            'variables' => $variables,
            'example_values' => [
                'user_name' => 'John',
                'content' => 'Sample content',
            ],
            'default_parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 1024,
            ],
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension_slug' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withoutVariables(): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => 'Simple template without variables',
            'variables' => [],
            'example_values' => null,
        ]);
    }
}
