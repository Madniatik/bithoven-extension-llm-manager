<?php

namespace Bithoven\LLMManager\Tests;

use Bithoven\LLMManager\LLMServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            LLMServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup LLM Manager config
        $app['config']->set('llm-manager.default_provider', 'openai');
        $app['config']->set('llm-manager.budget.monthly_limit', 100.00);
        $app['config']->set('llm-manager.cache.enabled', true);
        $app['config']->set('llm-manager.cache.ttl', 3600);
    }
}
