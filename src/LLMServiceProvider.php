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

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'llm-manager');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'llm-manager');

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
        
        // Register Extension Manager hooks
        $this->registerExtensionHooks();
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
    
    /**
     * Register Extension Manager hooks for install/uninstall
     */
    protected function registerExtensionHooks(): void
    {
        // Check if ExtensionManager exists (CPANEL context)
        if (!class_exists('App\Services\ExtensionManager')) {
            return;
        }

        // Register install hook
        try {
            \App\Services\ExtensionManager::registerInstallHook('llm-manager', function() {
                $this->installPermissions();
            });
        } catch (\Exception $e) {
            logger()->debug('ExtensionManager install hook registration failed (normal in standalone mode)', [
                'error' => $e->getMessage()
            ]);
        }

        // Register uninstall hook
        try {
            \App\Services\ExtensionManager::registerUninstallHook('llm-manager', function() {
                $this->uninstallPermissions();
            });
        } catch (\Exception $e) {
            logger()->debug('ExtensionManager uninstall hook registration failed (normal in standalone mode)', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Install permissions during extension installation
     * Implements Extension Permissions Protocol v2.0
     */
    protected function installPermissions(): void
    {
        // Only install if Spatie Permission is available
        if (!class_exists(Permission::class)) {
            logger()->warning('Spatie Permission not available, skipping permission installation');
            return;
        }

        $permissions = LLMPermissions::all();
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($permissions as $permissionData) {
            try {
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionData['name']],
                    [
                        'alias' => $permissionData['alias'],
                        'description' => $permissionData['description'],
                        'guard_name' => 'web'
                    ]
                );

                if ($permission->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                logger()->error('Failed to create permission: ' . $permissionData['name'], [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Assign permissions to Super Admin role
        try {
            $superAdmin = Role::where('name', 'Super Admin')->first();
            if ($superAdmin) {
                $superAdmin->givePermissionTo(LLMPermissions::names());
                logger()->info('LLM Manager permissions assigned to Super Admin role');
            } else {
                logger()->warning('Super Admin role not found, permissions not assigned to any role');
            }
        } catch (\Exception $e) {
            logger()->error('Failed to assign permissions to Super Admin', [
                'error' => $e->getMessage()
            ]);
        }

        logger()->info('LLM Manager permissions installed', [
            'created' => $createdCount,
            'skipped' => $skippedCount,
            'total' => count($permissions)
        ]);
    }
    
    /**
     * Uninstall permissions during extension uninstallation
     * Implements Extension Permissions Protocol v2.0
     */
    protected function uninstallPermissions(): void
    {
        try {
            // Delete role assignments
            DB::table('role_has_permissions')
                ->whereIn('permission_id', function($query) {
                    $query->select('id')
                        ->from('permissions')
                        ->where('name', 'like', 'extensions:llm-manager:%');
                })
                ->delete();

            // Delete permissions
            $deletedCount = Permission::where('name', 'like', 'extensions:llm-manager:%')->delete();

            logger()->info('LLM Manager permissions uninstalled', [
                'deleted' => $deletedCount
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to uninstall permissions', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
