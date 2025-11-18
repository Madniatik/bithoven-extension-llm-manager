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
        $config = LLMConfiguration::factory()->create();

        // Create usage logs for current month
        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 25.00,
            'executed_at' => now(),
        ]);

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 30.00,
            'executed_at' => now(),
        ]);

        // Create usage log for previous month (should not be counted)
        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 100.00,
            'executed_at' => now()->subMonth(),
        ]);

        $spending = $this->budgetManager->getMonthlySpending();

        $this->assertEquals(55.00, $spending);
    }

    /** @test */
    public function it_checks_if_budget_exceeded()
    {
        $config = LLMConfiguration::factory()->create();

        // Spending within budget
        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 50.00,
        ]);

        $this->assertFalse($this->budgetManager->isBudgetExceeded());

        // Spending exceeds budget
        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 60.00,
        ]);

        $this->assertTrue($this->budgetManager->isBudgetExceeded());
    }

    /** @test */
    public function it_checks_if_alert_threshold_reached()
    {
        config(['llm-manager.budget.alert_threshold' => 80]);

        $config = LLMConfiguration::factory()->create();

        // Below threshold (79%)
        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 79.00,
        ]);

        $this->assertFalse($this->budgetManager->isAlertThresholdReached());

        // At threshold (80%)
        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 1.00,
        ]);

        $this->assertTrue($this->budgetManager->isAlertThresholdReached());
    }

    /** @test */
    public function it_calculates_remaining_budget()
    {
        $config = LLMConfiguration::factory()->create();

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 35.00,
        ]);

        $remaining = $this->budgetManager->getRemainingBudget();

        $this->assertEquals(65.00, $remaining);
    }

    /** @test */
    public function it_calculates_budget_usage_percentage()
    {
        $config = LLMConfiguration::factory()->create();

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'test',
            'cost_usd' => 45.00,
        ]);

        $percentage = $this->budgetManager->getBudgetUsagePercentage();

        $this->assertEquals(45.0, $percentage);
    }

    /** @test */
    public function it_gets_spending_by_extension()
    {
        $config = LLMConfiguration::factory()->create();

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'extension-a',
            'cost_usd' => 20.00,
        ]);

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'extension-a',
            'cost_usd' => 10.00,
        ]);

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'extension_slug' => 'extension-b',
            'cost_usd' => 15.00,
        ]);

        $spendingByExtension = $this->budgetManager->getSpendingByExtension();

        $this->assertCount(2, $spendingByExtension);
        $this->assertEquals(30.00, $spendingByExtension['extension-a']);
        $this->assertEquals(15.00, $spendingByExtension['extension-b']);
    }
}
