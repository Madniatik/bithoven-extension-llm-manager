<?php

namespace Bithoven\LLMManager\Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

/**
 * List Provider Packages Command Integration Tests
 * 
 * @package Bithoven\LLMManager\Tests\Feature\Commands
 * @version 0.1.0
 * @since 0.4.0
 */
class ListProviderPackagesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_available_packages()
    {
        $exitCode = Artisan::call('llm:packages');

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        
        // Should show package information
        $this->assertStringContainsString('Provider Configuration Packages', $output);
        $this->assertStringContainsString('OpenAI', $output);
        $this->assertStringContainsString('Anthropic', $output);
        $this->assertStringContainsString('Ollama', $output);
    }

    /** @test */
    public function it_accepts_installed_filter()
    {
        $exitCode = Artisan::call('llm:packages', ['--installed' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Provider Configuration Packages', $output);
    }

    /** @test */
    public function it_accepts_available_filter()
    {
        $exitCode = Artisan::call('llm:packages', ['--available' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Provider Configuration Packages', $output);
    }

    /** @test */
    public function it_shows_usage_instructions()
    {
        $exitCode = Artisan::call('llm:packages');

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        
        // Should show usage instructions
        $this->assertStringContainsString('Usage:', $output);
        $this->assertStringContainsString('composer require', $output);
        $this->assertStringContainsString('php artisan llm:import', $output);
    }

    /** @test */
    public function it_shows_package_details()
    {
        $exitCode = Artisan::call('llm:packages');

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        
        // Should show package details
        $this->assertStringContainsString('bithoven/llm-provider-openai', $output);
        $this->assertStringContainsString('Configurations:', $output);
        $this->assertStringContainsString('Repository:', $output);
    }

    /** @test */
    public function it_shows_help_in_command_list()
    {
        $exitCode = Artisan::call('list', ['namespace' => 'llm']);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('llm:packages', $output);
        $this->assertStringContainsString('List available provider', $output);
    }
}
