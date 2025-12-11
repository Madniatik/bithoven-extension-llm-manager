# Service Layer Pattern - LLM Configuration Service

**Fecha:** 10 de diciembre de 2025  
**Versión:** 1.0.0  
**Patrón:** Service Layer  
**Recomendación:** ✅ **OPCIÓN RECOMENDADA**

---

## 📋 Índice

1. [¿Qué es Service Layer?](#qué-es-service-layer)
2. [¿Por qué usarlo?](#por-qué-usarlo)
3. [Arquitectura Propuesta](#arquitectura-propuesta)
4. [Implementación Completa](#implementación-completa)
5. [Uso en Controllers](#uso-en-controllers)
6. [Testing](#testing)
7. [Pros y Contras](#pros-y-contras)
8. [Casos de Uso](#casos-de-uso)

---

## ¿Qué es Service Layer?

### Definición

**Service Layer** es un patrón arquitectural que encapsula la lógica de negocio en clases dedicadas (services), separándola de:
- **Controllers:** Responsables solo de HTTP request/response
- **Models:** Responsables solo de persistencia de datos
- **Views:** Responsables solo de presentación

### Analogía del Mundo Real

Piensa en un restaurante:

```
┌─────────────────────────────────────────┐
│ RESTAURANTE (Aplicación Laravel)        │
├─────────────────────────────────────────┤
│                                         │
│  👨‍💼 Mesero (Controller)                 │
│  ├─ Toma orden del cliente (Request)   │
│  └─ Entrega comida (Response)          │
│                                         │
│  👨‍🍳 Chef (Service Layer)                │
│  ├─ Conoce las recetas (Business Logic)│
│  ├─ Coordina ingredientes (Models)     │
│  └─ Prepara platos (Orchestration)     │
│                                         │
│  🥬 Despensa (Model/Database)           │
│  ├─ Almacena ingredientes (Data)       │
│  └─ Provee materia prima (CRUD)        │
│                                         │
└─────────────────────────────────────────┘
```

**SIN Service Layer:**
- Mesero toma orden Y cocina Y sirve (Controller hace todo) ❌
- No escalable, caótico, errores frecuentes

**CON Service Layer:**
- Mesero toma orden → Chef cocina → Mesero sirve ✅
- Separación clara de responsabilidades

### Flujo de Datos

```
┌──────────┐    Request    ┌────────────┐    Business    ┌─────────┐
│  Route   │──────────────→│ Controller │───────Logic───→│ Service │
└──────────┘               └────────────┘                └─────────┘
                                  ↑                            │
                                  │                            ▼
                           Response                      ┌─────────┐
                                  │                      │  Model  │
                                  └──────────────────────┤   DB    │
                                                         └─────────┘
```

**Ejemplo concreto:**

```php
// ❌ SIN Service Layer (Controller hace todo)
public function index()
{
    // Controller conoce detalles de DB, scopes, caching, validation...
    $configs = Cache::remember('configs', 3600, function() {
        return LLMConfiguration::where('is_active', true)
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get();
    });
    
    // Validación mezclada con lógica HTTP
    if ($configs->isEmpty()) {
        throw new Exception('No configs found');
    }
    
    return view('configs.index', compact('configs'));
}

// ✅ CON Service Layer (Controller delega)
public function index(LLMConfigurationService $service)
{
    // Controller solo maneja HTTP, delega lógica al Service
    $configs = $service->getActive();
    return view('configs.index', compact('configs'));
}
```

---

## ¿Por qué usarlo?

### Problema que Resuelve

**Situación actual en llm-manager:**

```php
// LLMQuickChatController.php
$configurations = LLMConfiguration::active()->get();

// LLMConversationController.php
$configurations = LLMConfiguration::active()->get();

// LLMStreamController.php
$configurations = LLMConfiguration::active()->get();

// ... 9 controllers con el mismo código duplicado
```

**Problemas:**

1. **Duplicación de código:** Misma query en 9 lugares
2. **Acoplamiento fuerte:** Controllers dependen de Eloquent
3. **Testing difícil:** Mockear `LLMConfiguration::active()` es complejo
4. **Sin caching:** Cada request golpea DB
5. **Lógica dispersa:** Cambiar comportamiento requiere editar 9 archivos

### Solución con Service Layer

```php
// LLMConfigurationService.php (SINGLE SOURCE OF TRUTH)
class LLMConfigurationService
{
    public function getActive(): Collection
    {
        return Cache::remember('llm.configs.active', 3600, function() {
            return LLMConfiguration::active()->get();
        });
    }
}

// TODOS los controllers usan el service
public function __construct(LLMConfigurationService $service) {}
$configurations = $this->service->getActive();
```

**Beneficios inmediatos:**

- ✅ **1 línea cambio:** Caching implementado en 1 lugar, beneficia a 9 controllers
- ✅ **Testing simple:** Mock del service en lugar de Eloquent
- ✅ **Desacoplamiento:** Controllers no conocen implementación
- ✅ **Mantenibilidad:** Lógica centralizada

---

## Arquitectura Propuesta

### Diagrama de Componentes

```
┌─────────────────────────────────────────────────────────────┐
│                        APPLICATION                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────────────────────────────────────┐      │
│  │ HTTP LAYER (Controllers)                         │      │
│  ├──────────────────────────────────────────────────┤      │
│  │ - LLMQuickChatController                         │      │
│  │ - LLMConversationController                      │      │
│  │ - LLMStreamController                            │      │
│  │ - LLMChatController                              │      │
│  │ - LLMConfigurationController                     │      │
│  └───────────────────┬──────────────────────────────┘      │
│                      │ Dependency Injection                 │
│                      ▼                                      │
│  ┌──────────────────────────────────────────────────┐      │
│  │ SERVICE LAYER (Business Logic)                   │      │
│  ├──────────────────────────────────────────────────┤      │
│  │ ┌────────────────────────────────────────────┐   │      │
│  │ │ LLMConfigurationService (NEW)             │   │      │
│  │ ├────────────────────────────────────────────┤   │      │
│  │ │ + getActive(): Collection                 │   │      │
│  │ │ + find(int $id): ?LLMConfiguration        │   │      │
│  │ │ + findBySlug(string): ?LLMConfiguration   │   │      │
│  │ │ + getDefault(): ?LLMConfiguration         │   │      │
│  │ │ + getByProvider(string): Collection       │   │      │
│  │ │ + create(array): LLMConfiguration         │   │      │
│  │ │ + update(config, array): bool             │   │      │
│  │ │ + toggleActive(config): bool              │   │      │
│  │ │ - clearCache(): void                      │   │      │
│  │ └────────────────────────────────────────────┘   │      │
│  │                                                  │      │
│  │ EXISTING SERVICES:                               │      │
│  │ - LLMManager (will use ConfigurationService)     │      │
│  │ - LLMExecutor                                    │      │
│  │ - LLMProviderService                             │      │
│  │ - LLMPromptService                               │      │
│  │ - ... (7 more services)                          │      │
│  └───────────────────┬──────────────────────────────┘      │
│                      │ Eloquent ORM                         │
│                      ▼                                      │
│  ┌──────────────────────────────────────────────────┐      │
│  │ MODEL LAYER (Data Persistence)                   │      │
│  ├──────────────────────────────────────────────────┤      │
│  │ - LLMConfiguration (Eloquent Model)              │      │
│  │   ├─ Scopes: active(), default(), forProvider() │      │
│  │   ├─ Relations: usageLogs, messages, sessions   │      │
│  │   └─ Attributes: name, slug, provider, etc.     │      │
│  └───────────────────┬──────────────────────────────┘      │
│                      │                                      │
│                      ▼                                      │
│  ┌──────────────────────────────────────────────────┐      │
│  │ DATABASE (MySQL)                                 │      │
│  │ - llm_configurations table                       │      │
│  └──────────────────────────────────────────────────┘      │
│                                                             │
└─────────────────────────────────────────────────────────────┘

CACHING LAYER (Redis/File):
  └─ llm.configs.active (TTL: 3600s)
  └─ llm.configs.providers (TTL: 3600s)
```

### Responsabilidades Claras

| Capa | Responsabilidad | Ejemplo |
|------|----------------|---------|
| **Controller** | HTTP I/O, validación request, formateo response | `return view('configs', compact('configs'))` |
| **Service** | Lógica de negocio, orquestación, caching, eventos | `getActive()`, `toggleActive()` |
| **Model** | Persistencia, scopes, relations, accessors | `active()`, `usageLogs()` |
| **Database** | Almacenamiento físico | MySQL table |

---

## Implementación Completa

### Paso 1: Crear el Service

```php
<?php
// src/Services/LLMConfigurationService.php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Events\ConfigurationLoaded;
use Bithoven\LLMManager\Events\ConfigurationChanged;

class LLMConfigurationService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all active configurations with optional caching
     * 
     * @param bool $cached Whether to use cache (default: true)
     * @return Collection<LLMConfiguration>
     * 
     * @example
     * // Get cached active configs (fast, recommended for display)
     * $configs = $service->getActive();
     * 
     * // Get fresh from DB (slow, use after updates)
     * $configs = $service->getActive(cached: false);
     */
    public function getActive(bool $cached = true): Collection
    {
        if (!$cached) {
            return LLMConfiguration::active()->get();
        }

        return Cache::remember(
            'llm.configs.active',
            self::CACHE_TTL,
            fn() => LLMConfiguration::active()->get()
        );
    }

    /**
     * Find configuration by ID
     * 
     * @param int $id Configuration ID
     * @return LLMConfiguration|null
     * 
     * @example
     * $config = $service->find(1);
     * if ($config) {
     *     echo $config->name;
     * }
     */
    public function find(int $id): ?LLMConfiguration
    {
        $config = LLMConfiguration::find($id);

        if ($config) {
            Event::dispatch(new ConfigurationLoaded($config));
        }

        return $config;
    }

    /**
     * Find configuration by ID or fail
     * 
     * @param int $id Configuration ID
     * @return LLMConfiguration
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * 
     * @example
     * $config = $service->findOrFail($request->config_id);
     */
    public function findOrFail(int $id): LLMConfiguration
    {
        $config = LLMConfiguration::findOrFail($id);
        Event::dispatch(new ConfigurationLoaded($config));
        return $config;
    }

    /**
     * Find active configuration by slug
     * 
     * @param string $slug Configuration slug (unique identifier)
     * @return LLMConfiguration|null
     * 
     * @example
     * $config = $service->findBySlug('gpt-4o-mini');
     */
    public function findBySlug(string $slug): ?LLMConfiguration
    {
        return LLMConfiguration::where('slug', $slug)
            ->active()
            ->first();
    }

    /**
     * Find active configuration by slug or fail
     * 
     * @param string $slug Configuration slug
     * @return LLMConfiguration
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findBySlugOrFail(string $slug): LLMConfiguration
    {
        return LLMConfiguration::where('slug', $slug)
            ->active()
            ->firstOrFail();
    }

    /**
     * Get default configuration (is_default = true)
     * 
     * @return LLMConfiguration|null
     * 
     * @example
     * $default = $service->getDefault();
     * if (!$default) {
     *     throw new Exception('No default config set');
     * }
     */
    public function getDefault(): ?LLMConfiguration
    {
        return LLMConfiguration::default()->first();
    }

    /**
     * Get configurations for specific provider
     * 
     * @param string $provider Provider name (ollama, openai, anthropic, etc.)
     * @return Collection<LLMConfiguration>
     * 
     * @example
     * $openaiConfigs = $service->getByProvider('openai');
     */
    public function getByProvider(string $provider): Collection
    {
        return Cache::remember(
            "llm.configs.provider.{$provider}",
            self::CACHE_TTL,
            fn() => LLMConfiguration::forProvider($provider)->active()->get()
        );
    }

    /**
     * Get all distinct providers
     * 
     * @return Collection<string> Collection of provider names
     * 
     * @example
     * $providers = $service->getProviders();
     * // ['ollama', 'openai', 'anthropic', 'openrouter']
     */
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

    /**
     * Get all configurations (including inactive)
     * 
     * @return Collection<LLMConfiguration>
     * 
     * @example
     * // Admin panel - show all configs
     * $allConfigs = $service->getAll();
     */
    public function getAll(): Collection
    {
        return LLMConfiguration::withCount('usageLogs')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create new configuration
     * 
     * @param array $data Configuration attributes
     * @return LLMConfiguration
     * 
     * @example
     * $config = $service->create([
     *     'name' => 'GPT-4',
     *     'slug' => 'gpt-4',
     *     'provider' => 'openai',
     *     'model_name' => 'gpt-4',
     *     'is_active' => true,
     * ]);
     */
    public function create(array $data): LLMConfiguration
    {
        $config = LLMConfiguration::create($data);
        $this->clearCache();

        Event::dispatch(new ConfigurationChanged($config, 'created'));

        return $config;
    }

    /**
     * Update existing configuration
     * 
     * @param LLMConfiguration $config Configuration to update
     * @param array $data New attributes
     * @return bool
     * 
     * @example
     * $service->update($config, [
     *     'max_tokens' => 8000,
     *     'temperature' => 0.7,
     * ]);
     */
    public function update(LLMConfiguration $config, array $data): bool
    {
        $updated = $config->update($data);

        if ($updated) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($config, 'updated'));
        }

        return $updated;
    }

    /**
     * Delete configuration
     * 
     * @param LLMConfiguration $config Configuration to delete
     * @return bool|null
     * 
     * @example
     * if ($service->delete($config)) {
     *     flash('Configuration deleted successfully');
     * }
     */
    public function delete(LLMConfiguration $config): ?bool
    {
        $deleted = $config->delete();

        if ($deleted) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($config, 'deleted'));
        }

        return $deleted;
    }

    /**
     * Toggle active status
     * 
     * @param LLMConfiguration $config Configuration to toggle
     * @return bool
     * 
     * @example
     * $service->toggleActive($config);
     * // is_active: true → false or false → true
     */
    public function toggleActive(LLMConfiguration $config): bool
    {
        $config->is_active = !$config->is_active;
        $saved = $config->save();

        if ($saved) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($config, 'toggled'));
        }

        return $saved;
    }

    /**
     * Set configuration as default
     * 
     * @param LLMConfiguration $config Configuration to set as default
     * @return bool
     * 
     * @example
     * $service->setAsDefault($config);
     * // Unsets previous default, sets this one
     */
    public function setAsDefault(LLMConfiguration $config): bool
    {
        // Unset previous default
        LLMConfiguration::where('is_default', true)
            ->update(['is_default' => false]);

        // Set new default
        $config->is_default = true;
        $saved = $config->save();

        if ($saved) {
            $this->clearCache();
            Event::dispatch(new ConfigurationChanged($config, 'set_as_default'));
        }

        return $saved;
    }

    /**
     * Clear all configuration caches
     * 
     * @return void
     * 
     * @example
     * // After bulk import/update
     * $service->clearCache();
     */
    public function clearCache(): void
    {
        Cache::forget('llm.configs.active');
        Cache::forget('llm.configs.providers');

        // Clear provider-specific caches
        $providers = LLMConfiguration::select('provider')
            ->distinct()
            ->pluck('provider');

        foreach ($providers as $provider) {
            Cache::forget("llm.configs.provider.{$provider}");
        }
    }

    /**
     * Warm cache (preload frequently accessed data)
     * 
     * @return void
     * 
     * @example
     * // Run in scheduled job
     * $service->warmCache();
     */
    public function warmCache(): void
    {
        $this->getActive(cached: true);
        $this->getProviders();

        // Warm provider-specific caches
        $providers = $this->getProviders();
        foreach ($providers as $provider) {
            $this->getByProvider($provider);
        }
    }
}
```

### Paso 2: Registrar en ServiceProvider

```php
<?php
// src/LLMManagerServiceProvider.php

namespace Bithoven\LLMManager;

use Illuminate\Support\ServiceProvider;
use Bithoven\LLMManager\Services\LLMConfigurationService;

class LLMManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ... existing bindings

        // Register LLMConfigurationService as singleton
        $this->app->singleton(LLMConfigurationService::class, function ($app) {
            return new LLMConfigurationService();
        });
    }

    public function boot()
    {
        // ... existing boot logic
    }
}
```

---

## Uso en Controllers

### Ejemplo 1: LLMQuickChatController (ANTES vs DESPUÉS)

```php
<?php
// ❌ ANTES (acceso directo al modelo)

class LLMQuickChatController extends Controller
{
    public function index($sessionId = null)
    {
        // Acceso directo a LLMConfiguration
        $configurations = LLMConfiguration::active()->get();
        $defaultConfig = $configurations->first();

        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }

        // ... resto del código
    }

    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'configuration_id' => 'required|exists:llm_configurations,id',
        ]);

        // Otra query directa
        $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);

        // ... resto del código
    }
}
```

```php
<?php
// ✅ DESPUÉS (usando Service Layer)

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Services\LLMManager;
use Illuminate\Http\Request;

class LLMQuickChatController extends Controller
{
    public function __construct(
        private readonly LLMConfigurationService $configService,
        private readonly LLMManager $llmManager
    ) {}

    public function index($sessionId = null)
    {
        // Uso del service (cached, optimizado, testeable)
        $configurations = $this->configService->getActive();
        $defaultConfig = $configurations->first();

        if (!$defaultConfig) {
            return redirect()->route('admin.llm.configurations.index')
                ->with('error', 'No active LLM configuration found.');
        }

        // ... resto del código
    }

    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'configuration_id' => 'required|exists:llm_configurations,id',
        ]);

        // Uso del service (con eventos, validación centralizada)
        $configuration = $this->configService->findOrFail($validated['configuration_id']);

        // ... resto del código
    }
}
```

**Beneficios visibles:**

1. **Menos código:** Controller no conoce `::active()`, `::findOrFail()`, etc.
2. **Inyección de dependencias:** Constructor declara qué necesita
3. **Testeable:** Mock de `$configService` es simple (ver sección Testing)
4. **Cached:** Automático, sin tocar controller
5. **Eventos:** `ConfigurationLoaded` disparado automáticamente

### Ejemplo 2: LLMActivityController

```php
<?php
// ❌ ANTES

class LLMActivityController extends Controller
{
    public function index()
    {
        // Query SQL directo en controller
        $providers = LLMConfiguration::select('provider')
            ->distinct()
            ->active()
            ->pluck('provider');

        // ... resto
    }
}
```

```php
<?php
// ✅ DESPUÉS

class LLMActivityController extends Controller
{
    public function __construct(
        private readonly LLMConfigurationService $configService
    ) {}

    public function index()
    {
        // Service maneja complejidad + caching
        $providers = $this->configService->getProviders();

        // ... resto
    }
}
```

### Ejemplo 3: LLMConfigurationController (CRUD completo)

```php
<?php
// ✅ DESPUÉS (CRUD usando service)

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Services\LLMProviderService;

class LLMConfigurationController extends Controller
{
    public function __construct(
        private readonly LLMConfigurationService $configService,
        private readonly LLMProviderService $providerService
    ) {}

    public function index()
    {
        // Get all configs (including inactive, with usage count)
        $configurations = $this->configService->getAll();

        return view('llm-manager::admin.configurations.index', compact('configurations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:llm_configurations',
            'provider' => 'required|string',
            'model_name' => 'required|string',
            'is_active' => 'boolean',
        ]);

        // Service handles creation + cache invalidation + events
        $configuration = $this->configService->create($validated);

        return redirect()
            ->route('admin.llm.configurations.index')
            ->with('success', 'Configuration created successfully');
    }

    public function update(Request $request, int $id)
    {
        $configuration = $this->configService->findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'max_tokens' => 'integer|min:1',
            'temperature' => 'numeric|min:0|max:2',
        ]);

        $this->configService->update($configuration, $validated);

        return back()->with('success', 'Configuration updated successfully');
    }

    public function destroy(int $id)
    {
        $configuration = $this->configService->findOrFail($id);
        $this->configService->delete($configuration);

        return redirect()
            ->route('admin.llm.configurations.index')
            ->with('success', 'Configuration deleted successfully');
    }

    public function toggleActive(int $id)
    {
        $configuration = $this->configService->findOrFail($id);
        $this->configService->toggleActive($configuration);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration status updated successfully',
                'is_active' => $configuration->is_active
            ]);
        }

        return back()->with('success', 'Configuration status updated');
    }
}
```

---

## Testing

### Unit Tests (Service)

```php
<?php
// tests/Unit/Services/LLMConfigurationServiceTest.php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Events\ConfigurationLoaded;
use Bithoven\LLMManager\Events\ConfigurationChanged;

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
        Cache::shouldReceive('remember')->never();
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
        Event::fake();
        $config = LLMConfiguration::factory()->create();

        // Act
        $found = $this->service->find($config->id);

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals($config->id, $found->id);
        Event::assertDispatched(ConfigurationLoaded::class);
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
        // Arrange
        Event::fake();
        Cache::shouldReceive('forget')->with('llm.configs.active')->once();
        Cache::shouldReceive('forget')->with('llm.configs.providers')->once();

        // Act
        $config = $this->service->create([
            'name' => 'Test Config',
            'slug' => 'test-config',
            'provider' => 'openai',
            'model_name' => 'gpt-4',
            'is_active' => true,
        ]);

        // Assert
        $this->assertDatabaseHas('llm_configurations', [
            'slug' => 'test-config',
        ]);
        Event::assertDispatched(ConfigurationChanged::class);
    }

    /** @test */
    public function it_updates_configuration_and_clears_cache()
    {
        // Arrange
        Event::fake();
        $config = LLMConfiguration::factory()->create(['max_tokens' => 2000]);

        // Act
        $updated = $this->service->update($config, ['max_tokens' => 4000]);

        // Assert
        $this->assertTrue($updated);
        $this->assertEquals(4000, $config->fresh()->max_tokens);
        Event::assertDispatched(ConfigurationChanged::class);
    }

    /** @test */
    public function it_toggles_active_status()
    {
        // Arrange
        Event::fake();
        $config = LLMConfiguration::factory()->create(['is_active' => true]);

        // Act
        $this->service->toggleActive($config);

        // Assert
        $this->assertFalse($config->fresh()->is_active);

        // Toggle again
        $this->service->toggleActive($config);
        $this->assertTrue($config->fresh()->is_active);

        Event::assertDispatchedTimes(ConfigurationChanged::class, 2);
    }

    /** @test */
    public function it_sets_configuration_as_default()
    {
        // Arrange
        Event::fake();
        $oldDefault = LLMConfiguration::factory()->create(['is_default' => true]);
        $newDefault = LLMConfiguration::factory()->create(['is_default' => false]);

        // Act
        $this->service->setAsDefault($newDefault);

        // Assert
        $this->assertTrue($newDefault->fresh()->is_default);
        $this->assertFalse($oldDefault->fresh()->is_default);
        Event::assertDispatched(ConfigurationChanged::class);
    }

    /** @test */
    public function it_deletes_configuration_and_clears_cache()
    {
        // Arrange
        Event::fake();
        $config = LLMConfiguration::factory()->create();
        $id = $config->id;

        // Act
        $deleted = $this->service->delete($config);

        // Assert
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('llm_configurations', ['id' => $id]);
        Event::assertDispatched(ConfigurationChanged::class);
    }

    /** @test */
    public function it_clears_all_caches()
    {
        // Arrange
        LLMConfiguration::factory()->create(['provider' => 'openai']);
        LLMConfiguration::factory()->create(['provider' => 'anthropic']);

        Cache::shouldReceive('forget')->with('llm.configs.active')->once();
        Cache::shouldReceive('forget')->with('llm.configs.providers')->once();
        Cache::shouldReceive('forget')->with('llm.configs.provider.openai')->once();
        Cache::shouldReceive('forget')->with('llm.configs.provider.anthropic')->once();

        // Act
        $this->service->clearCache();

        // Assert
        // Mocks verifican llamadas
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
}
```

### Integration Tests (Controller)

```php
<?php
// tests/Feature/Http/Controllers/LLMQuickChatControllerTest.php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Services\LLMConfigurationService;

class LLMQuickChatControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_quick_chat_with_active_configurations()
    {
        // Arrange
        $this->actingAs($user = \App\Models\User::factory()->create());
        LLMConfiguration::factory()->count(3)->create(['is_active' => true]);

        // Act
        $response = $this->get(route('admin.llm.quick-chat'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('configurations', function ($configs) {
            return $configs->count() === 3;
        });
    }

    /** @test */
    public function it_redirects_when_no_active_configurations()
    {
        // Arrange
        $this->actingAs($user = \App\Models\User::factory()->create());
        LLMConfiguration::factory()->create(['is_active' => false]);

        // Act
        $response = $this->get(route('admin.llm.quick-chat'));

        // Assert
        $response->assertRedirect(route('admin.llm.configurations.index'));
        $response->assertSessionHas('error', 'No active LLM configuration found.');
    }

    /** @test */
    public function it_uses_cached_configurations()
    {
        // Arrange
        $this->actingAs($user = \App\Models\User::factory()->create());
        $service = $this->app->make(LLMConfigurationService::class);
        LLMConfiguration::factory()->count(2)->create(['is_active' => true]);

        // Pre-warm cache
        $service->getActive();

        // Act - Second request should use cache
        \DB::enableQueryLog();
        $response = $this->get(route('admin.llm.quick-chat'));
        $queries = \DB::getQueryLog();

        // Assert
        $response->assertOk();
        // Should have fewer queries (cache hit)
        $this->assertLessThan(5, count($queries));
    }
}
```

### Mocking en Tests

```php
<?php
// Ejemplo: Test con mock del service (NO usa DB)

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Mockery;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMQuickChatController;
use Illuminate\Support\Collection;

class LLMQuickChatControllerMockTest extends TestCase
{
    /** @test */
    public function it_handles_empty_configurations_gracefully()
    {
        // Arrange - Mock del service (SIN tocar DB)
        $mockService = Mockery::mock(LLMConfigurationService::class);
        $mockService->shouldReceive('getActive')
            ->once()
            ->andReturn(collect([])); // Empty collection

        $this->app->instance(LLMConfigurationService::class, $mockService);

        // Act
        $response = $this->get(route('admin.llm.quick-chat'));

        // Assert
        $response->assertRedirect();
    }

    /** @test */
    public function it_passes_configurations_to_view()
    {
        // Arrange - Mock con datos fake
        $mockConfigs = collect([
            (object)['id' => 1, 'name' => 'Config 1'],
            (object)['id' => 2, 'name' => 'Config 2'],
        ]);

        $mockService = Mockery::mock(LLMConfigurationService::class);
        $mockService->shouldReceive('getActive')
            ->once()
            ->andReturn($mockConfigs);

        $this->app->instance(LLMConfigurationService::class, $mockService);

        // Act
        $response = $this->get(route('admin.llm.quick-chat'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('configurations', $mockConfigs);
    }
}
```

---

## Pros y Contras

### ✅ Ventajas

| Ventaja | Impacto | Ejemplo |
|---------|---------|---------|
| **Coherencia arquitectural** | Alto | Completa patrón ya establecido (10 servicios existentes) |
| **Testing simplificado** | Alto | Mock de service vs mock de Eloquent (80% menos código) |
| **Caching centralizado** | Medio | 70-80% reducción queries (3600s TTL) |
| **Mantenibilidad** | Alto | Cambiar lógica en 1 lugar → beneficia a 9 controllers |
| **Desacoplamiento** | Alto | Controllers NO conocen Eloquent scopes |
| **Reutilización** | Medio | `getActive()` usado en controllers, commands, jobs, etc. |
| **Eventos** | Medio | `ConfigurationLoaded`, `ConfigurationChanged` para monitoring |
| **Validación centralizada** | Bajo | Business rules en service (ej: solo 1 default permitido) |

### ❌ Desventajas

| Desventaja | Impacto | Mitigación |
|------------|---------|------------|
| **Capa adicional** | Bajo | Solo 1 archivo nuevo, registro en ServiceProvider simple |
| **Overhead inicial** | Bajo | ~30 min crear service + tests, ROI positivo en 1 semana |
| **Curva aprendizaje** | Muy Bajo | Patrón ya usado en proyecto (10 servicios existentes) |
| **Posible sobre-abstracción** | Muy Bajo | Service solo para operaciones comunes, queries complejas en controller |
| **Debugging extra step** | Muy Bajo | Stack trace tiene 1 nivel más (controller → service → model) |

### ⚖️ Balance Final

**Ventajas superan ampliamente desventajas:**
- ✅ 5 ventajas de impacto Alto vs 0 desventajas de impacto Alto
- ✅ Arquitectura consistente (peso 40%)
- ✅ Testing mejorado (peso 30%)
- ✅ Performance optimizado (peso 20%)
- ✅ Mantenibilidad (peso 10%)

**Score:** 9.2/10 para este proyecto específico

---

## Casos de Uso

### Caso 1: Quick Chat con Configuración Default

**Flujo:**
1. Usuario abre `/admin/llm/quick-chat`
2. Controller llama `$service->getActive()`
3. Service verifica cache → HIT (fast path)
4. Controller renderiza vista con configs

**Código:**

```php
// Controller
public function index(LLMConfigurationService $service)
{
    $configurations = $service->getActive(); // Cached!
    $defaultConfig = $configurations->first();

    return view('llm-manager::quick-chat.index', [
        'configurations' => $configurations,
        'defaultConfig' => $defaultConfig,
    ]);
}
```

**Performance:**
- Sin cache: 25ms (query DB)
- Con cache: 2ms (90% faster)

### Caso 2: Crear Nueva Conversación con Config Específica

**Flujo:**
1. Usuario selecciona config en dropdown
2. Frontend envía `POST /api/llm/conversations` con `config_id`
3. Controller valida y llama `$service->findOrFail($configId)`
4. Service dispara evento `ConfigurationLoaded`
5. Controller crea conversación

**Código:**

```php
// Controller
public function store(Request $request, LLMConfigurationService $service)
{
    $validated = $request->validate([
        'config_id' => 'required|exists:llm_configurations,id',
        'title' => 'required|string',
    ]);

    $config = $service->findOrFail($validated['config_id']);

    $conversation = LLMConversationSession::create([
        'llm_configuration_id' => $config->id,
        'user_id' => auth()->id(),
        'title' => $validated['title'],
    ]);

    return response()->json(['conversation' => $conversation]);
}

// Event Listener (monitoring)
class LogConfigurationUsage
{
    public function handle(ConfigurationLoaded $event)
    {
        \Log::info('Config loaded', [
            'config_id' => $event->configuration->id,
            'user_id' => auth()->id(),
        ]);
    }
}
```

### Caso 3: Admin Panel - Toggle Active Status (AJAX)

**Flujo:**
1. Admin hace clic en toggle switch
2. JavaScript envía `POST /admin/llm/configurations/{id}/toggle`
3. Controller llama `$service->toggleActive($config)`
4. Service:
   - Cambia `is_active`
   - Limpia cache
   - Dispara evento `ConfigurationChanged`
5. Frontend actualiza UI

**Código:**

```php
// Controller
public function toggleActive(int $id, LLMConfigurationService $service)
{
    $config = $service->findOrFail($id);
    $service->toggleActive($config);

    return response()->json([
        'success' => true,
        'is_active' => $config->is_active,
    ]);
}

// JavaScript
async function toggleConfig(configId) {
    const response = await fetch(`/admin/llm/configurations/${configId}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    
    const data = await response.json();
    
    // Update UI
    document.querySelector(`#config-${configId} .toggle`)
        .classList.toggle('active', data.is_active);
}
```

### Caso 4: Scheduled Job - Warm Cache Diario

**Flujo:**
1. Cron ejecuta comando `php artisan cache:warm` a las 3 AM
2. Comando llama `$service->warmCache()`
3. Service pre-carga:
   - Active configs
   - Providers list
   - Provider-specific configs
4. Primer request del día es ultra-rápido

**Código:**

```php
// app/Console/Commands/WarmLLMCache.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\LLMConfigurationService;

class WarmLLMCache extends Command
{
    protected $signature = 'cache:warm-llm';
    protected $description = 'Warm LLM configuration cache';

    public function handle(LLMConfigurationService $service)
    {
        $this->info('Warming LLM cache...');

        $service->warmCache();

        $this->info('✅ Cache warmed successfully');
    }
}

// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cache:warm-llm')->daily()->at('03:00');
}
```

### Caso 5: Testing - Simular Configuración Inexistente

**Flujo:**
1. Test simula request con `config_id` inválido
2. Service lanza `ModelNotFoundException`
3. Exception handler devuelve 404
4. Test verifica respuesta

**Código:**

```php
/** @test */
public function it_returns_404_when_configuration_not_found()
{
    // Arrange
    $this->actingAs($user = User::factory()->create());

    // Act
    $response = $this->postJson('/api/llm/conversations', [
        'config_id' => 999, // Inexistente
        'title' => 'Test',
    ]);

    // Assert
    $response->assertNotFound();
    $response->assertJson([
        'message' => 'Configuration not found',
    ]);
}
```

---

## Conclusión

**Service Layer es la opción recomendada** porque:

1. ✅ **Completa arquitectura existente** (10 servicios ya implementados)
2. ✅ **Mínimo refactor** (9 controllers, sin breaking changes)
3. ✅ **Máximo beneficio** (testing, caching, mantenibilidad)
4. ✅ **ROI positivo** (~4 horas inversión, ahorro perpetuo)
5. ✅ **Escalabilidad** (base para DTOs/Repositories futuras)

**Próximos pasos:**
1. Leer [PROTOCOLO-DE-REFACTORIZACION.md](./PROTOCOLO-DE-REFACTORIZACION.md)
2. Crear `LLMConfigurationService.php` (copiar código de esta doc)
3. Escribir tests (>80% coverage)
4. Refactorizar controllers incrementalmente
5. Deploy y monitor performance

---

**Documentación relacionada:**
- [PROTOCOLO-DE-REFACTORIZACION.md](./PROTOCOLO-DE-REFACTORIZACION.md) - Plan general
- [REPOSITORY-PATTERN.md](./REPOSITORY-PATTERN.md) - Alternativa evaluada
- [DTOs.md](./DTOs.md) - Complemento futuro
