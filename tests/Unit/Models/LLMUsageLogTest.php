<?php

namespace Bithoven\LLMManager\Tests\Unit\Models;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMUsageLogTest extends TestCase
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
    public function it_can_set_cost_in_usd()
    {
        $log = new LLMUsageLog([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-1',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);

        $log->setCost(0.05, 'USD');

        $this->assertEquals('USD', $log->currency);
        $this->assertEquals(0.05, $log->cost_original);
        $this->assertEquals(0.05, $log->cost_usd);
    }

    /** @test */
    public function it_can_set_cost_in_eur_with_auto_conversion()
    {
        $log = new LLMUsageLog([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-2',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);

        // EUR rate from config: 1.08
        $log->setCost(0.05, 'EUR');

        $this->assertEquals('EUR', $log->currency);
        $this->assertEquals(0.05, $log->cost_original);
        $this->assertEquals(0.054, $log->cost_usd); // 0.05 * 1.08
    }

    /** @test */
    public function it_can_set_cost_with_explicit_exchange_rate()
    {
        $log = new LLMUsageLog([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-3',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);

        $log->setCost(100, 'JPY', 0.0067);

        $this->assertEquals('JPY', $log->currency);
        $this->assertEquals(100, $log->cost_original);
        $this->assertEquals(0.67, $log->cost_usd); // 100 * 0.0067
    }

    /** @test */
    public function it_uses_default_exchange_rate_for_unknown_currency()
    {
        $log = new LLMUsageLog([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-4',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
        ]);

        $log->setCost(0.05, 'XYZ'); // Unknown currency

        $this->assertEquals('XYZ', $log->currency);
        $this->assertEquals(0.05, $log->cost_original);
        $this->assertEquals(0.05, $log->cost_usd); // Default rate 1.0
    }

    /** @test */
    public function it_belongs_to_llm_configuration()
    {
        $log = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-5',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'currency' => 'USD',
            'cost_original' => 0.05,
            'cost_usd' => 0.05,
            'execution_time_ms' => 1200,
        ]);

        $this->assertInstanceOf(LLMConfiguration::class, $log->configuration);
        $this->assertEquals($this->configuration->id, $log->configuration->id);
    }

    /** @test */
    public function it_calculates_execution_time_in_seconds()
    {
        $log = new LLMUsageLog([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-6',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'execution_time_ms' => 2500,
        ]);

        $this->assertEquals(2.5, $log->execution_time_seconds);
    }

    /** @test */
    public function it_stores_metadata_as_json()
    {
        $metadata = [
            'temperature' => 0.7,
            'max_tokens' => 500,
            'stream' => false,
        ];

        $log = LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-request-7',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'currency' => 'USD',
            'cost_original' => 0.05,
            'cost_usd' => 0.05,
            'execution_time_ms' => 1200,
            'metadata' => $metadata,
        ]);

        $log->refresh();

        $this->assertEquals($metadata, $log->metadata);
        $this->assertEquals(0.7, $log->metadata['temperature']);
    }

    /** @test */
    public function it_can_filter_by_status()
    {
        LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-success',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'status' => 'success',
            'currency' => 'USD',
            'cost_original' => 0.05,
            'cost_usd' => 0.05,
            'execution_time_ms' => 1200,
        ]);

        LLMUsageLog::create([
            'llm_configuration_id' => $this->configuration->id,
            'request_id' => 'test-error',
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'status' => 'error',
            'error_message' => 'API Error',
            'currency' => 'USD',
            'cost_original' => 0,
            'cost_usd' => 0,
            'execution_time_ms' => 500,
        ]);

        $successLogs = LLMUsageLog::where('status', 'success')->get();
        $errorLogs = LLMUsageLog::where('status', 'error')->get();

        $this->assertCount(1, $successLogs);
        $this->assertCount(1, $errorLogs);
        $this->assertEquals('API Error', $errorLogs->first()->error_message);
    }

    /** @test */
    public function it_supports_all_configured_currencies()
    {
        $currencies = [
            'USD' => 1.0,
            'EUR' => 1.08,
            'GBP' => 1.25,
            'MXN' => 0.05,
            'CAD' => 0.73,
            'JPY' => 0.0067,
            'CNY' => 0.14,
            'INR' => 0.012,
            'BRL' => 0.20,
        ];

        foreach ($currencies as $currency => $expectedRate) {
            $log = new LLMUsageLog([
                'llm_configuration_id' => $this->configuration->id,
                'request_id' => "test-{$currency}",
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
            ]);

            $log->setCost(1.0, $currency);

            $this->assertEquals($currency, $log->currency);
            $this->assertEquals(1.0, $log->cost_original);
            $this->assertEquals($expectedRate, $log->cost_usd);
        }
    }
}
