<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Models\LLMConfiguration;
use App\Models\User;

class LLMConfigurationServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private LLMConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LLMConfigurationService::class);
    }

    /** @test */
    public function service_is_registered_as_singleton()
    {
        // Arrange & Act
        $service1 = app(LLMConfigurationService::class);
        $service2 = app(LLMConfigurationService::class);

        // Assert - Same instance (singleton)
        $this->assertSame($service1, $service2);
    }

    /** @test */
    public function controllers_can_inject_service_via_dependency_injection()
    {
        // This test ensures that the service is properly registered
        // and can be resolved via Laravel's service container
        
        $this->actingAs(User::factory()->create());
        
        LLMConfiguration::factory()->count(3)->create(['is_active' => true]);

        // Act - Hit a route that uses the service
        $response = $this->get(route('admin.llm.configurations.index'));

        // Assert - Page loads successfully (service was injected)
        $response->assertOk();
    }

    /** @test */
    public function cache_survives_across_multiple_requests()
    {
        // Arrange
        LLMConfiguration::factory()->count(2)->create(['is_active' => true]);

        // Act - First request warms cache
        $configs1 = $this->service->getActive();
        
        // Add new config (but cache should still be used)
        LLMConfiguration::factory()->create(['is_active' => true]);
        
        // Second request uses cache (still 2 configs)
        $configs2 = $this->service->getActive();

        // Assert - Cache hit (still shows 2 configs)
        $this->assertCount(2, $configs1);
        $this->assertCount(2, $configs2);
        
        // Now clear cache and fetch again
        $this->service->clearCache();
        $configs3 = $this->service->getActive();
        
        // Now we see all 3
        $this->assertCount(3, $configs3);
    }

    /** @test */
    public function service_integrates_with_eloquent_scopes()
    {
        // Arrange
        LLMConfiguration::factory()->create([
            'provider' => 'openai',
            'is_active' => true
        ]);
        LLMConfiguration::factory()->create([
            'provider' => 'openai',
            'is_active' => false // Inactive
        ]);
        LLMConfiguration::factory()->create([
            'provider' => 'anthropic',
            'is_active' => true
        ]);

        // Act
        $openaiConfigs = $this->service->getByProvider('openai');
        $allActive = $this->service->getActive();

        // Assert
        $this->assertCount(1, $openaiConfigs); // Only active OpenAI
        $this->assertCount(2, $allActive); // Both active configs
    }

    /** @test */
    public function multiple_services_can_share_cached_data()
    {
        // Arrange
        $service1 = app(LLMConfigurationService::class);
        $service2 = app(LLMConfigurationService::class);
        
        LLMConfiguration::factory()->count(3)->create(['is_active' => true]);

        // Act - Service1 warms cache
        $configs1 = $service1->getActive();
        
        // Service2 uses same cache
        $configs2 = $service2->getActive();

        // Assert - Both got same cached data
        $this->assertCount(3, $configs1);
        $this->assertCount(3, $configs2);
        $this->assertEquals($configs1->pluck('id')->sort()->values(), 
                           $configs2->pluck('id')->sort()->values());
    }
}
