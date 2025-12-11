# Repository Pattern - LLM Configuration Repository

**Fecha:** 10 de diciembre de 2025  
**Versión:** 1.0.0  
**Patrón:** Repository Pattern  
**Recomendación:** ⚠️ **OVER-ENGINEERING para este caso**

---

## 📋 Índice

1. [¿Qué es Repository Pattern?](#qué-es-repository-pattern)
2. [¿Por qué usarlo?](#por-qué-usarlo)
3. [Arquitectura Propuesta](#arquitectura-propuesta)
4. [Implementación Completa](#implementación-completa)
5. [Uso en Controllers](#uso-en-controllers)
6. [Testing](#testing)
7. [Pros y Contras](#pros-y-contras)
8. [Casos de Uso](#casos-de-uso)

---

## ¿Qué es Repository Pattern?

### Definición

**Repository Pattern** es un patrón arquitectural que abstrae completamente la capa de acceso a datos mediante interfaces, permitiendo cambiar el backend de persistencia (MySQL, MongoDB, Redis, API externa) sin modificar la lógica de negocio.

### Analogía del Mundo Real

Piensa en una biblioteca pública:

```
┌──────────────────────────────────────────────┐
│ BIBLIOTECA (Aplicación Laravel)              │
├──────────────────────────────────────────────┤
│                                              │
│  👤 Usuario (Controller)                     │
│  ├─ Solicita libro por título (Request)     │
│  └─ Recibe libro (Response)                 │
│                                              │
│  👨‍💼 Bibliotecario (Repository Interface)     │
│  ├─ findByTitle(string): Book               │
│  ├─ getAll(): Collection                    │
│  └─ save(Book): void                         │
│                                              │
│  📚 Sistema de Almacenamiento (Implementación)│
│  ├─ EloquentRepository (MySQL)              │
│  ├─ RedisRepository (Redis)                 │
│  ├─ FileRepository (JSON files)             │
│  └─ ApiRepository (External API)            │
│                                              │
│  🗄️ Almacén Físico (Data Layer)             │
│  ├─ Estantes (Tables)                        │
│  ├─ Archivos (Rows)                          │
│  └─ Fichas (Columns)                         │
│                                              │
└──────────────────────────────────────────────┘
```

**Clave:** Usuario NO sabe si los libros están en:
- Estantes físicos (MySQL)
- Sistema digital (Redis)
- Préstamo interbibliotecario (API)

Solo le importa que el bibliotecario cumpla el contrato (`findByTitle()`, `getAll()`).

### Flujo de Datos

```
┌──────────┐    Request    ┌────────────┐    Interface    ┌────────────────┐
│  Route   │──────────────→│ Controller │───────────────→│RepositoryInterface│
└──────────┘               └────────────┘                └────────────────┘
                                  ↑                              │
                                  │                              ▼
                           Response                    ┌────────────────┐
                                  │                    │Implementation  │
                                  │                    │(Eloquent/Redis)│
                                  └────────────────────┤                │
                                                       └────────┬───────┘
                                                                │
                                                                ▼
                                                       ┌────────────────┐
                                                       │ Data Source    │
                                                       │ (MySQL/Redis)  │
                                                       └────────────────┘
```

**Ejemplo concreto:**

```php
// ❌ SIN Repository (acoplamiento a Eloquent)
public function index()
{
    $configs = LLMConfiguration::where('is_active', true)->get();
    // ¿Qué pasa si queremos cambiar a Redis? Refactor total.
}

// ✅ CON Repository (desacoplado)
public function index(ConfigurationRepositoryInterface $repository)
{
    $configs = $repository->getActive();
    // Backend puede ser MySQL, Redis, API... Controller no lo sabe.
}
```

---

## ¿Por qué usarlo?

### Problema que Resuelve

**Escenario hipotético:**

Actualmente usas **MySQL** para configuraciones LLM. Supongamos que:

1. **Año 1:** Tienes 100 configuraciones → MySQL funciona perfecto
2. **Año 2:** Tienes 10,000 configuraciones → MySQL lento en queries complejas
3. **Decisión:** Migrar a **Redis** para configuraciones activas (cache warm)

**SIN Repository Pattern:**

```php
// Refactor MASIVO en 20+ archivos
// ANTES (MySQL)
$configs = LLMConfiguration::active()->get();

// DESPUÉS (Redis)
$configs = Redis::get('configs:active');
if (!$configs) {
    $configs = LLMConfiguration::active()->get();
    Redis::set('configs:active', $configs, 3600);
}
```

Resultado: 2 semanas de trabajo, 50+ bugs, downtime probable.

**CON Repository Pattern:**

```php
// SOLO cambias 1 archivo (ConfigurationRepository implementation)
// Bind diferente implementación en ServiceProvider

// app/Providers/AppServiceProvider.php
// ANTES
$this->app->bind(
    ConfigurationRepositoryInterface::class,
    EloquentConfigurationRepository::class
);

// DESPUÉS
$this->app->bind(
    ConfigurationRepositoryInterface::class,
    RedisConfigurationRepository::class // Nueva implementación
);
```

Resultado: 2 horas de trabajo, 0 bugs (interface garantiza contrato), zero downtime.

### Ventajas sobre Service Layer

| Feature | Service Layer | Repository Pattern |
|---------|---------------|-------------------|
| **Abstracción de datos** | Parcial (depende de Eloquent) | Total (agnóstico de backend) |
| **Cambiar backend** | Difícil (refactor services) | Fácil (nueva implementación) |
| **Testing** | Mock de service | Mock de interface (más clean) |
| **Flexibilidad** | Media | Máxima |
| **Complejidad** | Baja (1 clase) | Alta (interface + N implementaciones) |

---

## Arquitectura Propuesta

### Diagrama de Componentes

```
┌───────────────────────────────────────────────────────────────────┐
│                        APPLICATION                                │
├───────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ HTTP LAYER (Controllers)                               │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │ - LLMQuickChatController                               │      │
│  │ - LLMConversationController                            │      │
│  │ - LLMConfigurationController                           │      │
│  └─────────────────────┬──────────────────────────────────┘      │
│                        │ Dependency Injection                     │
│                        ▼                                          │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ REPOSITORY INTERFACE (Contract)                        │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │ interface ConfigurationRepositoryInterface             │      │
│  │ {                                                      │      │
│  │     public function getActive(): Collection;           │      │
│  │     public function find(int $id): ?Model;             │      │
│  │     public function findBySlug(string): ?Model;        │      │
│  │     public function create(array $data): Model;        │      │
│  │     public function update(Model, array): bool;        │      │
│  │     public function delete(Model): bool;               │      │
│  │ }                                                      │      │
│  └─────────────────────┬──────────────────────────────────┘      │
│                        │ Implementation                           │
│                        ▼                                          │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ REPOSITORY IMPLEMENTATIONS                             │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │                                                        │      │
│  │ ┌──────────────────────────────────────────────┐      │      │
│  │ │ EloquentConfigurationRepository (DEFAULT)    │      │      │
│  │ ├──────────────────────────────────────────────┤      │      │
│  │ │ Uses: LLMConfiguration Model                 │      │      │
│  │ │ Backend: MySQL                               │      │      │
│  │ └───────────────────┬──────────────────────────┘      │      │
│  │                     │                                  │      │
│  │                     ▼                                  │      │
│  │          ┌────────────────────┐                        │      │
│  │          │ LLMConfiguration   │                        │      │
│  │          │ (Eloquent Model)   │                        │      │
│  │          └────────┬───────────┘                        │      │
│  │                   │                                    │      │
│  │                   ▼                                    │      │
│  │          ┌────────────────────┐                        │      │
│  │          │ MySQL Database     │                        │      │
│  │          └────────────────────┘                        │      │
│  │                                                        │      │
│  │ ┌──────────────────────────────────────────────┐      │      │
│  │ │ RedisConfigurationRepository (FUTURE)        │      │      │
│  │ ├──────────────────────────────────────────────┤      │      │
│  │ │ Uses: Redis Facade                           │      │      │
│  │ │ Backend: Redis                               │      │      │
│  │ └───────────────────┬──────────────────────────┘      │      │
│  │                     │                                  │      │
│  │                     ▼                                  │      │
│  │          ┌────────────────────┐                        │      │
│  │          │ Redis Cache        │                        │      │
│  │          └────────────────────┘                        │      │
│  │                                                        │      │
│  │ ┌──────────────────────────────────────────────┐      │      │
│  │ │ ApiConfigurationRepository (FUTURE)          │      │      │
│  │ ├──────────────────────────────────────────────┤      │      │
│  │ │ Uses: HTTP Client                            │      │      │
│  │ │ Backend: External API                        │      │      │
│  │ └───────────────────┬──────────────────────────┘      │      │
│  │                     │                                  │      │
│  │                     ▼                                  │      │
│  │          ┌────────────────────┐                        │      │
│  │          │ External API       │                        │      │
│  │          │ (e.g. Config SaaS) │                        │      │
│  │          └────────────────────┘                        │      │
│  │                                                        │      │
│  └────────────────────────────────────────────────────────┘      │
│                                                                   │
│  SERVICE PROVIDER BINDING:                                        │
│  $this->app->bind(                                                │
│      ConfigurationRepositoryInterface::class,                     │
│      EloquentConfigurationRepository::class // Swap here!         │
│  );                                                               │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

### Responsabilidades Claras

| Capa | Responsabilidad | Ejemplo |
|------|----------------|---------|
| **Controller** | HTTP I/O, validación | `return view('configs', ['configs' => $repository->getActive()])` |
| **Repository Interface** | Contrato de operaciones | `public function getActive(): Collection;` |
| **Repository Implementation** | Lógica de acceso a datos específica | Eloquent, Redis, API client |
| **Data Source** | Almacenamiento físico | MySQL, Redis, External API |

---

## Implementación Completa

### Paso 1: Crear Interface

```php
<?php
// src/Contracts/Repositories/ConfigurationRepositoryInterface.php

namespace Bithoven\LLMManager\Contracts\Repositories;

use Illuminate\Support\Collection;
use Bithoven\LLMManager\Models\LLMConfiguration;

/**
 * Configuration Repository Contract
 * 
 * Defines operations for managing LLM configurations
 * regardless of underlying data storage implementation.
 */
interface ConfigurationRepositoryInterface
{
    /**
     * Get all active configurations
     * 
     * @return Collection<LLMConfiguration>
     */
    public function getActive(): Collection;

    /**
     * Find configuration by ID
     * 
     * @param int $id
     * @return LLMConfiguration|null
     */
    public function find(int $id): ?LLMConfiguration;

    /**
     * Find configuration by ID or fail
     * 
     * @param int $id
     * @return LLMConfiguration
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): LLMConfiguration;

    /**
     * Find configuration by slug
     * 
     * @param string $slug
     * @return LLMConfiguration|null
     */
    public function findBySlug(string $slug): ?LLMConfiguration;

    /**
     * Get default configuration
     * 
     * @return LLMConfiguration|null
     */
    public function getDefault(): ?LLMConfiguration;

    /**
     * Get configurations for provider
     * 
     * @param string $provider
     * @return Collection<LLMConfiguration>
     */
    public function getByProvider(string $provider): Collection;

    /**
     * Get all distinct providers
     * 
     * @return Collection<string>
     */
    public function getProviders(): Collection;

    /**
     * Get all configurations (including inactive)
     * 
     * @return Collection<LLMConfiguration>
     */
    public function getAll(): Collection;

    /**
     * Create new configuration
     * 
     * @param array $data
     * @return LLMConfiguration
     */
    public function create(array $data): LLMConfiguration;

    /**
     * Update configuration
     * 
     * @param LLMConfiguration $configuration
     * @param array $data
     * @return bool
     */
    public function update(LLMConfiguration $configuration, array $data): bool;

    /**
     * Delete configuration
     * 
     * @param LLMConfiguration $configuration
     * @return bool|null
     */
    public function delete(LLMConfiguration $configuration): ?bool;

    /**
     * Toggle active status
     * 
     * @param LLMConfiguration $configuration
     * @return bool
     */
    public function toggleActive(LLMConfiguration $configuration): bool;

    /**
     * Clear cache (if applicable)
     * 
     * @return void
     */
    public function clearCache(): void;
}
```

### Paso 2: Implementación Eloquent (Default)

```php
<?php
// src/Repositories/EloquentConfigurationRepository.php

namespace Bithoven\LLMManager\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Bithoven\LLMManager\Contracts\Repositories\ConfigurationRepositoryInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Events\ConfigurationLoaded;
use Bithoven\LLMManager\Events\ConfigurationChanged;

class EloquentConfigurationRepository implements ConfigurationRepositoryInterface
{
    private const CACHE_TTL = 3600;

    public function getActive(): Collection
    {
        return Cache::remember(
            'llm.configs.active',
            self::CACHE_TTL,
            fn() => LLMConfiguration::active()->get()
        );
    }

    public function find(int $id): ?LLMConfiguration
    {
        $config = LLMConfiguration::find($id);

        if ($config) {
            Event::dispatch(new ConfigurationLoaded($config));
        }

        return $config;
    }

    public function findOrFail(int $id): LLMConfiguration
    {
        $config = LLMConfiguration::findOrFail($id);
        Event::dispatch(new ConfigurationLoaded($config));
        return $config;
    }

    public function findBySlug(string $slug): ?LLMConfiguration
    {
        return LLMConfiguration::where('slug', $slug)
            ->active()
            ->first();
    }

    public function getDefault(): ?LLMConfiguration
    {
        return LLMConfiguration::default()->first();
    }

    public function getByProvider(string $provider): Collection
    {
        return Cache::remember(
            "llm.configs.provider.{$provider}",
            self::CACHE_TTL,
            fn() => LLMConfiguration::forProvider($provider)->active()->get()
        );
    }

    public function getProviders(): Collection
    {
        return Cache::remember(
            'llm.configs.providers',
            self::CACHE_TTL,
            fn() => LLMConfiguration::select('provider')
                ->distinct()
                ->active()
                ->pluck('provider')
        );
    }

    public function getAll(): Collection
    {
        return LLMConfiguration::withCount('usageLogs')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): LLMConfiguration
    {
        $config = LLMConfiguration::create($data);
        $this->clearCache();

        Event::dispatch(new ConfigurationChanged($config, 'created'));

        return $config;
    }

    public function update(LLMConfiguration $configuration, array $data): bool
    {
        $updated = $configuration->update($data);

        if ($updated) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($configuration, 'updated'));
        }

        return $updated;
    }

    public function delete(LLMConfiguration $configuration): ?bool
    {
        $deleted = $configuration->delete();

        if ($deleted) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($configuration, 'deleted'));
        }

        return $deleted;
    }

    public function toggleActive(LLMConfiguration $configuration): bool
    {
        $configuration->is_active = !$configuration->is_active;
        $saved = $configuration->save();

        if ($saved) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($configuration, 'toggled'));
        }

        return $saved;
    }

    public function clearCache(): void
    {
        Cache::forget('llm.configs.active');
        Cache::forget('llm.configs.providers');

        $providers = LLMConfiguration::select('provider')
            ->distinct()
            ->pluck('provider');

        foreach ($providers as $provider) {
            Cache::forget("llm.configs.provider.{$provider}");
        }
    }
}
```

### Paso 3: Implementación Redis (Ejemplo Futuro)

```php
<?php
// src/Repositories/RedisConfigurationRepository.php

namespace Bithoven\LLMManager\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Bithoven\LLMManager\Contracts\Repositories\ConfigurationRepositoryInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;

class RedisConfigurationRepository implements ConfigurationRepositoryInterface
{
    private const REDIS_KEY_PREFIX = 'llm:configs:';
    private const CACHE_TTL = 3600;

    public function getActive(): Collection
    {
        $cached = Redis::get(self::REDIS_KEY_PREFIX . 'active');

        if ($cached) {
            return collect(json_decode($cached, true))
                ->map(fn($data) => new LLMConfiguration($data));
        }

        // Fallback to DB if cache miss
        $configs = LLMConfiguration::active()->get();
        
        Redis::setex(
            self::REDIS_KEY_PREFIX . 'active',
            self::CACHE_TTL,
            $configs->toJson()
        );

        return $configs;
    }

    public function find(int $id): ?LLMConfiguration
    {
        $cached = Redis::get(self::REDIS_KEY_PREFIX . "id:{$id}");

        if ($cached) {
            return new LLMConfiguration(json_decode($cached, true));
        }

        $config = LLMConfiguration::find($id);

        if ($config) {
            Redis::setex(
                self::REDIS_KEY_PREFIX . "id:{$id}",
                self::CACHE_TTL,
                $config->toJson()
            );
        }

        return $config;
    }

    // ... resto de métodos similar al Eloquent pero con Redis layer

    public function create(array $data): LLMConfiguration
    {
        // Create in DB (source of truth)
        $config = LLMConfiguration::create($data);

        // Invalidate caches
        $this->clearCache();

        return $config;
    }

    public function clearCache(): void
    {
        $keys = Redis::keys(self::REDIS_KEY_PREFIX . '*');
        
        if (!empty($keys)) {
            Redis::del($keys);
        }
    }
}
```

### Paso 4: Registrar en ServiceProvider

```php
<?php
// src/LLMManagerServiceProvider.php

namespace Bithoven\LLMManager;

use Illuminate\Support\ServiceProvider;
use Bithoven\LLMManager\Contracts\Repositories\ConfigurationRepositoryInterface;
use Bithoven\LLMManager\Repositories\EloquentConfigurationRepository;
use Bithoven\LLMManager\Repositories\RedisConfigurationRepository;

class LLMManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ... existing bindings

        // Bind Repository Interface
        $this->app->bind(
            ConfigurationRepositoryInterface::class,
            function ($app) {
                // Switch implementation based on config
                $driver = config('llm-manager.repository_driver', 'eloquent');

                return match($driver) {
                    'redis' => new RedisConfigurationRepository(),
                    'eloquent' => new EloquentConfigurationRepository(),
                    default => new EloquentConfigurationRepository(),
                };
            }
        );
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/llm-manager.php' => config_path('llm-manager.php'),
        ], 'config');
    }
}
```

### Paso 5: Config File

```php
<?php
// config/llm-manager.php

return [
    /*
    |--------------------------------------------------------------------------
    | Repository Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default repository driver for LLM configurations.
    |
    | Supported: "eloquent", "redis"
    |
    */
    'repository_driver' => env('LLM_REPOSITORY_DRIVER', 'eloquent'),
];
```

---

## Uso en Controllers

### Ejemplo: Controller usando Repository

```php
<?php
// src/Http/Controllers/Admin/LLMQuickChatController.php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Contracts\Repositories\ConfigurationRepositoryInterface;
use Illuminate\Http\Request;

class LLMQuickChatController extends Controller
{
    public function __construct(
        private readonly ConfigurationRepositoryInterface $configRepository
    ) {}

    public function index($sessionId = null)
    {
        // Repository abstrae si es MySQL, Redis, API, etc.
        $configurations = $this->configRepository->getActive();
        $defaultConfig = $configurations->first();

        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }

        return view('llm-manager::quick-chat.index', [
            'configurations' => $configurations,
            'defaultConfig' => $defaultConfig,
        ]);
    }

    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'configuration_id' => 'required|integer',
        ]);

        $configuration = $this->configRepository->findOrFail($validated['configuration_id']);

        // ... create session logic
    }
}
```

**Ventaja:** Cambiar de Eloquent a Redis es solo cambiar 1 línea en `.env`:

```bash
# .env
LLM_REPOSITORY_DRIVER=redis  # Cambio de backend sin tocar código
```

---

## Testing

### Unit Tests (Repository Implementation)

```php
<?php
// tests/Unit/Repositories/EloquentConfigurationRepositoryTest.php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Repositories\EloquentConfigurationRepository;
use Bithoven\LLMManager\Models\LLMConfiguration;

class EloquentConfigurationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentConfigurationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentConfigurationRepository();
    }

    /** @test */
    public function it_gets_active_configurations()
    {
        LLMConfiguration::factory()->count(3)->create(['is_active' => true]);
        LLMConfiguration::factory()->create(['is_active' => false]);

        $configs = $this->repository->getActive();

        $this->assertCount(3, $configs);
    }

    /** @test */
    public function it_finds_configuration_by_id()
    {
        $config = LLMConfiguration::factory()->create();

        $found = $this->repository->find($config->id);

        $this->assertNotNull($found);
        $this->assertEquals($config->id, $found->id);
    }

    // ... more tests similar to Service Layer tests
}
```

### Integration Tests (Controller con Mock)

```php
<?php
// tests/Feature/Controllers/LLMQuickChatControllerTest.php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use Mockery;
use Bithoven\LLMManager\Contracts\Repositories\ConfigurationRepositoryInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMQuickChatControllerTest extends TestCase
{
    /** @test */
    public function it_uses_repository_to_get_configurations()
    {
        // Arrange - Mock del Repository Interface
        $mockRepository = Mockery::mock(ConfigurationRepositoryInterface::class);
        
        $mockConfigs = collect([
            LLMConfiguration::factory()->make(['id' => 1]),
            LLMConfiguration::factory()->make(['id' => 2]),
        ]);

        $mockRepository->shouldReceive('getActive')
            ->once()
            ->andReturn($mockConfigs);

        $this->app->instance(ConfigurationRepositoryInterface::class, $mockRepository);

        // Act
        $response = $this->get(route('admin.llm.quick-chat'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('configurations', $mockConfigs);
    }
}
```

### Ventaja: Test con múltiples implementaciones

```php
<?php
// tests/Feature/Repositories/RepositoryContractTest.php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Contracts\Repositories\ConfigurationRepositoryInterface;
use Bithoven\LLMManager\Repositories\EloquentConfigurationRepository;
use Bithoven\LLMManager\Repositories\RedisConfigurationRepository;
use Bithoven\LLMManager\Models\LLMConfiguration;

class RepositoryContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que TODAS las implementaciones cumplen el contrato
     * 
     * @dataProvider repositoryProvider
     */
    public function test_repository_contract_compliance($repositoryClass)
    {
        /** @var ConfigurationRepositoryInterface $repository */
        $repository = new $repositoryClass();

        // Arrange
        LLMConfiguration::factory()->count(3)->create(['is_active' => true]);

        // Act & Assert - Contract compliance
        $configs = $repository->getActive();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $configs);
        $this->assertCount(3, $configs);

        $config = $repository->find(1);
        $this->assertInstanceOf(LLMConfiguration::class, $config);
    }

    public function repositoryProvider(): array
    {
        return [
            'Eloquent' => [EloquentConfigurationRepository::class],
            'Redis' => [RedisConfigurationRepository::class],
            // Añadir futuras implementaciones aquí
        ];
    }
}
```

---

## Pros y Contras

### ✅ Ventajas

| Ventaja | Impacto | Ejemplo |
|---------|---------|---------|
| **Abstracción total de datos** | Muy Alto | Cambiar MySQL → Redis → API sin tocar controllers |
| **Testing ultra-clean** | Alto | Mock de interface (1 línea) vs mock de Eloquent (10 líneas) |
| **Flexibilidad máxima** | Muy Alto | Múltiples backends simultáneos (read from Redis, write to MySQL) |
| **Cumplimiento SOLID** | Alto | Dependency Inversion Principle (depende de interface, no implementación) |
| **Escalabilidad** | Alto | Fácil añadir CachedRepository, ApiRepository, FileRepository, etc. |
| **Contract enforcement** | Medio | Interface fuerza consistencia entre implementaciones |

### ❌ Desventajas

| Desventaja | Impacto | Mitigación |
|------------|---------|------------|
| **Complejidad arquitectural** | Alto | Requiere entender interfaces, bindings, IoC container |
| **Overhead inicial** | Alto | 3 archivos mínimo (interface + implementation + binding) vs 1 (service) |
| **Curva aprendizaje** | Alto | Equipo debe entender Repository Pattern |
| **Over-engineering para CRUD simple** | Muy Alto | LLMConfiguration es solo CRUD, no necesita cambiar backend |
| **Inconsistencia con proyecto** | Medio | Ningún otro módulo usa Repository Pattern |
| **Debugging más complejo** | Medio | Stack trace: Controller → Interface → Implementation → Model (2 niveles más) |
| **YAGNI violado** | Alto | "You Aren't Gonna Need It" - ¿realmente cambiarás a Redis/API? |

### ⚖️ Balance Final

**Desventajas superan ventajas para este caso específico:**
- ❌ 3 desventajas de impacto Alto vs 2 ventajas de impacto Muy Alto
- ❌ Complejidad NO justificada (CRUD simple, MySQL suficiente)
- ❌ Team overhead (curva aprendizaje alta)
- ❌ Inconsistencia arquitectural (único módulo con Repository)
- ✅ Beneficios solo se materializan SI cambias backend (probabilidad <10%)

**Score:** 5.5/10 para este proyecto específico

---

## Casos de Uso

### Caso 1: ¿Cuándo SÍ usar Repository Pattern?

**Escenarios justificados:**

1. **Multi-tenancy con backends diferentes:**
   ```php
   // Tenant A usa MySQL
   // Tenant B usa API externa (SaaS provider)
   // Tenant C usa MongoDB
   
   $repository = app(ConfigurationRepositoryInterface::class);
   $configs = $repository->getActive(); // Backend depende del tenant
   ```

2. **Migración gradual de backend:**
   ```php
   // Fase 1: 100% MySQL
   LLM_REPOSITORY_DRIVER=eloquent
   
   // Fase 2: 50% MySQL, 50% Redis (A/B testing)
   if (auth()->user()->id % 2 === 0) {
       LLM_REPOSITORY_DRIVER=redis
   }
   
   // Fase 3: 100% Redis
   LLM_REPOSITORY_DRIVER=redis
   ```

3. **Testing con múltiples backends:**
   ```php
   // Validar que ambas implementaciones dan mismo resultado
   $eloquentRepo = new EloquentConfigurationRepository();
   $redisRepo = new RedisConfigurationRepository();
   
   $this->assertEquals(
       $eloquentRepo->getActive(),
       $redisRepo->getActive()
   );
   ```

4. **Compliance/Audit requirements:**
   ```php
   // Primary: MySQL (source of truth)
   // Secondary: Audit log API (log todas las queries)
   
   class AuditConfigurationRepository implements ConfigurationRepositoryInterface
   {
       public function getActive(): Collection
       {
           $configs = $this->eloquentRepo->getActive();
           $this->auditApi->log('getActive', ['count' => $configs->count()]);
           return $configs;
       }
   }
   ```

### Caso 2: ¿Cuándo NO usar Repository Pattern?

**Escenarios injustificados (caso actual):**

1. **CRUD simple con Eloquent suficiente:**
   ```php
   // NO NECESITAS Repository si solo haces:
   $configs = LLMConfiguration::active()->get();
   $config = LLMConfiguration::find($id);
   $config->update($data);
   ```

2. **Sin planes de cambiar backend:**
   ```php
   // ¿Probabilidad de migrar MySQL → Redis? <10%
   // ¿Probabilidad de migrar MySQL → API externa? <5%
   // ¿Probabilidad de usar MongoDB? <1%
   // 
   // RESULTADO: Repository Pattern es YAGNI (You Aren't Gonna Need It)
   ```

3. **Equipo pequeño sin experiencia en Pattern:**
   ```php
   // Team size: 1-3 developers
   // Familiaridad con Repository Pattern: Baja
   // RESULTADO: Curva aprendizaje > beneficio
   ```

---

## Conclusión

### ⚠️ Repository Pattern NO recomendado para llm-manager

**Razones:**

1. ❌ **Over-engineering:** CRUD simple no justifica complejidad
2. ❌ **YAGNI:** Probabilidad <10% de cambiar backend
3. ❌ **Inconsistencia:** Ningún otro módulo usa Repository
4. ❌ **Team overhead:** Curva aprendizaje alta
5. ❌ **ROI negativo:** Complejidad > beneficio

**Alternativa recomendada:**

✅ **Service Layer** (ver [SERVICE-LAYER.md](./SERVICE-LAYER.md))
- Mismos beneficios de testing
- Caching centralizado
- Complejidad 70% menor
- ROI positivo

**Excepción:** SI en el futuro necesitas cambiar backend (Redis, API), ENTONCES migrar Service Layer → Repository Pattern es fácil (interface ya existe en service methods).

---

**Documentación relacionada:**
- [PROTOCOLO-DE-REFACTORIZACION.md](./PROTOCOLO-DE-REFACTORIZACION.md) - Plan general
- [SERVICE-LAYER.md](./SERVICE-LAYER.md) - **Opción recomendada**
- [DTOs.md](./DTOs.md) - Complemento futuro
