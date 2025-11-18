<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMMCPConnector;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMMCPConnectorFactory extends Factory
{
    protected $model = LLMMCPConnector::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'server_type' => fake()->randomElement(['filesystem', 'database', 'laravel', 'code']),
            'connection_url' => 'http://localhost:' . fake()->numberBetween(3000, 4000),
            'authentication' => [
                'type' => 'token',
                'token' => fake()->uuid(),
            ],
            'is_active' => true,
            'last_connected_at' => now()->subHours(fake()->numberBetween(1, 24)),
            'metadata' => [
                'version' => '1.0.0',
            ],
        ];
    }

    public function filesystem(): static
    {
        return $this->state(fn (array $attributes) => [
            'server_type' => 'filesystem',
            'connection_url' => 'http://localhost:3001',
        ]);
    }

    public function database(): static
    {
        return $this->state(fn (array $attributes) => [
            'server_type' => 'database',
            'connection_url' => 'http://localhost:3002',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_connected_at' => null,
        ]);
    }
}
