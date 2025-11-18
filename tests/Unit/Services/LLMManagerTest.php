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
        $config = LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-default-openai',
        ]);

        config(['llm-manager.default_configuration_id' => $config->id]);

        $defaultConfig = $this->manager->getConfiguration();

        $this->assertEquals($config->id, $defaultConfig->id);
    }

    /** @test */
    public function it_can_get_configuration_by_id()
    {
        $config = LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-test-config-by-id',
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
        $config = LLMConfiguration::factory()->inactive()->create([
            'slug' => 'manager-inactive-config',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LLM configuration is not active');

        $this->manager->getConfiguration($config->id);
    }

    /** @test */
    public function it_resolves_correct_provider()
    {
        $config = LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-openai-provider',
        ]);

        $provider = $this->manager->provider($config);

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
    }

    /** @test */
    public function it_caches_configurations_when_enabled()
    {
        config(['llm-manager.cache.enabled' => true]);
        config(['llm-manager.cache.ttl' => 3600]);

        $config = LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-cached-config',
        ]);

        // First call should cache
        $this->manager->getConfiguration($config->id);

        // Check cache
        $cached = Cache::get("llm_config_{$config->id}");
        $this->assertNotNull($cached);
        $this->assertEquals($config->id, $cached->id);
    }

    /** @test */
    public function it_returns_all_active_configurations()
    {
        LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-active-1',
        ]);

        LLMConfiguration::factory()->anthropic()->create([
            'slug' => 'manager-active-2',
        ]);

        LLMConfiguration::factory()->ollama()->inactive()->create([
            'slug' => 'manager-inactive',
        ]);

        $active = $this->manager->activeConfigurations();

        $this->assertCount(2, $active);
    }

    /** @test */
    public function it_returns_configurations_by_provider()
    {
        LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-openai-1',
            'model' => 'gpt-4',
        ]);

        LLMConfiguration::factory()->openai()->create([
            'slug' => 'manager-openai-2',
            'model' => 'gpt-3.5-turbo',
        ]);

        LLMConfiguration::factory()->anthropic()->create([
            'slug' => 'manager-anthropic',
        ]);

        $openaiConfigs = $this->manager->configurationsByProvider('openai');

        $this->assertCount(2, $openaiConfigs);
        $this->assertTrue($openaiConfigs->every(fn($c) => $c->provider === 'openai'));
    }
}
