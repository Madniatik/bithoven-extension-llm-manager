<?php

namespace Bithoven\LLMManager\Tests;

use Bithoven\LLMManager\LLMServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for UNIT tests (no database required)
 * 
 * Esta clase NO carga migraciones ni configura database.
 * Usar solo para tests puros (sin DB, sin models, sin filesystem).
 */
abstract class UnitTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LLMServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuración mínima (sin database)
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
