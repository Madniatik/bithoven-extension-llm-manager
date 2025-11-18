<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMConversationMessageFactory extends Factory
{
    protected $model = LLMConversationMessage::class;

    public function definition(): array
    {
        $role = fake()->randomElement(['user', 'assistant', 'system']);
        
        return [
            'session_id' => LLMConversationSession::factory(),
            'role' => $role,
            'content' => $role === 'user' ? fake()->sentence() : fake()->paragraph(),
            'metadata' => [],
            'tokens' => fake()->numberBetween(10, 500),
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'tokens' => fake()->numberBetween(10, 100),
        ]);
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
            'tokens' => fake()->numberBetween(50, 500),
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'system',
            'tokens' => fake()->numberBetween(5, 50),
        ]);
    }
}
