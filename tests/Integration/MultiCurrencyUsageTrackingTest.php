<?php

namespace Bithoven\LLMManager\Tests\Integration;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiCurrencyUsageTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected LLMConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        
                $this->configuration = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'test-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => encrypt('test-key'),
            'default_parameters' => ['temperature' => 0.7],
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_tracks_usage_in_multiple_currencies()
    {
        // Log usage in USD
        $usdLog = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'usd-request',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);
        $usdLog->setCost(0.05, 'USD');
        $usdLog->save();

        // Log usage in EUR
        $eurLog = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'eur-request',
            'prompt_tokens' => 200,
            'completion_tokens' => 100,
            'total_tokens' => 300,
        ]);
        $eurLog->setCost(0.08, 'EUR');
        $eurLog->save();

        // Log usage in GBP
        $gbpLog = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'gbp-request',
            'prompt_tokens' => 150,
            'completion_tokens' => 75,
            'total_tokens' => 225,
        ]);
        $gbpLog->setCost(0.06, 'GBP');
        $gbpLog->save();

        // Verify all logs saved correctly
        $this->assertEquals(3, $this->configuration->usageLogs()->count());

        // Verify USD conversion
        $this->assertEquals(0.05, $usdLog->fresh()->cost_usd);
        $this->assertEquals(0.0864, $eurLog->fresh()->cost_usd); // 0.08 * 1.08
        $this->assertEquals(0.075, $gbpLog->fresh()->cost_usd); // 0.06 * 1.25
    }

    /** @test */
    public function it_calculates_total_cost_across_currencies()
    {
        // Create usage logs in different currencies
        $currencies = [
            ['currency' => 'USD', 'amount' => 0.10],
            ['currency' => 'EUR', 'amount' => 0.10], // = 0.108 USD
            ['currency' => 'GBP', 'amount' => 0.10], // = 0.125 USD
            ['currency' => 'MXN', 'amount' => 10.00], // = 0.50 USD
        ];

        foreach ($currencies as $index => $data) {
            $log = LLMUsageLog::create([
                'llm_configuration_id' => $this->configuration->id,
                'request_id' => "request-{$index}",
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
            ]);
            $log->setCost($data['amount'], $data['currency']);
            $log->save();
        }

        // Total: 0.10 + 0.108 + 0.125 + 0.50 = 0.833 USD
        $totalCost = $this->configuration->usageLogs()->sum('cost_usd');

        $this->assertEquals(0.833, round($totalCost, 3));
    }

    /** @test */
    public function it_preserves_original_currency_and_amount()
    {
        $log = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'preserve-test',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);
        
        $log->setCost(100.00, 'JPY');
        $log->save();

        $log->refresh();

        $this->assertEquals('JPY', $log->currency);
        $this->assertEquals(100.00, $log->cost_original);
        $this->assertEquals(0.67, $log->cost_usd); // 100 * 0.0067
    }

    /** @test */
    public function it_handles_custom_exchange_rates()
    {
        $log = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'custom-rate',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);
        
        // Use custom exchange rate instead of config default
        $log->setCost(1000, 'JPY', 0.0070); // Custom rate
        $log->save();

        $this->assertEquals(7.00, $log->cost_usd); // 1000 * 0.0070
    }

    /** @test */
    public function it_calculates_statistics_with_multi_currency()
    {
        // Create 10 logs in various currencies
        for ($i = 1; $i <= 10; $i++) {
            $currencies = ['USD', 'EUR', 'GBP'];
            $currency = $currencies[$i % 3];
            
            $log = LLMUsageLog::create([
                'llm_configuration_id' => $this->configuration->id,
                'request_id' => "stat-request-{$i}",
                'prompt_tokens' => 100 * $i,
                'completion_tokens' => 50 * $i,
                'total_tokens' => 150 * $i,
                'execution_time_ms' => 1000 + ($i * 100),
            ]);
            
            $log->setCost(0.01 * $i, $currency);
            $log->save();
        }

        $stats = (object) [
            'total_requests' => $this->configuration->usageLogs()->count(),
            'total_cost' => $this->configuration->usageLogs()->sum('cost_usd'),
            'total_tokens' => $this->configuration->usageLogs()->sum('total_tokens'),
            'avg_execution_time' => $this->configuration->usageLogs()->avg('execution_time_ms'),
        ];

        $this->assertEquals(10, $stats->total_requests);
        $this->assertGreaterThan(0, $stats->total_cost);
        $this->assertEquals(8250, $stats->total_tokens); // Sum of 150*1 + 150*2 + ... + 150*10
        $this->assertGreaterThan(1000, $stats->avg_execution_time);
    }

    /** @test */
    public function it_filters_logs_by_currency()
    {
        // Create logs in different currencies
        for ($i = 1; $i <= 5; $i++) {
            $log = LLMUsageLog::create([
                'llm_configuration_id' => $this->configuration->id,
                'request_id' => "usd-{$i}",
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
            ]);
            $log->setCost(0.05, 'USD');
            $log->save();
        }

        for ($i = 1; $i <= 3; $i++) {
            $log = LLMUsageLog::create([
                'llm_configuration_id' => $this->configuration->id,
                'request_id' => "eur-{$i}",
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
            ]);
            $log->setCost(0.05, 'EUR');
            $log->save();
        }

        $usdLogs = LLMUsageLog::where('currency', 'USD')->get();
        $eurLogs = LLMUsageLog::where('currency', 'EUR')->get();

        $this->assertCount(5, $usdLogs);
        $this->assertCount(3, $eurLogs);
    }

    /** @test */
    public function it_supports_all_configured_currencies()
    {
        $configuredCurrencies = config('llm-manager.exchange_rates', [
            'USD' => 1.0,
            'EUR' => 1.08,
            'GBP' => 1.25,
            'MXN' => 0.05,
            'CAD' => 0.73,
            'JPY' => 0.0067,
            'CNY' => 0.14,
            'INR' => 0.012,
            'BRL' => 0.20,
        ]);

        foreach ($configuredCurrencies as $currency => $rate) {
            $log = LLMUsageLog::create([
                'llm_configuration_id' => $this->configuration->id,
                'request_id' => "test-{$currency}",
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
            ]);
            
            $log->setCost(10.00, $currency);
            $log->save();

            $this->assertEquals($currency, $log->fresh()->currency);
            $this->assertEquals(10.00, $log->fresh()->cost_original);
            $this->assertEqualsWithDelta(10.00 * $rate, (float)$log->fresh()->cost_usd, 0.0001, "Currency: {$currency}");
        }

        $this->assertEquals(9, LLMUsageLog::count());
    }
}
