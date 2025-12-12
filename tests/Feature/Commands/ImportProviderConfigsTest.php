<?php

namespace Bithoven\LLMManager\Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Support\Facades\Artisan;

/**
 * Import Provider Configs Command Integration Tests
 * 
 * @package Bithoven\LLMManager\Tests\Feature\Commands
 * @version 0.1.0
 * @since 0.4.0
 */
class ImportProviderConfigsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_error_when_package_not_installed()
    {
        $exitCode = Artisan::call('llm:import', ['provider' => 'nonexistent']);

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Provider package not found', $output);
        $this->assertStringContainsString('composer require', $output);
    }

    /** @test */
    public function it_shows_help_in_command_list()
    {
        $exitCode = Artisan::call('list', ['namespace' => 'llm']);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('llm:import', $output);
        $this->assertStringContainsString('Import LLM configurations', $output);
    }

    /** @test */
    public function it_accepts_dry_run_option()
    {
        // This test verifies the command accepts --dry-run flag
        // Actual import testing requires mock package structure
        
        $exitCode = Artisan::call('llm:import', [
            'provider' => 'test',
            '--dry-run' => true,
        ]);

        $this->assertContains($exitCode, [0, 1]); // Either success or package not found
    }

    /** @test */
    public function it_accepts_force_option()
    {
        $exitCode = Artisan::call('llm:import', [
            'provider' => 'test',
            '--force' => true,
        ]);

        $this->assertContains($exitCode, [0, 1]); // Either success or package not found
    }
}
