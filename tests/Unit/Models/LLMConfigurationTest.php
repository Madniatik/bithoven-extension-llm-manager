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
            'provider' => 'openai',
            'model' => 'gpt-4',
            'parameters' => ['temperature' => 0.7, 'max_tokens' => 1000],
            'is_active' => true,
        ]);

        $this->assertIsArray($config->parameters);
        $this->assertEquals(0.7, $config->parameters['temperature']);
    }

    /** @test */
    public function it_has_usage_logs_relationship()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test-extension',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 0.005,
            'response_time' => 1.5,
        ]);

        $this->assertCount(1, $config->usageLogs);
    }

    /** @test */
    public function scope_active_returns_only_active_configurations()
    {
        LLMConfiguration::create([
            'name' => 'Active Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Inactive Config',
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
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Anthropic Config',
            'provider' => 'anthropic',
            'model' => 'claude-3',
            'is_active' => true,
        ]);

        $openaiConfigs = LLMConfiguration::forProvider('openai')->get();

        $this->assertCount(1, $openaiConfigs);
        $this->assertEquals('openai', $openaiConfigs->first()->provider);
    }

    /** @test */
    public function it_calculates_total_cost()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test-extension',
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'total_tokens' => 150,
            'cost' => 0.005,
            'response_time' => 1.5,
        ]);

        LLMUsageLog::create([
            'configuration_id' => $config->id,
            'extension_slug' => 'test-extension',
            'prompt_tokens' => 200,
            'completion_tokens' => 100,
            'total_tokens' => 300,
            'cost' => 0.010,
            'response_time' => 2.0,
        ]);

        $this->assertEquals(0.015, $config->totalCost());
    }

    /** @test */
    public function it_calculates_total_requests()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMUsageLog::factory()->count(5)->create([
            'configuration_id' => $config->id,
        ]);

        $this->assertEquals(5, $config->totalRequests());
    }
}
