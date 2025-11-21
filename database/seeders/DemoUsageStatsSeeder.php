<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Models\LLMConfiguration;
use App\Models\User;

class DemoUsageStatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates usage statistics demo data for testing Statistics dashboard
     * Generates logs across different periods, extensions, and configurations
     */
    public function run(): void
    {
        // Get all configurations (expects 3: Ollama, OpenAI, OpenRouter)
        $configurations = LLMConfiguration::all();
        
        if ($configurations->isEmpty()) {
            $this->command->warn('No LLM configurations found. Please run LLMConfigurationSeeder first.');
            return;
        }

        // Get first user (fallback to ID 1 if no auth)
        $userId = User::first()?->id ?? 1;

        // TODAY: Recent usage (last few hours)
        $this->createTodayLogs($configurations->first(), $userId);

        // THIS WEEK: Distributed usage
        if ($configurations->count() > 1) {
            $this->createWeekLogs($configurations->skip(1)->first(), $userId);
        }

        // THIS MONTH: Historical data
        if ($configurations->count() > 2) {
            $this->createMonthLogs($configurations->last(), $userId);
        }

        // Additional logs for different extensions
        $this->createExtensionLogs($configurations->first(), $userId);

        $totalLogs = LLMUsageLog::count();
        $totalCost = LLMUsageLog::sum('cost_usd');
        $totalTokens = LLMUsageLog::sum('total_tokens');

        $this->command->info("✅ Created demo usage statistics:");
        $this->command->info("   - Total Logs: {$totalLogs}");
        $this->command->info("   - Total Cost: $" . number_format($totalCost, 4));
        $this->command->info("   - Total Tokens: " . number_format($totalTokens));
    }

    /**
     * Create logs for today (last 6 hours)
     */
    protected function createTodayLogs(LLMConfiguration $config, int $userId): void
    {
        for ($i = 0; $i < 8; $i++) {
            $executedAt = now()->subHours(rand(0, 6));
            
            LLMUsageLog::create([
                'llm_configuration_id' => $config->id,
                'user_id' => $userId,
                'extension_slug' => 'llm-manager',
                'prompt' => 'Demo prompt for testing statistics',
                'response' => 'Demo response with some content for testing purposes.',
                'parameters_used' => ['temperature' => 0.7, 'max_tokens' => 2000],
                'prompt_tokens' => rand(50, 200),
                'completion_tokens' => rand(100, 500),
                'total_tokens' => rand(150, 700),
                'currency' => 'USD',
                'cost_original' => 0.002,
                'cost_usd' => 0.002,
                'execution_time_ms' => rand(800, 3000),
                'status' => 'success',
                'executed_at' => $executedAt,
                'created_at' => $executedAt,
                'updated_at' => $executedAt,
            ]);
        }
    }

    /**
     * Create logs for this week
     */
    protected function createWeekLogs(LLMConfiguration $config, int $userId): void
    {
        for ($i = 0; $i < 15; $i++) {
            $executedAt = now()->subDays(rand(1, 6))->subHours(rand(0, 23));
            
            LLMUsageLog::create([
                'llm_configuration_id' => $config->id,
                'user_id' => $userId,
                'extension_slug' => rand(0, 1) ? 'tickets' : 'llm-manager',
                'prompt' => 'Weekly demo prompt',
                'response' => 'Weekly demo response for statistics testing.',
                'parameters_used' => ['temperature' => 0.5, 'max_tokens' => 1500],
                'prompt_tokens' => rand(100, 300),
                'completion_tokens' => rand(200, 600),
                'total_tokens' => rand(300, 900),
                'currency' => 'USD',
                'cost_original' => 0.005,
                'cost_usd' => 0.005,
                'execution_time_ms' => rand(1000, 4000),
                'status' => rand(0, 9) < 9 ? 'success' : 'error',
                'error_message' => rand(0, 9) < 9 ? null : 'Simulated API error',
                'executed_at' => $executedAt,
                'created_at' => $executedAt,
                'updated_at' => $executedAt,
            ]);
        }
    }

    /**
     * Create logs for this month
     */
    protected function createMonthLogs(LLMConfiguration $config, int $userId): void
    {
        for ($i = 0; $i < 25; $i++) {
            $executedAt = now()->subDays(rand(7, 29))->subHours(rand(0, 23));
            
            LLMUsageLog::create([
                'llm_configuration_id' => $config->id,
                'user_id' => $userId,
                'extension_slug' => 'dummy',
                'prompt' => 'Monthly demo prompt',
                'response' => 'Monthly demo response for historical statistics.',
                'parameters_used' => ['temperature' => 0.3, 'max_tokens' => 1000],
                'prompt_tokens' => rand(80, 250),
                'completion_tokens' => rand(150, 550),
                'total_tokens' => rand(230, 800),
                'currency' => 'USD',
                'cost_original' => 0.003,
                'cost_usd' => 0.003,
                'execution_time_ms' => rand(900, 3500),
                'status' => 'success',
                'executed_at' => $executedAt,
                'created_at' => $executedAt,
                'updated_at' => $executedAt,
            ]);
        }
    }

    /**
     * Create logs across multiple extensions
     */
    protected function createExtensionLogs(LLMConfiguration $config, int $userId): void
    {
        $extensions = ['llm-manager', 'tickets', 'dummy'];
        
        foreach ($extensions as $ext) {
            for ($i = 0; $i < 3; $i++) {
                $executedAt = now()->subDays(rand(0, 3))->subHours(rand(0, 12));
                
                LLMUsageLog::create([
                    'llm_configuration_id' => $config->id,
                    'user_id' => $userId,
                    'extension_slug' => $ext,
                    'prompt' => "Demo prompt from {$ext} extension",
                    'response' => "Demo response from {$ext} extension.",
                    'parameters_used' => ['temperature' => 0.7, 'max_tokens' => 2048],
                    'prompt_tokens' => rand(100, 200),
                    'completion_tokens' => rand(200, 400),
                    'total_tokens' => rand(300, 600),
                    'currency' => 'USD',
                    'cost_original' => 0.0025,
                    'cost_usd' => 0.0025,
                    'execution_time_ms' => rand(1000, 2500),
                    'status' => 'success',
                    'executed_at' => $executedAt,
                    'created_at' => $executedAt,
                    'updated_at' => $executedAt,
                ]);
            }
        }
    }
}
