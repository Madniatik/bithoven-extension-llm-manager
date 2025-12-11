<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMConfigurationServiceTest extends TestCase
{
    use RefreshDatabase;

    private LLMConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LLMConfigurationService();
    }

    /** @test */
    public function it_gets_active_configurations_with_caching()
    {
        // Arrange
        LLMConfiguration::factory()->count(3)->create(['is_active' => true]);
        LLMConfiguration::factory()->create(['is_active' => false]);

        // Act
        $configs = $this->service->getActive();

        // Assert
        $this->assertCount(3, $configs);
        $this->assertTrue(Cache::has('llm.configs.active'));
    }

    /** @test */
    public function it_gets_active_configurations_without_caching()
    {
        // Arrange
        LLMConfiguration::factory()->count(2)->create(['is_active' => true]);

        // Act
        $configs = $this->service->getActive(cached: false);

        // Assert
        $this->assertCount(2, $configs);
    }

    /** @test */
    public function it_finds_configuration_by_id()
    {
        // Arrange
        $config = LLMConfiguration::factory()->create();

        // Act
        $found = $this->service->find($config->id);

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($config->id, $found->id);
    }

    /** @test */
    public function it_returns_null_when_configuration_not_found()
    {
        // Act
        $found = $this->service->find(999);

        // Assert
        $this->assertNull($found);
    }

    /** @test */
    public function it_throws_exception_when_find_or_fail_not_found()
    {
        // Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->service->findOrFail(999);
    }

    /** @test */
    public function it_finds_configuration_by_slug()
    {
        // Arrange
        $config = LLMConfiguration::factory()->create([
            'slug' => 'gpt-4o-mini',
            'is_active' => true
        ]);

        // Act
        $found = $this->service->findBySlug('gpt-4o-mini');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('gpt-4o-mini', $found->slug);
    }

    /** @test */
    public function it_does_not_find_inactive_configuration_by_slug()
    {
        // Arrange
        LLMConfiguration::factory()->create([
            'slug' => 'gpt-4',
            'is_active' => false
        ]);

        // Act
        $found = $this->service->findBySlug('gpt-4');

        // Assert
        $this->assertNull($found);
    }

    /** @test */
    public function it_gets_default_configuration()
    {
        // Arrange
        LLMConfiguration::factory()->create(['is_default' => false]);
        $default = LLMConfiguration::factory()->create(['is_default' => true]);

        // Act
        $found = $this->service->getDefault();

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($default->id, $found->id);
        $this->assertTrue($found->is_default);
    }

    /** @test */
    public function it_gets_configurations_by_provider()
    {
        // Arrange
        LLMConfiguration::factory()->count(2)->create([
            'provider' => 'openai',
            'is_active' => true
        ]);
        LLMConfiguration::factory()->create([
            'provider' => 'anthropic',
            'is_active' => true
        ]);

        // Act
        $openaiConfigs = $this->service->getByProvider('openai');

        // Assert
        $this->assertCount(2, $openaiConfigs);
        $this->assertTrue($openaiConfigs->every(fn($c) => $c->provider === 'openai'));
    }

    /** @test */
    public function it_gets_all_providers()
    {
        // Arrange
        LLMConfiguration::factory()->create(['provider' => 'openai', 'is_active' => true]);
        LLMConfiguration::factory()->create(['provider' => 'anthropic', 'is_active' => true]);
        LLMConfiguration::factory()->create(['provider' => 'ollama', 'is_active' => true]);
        LLMConfiguration::factory()->create(['provider' => 'openai', 'is_active' => true]); // Duplicate

        // Act
        $providers = $this->service->getProviders();

        // Assert
        $this->assertCount(3, $providers); // Distinct
        $this->assertTrue($providers->contains('openai'));
        $this->assertTrue($providers->contains('anthropic'));
        $this->assertTrue($providers->contains('ollama'));
    }

    /** @test */
    public function it_creates_configuration_and_clears_cache()
    {
        // Act
        $config = $this->service->create([
            'name' => 'Test Config',
            'slug' => 'test-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        // Assert
        $this->assertDatabaseHas('llm_manager_configurations', [
            'slug' => 'test-config',
        ]);
        $this->assertFalse(Cache::has('llm.configs.active'));
    }

    /** @test */
    public function it_updates_configuration_and_clears_cache()
    {
        // Arrange
        $config = LLMConfiguration::factory()->create([
            'default_parameters' => ['max_tokens' => 2000]
        ]);

        // Warm cache
        $this->service->getActive();
        $this->assertTrue(Cache::has('llm.configs.active'));

        // Act
        $updated = $this->service->update($config, [
            'default_parameters' => ['max_tokens' => 4000]
        ]);

        // Assert
        $this->assertTrue($updated);
        $this->assertEquals(4000, $config->fresh()->default_parameters['max_tokens']);
        $this->assertFalse(Cache::has('llm.configs.active'));
    }

    /** @test */
    public function it_toggles_active_status()
    {
        // Arrange
        $config = LLMConfiguration::factory()->create(['is_active' => true]);

        // Act
        $this->service->toggleActive($config);

        // Assert
        $this->assertFalse($config->fresh()->is_active);

        // Toggle again
        $this->service->toggleActive($config);
        $this->assertTrue($config->fresh()->is_active);
    }

    /** @test */
    public function it_sets_configuration_as_default()
    {
        // Arrange
        $oldDefault = LLMConfiguration::factory()->create(['is_default' => true]);
        $newDefault = LLMConfiguration::factory()->create(['is_default' => false]);

        // Act
        $this->service->setAsDefault($newDefault);

        // Assert
        $this->assertTrue($newDefault->fresh()->is_default);
        $this->assertFalse($oldDefault->fresh()->is_default);
    }

    /** @test */
    public function it_deletes_configuration_and_clears_cache()
    {
        // Arrange
        $config = LLMConfiguration::factory()->create();
        $id = $config->id;

        // Act
        $deleted = $this->service->delete($config);

        // Assert
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('llm_manager_configurations', ['id' => $id]);
    }

    /** @test */
    public function it_warms_cache()
    {
        // Arrange
        LLMConfiguration::factory()->count(3)->create([
            'provider' => 'openai',
            'is_active' => true
        ]);

        // Act
        $this->service->warmCache();

        // Assert
        $this->assertTrue(Cache::has('llm.configs.active'));
        $this->assertTrue(Cache::has('llm.configs.providers'));
        $this->assertTrue(Cache::has('llm.configs.provider.openai'));
    }

    /** @test */
    public function it_clears_all_caches()
    {
        // Arrange
        LLMConfiguration::factory()->create(['provider' => 'openai', 'is_active' => true]);
        LLMConfiguration::factory()->create(['provider' => 'anthropic', 'is_active' => true]);
        
        // Warm all caches
        $this->service->warmCache();
        
        $this->assertTrue(Cache::has('llm.configs.active'));
        $this->assertTrue(Cache::has('llm.configs.providers'));

        // Act
        $this->service->clearCache();

        // Assert
        $this->assertFalse(Cache::has('llm.configs.active'));
        $this->assertFalse(Cache::has('llm.configs.providers'));
        $this->assertFalse(Cache::has('llm.configs.provider.openai'));
        $this->assertFalse(Cache::has('llm.configs.provider.anthropic'));
    }

    /** @test */
    public function it_gets_all_configurations_including_inactive()
    {
        // Arrange
        LLMConfiguration::factory()->count(2)->create(['is_active' => true]);
        LLMConfiguration::factory()->count(1)->create(['is_active' => false]);

        // Act
        $all = $this->service->getAll();

        // Assert
        $this->assertCount(3, $all);
    }
}
