<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LLMConversationSessionFactory extends Factory
{
    protected $model = LLMConversationSession::class;

    public function definition(): array
    {
        $startedAt = now()->subHours(fake()->numberBetween(1, 24));
        
        return [
            'session_id' => Str::uuid()->toString(),
            'user_id' => fake()->numberBetween(1, 100),
            'extension_slug' => fake()->randomElement(['llm-manager', 'tickets', null]),
            'llm_configuration_id' => LLMConfiguration::factory(),
            'title' => fake()->sentence(),
            'metadata' => [
                'source' => 'web',
            ],
            'started_at' => $startedAt,
            'last_activity_at' => $startedAt->copy()->addMinutes(fake()->numberBetween(1, 60)),
            'expires_at' => now()->addDays(7),
            'is_active' => true,
        ];
    }

    public function ended(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
                'last_activity_at' => now()->subHours(1),
            ];
        });
    }
}
