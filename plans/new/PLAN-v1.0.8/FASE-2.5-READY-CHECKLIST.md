# FASE 2.5 - Ready to Proceed Checklist ✅

**Fecha:** 12 de diciembre de 2025  
**Status:** ✅ READY TO START

---

## ✅ Pre-requisitos Completados

### 1. Backups Realizados
- ✅ `backups/bithoven_laravel.bak.sql` (base de datos completa)
- ✅ `backups/llm-manager.bak.sql` (solo tablas llm_manager_*)

### 2. Extension Desinstalada
- ✅ `php artisan bithoven:extension:uninstall llm-manager` ejecutado
- ✅ Tablas `llm_manager_*` eliminadas de DB
- ✅ Vendor packages limpiados

### 3. Provider Package Workspace
- ✅ Carpeta creada: `/Users/madniatik/CODE/LARAVEL/BITHOVEN/PACKAGES/llm-provider-ollama`
- ✅ VSCode workspace agregado: `llm-provider-ollama`

### 4. Documentación Actualizada
- ✅ ARCHITECTURE-FINAL-ANALYSIS.md v2.1.0
  - Provider Archiving (is_installed, archived_at, metadata)
  - Dev-Mode Protocol (.repo pattern)
  - Package Naming (/PACKAGES/llm-provider-*)
  - Foreign Keys (ON DELETE RESTRICT)

---

## 🎯 FASE 2.5 Scope

**Duración estimada:** 5.5 horas  
**Objetivo:** Refactoring completo de database con nueva arquitectura

### Subtareas

#### 1. ✅ Backup de datos actuales (COMPLETADO)

#### 2. ✅ Desinstalar extension actual (COMPLETADO)

#### 3. Reorganizar migrations (1h)
- [ ] Borrar 5 migrations obsoletas
- [ ] Crear `2025_11_18_000001_create_llm_manager_providers_table.php`
  - Campos: id, slug, name, package, version, api_endpoint, capabilities
  - **NUEVO:** is_installed, archived_at, metadata
- [ ] Crear `2025_11_18_000002_create_llm_manager_provider_configurations_table.php`
  - **RENAME:** `llm_manager_configurations` → `llm_manager_provider_configurations`
  - Campos consolidados: cost_per_1k_*, currency
  - FK: provider_id → llm_manager_providers (ON DELETE RESTRICT)
- [ ] Renumerar resto (000008 y 000014 corregidos)
- [ ] Consolidar campos en:
  - `000003_usage_logs` (multi-currency)
  - `000005_prompt_templates` (is_global)
  - `000012_tool_definitions` (is_enabled, execution_timeout)
- [ ] Actualizar FKs en:
  - `000003_usage_logs`: llm_provider_configuration_id
  - `000006_conversation_sessions`: llm_provider_configuration_id

#### 4. Refactor Models (1h)
- [ ] Rename: `src/Models/LLMConfiguration.php` → `LLMProviderConfiguration.php`
- [ ] Create: `src/Models/LLMProvider.php`
  - Relationships: hasMany(configurations), scopes: active(), withArchived()
- [ ] Update relationships en:
  - LLMProviderConfiguration: belongsTo(LLMProvider), hasMany(usageLogs, etc.)
  - LLMUsageLog: belongsTo(LLMProviderConfiguration)
  - LLMConversationSession: belongsTo(LLMProviderConfiguration)

#### 5. Update Services/Controllers (1h)
- [ ] `src/Services/LLMConfigurationService.php`
  - Usar tabla `llm_manager_provider_configurations`
  - Actualizar queries y cache keys
- [ ] `src/Console/Commands/ImportProviderConfigs.php`
  - Detectar providers archived (withArchived scope)
  - Restore logic si is_installed = false
  - Create/Update provider record antes de configs
- [ ] Controllers (6 archivos):
  - `LLMModelController.php`
  - `LLMChatController.php`
  - `LLMUsageController.php`
  - `LLMPromptController.php`
  - `LLMConversationController.php`
  - `LLMAgentController.php`
  - Actualizar imports: LLMConfiguration → LLMProviderConfiguration

#### 6. Update Seeders y Commands (45 min)
- [ ] Create: `database/seeders/LLMProvidersSeeder.php`
  - Seed 6 providers: ollama, openai, anthropic, openrouter, google, cohere
- [ ] Rename: `LLMConfigurationSeeder.php` → `LLMProviderConfigurationSeeder.php`
  - Create providers primero, luego configs con provider_id FK
- [ ] Update: `LLMDemoSeeder.php`
  - Usar nuevos models
- [ ] Update: `LLMUninstallSeeder.php`
  - Data preservation logic (check usage_logs, conversations)
- [ ] Create: `src/Console/Commands/ArchiveProvider.php`
  - Command: `llm:archive-provider {slug}`
  - Archive logic: is_active = false, is_installed = false, archived_at = now()
  - Deactivate configurations

#### 7. Instalar extension nueva (5 min)
- [ ] `php artisan bithoven:extension:install llm-manager`
- [ ] Verificar migrations ejecutadas correctamente
- [ ] Verificar tablas creadas con estructura correcta

#### 8. Seed datos demo (5 min)
- [ ] `php artisan db:seed --class=Bithoven\LLMManager\Database\Seeders\LLMProvidersSeeder`
- [ ] `php artisan db:seed --class=Bithoven\LLMManager\Database\Seeders\LLMProviderConfigurationSeeder`
- [ ] `php artisan db:seed --class=Bithoven\LLMManager\Database\Seeders\LLMDemoSeeder`
- [ ] Verificar datos insertados correctamente

#### 9. Testing completo (1h)
- [ ] Actualizar 44 tests existentes:
  - `tests/Unit/LLMConfigurationServiceTest.php` → `LLMProviderConfigurationServiceTest.php`
  - Actualizar imports y assertions
- [ ] Validar estructura de DB:
  - [ ] Tabla `llm_manager_providers` existe
  - [ ] Tabla `llm_manager_provider_configurations` existe
  - [ ] FKs correctas (ON DELETE RESTRICT)
  - [ ] Índices correctos
- [ ] Validar seeders:
  - [ ] 6 providers insertados
  - [ ] Configuraciones con provider_id correcto
- [ ] Smoke tests en UI:
  - [ ] Admin panel → LLM Models (listar configurations)
  - [ ] Chat interface funciona
  - [ ] Usage logs se guardan correctamente
  - [ ] Stats/Metrics accesibles

---

## 🔑 Key Decisions Implemented

### 1. Provider Archiving (Data Preservation)
```php
// Desinstalación NO borra datos
composer remove bithoven/llm-provider-anthropic
→ php artisan llm:archive-provider anthropic
→ is_installed = false, is_active = false, archived_at = now()
→ Configurations: is_active = false
→ Data: PRESERVED (logs, conversations, metrics)

// Reinstalación restaura datos
composer require bithoven/llm-provider-anthropic
→ php artisan llm:import anthropic
→ Detecta archived provider
→ is_installed = true, is_active = true, archived_at = null
→ Configurations: is_active = true
→ Data: RESTORED (historia completa)
```

### 2. Foreign Keys (Data Integrity)
```php
// Todas las FKs con ON DELETE RESTRICT
$table->foreignId('provider_id')
    ->constrained('llm_manager_providers')
    ->onDelete('restrict');  // ❌ Previene borrado físico

// Beneficios:
// ✅ Integridad referencial garantizada
// ✅ Stats/Metrics históricos accesibles
// ✅ GDPR/Compliance (datos preservados)
// ✅ Auditoría completa
```

### 3. Migration Order (Dependencies)
```
000001_providers (PADRE)
000002_provider_configurations (HIJO, FK → providers)
000003_usage_logs (FK → provider_configurations)
000004_custom_metrics
000005_prompt_templates (con is_global consolidado)
000006_conversation_sessions (FK → provider_configurations)
000007_conversation_messages
000008_document_knowledge_base (número corregido)
000009_mcp_connectors
000010_agent_workflows
000011_tool_definitions (con campos consolidados)
000012_tool_executions
000013_user_workspace_preferences
000014_parameter_overrides (número corregido)
```

### 4. Package Location (Confirmed)
```
/Users/madniatik/CODE/LARAVEL/BITHOVEN/
├── CPANEL/                    # App principal
├── EXTENSIONS/                # Extension Manager extensions
│   └── bithoven-extension-llm-manager/
└── PACKAGES/                  # Composer packages (NO extensiones)
    └── llm-provider-ollama/   ✅ CREADO
```

### 5. Dev-Mode Protocol (.repo pattern)
```bash
# Extension Manager pattern (2 symlinks)
vendor/bithoven/llm-manager → .repo (backup)
public/vendor/bithoven/llm-manager → .repo (backup)
vendor/bithoven/llm-manager (symlink) → /EXTENSIONS/...
public/vendor/bithoven/llm-manager (symlink) → /EXTENSIONS/.../public

# LLM Manager pattern (FASE 4)
vendor/bithoven/llm-provider-ollama → .repo (backup)
vendor/bithoven/llm-provider-ollama (symlink) → /PACKAGES/llm-provider-ollama
```

---

## 📋 Migrations a ELIMINAR (obsoletas)

```bash
# Consolidadas en originales:
database/migrations/
├── ❌ 2025_11_18_071800_add_multi_currency_support_to_llm_usage_logs.php
├── ❌ 2025_11_21_000001_add_is_global_to_llm_manager_prompt_templates_table.php
├── ❌ 2025_11_21_000013_add_missing_fields_to_tool_definitions_table.php
├── ❌ 2025_11_21_235900_add_openrouter_to_provider_enum.php
└── ❌ 2025_11_18_000001_create_llm_manager_configurations_table.php (old structure)
```

---

## 📝 Files to Create/Modify

### New Files (5)
1. `database/migrations/2025_11_18_000001_create_llm_manager_providers_table.php`
2. `database/migrations/2025_11_18_000002_create_llm_manager_provider_configurations_table.php`
3. `src/Models/LLMProvider.php`
4. `database/seeders/LLMProvidersSeeder.php`
5. `src/Console/Commands/ArchiveProvider.php`

### Rename (2)
1. `src/Models/LLMConfiguration.php` → `LLMProviderConfiguration.php`
2. `database/seeders/LLMConfigurationSeeder.php` → `LLMProviderConfigurationSeeder.php`

### Update (14)
1. `src/Services/LLMConfigurationService.php`
2. `src/Console/Commands/ImportProviderConfigs.php`
3. `src/Http/Controllers/Admin/LLMModelController.php`
4. `src/Http/Controllers/LLMChatController.php`
5. `src/Http/Controllers/LLMUsageController.php`
6. `src/Http/Controllers/LLMPromptController.php`
7. `src/Http/Controllers/LLMConversationController.php`
8. `src/Http/Controllers/LLMAgentController.php`
9. `database/seeders/LLMDemoSeeder.php`
10. `database/seeders/LLMUninstallSeeder.php`
11. `database/migrations/2025_11_18_000003_create_llm_manager_usage_logs_table.php` (consolidar campos)
12. `database/migrations/2025_11_18_000005_create_llm_manager_prompt_templates_table.php` (consolidar is_global)
13. `database/migrations/2025_11_18_000012_create_llm_manager_tool_definitions_table.php` (consolidar campos)
14. All 44 tests (actualizar imports)

---

## ⚠️ Critical Reminders

1. **NO usar `migrate:fresh`** - Destruye toda la base de datos de CPANEL
2. **Usar Extension Manager install/uninstall** - Scope limitado a llm_manager
3. **ON DELETE RESTRICT** en todas las FKs - Data preservation
4. **is_installed + archived_at** - Track package lifecycle
5. **Provider table FIRST** - Migration order crítico
6. **Tests BEFORE commit** - Validar 44 tests pasan

---

## 🚀 Next Steps

1. **Proceder con subtarea 3:** Reorganizar migrations
2. **Commit frecuente:** Pequeños commits con scope claro
3. **Testing continuo:** Después de cada subtarea mayor
4. **Dev-mode:** Usar Extension Manager dev-mode para desarrollo local
5. **FASE 3:** First Provider (llm-provider-ollama) una vez FASE 2.5 complete

---

**¿Proceder con FASE 2.5?** ✅ READY
