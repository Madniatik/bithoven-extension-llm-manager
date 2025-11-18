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
        $config = LLMConfiguration::factory()->openai()->create([
            'name' => 'Test OpenAI',
        ]);

        $this->assertDatabaseHas('llm_manager_configurations', [
            'name' => 'Test OpenAI',
            'provider' => 'openai',
        ]);
    }

    /** @test */
    public function it_encrypts_api_key()
    {
        $config = LLMConfiguration::factory()->create([
            'api_key' => 'sk-test123',
        ]);

        // API key should be accessible
        $this->assertEquals('sk-test123', $config->api_key);

        // But stored encrypted in database
        $this->assertNotEquals('sk-test123', $config->getAttributes()['api_key']);
    }

    /** @test */
    public function it_casts_parameters_to_array()
    {
        $config = LLMConfiguration::factory()->create([
            'default_parameters' => ['temperature' => 0.7, 'max_tokens' => 1000],
        ]);

        $this->assertIsArray($config->default_parameters);
        $this->assertEquals(0.7, $config->default_parameters['temperature']);
    }

    /** @test */
    public function it_has_usage_logs_relationship()
    {
        $config = LLMConfiguration::factory()->create();

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
        ]);

        $this->assertCount(1, $config->usageLogs);
        $this->assertInstanceOf(LLMUsageLog::class, $config->usageLogs->first());
    }

    /** @test */
    public function scope_active_returns_only_active_configurations()
    {
        LLMConfiguration::factory()->create([
            'name' => 'Active Config',
        ]);

        LLMConfiguration::factory()->inactive()->create([
            'name' => 'Inactive Config',
        ]);

        $activeConfigs = LLMConfiguration::active()->get();

        $this->assertCount(1, $activeConfigs);
        $this->assertEquals('Active Config', $activeConfigs->first()->name);
    }

    /** @test */
    public function scope_for_provider_filters_by_provider()
    {
        LLMConfiguration::factory()->openai()->create([
            'name' => 'OpenAI Config',
        ]);

        LLMConfiguration::factory()->anthropic()->create([
            'name' => 'Anthropic Config',
        ]);

        $openaiConfigs = LLMConfiguration::forProvider('openai')->get();

        $this->assertCount(1, $openaiConfigs);
        $this->assertEquals('openai', $openaiConfigs->first()->provider);
    }

    /** @test */
    public function it_calculates_total_cost_with_multi_currency()
    {
        $config = LLMConfiguration::factory()->create();

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'currency' => 'USD',
            'cost_original' => 0.005,
            'cost_usd' => 0.005,
        ]);

        LLMUsageLog::factory()->create([
            'llm_configuration_id' => $config->id,
            'currency' => 'EUR',
            'cost_original' => 0.010,
            'cost_usd' => 0.0108,
        ]);

        $totalCost = $config->usageLogs()->sum('cost_usd');
        $this->assertEquals(0.0158, $totalCost);
    }

    /** @test */
    public function it_calculates_total_requests()
    {
        $config = LLMConfiguration::factory()->create();

        LLMUsageLog::factory()->count(5)->create([
            'llm_configuration_id' => $config->id,
        ]);

        $this->assertEquals(5, $config->usageLogs()->count());
    }
}
