<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMConversationLog;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMConversationLogFactory extends Factory
{
    protected $model = LLMConversationLog::class;

    public function definition(): array
    {
        return [
            'llm_configuration_id' => LLMConfiguration::factory(),
            'extension_slug' => fake()->randomElement(['llm-manager', 'tickets', null]),
            'user_id' => fake()->numberBetween(1, 100),
            'conversation_data' => [
                'messages' => [
                    ['role' => 'user', 'content' => fake()->sentence()],
                    ['role' => 'assistant', 'content' => fake()->paragraph()],
                ],
            ],
            'total_tokens' => fake()->numberBetween(100, 2000),
            'total_cost_usd' => fake()->randomFloat(4, 0.001, 0.1),
            'metadata' => [
                'source' => 'web',
            ],
        ];
    }

    public function withMessages(int $count): static
    {
        return $this->state(function (array $attributes) use ($count) {
            $messages = [];
            for ($i = 0; $i < $count; $i++) {
                $messages[] = [
                    'role' => $i % 2 === 0 ? 'user' : 'assistant',
                    'content' => fake()->sentence(),
                ];
            }
            
            return [
                'conversation_data' => ['messages' => $messages],
            ];
        });
    }
}
