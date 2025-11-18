<?php

namespace Bithoven\LLMManager\Tests\Unit\Services;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\Providers\OpenAIProvider;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class LLMManagerTest extends TestCase
{
    use RefreshDatabase;

    protected LLMManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(LLMManager::class);
    }

    /** @test */
    public function it_can_get_default_configuration()
    {
        $config = LLMConfiguration::create([
            'name' => 'Default OpenAI',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test',
            'is_active' => true,
        ]);

        config(['llm-manager.default_configuration_id' => $config->id]);

        $defaultConfig = $this->manager->getConfiguration();

        $this->assertEquals($config->id, $defaultConfig->id);
    }

    /** @test */
    public function it_can_get_configuration_by_id()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test',
            'is_active' => true,
        ]);

        $retrievedConfig = $this->manager->getConfiguration($config->id);

        $this->assertEquals($config->id, $retrievedConfig->id);
    }

    /** @test */
    public function it_throws_exception_for_invalid_configuration()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LLM configuration not found');

        $this->manager->getConfiguration(999);
    }

    /** @test */
    public function it_throws_exception_for_inactive_configuration()
    {
        $config = LLMConfiguration::create([
            'name' => 'Inactive Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test',
            'is_active' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LLM configuration is not active');

        $this->manager->getConfiguration($config->id);
    }

    /** @test */
    public function it_resolves_correct_provider()
    {
        $config = LLMConfiguration::create([
            'name' => 'OpenAI Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test',
            'is_active' => true,
        ]);

        $provider = $this->manager->provider($config);

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
    }

    /** @test */
    public function it_throws_exception_for_unsupported_provider()
    {
        $config = LLMConfiguration::create([
            'name' => 'Unknown Provider',
            'provider' => 'unknown-provider',
            'model' => 'test-model',
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported LLM provider: unknown-provider');

        $this->manager->provider($config);
    }

    /** @test */
    public function it_caches_configurations_when_enabled()
    {
        config(['llm-manager.cache.enabled' => true]);
        config(['llm-manager.cache.ttl' => 3600]);

        $config = LLMConfiguration::create([
            'name' => 'Cached Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test',
            'is_active' => true,
        ]);

        // First call - should cache
        $this->manager->getConfiguration($config->id);

        // Second call - should use cache
        $cached = Cache::get("llm_config_{$config->id}");

        $this->assertNotNull($cached);
        $this->assertEquals($config->id, $cached->id);
    }

    /** @test */
    public function it_returns_all_active_configurations()
    {
        LLMConfiguration::create([
            'name' => 'Active 1',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Active 2',
            'provider' => 'anthropic',
            'model' => 'claude-3',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Inactive',
            'provider' => 'ollama',
            'model' => 'llama2',
            'is_active' => false,
        ]);

        $active = $this->manager->activeConfigurations();

        $this->assertCount(2, $active);
    }

    /** @test */
    public function it_returns_configurations_by_provider()
    {
        LLMConfiguration::create([
            'name' => 'OpenAI 1',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'OpenAI 2',
            'provider' => 'openai',
            'model' => 'gpt-3.5-turbo',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Anthropic',
            'provider' => 'anthropic',
            'model' => 'claude-3',
            'is_active' => true,
        ]);

        $openaiConfigs = $this->manager->configurationsByProvider('openai');

        $this->assertCount(2, $openaiConfigs);
        $this->assertTrue($openaiConfigs->every(fn($c) => $c->provider === 'openai'));
    }
}
