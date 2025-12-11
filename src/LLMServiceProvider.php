<?php

namespace Bithoven\LLMManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Bithoven\LLMManager\Database\Seeders\Data\LLMPermissions;
use Bithoven\LLMManager\Services\LLMManager;
use Bithoven\LLMManager\Services\LLMExecutor;
use Bithoven\LLMManager\Services\LLMBudgetManager;
use Bithoven\LLMManager\Services\LLMMetricsService;
use Bithoven\LLMManager\Services\LLMPromptService;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Services\Conversations\LLMConversationManager;
use Bithoven\LLMManager\Services\LLMRAGService;
use Bithoven\LLMManager\Services\LLMEmbeddingsService;
use Bithoven\LLMManager\Services\Workflows\LLMWorkflowEngine;
use Bithoven\LLMManager\Services\Tools\LLMToolService;
use Bithoven\LLMManager\Services\Tools\LLMToolExecutor;
use Bithoven\LLMManager\Services\Tools\LLMFunctionCallingAdapter;
use Bithoven\LLMManager\Services\MCP\LLMMCPConnectorManager;
use Bithoven\LLMManager\Console\Commands\LLMMCPStartCommand;
use Bithoven\LLMManager\Console\Commands\LLMMCPListCommand;
use Bithoven\LLMManager\Console\Commands\LLMMCPAddCommand;
use Bithoven\LLMManager\Console\Commands\LLMIndexDocumentsCommand;
use Bithoven\LLMManager\Console\Commands\LLMGenerateEmbeddingsCommand;
use Bithoven\LLMManager\Console\Commands\LLMTestCommand;

class LLMServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/llm-manager.php',
            'llm-manager'
        );

        // Register core services as singletons
        $this->app->singleton(LLMManager::class, function ($app) {
            return new LLMManager($app);
        });

        $this->app->singleton(LLMExecutor::class, function ($app) {
            return new LLMExecutor($app->make(LLMManager::class));
        });

        $this->app->singleton(LLMBudgetManager::class, function ($app) {
            return new LLMBudgetManager();
        });

        // Register advanced services
        $this->app->singleton(LLMMetricsService::class, function ($app) {
            return new LLMMetricsService();
        });

        $this->app->singleton(LLMPromptService::class, function ($app) {
            return new LLMPromptService();
        });

        // Register configuration service (FASE 1 - v1.0.8)
        $this->app->singleton(LLMConfigurationService::class, function ($app) {
            return new LLMConfigurationService();
        });

        // Register orchestration services
        $this->app->singleton(LLMConversationManager::class, function ($app) {
            return new LLMConversationManager();
        });

        $this->app->singleton(LLMRAGService::class, function ($app) {
            return new LLMRAGService(
                $app->make(LLMEmbeddingsService::class)
            );
        });

        $this->app->singleton(LLMEmbeddingsService::class, function ($app) {
            return new LLMEmbeddingsService();
        });

        $this->app->singleton(LLMWorkflowEngine::class, function ($app) {
            return new LLMWorkflowEngine(
                $app->make(LLMManager::class)
            );
        });

        // Register hybrid tools services
        $this->app->singleton(LLMToolService::class, function ($app) {
            return new LLMToolService();
        });

        $this->app->singleton(LLMToolExecutor::class, function ($app) {
            return new LLMToolExecutor(
                $app->make(LLMToolService::class),
                $app->make(LLMMCPConnectorManager::class)
            );
        });

        $this->app->singleton(LLMFunctionCallingAdapter::class, function ($app) {
            return new LLMFunctionCallingAdapter(
                $app->make(LLMManager::class),
                $app->make(LLMToolService::class),
                $app->make(LLMToolExecutor::class)
            );
        });

        $this->app->singleton(LLMMCPConnectorManager::class, function ($app) {
            return new LLMMCPConnectorManager();
        });

        // Register facade alias
        $this->app->alias(LLMManager::class, 'llm');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/llm-manager.php' => config_path('llm-manager.php'),
        ], 'llm-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/llm-manager'),
        ], 'llm-views');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/llm-manager'),
        ], 'llm-lang');

        // Publish public assets (JS, CSS, images)
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/bithoven/llm-manager'),
        ], 'llm-assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'llm-manager');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'llm-manager');

        // Register Blade class-based components
        \Illuminate\Support\Facades\Blade::component('llm-manager-chat-workspace', \Bithoven\LLMManager\View\Components\Chat\ChatWorkspace::class);

        // Register Debug Console for extension (global via view share)
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            static $registered = false;
            if (!$registered && config('llm-manager.debug_console.level', 'none') !== 'none') {
                $registered = true;
                $view->with('__llmDebugConsoleRegistration', view('llm-manager::partials.debug-console-registration')->render());
            }
        });

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        
        // Load breadcrumbs
        if (file_exists(__DIR__ . '/../routes/breadcrumbs.php')) {
            require __DIR__ . '/../routes/breadcrumbs.php';
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                LLMMCPStartCommand::class,
                LLMMCPListCommand::class,
                LLMMCPAddCommand::class,
                LLMIndexDocumentsCommand::class,
                LLMGenerateEmbeddingsCommand::class,
                LLMTestCommand::class,
            ]);
        }

        // Register middleware
        $this->registerMiddleware();
        
        // NOTE: Permission management handled by LLMPermissionsSeeder (core seeder)
        // No hooks needed - ExtensionSeederManager runs seeders automatically
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        // Admin middleware group
        Route::aliasMiddleware('llm.admin', \Bithoven\LLMManager\Http\Middleware\LLMAdminMiddleware::class);
        
        // API middleware group
        Route::aliasMiddleware('llm.api', \Bithoven\LLMManager\Http\Middleware\LLMApiMiddleware::class);
    }
}
