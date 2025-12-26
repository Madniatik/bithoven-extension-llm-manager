# Provider Repositories - Arquitectura Final v2.0

**Fecha:** 11 de diciembre de 2025  
**Versión:** 2.1.0  
**Status:** ✅ Análisis Completo - Ready for Implementation

---

## 🎯 Decisiones Arquitectónicas Finales

### 1. Provider Registry: DB como Source of Truth

**❌ DESCARTADO:** Hybrid approach (core + dynamic)  
**✅ ADOPTADO:** Database-driven (pure packages)

**Razón:** Single source of truth, sin confusión, extensible.

---

### 2. Naming Convention Actualizado

**❌ ANTES:** `llm_manager_configurations`  
**✅ AHORA:** `llm_manager_provider_configurations`

**Razón:** Más descriptivo, clarifica que son configuraciones de providers específicos.

---

### 3. Estructura de Datos

**ANTES (actual):**
```
llm_manager_configurations (TABLA OBSOLETA)
├── id
├── name
├── slug
├── provider (ENUM: ollama,openai,anthropic,openrouter,local,custom) ← Hardcoded
├── model
├── api_endpoint
├── api_key
├── default_parameters (JSON)
├── capabilities (JSON)
├── is_active
├── is_default
└── description
```

**Problemas identificados:**
- ❌ ENUM `provider` limita a lista fija
- ❌ Agregar provider requiere ALTER TABLE (migration)
- ❌ Nombre ambiguo (¿configurations de qué?)
- ❌ Un provider puede tener múltiples models (relación 1:N perdida)

**DESPUÉS (nueva arquitectura - desarrollo):**

```
┌────────────────────────────────────────────────────────────┐
│ llm_manager_providers (NUEVA TABLA)                        │
├────────────────────────────────────────────────────────────┤
│ id          bigint                                         │
│ slug        varchar(100) UNIQUE  # openai, anthropic, etc │
│ name        varchar(100)         # OpenAI, Anthropic, etc │
│ package     varchar(255) NULL    # bithoven/llm-provider-*│
│ version     varchar(20) NULL     # 0.1.0                  │
│ api_endpoint varchar(255) NULL   # https://api.openai.com │
│ capabilities json NULL           # {vision, streaming, etc}│
│ is_active   boolean              # Available for use      │
│ metadata    json NULL            # Extra provider info    │
│ created_at                                                 │
│ updated_at                                                 │
└────────────────────────────────────────────────────────────┘
         ▲
         │ 1:N relationship
         │
┌────────────────────────────────────────────────────────────┐
│ llm_manager_provider_configurations (NUEVO NOMBRE)         │
├────────────────────────────────────────────────────────────┤
│ id          bigint                                         │
│ provider_id bigint FK → llm_manager_providers.id          │
│ name        varchar(100)         # GPT-4o, Claude 3.5     │
│ slug        varchar(100) UNIQUE  # gpt-4o, claude-3-5     │
│ model       varchar(100)         # gpt-4o, claude-3-5-... │
│ api_key     text NULL            # Encrypted             │
│ default_parameters json NULL     # Model-specific params  │
│ is_active   boolean                                        │
│ is_default  boolean                                        │
│ description text NULL                                      │
│ created_at                                                 │
│ updated_at                                                 │
│                                                            │
│ # Campos consolidados de migraciones posteriores:         │
│ cost_per_1k_input_tokens decimal(10,6) NULL  # Multi-currency│
│ cost_per_1k_output_tokens decimal(10,6) NULL              │
│ currency varchar(3) DEFAULT 'USD'                          │
└────────────────────────────────────────────────────────────┘
         ▲
         │ N:M relationships
         │
┌────────────────────────────────────────────────────────────┐
│ llm_manager_usage_logs (ACTUALIZAR FK)                     │
├────────────────────────────────────────────────────────────┤
│ llm_provider_configuration_id (FK actualizada)             │
│ → llm_manager_provider_configurations.id                   │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ llm_manager_conversation_sessions (ACTUALIZAR FK)          │
├────────────────────────────────────────────────────────────┤
│ llm_provider_configuration_id (FK actualizada)             │
│ → llm_manager_provider_configurations.id                   │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ llm_manager_prompt_templates (CONSOLIDAR CAMPOS)           │
├────────────────────────────────────────────────────────────┤
│ is_global boolean DEFAULT false  # Consolidado en migration│
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ llm_manager_tool_definitions (CONSOLIDAR CAMPOS)           │
├────────────────────────────────────────────────────────────┤
│ is_enabled boolean DEFAULT true  # Consolidado en migration│
│ execution_timeout int NULL       # Consolidado en migration│
└────────────────────────────────────────────────────────────┘
```

**Beneficios:**
- ✅ Providers dinámicos (agregar sin migration)
- ✅ Un provider = múltiples configs (relación correcta)
- ✅ Package info almacenada (version, source)
- ✅ Extensible por community packages
- ✅ No ENUM (campo string `slug`)
- ✅ Migraciones consolidadas (menos archivos, más mantenible)

---

### 4. Orden de Migraciones (Desarrollo)

**⚠️ CRÍTICO:** En desarrollo, crear estructura correcta desde inicio.

**Orden obligatorio (dependencias FK):**
```
database/migrations/
├── 2025_11_18_000001_create_llm_manager_providers_table.php                    ← 1º PADRE
├── 2025_11_18_000002_create_llm_manager_provider_configurations_table.php      ← 2º HIJO (FK providers)
├── 2025_11_18_000003_create_llm_manager_usage_logs_table.php                   ← 3º (FK provider_configurations)
├── 2025_11_18_000004_create_llm_manager_custom_metrics_table.php               ← 4º
├── 2025_11_18_000005_create_llm_manager_prompt_templates_table.php             ← 5º (con is_global)
├── 2025_11_18_000006_create_llm_manager_conversation_sessions_table.php        ← 6º (FK provider_configurations)
├── 2025_11_18_000007_create_llm_manager_conversation_messages_table.php        ← 7º
├── 2025_11_18_000008_create_llm_manager_document_knowledge_base_table.php      ← 8º
├── 2025_11_18_000009_create_llm_manager_mcp_connectors_table.php               ← 9º
├── 2025_11_18_000010_create_llm_manager_agent_workflows_table.php              ← 10º
├── 2025_11_18_000011_create_llm_manager_tool_definitions_table.php             ← 11º (con campos consolidados)
├── 2025_11_18_000012_create_llm_manager_tool_executions_table.php              ← 12º
├── 2025_11_18_000013_create_llm_manager_user_workspace_preferences_table.php   ← 13º
└── 2025_11_18_000014_create_llm_manager_parameter_overrides_table.php          ← 14º
```

**Migraciones ELIMINADAS (consolidadas en originales):**
- ❌ `2025_11_18_071800_add_multi_currency_support_to_llm_usage_logs.php`
  - **Razón:** Campos integrados en `000003_create_llm_manager_usage_logs_table.php`
  
- ❌ `2025_11_21_000001_add_is_global_to_llm_manager_prompt_templates_table.php`
  - **Razón:** Campo integrado en `000005_create_llm_manager_prompt_templates_table.php`
  
- ❌ `2025_11_21_000013_add_missing_fields_to_tool_definitions_table.php`
  - **Razón:** Campos integrados en `000012_create_llm_manager_tool_definitions_table.php`
  
- ❌ `2025_11_21_235900_add_openrouter_to_provider_enum.php`
  - **Razón:** ENUM eliminado, providers dinámicos en tabla `llm_manager_providers`

**Beneficios de consolidación:**
- ✅ Menos archivos de migration (14 vs 18)
- ✅ No hay migraciones "parcheadas"
- ✅ Estructura completa desde inicio
- ✅ Más mantenible a largo plazo

---

### 5. Package Archiving: Data Preservation Protocol

**Problema:** Si se desinstala un provider package, ¿qué pasa con los datos?

**Solución:** Archiving (NO deletion física)

#### Workflow de Desinstalación

```bash
# Usuario desinstala package
composer remove bithoven/llm-provider-anthropic

# Extension Manager detecta cambio en composer.lock
# → Ejecuta automáticamente:
php artisan llm:archive-provider anthropic
```

**Command `llm:archive-provider`:**

```php
// src/Console/Commands/ArchiveProvider.php

public function handle(): void
{
    $slug = $this->argument('slug');
    $provider = LLMProvider::where('slug', $slug)->first();
    
    if (!$provider) {
        $this->error("Provider {$slug} not found");
        return;
    }
    
    // Check dependencies
    $configCount = $provider->configurations()->count();
    $usageCount = LLMUsageLog::whereHas('configuration', 
        fn($q) => $q->where('provider_id', $provider->id)
    )->count();
    
    $this->warn("⚠️  Provider {$slug} has:");
    $this->line("  - {$configCount} configurations");
    $this->line("  - {$usageCount} usage logs");
    
    // Archive (NO delete)
    $provider->update([
        'is_active' => false,
        'is_installed' => false,
        'archived_at' => now(),
        'metadata' => array_merge($provider->metadata ?? [], [
            'archived_reason' => 'package_uninstalled',
            'last_installed_at' => $provider->updated_at,
            'config_count' => $configCount,
            'usage_count' => $usageCount
        ])
    ]);
    
    // Deactivate configurations (NO delete)
    $provider->configurations()->update(['is_active' => false]);
    
    $this->info("✅ Provider {$slug} archived (data preserved)");
    $this->line("   - Configurations: disabled");
    $this->line("   - Usage logs: preserved");
    $this->line("   - Stats/Metrics: still accessible");
}
```

#### Workflow de Reinstalación

```bash
# Usuario reinstala package
composer require bithoven/llm-provider-anthropic

# Ejecuta import
php artisan llm:import anthropic
```

**Command `llm:import` (con detección de archived):**

```php
// src/Console/Commands/ImportProviderConfigs.php

public function handle(): void
{
    $provider = $this->argument('provider');
    
    // Check if archived
    $existingProvider = LLMProvider::where('slug', $provider)
        ->withArchived()  // Custom scope
        ->first();
    
    if ($existingProvider && $existingProvider->archived_at) {
        $this->warn("🔄 Provider {$provider} was archived");
        $this->line("   Restoring existing data...");
        
        // Restore
        $existingProvider->update([
            'is_active' => true,
            'is_installed' => true,
            'archived_at' => null,
            'version' => $this->getPackageVersion($provider),
            'metadata' => array_merge($existingProvider->metadata ?? [], [
                'restored_at' => now(),
                'restore_count' => ($existingProvider->metadata['restore_count'] ?? 0) + 1
            ])
        ]);
        
        // Reactivate configurations
        $existingProvider->configurations()->update(['is_active' => true]);
        
        $this->info("✅ Provider {$provider} restored");
        $this->line("   - {$existingProvider->configurations()->count()} configurations reactivated");
        $this->line("   - Historical data preserved");
        
        return;
    }
    
    // Normal import (new provider)
    $this->importNewProvider($provider);
}
```

**Beneficios:**
- ✅ Integridad referencial (FKs nunca rompen)
- ✅ Stats/Metrics históricos accesibles
- ✅ GDPR/Compliance (datos preservados)
- ✅ Reinstalación sin pérdida de historia
- ✅ Auditoría completa (archived_at, metadata)

**Foreign Keys:**
```php
// Todas las FKs usan ON DELETE RESTRICT
$table->foreignId('provider_id')
    ->constrained('llm_manager_providers')
    ->onDelete('restrict');  // ❌ Previene borrado físico
```

---

### 6. Package Naming y Location

**✅ CONFIRMADO:** `/PACKAGES/llm-provider-*/`

```
/Users/madniatik/CODE/LARAVEL/BITHOVEN/
├── CPANEL/                    # App principal
├── DOCS/                      # Documentación
├── EXTENSIONS/                # Extension Manager extensions
│   ├── bithoven-extension-dummy/
│   ├── bithoven-extension-tickets/
│   └── bithoven-extension-llm-manager/  ← Extension principal
│
└── PACKAGES/                  # Composer packages (NO extensiones)
    ├── llm-provider-ollama/        ✅ CREADO
    ├── llm-provider-anthropic/     ⏳ FASE 3
    ├── llm-provider-openai/        ⏳ FASE 5
    ├── llm-provider-openrouter/    ⏳ FASE 5
    ├── llm-provider-google/        ⏳ FASE 5
    └── llm-provider-cohere/        ⏳ FASE 5
```

**Naming Convention:**
- Local path: `/PACKAGES/llm-provider-{name}/`
- Composer package: `bithoven/llm-provider-{name}`
- GitHub repo: `bithoven/llm-provider-{name}`

**VSCode Workspace:**
- ✅ llm-provider-ollama agregado a workspace

---

### 7. Dev-Mode Protocol (Extension Manager Pattern)

**Extension Manager dev-mode behavior:**

```bash
# Activar dev-mode
php artisan bithoven:extension:dev-mode llm-manager enable

# 1. Renombrar carpetas existentes (NO borrar)
vendor/bithoven/llm-manager → vendor/bithoven/llm-manager.repo
public/vendor/bithoven/llm-manager → public/vendor/bithoven/llm-manager.repo

# 2. Crear symlinks
vendor/bithoven/llm-manager (symlink) → /EXTENSIONS/bithoven-extension-llm-manager
public/vendor/bithoven/llm-manager (symlink) → /EXTENSIONS/bithoven-extension-llm-manager/public

# Desactivar dev-mode
php artisan bithoven:extension:dev-mode llm-manager disable

# 1. Borrar symlinks
# 2. Restaurar carpetas originales (.repo → original)
```

**LLM Manager dev-mode (MISMO PATRÓN):**

```bash
# Activar dev-mode para provider packages
php artisan llm:packages:dev-mode ollama enable

# 1. Renombrar carpeta existente (NO borrar)
vendor/bithoven/llm-provider-ollama → vendor/bithoven/llm-provider-ollama.repo

# 2. Crear symlink
vendor/bithoven/llm-provider-ollama (symlink) → /PACKAGES/llm-provider-ollama

# Desactivar dev-mode
php artisan llm:packages:dev-mode ollama disable

# 1. Borrar symlink
# 2. Restaurar carpeta original (.repo → original)
```

**Beneficios:**
- ✅ Consistencia con Extension Manager
- ✅ Seguridad (backup automático en .repo)
- ✅ Reversible sin pérdida de datos
- ✅ No requiere git push/pull en desarrollo
- ✅ Cambios inmediatos sin composer update

**FASE 4:** Implementar `llm:packages:dev-mode` command

---

### 8. Workflow de Instalación de Packages

```bash
# Paso 1: User instala package
cd /path/to/CPANEL
composer require bithoven/llm-provider-anthropic

# Paso 2: Import configs
php artisan llm:import anthropic

# Backend workflow:
┌─────────────────────────────────────────────────────────┐
│ ImportProviderConfigs::handle()                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ 1. Read manifest.json                                   │
│    ├─ provider: "anthropic"                             │
│    ├─ package: "bithoven/llm-provider-anthropic"        │
│    ├─ version: "0.1.0"                                  │
│    └─ configurations: 6                                 │
│                                                         │
│ 2. Create/Update Provider Record                        │
│    INSERT INTO llm_manager_providers                    │
│    (slug, name, package, version, ...)                  │
│    VALUES ('anthropic', 'Anthropic', ...)               │
│    ON DUPLICATE KEY UPDATE ...                          │
│    → $provider_id = 2                                   │
│                                                         │
│ 3. Import Configurations                                │
│    foreach (config_files as $file) {                    │
│        INSERT INTO llm_manager_provider_configurations  │
│        (provider_id, name, model, ...)                  │
│        VALUES ($provider_id, 'Claude 3.5 Sonnet', ...)  │
│    }                                                    │
│                                                         │
│ ✅ Result:                                              │
│ - 1 provider record                                     │
│ - 6 configuration records                               │
│ - Ready to use in Quick Chat                            │
└─────────────────────────────────────────────────────────┘
```

---

### 6. Estrategia de Migration en Desarrollo

**✅ DECISIÓN CONFIRMADA:** Opción B - Empezar Limpio

**Contexto:**
- Extension desinstalada de CPANEL
- Backups realizados:
  - `backups/bithoven_laravel.bak.sql` (base de datos completa)
  - `backups/llm-manager.bak.sql` (solo tablas llm_manager_*)
- Datos de prueba no críticos

**Workflow confirmado:**

```bash
# 1. Extension ya desinstalada ✅
# php artisan bithoven:extension:uninstall llm-manager

# 2. Backups ya realizados ✅
# - backups/bithoven_laravel.bak.sql
# - backups/llm-manager.bak.sql

# 3. Reinstalar con nueva estructura (FASE 2.5)
php artisan bithoven:extension:install llm-manager

# 4. Seed con datos demo
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\LLMDemoSeeder
```

**Ventajas:**
- ✅ Estructura correcta desde inicio
- ✅ Migraciones consolidadas (14 vs 18)
- ✅ Sin migrations "parcheadas"
- ✅ Backups disponibles para referencia/recuperación
- ✅ Rápido y limpio

---

#### ⚠️ COMANDOS PROHIBIDOS:

```bash
# ❌ PELIGRO: Borra TODA la base de datos de CPANEL
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan migrate:refresh

# Consecuencias:
# - Usuarios borrados
# - Permisos borrados
# - Extensiones borradas  
# - TODO el sistema roto
# - Pérdida de datos de producción
```

**✅ COMANDOS SEGUROS (scope limitado a llm-manager):**
```bash
php artisan bithoven:extension:uninstall llm-manager  # Solo tablas llm_manager_*
php artisan bithoven:extension:install llm-manager    # Solo tablas llm_manager_*
```

---

### 5. Desinstalación de Extensión

**Escenario:**
```
User tiene:
├── 500 usage_logs con GPT-4o
├── 20 conversation_sessions con Claude 3.5
├── 1000+ conversation_messages
└── Extension llm-manager se desinstala
```

**¿Qué pasa con los datos?**

**❌ ANTES (incorrecto):**
```sql
-- UninstallSeeder borra TODOS los datos
DROP TABLE llm_manager_configurations;
DROP TABLE llm_manager_usage_logs;
DROP TABLE llm_manager_conversation_sessions;
-- ❌ PÉRDIDA TOTAL DE DATOS
```

**✅ AHORA (correcto):**

**Protocolo de Desinstalación con Preservación de Datos:**

```php
// database/seeders/LLMUninstallSeeder.php (UPDATED)

public function run(): void
{
    $this->command->warn('⚠️  Extension uninstall requested');
    
    // Step 1: Check if data exists
    $hasData = $this->checkDataExistence();
    
    if ($hasData) {
        $this->command->newLine();
        $this->command->error('❌ CANNOT UNINSTALL: Critical data detected');
        $this->command->newLine();
        $this->displayDataSummary();
        $this->command->newLine();
        $this->command->warn('📋 Data must be preserved:');
        $this->command->line('   - Usage logs (billing/analytics)');
        $this->command->line('   - Conversation history (user data)');
        $this->command->line('   - Configuration records (audit trail)');
        $this->command->newLine();
        $this->command->info('✅ SOLUTION: Extension DISABLED but data preserved');
        $this->command->line('   - Routes disabled');
        $this->command->line('   - Menu items hidden');
        $this->command->line('   - Permissions revoked');
        $this->command->line('   - Data tables intact');
        $this->command->newLine();
        
        // Disable extension (mark as inactive)
        DB::table('extensions')->where('slug', 'llm-manager')->update([
            'is_active' => false,
            'disabled_at' => now(),
            'disabled_reason' => 'User uninstall request - data preserved',
        ]);
        
        // Revoke permissions (cleanup)
        $this->revokePermissions();
        
        return; // EXIT - NO DATA DELETION
    }
    
    // Step 2: If no data, safe to drop tables
    $this->command->info('✅ No data found - safe to uninstall');
    $this->dropTables();
    $this->revokePermissions();
}

protected function checkDataExistence(): bool
{
    return DB::table('llm_manager_usage_logs')->exists()
        || DB::table('llm_manager_conversation_sessions')->exists()
        || DB::table('llm_manager_configurations')->where('id', '>', 5)->exists();
}

protected function displayDataSummary(): void
{
    $stats = [
        ['Usage Logs', DB::table('llm_manager_usage_logs')->count()],
        ['Conversations', DB::table('llm_manager_conversation_sessions')->count()],
        ['Messages', DB::table('llm_manager_conversation_messages')->count()],
        ['Configurations', DB::table('llm_manager_configurations')->count()],
        ['Providers', DB::table('llm_manager_providers')->count()],
    ];
    
    $this->command->table(['Data Type', 'Records'], $stats);
}
```

**Comportamiento:**

**Caso A: Extension sin datos (fresh install)**
```bash
php artisan bithoven:extension:uninstall llm-manager

✅ No data found - safe to uninstall
✅ Tables dropped
✅ Permissions revoked
✅ Extension removed
```

**Caso B: Extension con datos (production)**
```bash
php artisan bithoven:extension:uninstall llm-manager

⚠️  Extension uninstall requested
❌ CANNOT UNINSTALL: Critical data detected

┌─────────────────┬─────────┐
│ Data Type       │ Records │
├─────────────────┼─────────┤
│ Usage Logs      │ 500     │
│ Conversations   │ 20      │
│ Messages        │ 1000    │
│ Configurations  │ 10      │
│ Providers       │ 3       │
└─────────────────┴─────────┘

📋 Data must be preserved:
   - Usage logs (billing/analytics)
   - Conversation history (user data)
   - Configuration records (audit trail)

✅ SOLUTION: Extension DISABLED but data preserved
   - Routes disabled
   - Menu items hidden
   - Permissions revoked
   - Data tables intact
```

**Beneficios:**
- ✅ NO pérdida accidental de datos
- ✅ Compliance (GDPR, audit trails)
- ✅ Reversible (re-enable extension)
- ✅ Manual cleanup si user confirma

---

### 7. Naming: llm_manager_provider_configurations

**✅ SÍ RENOMBRAR:** `llm_manager_configurations` → `llm_manager_provider_configurations`

**Razón:**
- Más descriptivo y específico
- Evita confusión con otros tipos de configuración
- Clarifica la relación con providers

**Clarificación de conceptos:**

```
Provider (llm_manager_providers):
├── slug: "anthropic"
├── name: "Anthropic"
├── package: "bithoven/llm-provider-anthropic"
└── capabilities: {vision: true, streaming: true}
    ▼
Provider Configuration (llm_manager_provider_configurations):
├── provider_id: 2 (FK → anthropic)
├── name: "Claude 3.5 Sonnet"
├── model: "claude-3-5-sonnet-20241022"
├── api_key: "sk-ant-..."
└── default_parameters: {temperature: 1.0, max_tokens: 4096}
    ▼
Provider Configuration (llm_manager_provider_configurations):
├── provider_id: 2 (FK → anthropic)
├── name: "Claude 3 Opus"
├── model: "claude-3-opus-20240229"
├── api_key: "sk-ant-..."
└── default_parameters: {temperature: 0.7, max_tokens: 8000}
```

**Relación:** 1 Provider → N Provider Configurations

---

## 🎯 Resumen de Decisiones Finales

### 1. Provider Registry: Database-Driven
- ✅ Nueva tabla: `llm_manager_providers`
- ✅ Source of truth: DB (NO hardcoded lists)
- ✅ Providers dinámicos (packages)
- ❌ NO híbrido (confuso)

### 2. Naming Convention
- ✅ `llm_manager_providers` (nueva tabla padre)
- ✅ `llm_manager_provider_configurations` (tabla hija renombrada)
- ✅ Más descriptivo y específico

### 3. Orden de Migraciones (Desarrollo)
- ✅ Providers primero (#001)
- ✅ Provider Configurations segundo (#002, con FK)
- ✅ Usage Logs tercero (#003, con FK a configurations)
- ✅ Resto ajustado (+1 en secuencia)

### 4. Consolidación de Migraciones
- ✅ Multi-currency integrado en usage_logs migration
- ✅ is_global integrado en prompt_templates migration
- ✅ Campos extra integrados en tool_definitions migration
- ❌ ENUM provider eliminado (migration obsoleta)
- ✅ 4 migrations menos (14 vs 18)

### 5. Estrategia de Migration
- ✅ Opción A: Backup + Script de migración (preservar datos)
- ✅ Opción B: Uninstall + Install limpio (sin datos)
- ❌ NUNCA usar `migrate:fresh` (destruye app completa)

### 6. Desinstalación: Data Preservation
- ✅ UninstallSeeder verifica datos antes de borrar
- ✅ Si hay datos: Extension disabled, data preserved
- ✅ Compliance y audit trail

---

## 📂 Archivos a Crear/Modificar

### Nuevos Archivos

**Migrations (orden correcto):**
- `2025_11_18_000001_create_llm_manager_providers_table.php`
- `2025_11_18_000002_create_llm_manager_provider_configurations_table.php`
- Resto de migrations renumeradas (+1)

**Models:**
- `src/Models/LLMProvider.php`
- `src/Models/LLMProviderConfiguration.php` (renombrar desde LLMConfiguration)

**Seeders:**
- `database/seeders/LLMProvidersSeeder.php`

**Commands (para migration de datos):**
- `src/Console/Commands/MigrateLegacyData.php` (backup restore)

### Archivos a ELIMINAR

**Migrations obsoletas (consolidadas):**
- ❌ `2025_11_18_071800_add_multi_currency_support_to_llm_usage_logs.php`
- ❌ `2025_11_21_000001_add_is_global_to_llm_manager_prompt_templates_table.php`
- ❌ `2025_11_21_000013_add_missing_fields_to_tool_definitions_table.php`
- ❌ `2025_11_21_235900_add_openrouter_to_provider_enum.php`

**Migration original obsoleta:**
- ❌ `2025_11_18_000001_create_llm_manager_configurations_table.php` (reemplazada por nueva estructura)

### Archivos a Modificar

**Models:**
- `src/Models/LLMConfiguration.php` → renombrar a `LLMProviderConfiguration.php`
- Actualizar relaciones: `belongsTo(LLMProvider)`, `hasMany(LLMUsageLog)`, etc.

**Services:**
- `src/Services/LLMConfigurationService.php` (usar tabla `llm_manager_provider_configurations`)
- `src/Console/Commands/ImportProviderConfigs.php` (crear provider record antes de configs)

**Controllers:**
- `src/Http/Controllers/Admin/LLMModelController.php` (usar `LLMProviderConfiguration`)
- Todos los controllers que usan `LLMConfiguration` (6 archivos)

**Seeders:**
- `database/seeders/LLMConfigurationSeeder.php` → renombrar a `LLMProviderConfigurationSeeder.php`
- Actualizar para crear providers primero
- `database/seeders/LLMUninstallSeeder.php` (data preservation logic)

**Tests:**
- Actualizar todos los tests que usan `LLMConfiguration` → `LLMProviderConfiguration`

---

## 🚀 Timeline Actualizado

| Fase | Descripción | Horas | Status |
|------|-------------|-------|--------|
| **FASE 1** | Service Layer | 2h | ✅ Complete |
| **FASE 2** | Core Import System | 3h | ✅ Complete |
| **FASE 2.5** | Database Refactoring | 5.5h | 🆕 ⏳ Next |
| **FASE 3** | First Provider (Ollama) | 4h | 🔜 Pending |
| **FASE 4** | Developer Tools (Dev Mode) | 6h | 🔜 Pending |
| **FASE 5** | Additional Providers | 6h | 🔜 Pending |
| **FASE 6** | Testing & Docs | 3h | 🔜 Pending |

**Total:** 29.5h (36h original - 7h Admin UI - 1h migration datos + 1.5h archiving/dev-mode)

**FASE 2.5 (NEW):** Database refactoring completo
- ✅ Backup datos realizados
- ✅ Extension desinstalada
- ✅ llm-provider-ollama workspace creado
- Reorganizar migrations (providers primero, 14 total)
- Consolidar migrations (eliminar 4 archivos)
- Provider Archiving (is_installed, archived_at, ON DELETE RESTRICT)
- Rename: LLMConfiguration → LLMProviderConfiguration
- Create: LLMProvider model + ArchiveProvider command
- Update: All controllers/services/tests
- Install extension con nueva estructura
- Seed datos demo
- Validación completa

**⚠️ Fase más crítica:** Requiere testing exhaustivo

---

## ✅ Siguiente Paso

**FASE 2.5: Database Refactoring (5.5 horas)**

### Subtareas:

1. **✅ Backup de datos actuales** (COMPLETADO)
   - backups/bithoven_laravel.bak.sql (base completa)
   - backups/llm-manager.bak.sql (solo llm_manager_*)

2. **✅ Desinstalar extension actual** (COMPLETADO)
   - Extension desinstalada de CPANEL
   - Tablas llm_manager_* eliminadas

3. **Reorganizar migrations** (1h)
   - Borrar 5 migrations obsoletas
   - Crear `000001_create_llm_manager_providers_table.php` (con is_installed, archived_at)
   - Crear `000002_create_llm_manager_provider_configurations_table.php` (consolidado)
   - Renumerar resto (000008 y 000014 corregidos, total 14 migrations)
   - Consolidar campos en usage_logs, prompt_templates, tool_definitions
   - FKs con ON DELETE RESTRICT (data preservation)

4. **Refactor Models** (1h)
   - Rename: `LLMConfiguration` → `LLMProviderConfiguration`
   - Create: `LLMProvider` model
   - Update relationships

5. **Update Services/Controllers** (1h)
   - `LLMConfigurationService` (usar nueva tabla)
   - 6 Controllers (actualizar imports)
   - `ImportProviderConfigs` command

6. **Update Seeders y Commands** (45 min)
   - Create: `LLMProvidersSeeder`
   - Update: `LLMProviderConfigurationSeeder` (antiguo LLMConfigurationSeeder)
   - Update: `LLMDemoSeeder`
   - Update: `LLMUninstallSeeder` (data preservation)
   - Create: `ArchiveProvider` command (llm:archive-provider)
   - Update: `ImportProviderConfigs` (detectar archived providers)

7. **Instalar extension nueva** (5 min)
   - `php artisan bithoven:extension:install llm-manager`

8. **Seed datos demo** (5 min)
   - Ejecutar LLMProvidersSeeder
   - Ejecutar LLMProviderConfigurationSeeder
   - Ejecutar LLMDemoSeeder

9. **Testing completo** (1h)
   - Actualizar 44 tests existentes
   - Validar estructura de DB
   - Validar FKs
   - Validar seeders
   - Smoke tests en UI

**¿Proceder con FASE 2.5?**
