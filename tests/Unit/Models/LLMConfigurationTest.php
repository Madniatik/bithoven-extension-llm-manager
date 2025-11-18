<?php

namespace Bithoven\LLMManager\Tests\Unit\Models;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_configuration()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test OpenAI',
            'slug' => 'test-openai',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test123',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('llm_configurations', [
            'name' => 'Test OpenAI',
            'provider' => 'openai',
            'model' => 'gpt-4',
        ]);
    }

    /** @test */
    public function it_encrypts_api_key()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'test-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test123',
            'is_active' => true,
        ]);

        // API key should be accessible
        $this->assertEquals('sk-test123', $config->api_key);

        // But stored encrypted in database
        $this->assertNotEquals('sk-test123', $config->getAttributes()['api_key']);
    }

    /** @test */
    public function it_casts_parameters_to_array()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'test-config-params',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'default_parameters' => ['temperature' => 0.7, 'max_tokens' => 1000],
            'is_active' => true,
        ]);

        $this->assertIsArray($config->default_parameters);
        $this->assertEquals(0.7, $config->default_parameters['temperature']);
    }

    /** @test */
    public function it_has_usage_logs_relationship()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'test-config-rel',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'llm_configuration_id' => $config->id,
            'request_id' => 'test-req-1',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'currency' => 'USD',
            'cost_original' => 0.005,
            'cost_usd' => 0.005,
            'execution_time_ms' => 1500,
        ]);

        $this->assertCount(1, $config->usageLogs);
        $this->assertInstanceOf(LLMUsageLog::class, $config->usageLogs->first());
    }

    /** @test */
    public function scope_active_returns_only_active_configurations()
    {
        LLMConfiguration::create([
            'name' => 'Active Config',
            'slug' => 'active-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Inactive Config',
            'slug' => 'inactive-config',
            'provider' => 'anthropic',
            'model' => 'claude-3',
            'is_active' => false,
        ]);

        $activeConfigs = LLMConfiguration::active()->get();

        $this->assertCount(1, $activeConfigs);
        $this->assertEquals('Active Config', $activeConfigs->first()->name);
    }

    /** @test */
    public function scope_for_provider_filters_by_provider()
    {
        LLMConfiguration::create([
            'name' => 'OpenAI Config',
            'slug' => 'openai-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Anthropic Config',
            'slug' => 'anthropic-config',
            'provider' => 'anthropic',
            'model' => 'claude-3',
            'is_active' => true,
        ]);

        $openaiConfigs = LLMConfiguration::forProvider('openai')->get();

        $this->assertCount(1, $openaiConfigs);
        $this->assertEquals('openai', $openaiConfigs->first()->provider);
    }

    /** @test */
    public function it_calculates_total_cost_with_multi_currency()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'test-config-currency',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'llm_configuration_id' => $config->id,
            'request_id' => 'req-1',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'currency' => 'USD',
            'cost_original' => 0.005,
            'cost_usd' => 0.005,
            'execution_time_ms' => 1500,
        ]);

        LLMUsageLog::create([
            'llm_configuration_id' => $config->id,
            'request_id' => 'req-2',
            'prompt_tokens' => 200,
            'completion_tokens' => 100,
            'total_tokens' => 300,
            'currency' => 'EUR',
            'cost_original' => 0.010,
            'cost_usd' => 0.0108,
            'execution_time_ms' => 2000,
        ]);

        $totalCost = $config->usageLogs()->sum('cost_usd');
        $this->assertEquals(0.0158, $totalCost);
    }

    /** @test */
    public function it_calculates_total_requests()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'test-config-requests',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            LLMUsageLog::create([
                'llm_configuration_id' => $config->id,
                'request_id' => "req-{$i}",
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
                'currency' => 'USD',
                'cost_original' => 0.005,
                'cost_usd' => 0.005,
                'execution_time_ms' => 1500,
            ]);
        }

        $this->assertEquals(5, $config->usageLogs()->count());
    }
}
