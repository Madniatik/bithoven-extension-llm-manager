<?php

namespace Bithoven\LLMManager\Tests;

use Bithoven\LLMManager\LLMServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load Laravel's default migrations (users, password_reset_tokens, etc.)
        $this->loadLaravelMigrations();
        
        // Load Spatie Permission migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../CPANEL/vendor/spatie/laravel-permission/database/migrations');
        
        // Load extension's migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            PermissionServiceProvider::class,
            LLMServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use SQLite for testing (simpler, no FK constraint issues)
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Disable ActivityLog in tests to avoid null user issues
        $app['config']->set('activitylog.enabled', false);
        
        // Register view path for mock components
        $app['config']->set('view.paths', array_merge(
            $app['config']->get('view.paths', []),
            [__DIR__ . '/stubs/views']
        ));
        
        // Add components namespace for mock default-layout
        $app['config']->set('view.components', [
            'default-layout' => \Illuminate\View\Component::class,
        ]);

        // Setup LLM Manager config
        $app['config']->set('llm-manager.default_provider', 'openai');
        $app['config']->set('llm-manager.budget.monthly_limit', 100.00);
        $app['config']->set('llm-manager.cache.enabled', true);
        $app['config']->set('llm-manager.cache.ttl', 3600);
        
        // Setup exchange rates for multi-currency testing
        $app['config']->set('llm-manager.exchange_rates', [
            'USD' => 1.0,
            'EUR' => 1.08,
            'GBP' => 1.25,
            'MXN' => 0.05,
            'CAD' => 0.73,
            'JPY' => 0.0067,
            'CNY' => 0.14,
            'INR' => 0.012,
            'BRL' => 0.20,
        ]);
    }
}
