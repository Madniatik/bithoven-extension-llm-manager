<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMToolExecution;
use Bithoven\LLMManager\Models\LLMToolDefinition;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMToolExecutionFactory extends Factory
{
    protected $model = LLMToolExecution::class;

    public function definition(): array
    {
        $startedAt = now()->subMinutes(fake()->numberBetween(1, 30));
        
        return [
            'llm_tool_definition_id' => LLMToolDefinition::factory(),
            'llm_conversation_session_id' => LLMConversationSession::factory(),
            'input_parameters' => [
                'input' => fake()->sentence(),
            ],
            'output_result' => [
                'result' => fake()->paragraph(),
                'status' => 'success',
            ],
            'started_at' => $startedAt,
            'completed_at' => $startedAt->copy()->addSeconds(fake()->numberBetween(1, 60)),
            'execution_time_ms' => fake()->numberBetween(100, 5000),
            'status' => 'completed',
            'error_message' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
            'output_result' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
            'execution_time_ms' => null,
            'output_result' => null,
        ]);
    }
}
