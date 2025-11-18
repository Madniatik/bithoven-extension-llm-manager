<?php

namespace Bithoven\LLMManager\Tests\Unit\Services;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Services\LLMBudgetManager;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMBudgetManagerTest extends TestCase
{
    use RefreshDatabase;

    protected LLMBudgetManager $budgetManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->budgetManager = app(LLMBudgetManager::class);
        
        config(['llm-manager.budget.monthly_limit' => 100.00]);
        config(['llm-manager.budget.alert_threshold' => 80]);
    }

    /** @test */
    public function it_calculates_monthly_spending_correctly()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        // Create usage logs for current month
        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 25.00,
            'response_time' => 1.0,
            'created_at' => now(),
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 30.00,
            'response_time' => 1.0,
            'created_at' => now(),
        ]);

        // Create usage log for previous month (should not be counted)
        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 100.00,
            'response_time' => 1.0,
            'created_at' => now()->subMonth(),
        ]);

        $spending = $this->budgetManager->getMonthlySpending();

        $this->assertEquals(55.00, $spending);
    }

    /** @test */
    public function it_checks_if_budget_exceeded()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        // Spending within budget
        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 50.00,
            'response_time' => 1.0,
        ]);

        $this->assertFalse($this->budgetManager->isBudgetExceeded());

        // Spending exceeds budget
        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 60.00,
            'response_time' => 1.0,
        ]);

        $this->assertTrue($this->budgetManager->isBudgetExceeded());
    }

    /** @test */
    public function it_checks_if_alert_threshold_reached()
    {
        config(['llm-manager.budget.alert_threshold' => 80]);

        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        // Below threshold (79%)
        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 79.00,
            'response_time' => 1.0,
        ]);

        $this->assertFalse($this->budgetManager->isAlertThresholdReached());

        // At threshold (80%)
        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 1.00,
            'response_time' => 1.0,
        ]);

        $this->assertTrue($this->budgetManager->isAlertThresholdReached());
    }

    /** @test */
    public function it_calculates_remaining_budget()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 35.00,
            'response_time' => 1.0,
        ]);

        $remaining = $this->budgetManager->getRemainingBudget();

        $this->assertEquals(65.00, $remaining);
    }

    /** @test */
    public function it_calculates_budget_usage_percentage()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 45.00,
            'response_time' => 1.0,
        ]);

        $percentage = $this->budgetManager->getBudgetUsagePercentage();

        $this->assertEquals(45.0, $percentage);
    }

    /** @test */
    public function it_gets_spending_by_extension()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'extension-a',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 20.00,
            'response_time' => 1.0,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'extension-a',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 10.00,
            'response_time' => 1.0,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'extension-b',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 15.00,
            'response_time' => 1.0,
        ]);

        $spendingByExtension = $this->budgetManager->getSpendingByExtension();

        $this->assertCount(2, $spendingByExtension);
        $this->assertEquals(30.00, $spendingByExtension['extension-a']);
        $this->assertEquals(15.00, $spendingByExtension['extension-b']);
    }
}
