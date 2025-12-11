# Provider Configuration Repositories - LLM Manager

**Fecha:** 11 de diciembre de 2025  
**Versión:** 1.0.0  
**Concepto:** Provider Config Repositories (Composer Packages)  
**Recomendación:** ✅ **FEATURE DE ALTO VALOR** (implementar después de Service Layer)

---

## 📋 Índice

1. [Concepto](#concepto)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Estructura de Packages](#estructura-de-packages)
4. [Implementación](#implementación)
5. [Comandos Artisan](#comandos-artisan)
6. [Casos de Uso](#casos-de-uso)
7. [Roadmap de Implementación](#roadmap-de-implementación)

---

## Concepto

### ¿Qué es un Provider Repository?

Un **Provider Repository** es un Composer package que contiene:
- ✅ Configuraciones pre-optimizadas de modelos LLM
- ✅ Templates de prompts específicos del provider
- ✅ Best practices y parámetros recomendados
- ✅ Metadata (pricing, capabilities, limits)
- ✅ System prompts probados en producción

### Problema que Resuelve

**ACTUAL (manual):**
```php
// Usuario debe investigar y configurar manualmente cada modelo
1. Buscar documentación de GPT-4o
2. Encontrar parámetros óptimos (max_tokens, temperature, etc.)
3. Probar y ajustar
4. Repetir para cada modelo (50+ opciones)
```

**CON REPOSITORIES (automatizado):**
```bash
composer require bithoven/llm-provider-openai
php artisan llm:import openai

✅ 10 configuraciones importadas:
  - GPT-4o (recommended)
  - GPT-4o-mini (cost-effective)
  - GPT-4-turbo (legacy)
  - ... 7 más

✅ Templates incluidos:
  - System prompts optimizados
  - RAG templates
  - Function calling examples
```

---

## Arquitectura del Sistema

### Ecosystem Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    BITHOVEN ECOSYSTEM                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  🌐 GITHUB REPOSITORIES (Public)                            │
│  ├─ bithoven/llm-provider-openai                           │
│  ├─ bithoven/llm-provider-anthropic                        │
│  ├─ bithoven/llm-provider-ollama                           │
│  ├─ bithoven/llm-provider-openrouter                       │
│  └─ community/llm-provider-custom (3rd party)              │
│                                                             │
│       ▼ composer require                                    │
│                                                             │
│  📦 COMPOSER PACKAGES (vendor/)                             │
│  └─ vendor/bithoven/llm-provider-openai/                   │
│     ├─ configs/                                            │
│     │  ├─ gpt-4o.json                                      │
│     │  ├─ gpt-4o-mini.json                                 │
│     │  └─ ...                                              │
│     ├─ prompts/                                            │
│     │  ├─ system/                                          │
│     │  └─ templates/                                       │
│     └─ src/OpenAIProviderRepository.php                    │
│                                                             │
│       ▼ php artisan llm:import                              │
│                                                             │
│  🗄️ DATABASE (llm_configurations)                          │
│  ├─ GPT-4o (imported from package)                         │
│  ├─ GPT-4o-mini (imported from package)                    │
│  └─ Custom configs (user created)                          │
│                                                             │
│       ▼ LLMConfigurationService                             │
│                                                             │
│  🚀 APPLICATION (llm-manager)                               │
│  └─ Users use imported configs                             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

```
Developer Creates Package → GitHub → Packagist → composer install
    → Package in vendor/ → Artisan import → DB → App uses it
```

---

## Estructura de Packages

### Package Structure (Example: OpenAI)

```
bithoven/llm-provider-openai/
├── composer.json                    # Package metadata
├── README.md                        # Documentation
├── CHANGELOG.md                     # Version history
├── LICENSE                          # MIT License
│
├── configs/                         # Model configurations
│   ├── gpt-4o.json                 # GPT-4 Omni
│   ├── gpt-4o-mini.json            # GPT-4 Omni Mini
│   ├── gpt-4-turbo.json            # GPT-4 Turbo (legacy)
│   ├── gpt-3.5-turbo.json          # GPT-3.5 Turbo
│   └── manifest.json               # Package manifest
│
├── prompts/                         # Prompt templates
│   ├── system/
│   │   ├── default-assistant.txt
│   │   ├── code-expert.txt
│   │   └── creative-writer.txt
│   ├── templates/
│   │   ├── rag-query.txt
│   │   └── function-calling.txt
│   └── manifest.json
│
├── docs/                            # Additional documentation
│   ├── best-practices.md
│   ├── pricing.md
│   └── examples.md
│
└── src/                             # PHP classes (optional)
    ├── OpenAIProviderRepository.php
    └── Validators/
        └── OpenAIConfigValidator.php
```

### Config File Format (JSON Schema)

```json
{
  "$schema": "https://bithoven.dev/schemas/llm-config-v1.json",
  "version": "1.0.0",
  "metadata": {
    "package": "bithoven/llm-provider-openai",
    "created_at": "2025-12-11T00:00:00Z",
    "updated_at": "2025-12-11T00:00:00Z",
    "author": "Bithoven Team"
  },
  "configuration": {
    "name": "GPT-4 Omni (Recommended)",
    "slug": "gpt-4o",
    "provider": "openai",
    "model_name": "gpt-4o",
    "description": "OpenAI's most advanced multimodal model",
    "api_endpoint": "https://api.openai.com/v1/chat/completions",
    "default_parameters": {
      "max_tokens": 4096,
      "temperature": 0.7,
      "top_p": 1.0,
      "frequency_penalty": 0.0,
      "presence_penalty": 0.0
    },
    "capabilities": [
      "text-generation",
      "vision",
      "function-calling",
      "json-mode",
      "streaming"
    ],
    "limits": {
      "context_window": 128000,
      "max_output_tokens": 4096,
      "requests_per_minute": 10000,
      "tokens_per_minute": 2000000
    },
    "pricing": {
      "currency": "USD",
      "input_per_1k_tokens": 0.005,
      "output_per_1k_tokens": 0.015,
      "batch_discount": 0.5
    },
    "recommended_use_cases": [
      "General purpose chat",
      "Code generation",
      "Image analysis",
      "Function calling",
      "JSON structured outputs"
    ],
    "tags": ["recommended", "multimodal", "production-ready"],
    "is_active": true,
    "is_default": false
  }
}
```

---

## Implementación

### FASE 1: Service Layer (Prerequisito)

**Duración:** ~4 horas

**Por qué primero:**
- Import command necesita `LLMConfigurationService` para crear configs
- Validación centralizada
- Cache management

**Referencia:** Ver `SERVICE-LAYER.md` para implementación completa

### FASE 2: Package Structure & Validator

**Archivo:** `src/Services/ProviderRepositoryValidator.php`

```php
<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\Validator;

class ProviderRepositoryValidator
{
    /**
     * Validate provider config JSON file
     * 
     * @param array $config Parsed JSON
     * @return array Validation errors (empty if valid)
     */
    public function validate(array $config): array
    {
        $validator = Validator::make($config, [
            'version' => 'required|string',
            'metadata' => 'required|array',
            'metadata.package' => 'required|string',
            'configuration' => 'required|array',
            'configuration.name' => 'required|string',
            'configuration.slug' => 'required|string|regex:/^[a-z0-9\-]+$/',
            'configuration.provider' => 'required|string',
            'configuration.model_name' => 'required|string',
            'configuration.api_endpoint' => 'required|url',
            'configuration.default_parameters' => 'required|array',
            'configuration.default_parameters.max_tokens' => 'required|integer|min:1',
            'configuration.default_parameters.temperature' => 'required|numeric|min:0|max:2',
        ]);

        return $validator->errors()->toArray();
    }

    /**
     * Check if package manifest exists and is valid
     */
    public function validatePackage(string $packagePath): bool
    {
        $manifestPath = $packagePath . '/configs/manifest.json';
        
        if (!file_exists($manifestPath)) {
            return false;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        return isset($manifest['package_name']) 
            && isset($manifest['version'])
            && isset($manifest['configurations']);
    }
}
```

### FASE 3: Import Command

**Archivo:** `src/Console/Commands/ImportProviderConfigs.php`

```php
<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Services\ProviderRepositoryValidator;

class ImportProviderConfigs extends Command
{
    protected $signature = 'llm:import 
                            {provider : Provider name (openai, anthropic, etc.)}
                            {--force : Overwrite existing configurations}
                            {--dry-run : Show what would be imported without saving}';

    protected $description = 'Import LLM configurations from provider repository package';

    public function __construct(
        private LLMConfigurationService $configService,
        private ProviderRepositoryValidator $validator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $provider = $this->argument('provider');
        $packagePath = base_path("vendor/bithoven/llm-provider-{$provider}");

        // Check if package exists
        if (!is_dir($packagePath)) {
            $this->error("Provider package not found: bithoven/llm-provider-{$provider}");
            $this->info("Install with: composer require bithoven/llm-provider-{$provider}");
            return 1;
        }

        // Validate package structure
        if (!$this->validator->validatePackage($packagePath)) {
            $this->error("Invalid package structure");
            return 1;
        }

        // Get config files
        $configsPath = $packagePath . '/configs';
        $configFiles = glob($configsPath . '/*.json');
        
        // Remove manifest.json
        $configFiles = array_filter($configFiles, fn($file) => !str_ends_with($file, 'manifest.json'));

        if (empty($configFiles)) {
            $this->warn("No configuration files found in package");
            return 1;
        }

        $this->info("Found " . count($configFiles) . " configurations to import");
        $this->newLine();

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($configFiles as $file) {
            $result = $this->importConfigFile($file);
            
            match($result['status']) {
                'imported' => $imported++,
                'skipped' => $skipped++,
                'error' => $errors++,
            };
        }

        // Summary
        $this->newLine();
        $this->info("Import completed:");
        $this->line("  ✅ Imported: {$imported}");
        $this->line("  ⏭️  Skipped: {$skipped}");
        $this->line("  ❌ Errors: {$errors}");

        return 0;
    }

    private function importConfigFile(string $filePath): array
    {
        $filename = basename($filePath);
        
        try {
            // Parse JSON
            $data = json_decode(file_get_contents($filePath), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("  ❌ {$filename}: Invalid JSON");
                return ['status' => 'error'];
            }

            // Validate structure
            $errors = $this->validator->validate($data);
            
            if (!empty($errors)) {
                $this->error("  ❌ {$filename}: Validation failed");
                foreach ($errors as $field => $messages) {
                    $this->line("     - {$field}: " . implode(', ', $messages));
                }
                return ['status' => 'error'];
            }

            $config = $data['configuration'];

            // Check if exists
            $exists = $this->configService->findBySlug($config['slug']);
            
            if ($exists && !$this->option('force')) {
                $this->warn("  ⏭️  {$filename}: Already exists (use --force to overwrite)");
                return ['status' => 'skipped'];
            }

            // Dry run check
            if ($this->option('dry-run')) {
                $this->info("  🔍 {$filename}: Would import '{$config['name']}'");
                return ['status' => 'imported'];
            }

            // Import
            if ($exists) {
                $this->configService->update($exists->id, $config);
                $this->info("  🔄 {$filename}: Updated '{$config['name']}'");
            } else {
                $this->configService->create($config);
                $this->info("  ✅ {$filename}: Imported '{$config['name']}'");
            }

            return ['status' => 'imported'];

        } catch (\Exception $e) {
            $this->error("  ❌ {$filename}: {$e->getMessage()}");
            return ['status' => 'error'];
        }
    }
}
```

### FASE 4: List Available Packages Command

```php
<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;

class ListProviderPackages extends Command
{
    protected $signature = 'llm:packages 
                            {--installed : Show only installed packages}';

    protected $description = 'List available provider repository packages';

    public function handle(): int
    {
        $this->info("Available Provider Packages:");
        $this->newLine();

        $packages = [
            'bithoven/llm-provider-openai' => [
                'name' => 'OpenAI',
                'configs' => 10,
                'installed' => $this->isInstalled('openai'),
            ],
            'bithoven/llm-provider-anthropic' => [
                'name' => 'Anthropic (Claude)',
                'configs' => 6,
                'installed' => $this->isInstalled('anthropic'),
            ],
            'bithoven/llm-provider-ollama' => [
                'name' => 'Ollama (Local)',
                'configs' => 15,
                'installed' => $this->isInstalled('ollama'),
            ],
        ];

        foreach ($packages as $package => $info) {
            if ($this->option('installed') && !$info['installed']) {
                continue;
            }

            $status = $info['installed'] ? '✅ Installed' : '📦 Available';
            
            $this->line("  {$status} - {$info['name']}");
            $this->line("    Package: {$package}");
            $this->line("    Configs: {$info['configs']}");
            
            if (!$info['installed']) {
                $this->line("    Install: composer require {$package}");
            }
            
            $this->newLine();
        }

        return 0;
    }

    private function isInstalled(string $provider): bool
    {
        return is_dir(base_path("vendor/bithoven/llm-provider-{$provider}"));
    }
}
```

---

## Comandos Artisan

### Comandos Disponibles

```bash
# Listar packages disponibles
php artisan llm:packages
php artisan llm:packages --installed

# Importar configuraciones de un provider
php artisan llm:import openai
php artisan llm:import anthropic --force
php artisan llm:import ollama --dry-run

# Ver detalles de un package
php artisan llm:package:info openai

# Actualizar packages instalados
php artisan llm:update-all
```

### Ejemplo de Uso

```bash
# 1. Instalar package
composer require bithoven/llm-provider-openai

# 2. Ver qué se va a importar
php artisan llm:import openai --dry-run

Output:
Found 10 configurations to import

  🔍 gpt-4o.json: Would import 'GPT-4 Omni (Recommended)'
  🔍 gpt-4o-mini.json: Would import 'GPT-4 Omni Mini'
  🔍 gpt-4-turbo.json: Would import 'GPT-4 Turbo'
  ... 7 más

# 3. Importar
php artisan llm:import openai

Output:
Found 10 configurations to import

  ✅ gpt-4o.json: Imported 'GPT-4 Omni (Recommended)'
  ✅ gpt-4o-mini.json: Imported 'GPT-4 Omni Mini'
  ✅ gpt-4-turbo.json: Imported 'GPT-4 Turbo'
  ... 7 más

Import completed:
  ✅ Imported: 10
  ⏭️  Skipped: 0
  ❌ Errors: 0
```

---

## Casos de Uso

### Caso 1: Setup Rápido de Proyecto Nuevo

```bash
# Nuevo proyecto Laravel con llm-manager
composer create-project laravel/laravel my-llm-app
cd my-llm-app

# Instalar llm-manager
composer require bithoven/llm-manager

# Instalar providers
composer require bithoven/llm-provider-openai
composer require bithoven/llm-provider-anthropic

# Importar configs
php artisan llm:import openai
php artisan llm:import anthropic

# ✅ 16 configuraciones listas para usar en 2 minutos
```

### Caso 2: Actualizar Configuraciones cuando Provider Actualiza Modelos

```bash
# OpenAI lanza GPT-5
# Maintainer actualiza package bithoven/llm-provider-openai v2.0.0

# Usuario actualiza
composer update bithoven/llm-provider-openai

# Reimportar configs (actualiza existentes)
php artisan llm:import openai --force

# ✅ Nuevas configuraciones GPT-5 disponibles automáticamente
```

### Caso 3: Crear Provider Repository Privado (Empresa)

```bash
# Empresa tiene configs custom de proveedores privados

# 1. Crear package privado
mkdir packages/acme/llm-provider-azure-custom
cd packages/acme/llm-provider-azure-custom

# 2. Estructura
configs/
├── azure-gpt-4-turbo-eu.json    # Endpoint europeo
├── azure-gpt-4-turbo-us.json    # Endpoint US
└── manifest.json

# 3. composer.json
{
  "name": "acme/llm-provider-azure-custom",
  "type": "library",
  "repositories": [
    {
      "type": "path",
      "url": "../packages/acme/llm-provider-azure-custom"
    }
  ]
}

# 4. Instalar localmente
composer require acme/llm-provider-azure-custom

# 5. Importar
php artisan llm:import azure-custom

# ✅ Configs privadas importadas
```

---

## Roadmap de Implementación

### Roadmap Completo

```
┌─────────────────────────────────────────────────────────┐
│                   IMPLEMENTATION PHASES                 │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  FASE 1: Service Layer (PREREQUISITO) - 4 horas        │
│  ├─ LLMConfigurationService                            │
│  ├─ Refactor controllers                               │
│  └─ Tests (>80% coverage)                              │
│  Status: 📋 Documentado en SERVICE-LAYER.md            │
│                                                         │
│  FASE 2: Core Import System - 6 horas                  │
│  ├─ ProviderRepositoryValidator                        │
│  ├─ ImportProviderConfigs command                      │
│  ├─ ListProviderPackages command                       │
│  └─ Tests                                              │
│  Status: 📋 Documentado arriba                         │
│                                                         │
│  FASE 3: First Provider Package - 4 horas              │
│  ├─ Create bithoven/llm-provider-openai repo           │
│  ├─ 10 config files (GPT-4o, GPT-4o-mini, etc.)       │
│  ├─ Prompts templates                                  │
│  ├─ Documentation                                      │
│  └─ Publish to Packagist                               │
│  Status: ⏳ Pendiente                                   │
│                                                         │
│  FASE 4: Additional Providers - 8 horas                │
│  ├─ bithoven/llm-provider-anthropic                    │
│  ├─ bithoven/llm-provider-ollama                       │
│  └─ bithoven/llm-provider-openrouter                   │
│  Status: ⏳ Pendiente                                   │
│                                                         │
│  FASE 5: Advanced Features - 6 horas                   │
│  ├─ Version management                                 │
│  ├─ Auto-update detection                              │
│  ├─ Package dependency resolution                      │
│  └─ UI for package management                          │
│  Status: 🔮 Futuro                                     │
│                                                         │
│  FASE 6: Marketplace & Community - 8 horas             │
│  ├─ Public registry/marketplace                        │
│  ├─ Community contributions                            │
│  ├─ Rating & reviews                                   │
│  └─ Discovery system                                   │
│  Status: 🔮 Futuro                                     │
│                                                         │
│  TOTAL: ~36 horas (~1 semana de desarrollo)            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Prioridades

**P0 (Crítico - hacer primero):**
- ✅ Service Layer (4h) - Sin esto, nada funciona

**P1 (Alto - feature principal):**
- ⏳ Core Import System (6h)
- ⏳ OpenAI Provider Package (4h)

**P2 (Medio - expandir ecosystem):**
- ⏳ Additional Provider Packages (8h)

**P3 (Bajo - nice to have):**
- 🔮 Advanced Features (6h)
- 🔮 Marketplace (8h)

### Timeline Recomendado

```
Semana 1:
  Día 1-2: Implementar Service Layer (4h)
  Día 3-4: Core Import System (6h)
  Día 5:   Testing & docs (2h)

Semana 2:
  Día 1-2: OpenAI Provider Package (4h)
  Día 3-5: Additional Providers (8h)

Semana 3:
  Testing, refinamiento, documentación
  Launch público 🚀
```

---

## Resumen Ejecutivo

### Por qué implementar esto

**Valor para usuarios:**
- ⚡ Setup en minutos vs horas
- 🎯 Best practices incluidas
- 🔄 Updates automáticos con composer
- 📚 Templates probados en producción
- 🌍 Ecosystem compartido

**Valor para el proyecto:**
- 🚀 Feature diferenciadora (competidores no tienen)
- 👥 Community engagement
- 📈 Adoption más rápida
- 🔌 Extensibilidad infinita

### Dependencias

```
PROVIDER-REPOSITORIES
      ↓ REQUIERE
SERVICE-LAYER (prerequisito)
      ↓ BENEFICIA DE
DTOs (opcional, mejora type safety)
```

### Próximo Paso

1. **Leer:** `SERVICE-LAYER.md` (1,569 líneas)
2. **Implementar:** Service Layer (~4 horas)
3. **Validar:** Tests passing (>80% coverage)
4. **Continuar:** Core Import System (este documento)

---

**¿Listo para empezar?** 

Recomendación: Implementar Service Layer primero (ver `SERVICE-LAYER.md`), luego volver a este documento para Phase 2.

**Total investment:** ~10 horas para MVP funcional (Service Layer + Import System + 1 provider package)

**Expected ROI:** Feature única que diferencia bithoven-llm-manager de competidores, acelera adoption, habilita ecosystem de comunidad.
