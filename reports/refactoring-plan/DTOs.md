# Data Transfer Objects (DTOs) - LLM Configuration

**Fecha:** 10 de diciembre de 2025  
**Versión:** 0.1.0  
**Patrón:** Data Transfer Objects (DTOs)  
**Recomendación:** ⚠️ **COMPLEMENTO, no solución primaria**

---

## 📋 Índice

1. [¿Qué son DTOs?](#qué-son-dtos)
2. [¿Por qué usarlos?](#por-qué-usarlos)
3. [Arquitectura Propuesta](#arquitectura-propuesta)
4. [Implementación Completa](#implementación-completa)
5. [Uso en la Aplicación](#uso-en-la-aplicación)
6. [Testing](#testing)
7. [Pros y Contras](#pros-y-contras)
8. [Casos de Uso](#casos-de-uso)

---

## ¿Qué son DTOs?

### Definición

**Data Transfer Object (DTO)** es un objeto simple que transporta datos entre capas de la aplicación. No contiene lógica de negocio, solo propiedades y getters/setters.

### Analogía del Mundo Real

Piensa en un formulario de pedido en un restaurante:

```
┌──────────────────────────────────────────────┐
│ RESTAURANTE (Aplicación Laravel)             │
├──────────────────────────────────────────────┤
│                                              │
│  👤 Cliente (Frontend/Request)               │
│  └─ Llena formulario de pedido              │
│                                              │
│       ▼                                      │
│  📋 Formulario (DTO)                         │
│  ├─ Mesa: 5                                  │
│  ├─ Plato: Pasta Carbonara                  │
│  ├─ Cantidad: 2                              │
│  ├─ Nota: Sin bacon                          │
│  └─ [SOLO DATOS, NO PROCESA]                │
│                                              │
│       ▼                                      │
│  👨‍🍳 Chef (Service/Controller)                │
│  ├─ Lee formulario (DTO)                    │
│  ├─ Valida datos (type-safe)                │
│  └─ Procesa pedido (business logic)         │
│                                              │
│       ▼                                      │
│  🗄️ Base de datos (Model)                   │
│  └─ Almacena pedido                          │
│                                              │
└──────────────────────────────────────────────┘
```

**Clave:** Formulario (DTO) no cocina, no valida stock, no calcula precio. Solo transporta información de manera estructurada.

### Comparación con otros patrones

```
┌─────────────────────────────────────────────────────────┐
│                   DATA HANDLING                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ELOQUENT MODEL (Active Record)                        │
│  ├─ Datos + Persistencia + Business Logic              │
│  └─ Ejemplo: $config->save(), $config->usageLogs()     │
│                                                         │
│  DTO (Data Transfer Object)                            │
│  ├─ SOLO Datos (read-only)                             │
│  └─ Ejemplo: $dto->getName(), $dto->getMaxTokens()     │
│                                                         │
│  VALUE OBJECT (Domain-Driven Design)                   │
│  ├─ Datos + Validación + Inmutabilidad                 │
│  └─ Ejemplo: new Temperature(25) throws si < -273      │
│                                                         │
│  ARRAY (Plain PHP)                                     │
│  ├─ Sin type safety, sin IDE autocomplete              │
│  └─ Ejemplo: $data['max_tokens'] // typo? runtime error│
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Flujo de Datos con DTO

```
┌──────────┐    Request    ┌────────────┐    DTO    ┌──────────┐
│  Route   │──────────────→│ Controller │──────────→│ Service  │
└──────────┘               └────────────┘           └──────────┘
                                  ↑                       │
                                  │                       ▼
                           Response DTO           ┌──────────┐
                                  │               │  Model   │
                                  │               │   DB     │
                                  └───────────────┤          │
                                                  └──────────┘

EJEMPLO:
1. Request: ['name' => 'GPT-4', 'max_tokens' => 8000]
2. Controller crea: ConfigurationDTO::fromRequest($request)
3. Service recibe: processConfiguration(ConfigurationDTO $dto)
4. Service persiste: LLMConfiguration::create($dto->toArray())
5. Service devuelve: ConfigurationDTO::fromModel($config)
6. Controller responde: return response()->json($dto->toArray())
```

**Ejemplo concreto:**

```php
// ❌ SIN DTO (array sin tipo)
public function store(Request $request)
{
    $data = $request->all(); // array asociativo
    // IDE no sabe qué keys existen
    // Typos: $data['max_token'] vs $data['max_tokens']
    
    $service->create($data); // ¿Qué estructura espera?
}

// ✅ CON DTO (type-safe)
public function store(Request $request)
{
    $dto = ConfigurationDTO::fromRequest($request);
    // IDE autocomplete: $dto->getName(), $dto->getMaxTokens()
    // Typos: Imposibles (compile-time error)
    
    $service->create($dto); // Contrato claro
}
```

---

## ¿Por qué usarlos?

### Problema que Resuelve

**Situación actual en llm-manager:**

```php
// Controller
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'max_tokens' => 'required|integer',
        // ... 20 campos más
    ]);
    
    // ¿Qué estructura tiene $validated?
    // IDE no sabe, solo array asociativo
    
    $this->service->create($validated); // Pasando array genérico
}

// Service
public function create(array $data): LLMConfiguration
{
    // ¿Qué keys existen en $data?
    // ¿Son opcionales u obligatorias?
    // ¿Qué tipos tienen?
    
    return LLMConfiguration::create($data);
}
```

**Problemas:**

1. **Sin type safety:** `$data['max_tokens']` puede ser string, int, null... runtime error
2. **Sin IDE autocomplete:** No sabes qué keys existen hasta revisar código
3. **Documentación implícita:** Tienes que leer `$request->validate()` para saber estructura
4. **Refactor peligroso:** Cambiar key en 1 lugar rompe en N lugares sin avisar

### Solución con DTOs

```php
// DTO
class ConfigurationDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $provider,
        public readonly string $modelName,
        public readonly int $maxTokens,
        public readonly float $temperature,
        public readonly bool $isActive = true,
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            provider: $request->validated('provider'),
            modelName: $request->validated('model_name'),
            maxTokens: $request->validated('max_tokens'),
            temperature: $request->validated('temperature', 0.7),
            isActive: $request->validated('is_active', true),
        );
    }
}

// Controller
public function store(Request $request)
{
    $dto = ConfigurationDTO::fromRequest($request);
    // IDE sabe: $dto->name, $dto->maxTokens, etc.
    // Type-safe: $dto->maxTokens es SIEMPRE int
    
    $this->service->create($dto);
}

// Service
public function create(ConfigurationDTO $dto): LLMConfiguration
{
    // Contrato claro: recibe ConfigurationDTO
    // No más "mystery arrays"
    
    return LLMConfiguration::create([
        'name' => $dto->name,
        'max_tokens' => $dto->maxTokens,
        // ...
    ]);
}
```

**Beneficios inmediatos:**

1. ✅ **Type safety:** `$dto->maxTokens` es SIEMPRE `int`
2. ✅ **IDE autocomplete:** `$dto->` muestra todas las propiedades
3. ✅ **Self-documenting:** Constructor es la documentación
4. ✅ **Refactor seguro:** Renombrar propiedad → IDE encuentra todos los usos

---

## Arquitectura Propuesta

### Diagrama de Componentes

```
┌───────────────────────────────────────────────────────────────────┐
│                        APPLICATION LAYERS                         │
├───────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ HTTP LAYER (Controllers)                               │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │                                                        │      │
│  │  public function store(Request $request)               │      │
│  │  {                                                     │      │
│  │      // Transform Request → DTO                       │      │
│  │      $dto = ConfigurationDTO::fromRequest($request);  │      │
│  │                                                        │      │
│  │      // Pass DTO to Service                           │      │
│  │      $result = $this->service->create($dto);          │      │
│  │                                                        │      │
│  │      // Transform Model → DTO → JSON                  │      │
│  │      return ConfigurationDTO::fromModel($result)      │      │
│  │          ->toArray();                                 │      │
│  │  }                                                     │      │
│  │                                                        │      │
│  └─────────────────────┬──────────────────────────────────┘      │
│                        │                                          │
│                        ▼                                          │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ DTOs (Data Transfer Layer)                             │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │                                                        │      │
│  │  ConfigurationDTO (Main)                              │      │
│  │  ├─ fromRequest(Request): self                        │      │
│  │  ├─ fromModel(LLMConfiguration): self                 │      │
│  │  ├─ fromArray(array): self                            │      │
│  │  ├─ toArray(): array                                  │      │
│  │  └─ toModel(): LLMConfiguration                       │      │
│  │                                                        │      │
│  │  CreateConfigurationDTO (Specific)                    │      │
│  │  ├─ For creation only                                 │      │
│  │  └─ Required fields only                              │      │
│  │                                                        │      │
│  │  UpdateConfigurationDTO (Specific)                    │      │
│  │  ├─ For updates only                                  │      │
│  │  └─ Optional fields (partial update)                  │      │
│  │                                                        │      │
│  │  ConfigurationListItemDTO (Lightweight)               │      │
│  │  ├─ For lists/indexes                                 │      │
│  │  └─ Minimal fields (id, name, status)                 │      │
│  │                                                        │      │
│  └─────────────────────┬──────────────────────────────────┘      │
│                        │                                          │
│                        ▼                                          │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ SERVICE LAYER (Business Logic)                         │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │                                                        │      │
│  │  LLMConfigurationService                              │      │
│  │  ├─ create(CreateConfigurationDTO): ConfigurationDTO  │      │
│  │  ├─ update(int $id, UpdateDTO): ConfigurationDTO      │      │
│  │  ├─ getById(int $id): ConfigurationDTO                │      │
│  │  └─ getAll(): Collection<ConfigurationListItemDTO>    │      │
│  │                                                        │      │
│  └─────────────────────┬──────────────────────────────────┘      │
│                        │                                          │
│                        ▼                                          │
│  ┌────────────────────────────────────────────────────────┐      │
│  │ MODEL LAYER (Data Persistence)                         │      │
│  ├────────────────────────────────────────────────────────┤      │
│  │                                                        │      │
│  │  LLMConfiguration (Eloquent Model)                    │      │
│  │  ├─ Database schema                                   │      │
│  │  ├─ Relationships                                     │      │
│  │  └─ Scopes                                            │      │
│  │                                                        │      │
│  └────────────────────────────────────────────────────────┘      │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘

DATA FLOW:
Request → DTO → Service → Model → DB
DB → Model → DTO → Response
```

### Responsabilidades Claras

| Capa | Responsabilidad | Ejemplo |
|------|----------------|---------|
| **Controller** | HTTP I/O, transformaciones Request↔DTO | `ConfigurationDTO::fromRequest($request)` |
| **DTO** | Transporte de datos type-safe, transformaciones | `toArray()`, `fromModel()` |
| **Service** | Lógica de negocio, validación, orquestación | `create(CreateConfigurationDTO $dto)` |
| **Model** | Persistencia, relaciones, scopes | `LLMConfiguration::create()` |

---

## Implementación Completa

### Paso 1: DTO Base (Abstract)

```php
<?php
// src/DTOs/AbstractDTO.php

namespace Bithoven\LLMManager\DTOs;

abstract class AbstractDTO
{
    /**
     * Convert DTO to array
     */
    abstract public function toArray(): array;

    /**
     * Convert to JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
```

### Paso 2: ConfigurationDTO (Main)

```php
<?php
// src/DTOs/ConfigurationDTO.php

namespace Bithoven\LLMManager\DTOs;

use Illuminate\Http\Request;
use Bithoven\LLMManager\Models\LLMConfiguration;

/**
 * Configuration Data Transfer Object
 * 
 * Immutable object for transferring LLM configuration data
 * between application layers.
 */
class ConfigurationDTO extends AbstractDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $provider,
        public readonly string $modelName,
        public readonly ?string $apiEndpoint,
        public readonly ?string $apiKey,
        public readonly int $maxTokens,
        public readonly float $temperature,
        public readonly int $topP,
        public readonly int $topK,
        public readonly bool $isActive,
        public readonly bool $isDefault,
        public readonly ?string $description = null,
        public readonly ?array $metadata = null,
    ) {}

    /**
     * Create DTO from HTTP Request
     * 
     * @param Request $request Validated request
     * @return self
     * 
     * @example
     * $dto = ConfigurationDTO::fromRequest($request);
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            id: null, // New record
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            provider: $request->validated('provider'),
            modelName: $request->validated('model_name'),
            apiEndpoint: $request->validated('api_endpoint'),
            apiKey: $request->validated('api_key'),
            maxTokens: (int) $request->validated('max_tokens', 2000),
            temperature: (float) $request->validated('temperature', 0.7),
            topP: (int) $request->validated('top_p', 1),
            topK: (int) $request->validated('top_k', 50),
            isActive: (bool) $request->validated('is_active', true),
            isDefault: (bool) $request->validated('is_default', false),
            description: $request->validated('description'),
            metadata: $request->validated('metadata'),
        );
    }

    /**
     * Create DTO from Eloquent Model
     * 
     * @param LLMConfiguration $model
     * @return self
     * 
     * @example
     * $dto = ConfigurationDTO::fromModel($config);
     */
    public static function fromModel(LLMConfiguration $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            provider: $model->provider,
            modelName: $model->model_name,
            apiEndpoint: $model->api_endpoint,
            apiKey: $model->api_key, // Consider masking in production
            maxTokens: $model->max_tokens,
            temperature: $model->temperature,
            topP: $model->top_p,
            topK: $model->top_k,
            isActive: $model->is_active,
            isDefault: $model->is_default,
            description: $model->description,
            metadata: $model->metadata,
        );
    }

    /**
     * Create DTO from array
     * 
     * @param array $data
     * @return self
     * 
     * @example
     * $dto = ConfigurationDTO::fromArray([
     *     'name' => 'GPT-4',
     *     'slug' => 'gpt-4',
     *     // ...
     * ]);
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            slug: $data['slug'],
            provider: $data['provider'],
            modelName: $data['model_name'],
            apiEndpoint: $data['api_endpoint'] ?? null,
            apiKey: $data['api_key'] ?? null,
            maxTokens: (int) ($data['max_tokens'] ?? 2000),
            temperature: (float) ($data['temperature'] ?? 0.7),
            topP: (int) ($data['top_p'] ?? 1),
            topK: (int) ($data['top_k'] ?? 50),
            isActive: (bool) ($data['is_active'] ?? true),
            isDefault: (bool) ($data['is_default'] ?? false),
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    /**
     * Convert DTO to array (for JSON responses, Model creation)
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'provider' => $this->provider,
            'model_name' => $this->modelName,
            'api_endpoint' => $this->apiEndpoint,
            'api_key' => $this->apiKey,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'top_p' => $this->topP,
            'top_k' => $this->topK,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convert DTO to array for API response (without sensitive data)
     * 
     * @return array
     */
    public function toPublicArray(): array
    {
        $data = $this->toArray();
        
        // Mask sensitive data
        if (isset($data['api_key'])) {
            $data['api_key'] = '••••••••';
        }
        
        return $data;
    }

    /**
     * Get only fields for Model creation/update
     * 
     * @return array
     */
    public function toModelArray(): array
    {
        $data = $this->toArray();
        
        // Remove ID (Eloquent handles it)
        unset($data['id']);
        
        return $data;
    }

    /**
     * Create Eloquent Model from DTO
     * 
     * @return LLMConfiguration
     */
    public function toModel(): LLMConfiguration
    {
        return new LLMConfiguration($this->toModelArray());
    }

    /**
     * Check if configuration is for specific provider
     * 
     * @param string $provider
     * @return bool
     */
    public function isProvider(string $provider): bool
    {
        return strtolower($this->provider) === strtolower($provider);
    }

    /**
     * Get display name (for UI)
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->isDefault 
            ? "{$this->name} (Default)" 
            : $this->name;
    }

    /**
     * Get status label (for UI)
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        return $this->isActive ? 'Active' : 'Inactive';
    }
}
```

### Paso 3: DTOs Específicos

```php
<?php
// src/DTOs/CreateConfigurationDTO.php

namespace Bithoven\LLMManager\DTOs;

use Illuminate\Http\Request;

/**
 * DTO for creating new configurations
 * 
 * Only includes required fields
 */
class CreateConfigurationDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $provider,
        public readonly string $modelName,
        public readonly int $maxTokens = 2000,
        public readonly float $temperature = 0.7,
        public readonly bool $isActive = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            provider: $request->validated('provider'),
            modelName: $request->validated('model_name'),
            maxTokens: (int) $request->validated('max_tokens', 2000),
            temperature: (float) $request->validated('temperature', 0.7),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'provider' => $this->provider,
            'model_name' => $this->modelName,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'is_active' => $this->isActive,
        ];
    }
}
```

```php
<?php
// src/DTOs/UpdateConfigurationDTO.php

namespace Bithoven\LLMManager\DTOs;

use Illuminate\Http\Request;

/**
 * DTO for updating configurations
 * 
 * All fields optional (partial update)
 */
class UpdateConfigurationDTO extends AbstractDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $maxTokens = null,
        public readonly ?float $temperature = null,
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            maxTokens: $request->has('max_tokens') 
                ? (int) $request->validated('max_tokens') 
                : null,
            temperature: $request->has('temperature')
                ? (float) $request->validated('temperature')
                : null,
            isActive: $request->has('is_active')
                ? (bool) $request->validated('is_active')
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'is_active' => $this->isActive,
        ], fn($value) => $value !== null);
    }
}
```

```php
<?php
// src/DTOs/ConfigurationListItemDTO.php

namespace Bithoven\LLMManager\DTOs;

use Bithoven\LLMManager\Models\LLMConfiguration;

/**
 * Lightweight DTO for list/index views
 * 
 * Only essential fields to reduce memory
 */
class ConfigurationListItemDTO extends AbstractDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $provider,
        public readonly bool $isActive,
        public readonly bool $isDefault,
    ) {}

    public static function fromModel(LLMConfiguration $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            provider: $model->provider,
            isActive: $model->is_active,
            isDefault: $model->is_default,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'provider' => $this->provider,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
        ];
    }
}
```

---

## Uso en la Aplicación

### Ejemplo 1: Controller con DTOs

```php
<?php
// src/Http/Controllers/Admin/LLMConfigurationController.php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\DTOs\CreateConfigurationDTO;
use Bithoven\LLMManager\DTOs\UpdateConfigurationDTO;
use Bithoven\LLMManager\DTOs\ConfigurationDTO;
use Bithoven\LLMManager\DTOs\ConfigurationListItemDTO;

class LLMConfigurationController extends Controller
{
    public function __construct(
        private readonly LLMConfigurationService $service
    ) {}

    /**
     * List all configurations (lightweight DTOs)
     */
    public function index()
    {
        $configurations = $this->service->getAll()
            ->map(fn($config) => ConfigurationListItemDTO::fromModel($config));

        return view('llm-manager::admin.configurations.index', [
            'configurations' => $configurations,
        ]);
    }

    /**
     * Show single configuration (full DTO)
     */
    public function show(int $id)
    {
        $dto = $this->service->getById($id);

        return view('llm-manager::admin.configurations.show', [
            'configuration' => $dto,
        ]);
    }

    /**
     * Store new configuration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:llm_configurations',
            'provider' => 'required|string',
            'model_name' => 'required|string',
            'max_tokens' => 'integer|min:1',
            'temperature' => 'numeric|min:0|max:2',
        ]);

        // Transform Request → DTO
        $dto = CreateConfigurationDTO::fromRequest($request);

        // Service handles DTO
        $created = $this->service->create($dto);

        return redirect()
            ->route('admin.llm.configurations.show', $created->id)
            ->with('success', 'Configuration created successfully');
    }

    /**
     * Update configuration (partial)
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'max_tokens' => 'integer|min:1',
            'temperature' => 'numeric|min:0|max:2',
            'is_active' => 'boolean',
        ]);

        // Transform Request → DTO (partial)
        $dto = UpdateConfigurationDTO::fromRequest($request);

        // Service handles update
        $updated = $this->service->update($id, $dto);

        return back()->with('success', 'Configuration updated successfully');
    }

    /**
     * API endpoint (JSON response)
     */
    public function apiShow(int $id)
    {
        $dto = $this->service->getById($id);

        // Transform DTO → JSON (public data only)
        return response()->json($dto->toPublicArray());
    }
}
```

### Ejemplo 2: Service usando DTOs

```php
<?php
// src/Services/LLMConfigurationService.php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Collection;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\DTOs\ConfigurationDTO;
use Bithoven\LLMManager\DTOs\CreateConfigurationDTO;
use Bithoven\LLMManager\DTOs\UpdateConfigurationDTO;

class LLMConfigurationService
{
    /**
     * Create configuration from DTO
     * 
     * @param CreateConfigurationDTO $dto
     * @return ConfigurationDTO
     */
    public function create(CreateConfigurationDTO $dto): ConfigurationDTO
    {
        $config = LLMConfiguration::create($dto->toArray());

        return ConfigurationDTO::fromModel($config);
    }

    /**
     * Update configuration with DTO
     * 
     * @param int $id
     * @param UpdateConfigurationDTO $dto
     * @return ConfigurationDTO
     */
    public function update(int $id, UpdateConfigurationDTO $dto): ConfigurationDTO
    {
        $config = LLMConfiguration::findOrFail($id);
        
        // Only update provided fields (partial update)
        $config->update($dto->toArray());

        return ConfigurationDTO::fromModel($config->fresh());
    }

    /**
     * Get configuration by ID as DTO
     * 
     * @param int $id
     * @return ConfigurationDTO
     */
    public function getById(int $id): ConfigurationDTO
    {
        $config = LLMConfiguration::findOrFail($id);
        
        return ConfigurationDTO::fromModel($config);
    }

    /**
     * Get all configurations (Models, not DTOs)
     * 
     * Controller decides which DTO to use
     * 
     * @return Collection<LLMConfiguration>
     */
    public function getAll(): Collection
    {
        return LLMConfiguration::withCount('usageLogs')
            ->orderBy('is_active', 'desc')
            ->get();
    }
}
```

---

## Testing

### Unit Tests (DTO Transformations)

```php
<?php
// tests/Unit/DTOs/ConfigurationDTOTest.php

namespace Tests\Unit\DTOs;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Bithoven\LLMManager\DTOs\ConfigurationDTO;
use Bithoven\LLMManager\Models\LLMConfiguration;

class ConfigurationDTOTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_dto_from_request()
    {
        $request = Request::create('/test', 'POST', [
            'name' => 'GPT-4',
            'slug' => 'gpt-4',
            'provider' => 'openai',
            'model_name' => 'gpt-4',
            'max_tokens' => 8000,
            'temperature' => 0.7,
        ]);

        $request->setValidator(
            validator($request->all(), [
                'name' => 'required',
                'slug' => 'required',
                'provider' => 'required',
                'model_name' => 'required',
                'max_tokens' => 'integer',
                'temperature' => 'numeric',
            ])
        );

        $dto = ConfigurationDTO::fromRequest($request);

        $this->assertEquals('GPT-4', $dto->name);
        $this->assertEquals(8000, $dto->maxTokens);
        $this->assertIsInt($dto->maxTokens); // Type safety
    }

    /** @test */
    public function it_creates_dto_from_model()
    {
        $config = LLMConfiguration::factory()->create([
            'name' => 'Test Config',
            'max_tokens' => 4000,
        ]);

        $dto = ConfigurationDTO::fromModel($config);

        $this->assertEquals($config->id, $dto->id);
        $this->assertEquals($config->name, $dto->name);
        $this->assertEquals($config->max_tokens, $dto->maxTokens);
    }

    /** @test */
    public function it_converts_dto_to_array()
    {
        $dto = new ConfigurationDTO(
            id: 1,
            name: 'Test',
            slug: 'test',
            provider: 'openai',
            modelName: 'gpt-4',
            apiEndpoint: null,
            apiKey: null,
            maxTokens: 4000,
            temperature: 0.7,
            topP: 1,
            topK: 50,
            isActive: true,
            isDefault: false,
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('max_tokens', $array);
        $this->assertEquals(4000, $array['max_tokens']);
    }

    /** @test */
    public function it_masks_sensitive_data_in_public_array()
    {
        $dto = new ConfigurationDTO(
            id: 1,
            name: 'Test',
            slug: 'test',
            provider: 'openai',
            modelName: 'gpt-4',
            apiEndpoint: null,
            apiKey: 'sk-secret-key-12345',
            maxTokens: 4000,
            temperature: 0.7,
            topP: 1,
            topK: 50,
            isActive: true,
            isDefault: false,
        );

        $publicArray = $dto->toPublicArray();

        $this->assertEquals('••••••••', $publicArray['api_key']);
    }

    /** @test */
    public function it_checks_provider()
    {
        $dto = new ConfigurationDTO(
            id: 1,
            name: 'Test',
            slug: 'test',
            provider: 'OpenAI', // Mixed case
            modelName: 'gpt-4',
            apiEndpoint: null,
            apiKey: null,
            maxTokens: 4000,
            temperature: 0.7,
            topP: 1,
            topK: 50,
            isActive: true,
            isDefault: false,
        );

        $this->assertTrue($dto->isProvider('openai')); // Case insensitive
        $this->assertFalse($dto->isProvider('anthropic'));
    }
}
```

### Integration Tests (Controller)

```php
<?php
// tests/Feature/Controllers/LLMConfigurationControllerTest.php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMConfigurationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_configuration_with_dto()
    {
        $this->actingAs($user = \App\Models\User::factory()->create());

        $response = $this->postJson('/admin/llm/configurations', [
            'name' => 'GPT-4',
            'slug' => 'gpt-4',
            'provider' => 'openai',
            'model_name' => 'gpt-4',
            'max_tokens' => 8000,
            'temperature' => 0.7,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('llm_configurations', [
            'name' => 'GPT-4',
            'max_tokens' => 8000,
        ]);
    }

    /** @test */
    public function it_returns_public_dto_in_api_response()
    {
        $this->actingAs($user = \App\Models\User::factory()->create());

        $config = LLMConfiguration::factory()->create([
            'api_key' => 'sk-secret-key-12345',
        ]);

        $response = $this->getJson("/api/llm/configurations/{$config->id}");

        $response->assertOk();
        $response->assertJson([
            'api_key' => '••••••••', // Masked
        ]);
    }
}
```

---

## Pros y Contras

### ✅ Ventajas

| Ventaja | Impacto | Ejemplo |
|---------|---------|---------|
| **Type safety** | Alto | `$dto->maxTokens` es SIEMPRE `int`, no string/null/array |
| **IDE autocomplete** | Alto | `$dto->` muestra todas propiedades disponibles |
| **Self-documenting** | Medio | Constructor es documentación viva |
| **Refactor seguro** | Alto | Renombrar propiedad → IDE encuentra todos usos |
| **Validación en construcción** | Medio | Type hints validan en constructor |
| **Transformaciones claras** | Medio | `fromRequest()`, `toArray()`, `fromModel()` explícitas |
| **Separación de concerns** | Medio | DTO ≠ Model (presentación vs persistencia) |

### ❌ Desventajas

| Desventaja | Impacto | Mitigación |
|------------|---------|------------|
| **NO resuelve acoplamiento** | Muy Alto | Controllers siguen accediendo Model directamente para obtener data |
| **Boilerplate código** | Alto | Duplicar propiedades Model en DTO (mantenimiento doble) |
| **Overhead memoria** | Medio | DTO + Model en memoria (2x objetos) |
| **Curva aprendizaje** | Bajo | Equipo debe entender cuándo usar DTO vs Model vs Array |
| **Sin beneficio de caching** | Alto | DTO no cachea, solo transporta (necesita Service Layer) |
| **Transformaciones extra** | Medio | Model → DTO → Array → JSON (overhead) |

### ⚖️ Balance Final

**DTOs NO resuelven problema principal (acoplamiento Controller-Model):**
- ❌ Controllers siguen llamando `LLMConfiguration::active()->get()`
- ❌ DTOs solo mejoran type safety DESPUÉS de obtener datos
- ❌ Sin Service Layer, DTOs solo añaden complejidad sin beneficio arquitectural

**DTOs son COMPLEMENTO, no solución primaria:**
- ✅ Usar CON Service Layer: Excelente
- ❌ Usar SIN Service Layer: Boilerplate inútil

**Score:** 6.5/10 como solución standalone, 8.5/10 como complemento de Service Layer

---

## Casos de Uso

### Caso 1: ¿Cuándo SÍ usar DTOs?

**Escenarios justificados:**

1. **APIs públicas con contratos estrictos:**
   ```php
   // API v1 - Contrato estable
   public function apiV1Show(int $id): JsonResponse
   {
       $dto = ConfigurationDTO::fromModel($config);
       return response()->json($dto->toPublicArray()); // Masked API key
   }
   ```

2. **Transformaciones complejas Request → Model:**
   ```php
   // Request tiene nombres diferentes a DB
   class ConfigurationDTO
   {
       public static function fromRequest(Request $request): self
       {
           return new self(
               // Request: 'llm_name' → Model: 'model_name'
               modelName: $request->validated('llm_name'),
               // Request: 'max_length' → Model: 'max_tokens'
               maxTokens: $request->validated('max_length'),
           );
       }
   }
   ```

3. **Protección de datos sensibles:**
   ```php
   public function toPublicArray(): array
   {
       $data = $this->toArray();
       $data['api_key'] = '••••••••'; // Mask sensitive data
       return $data;
   }
   ```

4. **Validación type-safe en Services:**
   ```php
   // ANTES (array misterioso)
   public function create(array $data): LLMConfiguration

   // DESPUÉS (contrato claro)
   public function create(CreateConfigurationDTO $dto): ConfigurationDTO
   ```

### Caso 2: ¿Cuándo NO usar DTOs?

**Escenarios injustificados:**

1. **CRUD simple sin transformaciones:**
   ```php
   // ❌ Overhead innecesario
   $dto = ConfigurationDTO::fromModel($config);
   return $dto->toArray(); // ¿Por qué no $config->toArray()?

   // ✅ Eloquent suficiente
   return $config->toArray();
   ```

2. **Sin necesidad de type safety:**
   ```php
   // Si solo pasas a vista, Eloquent ya es type-safe
   return view('config.show', ['config' => $config]);
   ```

3. **Sin Service Layer:**
   ```php
   // ❌ DTO sin Service Layer = boilerplate inútil
   public function index()
   {
       // Sigue accediendo Model directamente
       $configs = LLMConfiguration::active()->get();
       
       // DTO solo añade paso extra sin beneficio
       $dtos = $configs->map(fn($c) => ConfigurationDTO::fromModel($c));
       
       return view('configs', ['configs' => $dtos]);
   }
   
   // ✅ Sin DTO es más simple y funciona igual
   public function index()
   {
       $configs = LLMConfiguration::active()->get();
       return view('configs', ['configs' => $configs]);
   }
   ```

---

## Conclusión

### ⚠️ DTOs como solución standalone: NO RECOMENDADO

**Razones:**

1. ❌ **NO resuelve acoplamiento:** Controllers siguen accediendo Model directamente
2. ❌ **Boilerplate alto:** Duplicar propiedades sin beneficio claro
3. ❌ **Sin beneficio caching:** DTOs no cachean, necesitas Service Layer
4. ❌ **Overhead:** Model + DTO en memoria sin ROI

### ✅ DTOs como COMPLEMENTO de Service Layer: RECOMENDADO

**Arquitectura ideal:**

```
Controller → Service (usa DTOs) → Model
          ↑
          └── DTOs garantizan type safety entre capas
```

**Plan recomendado:**

1. **FASE 1:** Implementar Service Layer (ver [SERVICE-LAYER.md](./SERVICE-LAYER.md))
2. **FASE 2:** Añadir DTOs gradualmente donde aporten valor:
   - APIs públicas (mask sensitive data)
   - Transformaciones complejas Request → Model
   - Type safety en Service contracts

**Próximos pasos:**
1. Leer [SERVICE-LAYER.md](./SERVICE-LAYER.md) - Opción recomendada
2. Implementar Service Layer primero
3. Evaluar DTOs después si el equipo los necesita

---

**Documentación relacionada:**
- [PROTOCOLO-DE-REFACTORIZACION.md](./PROTOCOLO-DE-REFACTORIZACION.md) - Plan general
- [SERVICE-LAYER.md](./SERVICE-LAYER.md) - **Opción recomendada**
- [REPOSITORY-PATTERN.md](./REPOSITORY-PATTERN.md) - Alternativa evaluada
