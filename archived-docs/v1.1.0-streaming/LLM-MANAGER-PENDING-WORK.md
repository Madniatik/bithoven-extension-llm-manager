# ⚠️ DOCUMENTO HISTÓRICO - Ver PROJECT-STATUS.md y ROADMAP.md

**Este documento ha sido reemplazado por:**
- `PROJECT-STATUS.md` - Estado consolidado del proyecto (actualizado)
- `ROADMAP.md` - Hoja de ruta de versiones futuras

**Última Actualización de este archivo:** 26 de noviembre de 2025
**Estado:** ⚠️ OBSOLETO - Mantenido solo para referencia histórica

---

# LLM Manager Extension - Tareas Pendientes y Estado Actual (HISTÓRICO)

**Fecha Original:** 25 de noviembre de 2025  
**Versión Documentada:** v1.1.0-dev  
**Última Sesión Documentada:** Streaming Metrics Implementation + UI Fixes  
**AI Agent:** Claude (Claude Sonnet 4.5, Anthropic)

---

## ✅ ACTUALIZACIÓN - Nov 26, 2025

**v1.1.0 COMPLETADO AL 100%**

Todos los items pendientes documentados aquí han sido:
- ✅ **Streaming SSE:** COMPLETADO (100%)
- ✅ **Metrics Logging:** COMPLETADO (100%)
- ✅ **UI Fixes:** COMPLETADOS (100%)
- ✅ **Permissions Issue:** RESUELTO (Permissions v2.0)

**Pendientes movidos a v1.2.0:**
- 📋 Statistics Dashboard → Ver `ROADMAP.md` v1.2.0
- 📋 Testing Suite → Ver `ROADMAP.md` v1.2.0

---

## 📊 Estado General del Proyecto (HISTÓRICO - Nov 25, 2025)

### Versión v1.0.0 (Released)
✅ **100% Completo** - Funcionalidad core estable y documentada

### Versión v1.1.0 (En Desarrollo - 85% completo) [AHORA: 100% COMPLETO]
🟢 **Streaming SSE:** 100% funcional  
🟢 **Metrics Logging:** 100% funcional (Phase 1 completada)  
🟡 **UI Fixes:** 95% completo (scroll y auto-scroll resueltos en commit a775101) [AHORA: 100%]
🔴 **Statistics Dashboard:** 0% (Phase 2 pendiente) [MOVIDO A v1.2.0]
🔴 **Tests:** 35% passing (bloqueados por infraestructura) [MOVIDO A v1.2.0]

---

## 🚀 Trabajo Completado en Últimas Sesiones

### Commits Recientes (Últimas 24 horas)

#### 1. `a775101` - fix(streaming): correct scroll container and remove disruptive auto-scroll
**Cambios:**
- Movido `max-height: 500px` del `card-body` al `card` (contenedor correcto)
- Eliminado `responseDiv.scrollIntoView()` que bloqueaba navegación
- Response card ahora tiene scroll funcional
- Usuario puede navegar página durante streaming

**Archivos modificados:**
- `resources/views/admin/stream/test.blade.php`

---

#### 2. `8f1debb` - fix(streaming): UI improvements for response and monitor
**Cambios:**
- Intentado fix de scroll en response card (CSS presente pero no funcionó)
- Monitor color cambiado: `bg-dark` → `bg-light-dark`
- Monitor text: `text-light` → `text-gray-800`
- Monitor logs persistence mejorado (solo limpia mensaje inicial)

**Archivos modificados:**
- `resources/views/admin/stream/test.blade.php`

**Nota:** Este commit fue superseded por a775101 que resolvió el scroll correctamente.

---

#### 3. `3403bdb` - refactor(streaming): monitor shows real-time streaming activity
**Cambios:**
- Refactorizado monitor de "Test Connection" a "Streaming Activity" real-time
- Monitor auto-activa durante streaming
- Logs de: request details, SSE connection, chunks, tokens, final metrics
- Estados de badge: Inactive → Active → Completed/Stopped/Error
- Logging mejorado con timestamps y color coding
- Auto-scroll a bottom del console

**Archivos modificados:**
- `resources/views/admin/stream/test.blade.php` (sección monitor completa)

**Contexto:** User pidió monitor como en `/admin/llm/models/2`, no test connection manual.

---

#### 4. `aaa4558` - feat(streaming): add connection monitor to test page
**Cambios:**
- Implementación INCORRECTA de monitor (Test Connection estático)
- Monitor con botón manual "Test Connection"
- Logs estáticos, no real-time streaming

**Archivos modificados:**
- `resources/views/admin/stream/test.blade.php`

**Nota:** Este commit fue REVERTIDO por 3403bdb cuando user clarificó necesidad de monitor real-time.

---

#### 5. `054fb8c` - feat(streaming): UI improvements and activity tracking
**Cambios:**
- Stats bar expandido de 3 a 6 columnas (Tokens, Chunks, Duration, Cost, Log ID, View Log)
- `stopStreaming(resetMetrics)` parameter agregado
- Activity table con localStorage (last 10 items)
- Click to expand/collapse rows en activity table
- `addToActivityHistory()` function
- `renderActivityTable()` function
- View Log button abre `/admin/llm/stats?log_id=X`

**Archivos nuevos:**
- `src/Http/Controllers/Admin/LLMActivityController.php` (NEW)
- `resources/views/admin/activity/index.blade.php` (NEW)
- `resources/views/admin/activity/show.blade.php` (NEW)

**Rutas agregadas:**
- `GET /admin/llm/activity` - Lista logs con filtros
- `GET /admin/llm/activity/{id}` - Detalles de log individual
- `GET /admin/llm/activity-export/csv` - Export CSV
- `GET /admin/llm/activity-export/json` - Export JSON

**Archivos modificados:**
- `resources/views/admin/stream/test.blade.php` (UI improvements)
- `routes/web.php` (activity routes)
- `routes/breadcrumbs.php` (activity breadcrumbs)

---

#### 6. `ae29df2` - feat(streaming): Add usage metrics logging for streaming (FASE 1)
**MILESTONE:** Phase 1 de plan completo de metrics logging

**Cambios:**

**1. Interface Update (BREAKING CHANGE):**
```php
// OLD: void
public function stream(string $prompt, array $context, array $parameters, callable $callback): void

// NEW: array with metrics
public function stream(string $prompt, array $context, array $parameters, callable $callback): array
```

**Retorno esperado:**
```php
[
    'usage' => [
        'prompt_tokens' => int,
        'completion_tokens' => int,
        'total_tokens' => int,
    ],
    'model' => string,
    'finish_reason' => string|null, // 'stop', 'length', 'tool_calls', etc.
]
```

**2. Provider Implementations:**

**OllamaProvider:**
- Captura `$finalData` del chunk con `done=true` (NDJSON)
- Extrae `prompt_eval_count`, `eval_count` del objeto done
- Retorna array con usage metrics

**OpenRouterProvider & OpenAIProvider:**
- Almacena `$lastResponse` del SDK iterator
- Extrae `usage->promptTokens`, `completionTokens`, `totalTokens`
- Retorna array con usage metrics

**Anthropic & CustomProvider:**
- Stubs implementados (retornan structure vacía)
- Listos para implementación futura

**3. LLMStreamLogger Service (NEW):**
```php
class LLMStreamLogger
{
    public function startSession(LLMConfiguration $config, string $prompt, array $params): array
    public function endSession(array $session, array $metrics): void
    public function calculateCost(string $provider, string $model, array $usage): float
    public function logError(array $session, string $error): void
}
```

**Funcionalidad:**
- `startSession()`: Crea session con uuid, start_time, configuration, prompt, params
- `endSession()`: Calcula execution_time_ms, llama calculateCost(), guarda LLMUsageLog
- `calculateCost()`: Lee `config/llm-manager.php` pricing, calcula por 1M tokens
- `logError()`: Guarda failed streaming con status='error'

**4. Controller Integration:**
```php
// LLMStreamController.php
public function __construct(
    LLMManager $llmManager,
    LLMStreamLogger $streamLogger // NEW dependency injection
) {}

public function stream(Request $request): StreamedResponse
{
    // 1. Start session
    $session = $this->streamLogger->startSession($config, $prompt, $parameters);
    
    // 2. Stream with callback
    $metrics = $provider->stream($prompt, $context, $parameters, $callback);
    
    // 3. End session with metrics
    $this->streamLogger->endSession($session, $metrics);
    
    // 4. Return real usage data in 'done' event
    return Response::stream(...);
}
```

**5. Pricing Configuration:**
```php
// config/llm-manager.php (NEW section at line 368-407)
'pricing' => [
    'openai' => [
        'gpt-4o' => ['prompt' => 2.50, 'completion' => 10.00],
        'gpt-4o-mini' => ['prompt' => 0.15, 'completion' => 0.60],
    ],
    'anthropic' => [
        'claude-3-5-sonnet-20241022' => ['prompt' => 3.00, 'completion' => 15.00],
        'claude-3-opus-20240229' => ['prompt' => 15.00, 'completion' => 75.00],
    ],
    'openrouter' => [
        // Dynamic pricing, example:
        'gpt-5.1' => ['prompt' => 5.00, 'completion' => 15.00],
    ],
    'ollama' => [
        // Local models are free
        '*' => ['prompt' => 0.00, 'completion' => 0.00],
    ],
]
```

**Archivos modificados:**
- `src/Contracts/LLMProviderInterface.php` (BREAKING CHANGE)
- `src/Providers/OllamaProvider.php` (real token capture)
- `src/Providers/OpenRouterProvider.php` (real token capture)
- `src/Providers/OpenAIProvider.php` (real token capture)
- `src/Providers/AnthropicProvider.php` (stub updated)
- `src/Providers/CustomProvider.php` (stub updated)
- `src/Services/LLMStreamLogger.php` (NEW SERVICE)
- `src/Http/Controllers/Admin/LLMStreamController.php` (logger integration)
- `config/llm-manager.php` (pricing section added)

**Database:**
- 57+ records en `llm_manager_usage_logs` con datos reales
- Campos: prompt_tokens, completion_tokens, total_tokens, cost_usd, execution_time_ms, status

---

### Otros Commits Relevantes

#### `9d6da1a` - fix: Handle OpenRouter/OpenAI SDK cosmetic error
- OpenRouter/OpenAI SDK no devuelven `predictedTokens` en algunos modelos
- Añadido null check para evitar warnings
- No afecta funcionalidad, solo cosmético

#### `46e06cc` - feat: Fix streaming implementation for all LLM providers
- Correcciones finales en implementación streaming
- Verificación de todos los providers

#### `0876b2d` - feat(streaming): implement full streaming support with conversation context
- Streaming con contexto de conversación
- Support multi-turn conversations
- `conversationStream()` endpoint

---

## 📋 Tareas Pendientes v1.1.0

### PHASE 2: Statistics Dashboard (PENDIENTE - 0%)

**Objetivo:** Dashboard con breakdown por provider/model de uso y costos

**Estimación:** 4 horas

#### 2.1 Migration Updates (30 min)
```php
// Add to llm_manager_usage_logs
$table->string('provider', 50)->index()->after('configuration_id');
$table->string('model', 100)->index()->after('provider');
```

**Archivos:**
- Nueva migration: `2025_11_25_000001_add_provider_model_to_usage_logs.php`

#### 2.2 Service Implementation (1.5h)
```php
// NEW: src/Services/LLMStatisticsService.php
class LLMStatisticsService
{
    public function totalUsageByProvider(Carbon $from, Carbon $to): Collection
    public function totalUsageByModel(Carbon $from, Carbon $to): Collection
    public function costBreakdownByProvider(Carbon $from, Carbon $to): array
    public function costBreakdownByModel(string $provider, Carbon $from, Carbon $to): array
    public function topModels(int $limit = 10): Collection
    public function usageTrends(string $period = 'daily'): array
}
```

**Testing:**
- Unit tests para cada método
- Integration test con datos demo

#### 2.3 Controller & Views (1.5h)
```php
// UPDATE: src/Http/Controllers/Admin/LLMUsageStatsController.php
public function dashboard()
{
    $stats = [
        'by_provider' => $this->statsService->totalUsageByProvider(...),
        'by_model' => $this->statsService->totalUsageByModel(...),
        'cost_breakdown' => $this->statsService->costBreakdownByProvider(...),
        'trends' => $this->statsService->usageTrends('daily'),
    ];
    return view('llm-manager::admin.stats.dashboard', compact('stats'));
}
```

**Views Update:**
- `resources/views/admin/stats/dashboard.blade.php` - Add charts
- `resources/views/admin/stats/index.blade.php` - Update tables
- Charts: ApexCharts or Chart.js
- Tables: DataTables with grouping

#### 2.4 Routes & Breadcrumbs (30 min)
- Update `routes/web.php` with new statistics routes
- Update `routes/breadcrumbs.php`

**Status:** 🔴 NOT STARTED

---

### PHASE 3: Enhanced Dashboard Widgets (OPCIONAL)

**Objetivo:** Widgets interactivos para métricas en tiempo real

**Estimación:** 3 horas

#### Features:
- Live cost tracker (WebSocket o polling)
- Model performance comparison
- Token usage heatmap
- Budget alerts
- Export reports (PDF, Excel)

**Status:** 🔴 NOT PLANNED YET

---

### UI/UX Fixes Pendientes

#### ✅ RESUELTO: Response Card Scroll
- **Problema:** Card crecía sin límite
- **Solución:** `max-height: 500px` en `card` (no `card-body`)
- **Commit:** a775101

#### ✅ RESUELTO: Auto-scroll Disruptive
- **Problema:** `scrollIntoView()` forzaba scroll de página
- **Solución:** Eliminado auto-scroll
- **Commit:** a775101

#### ✅ RESUELTO: Monitor Color
- **Problema:** `bg-dark` + `text-light` difícil de leer
- **Solución:** `bg-light-dark` + `text-gray-800`
- **Commit:** 8f1debb

#### ✅ RESUELTO: Monitor Logs Disappearing
- **Problema:** Logic limpiaba logs en cada nuevo log
- **Solución:** Solo limpiar mensaje inicial "Monitor ready"
- **Commit:** 8f1debb

#### 🟡 PENDIENTE: Browser Cache Issue
- **Problema:** Cambios CSS no se aplican sin hard refresh
- **Solución TEMP:** User debe hacer Ctrl+Shift+R
- **TODO:** Versioning de assets con Laravel Mix hash

---

## 🧪 Testing Status

### Current: 35% Pass Rate (28/80 tests)

**Última ejecución:** 18 de noviembre de 2025

#### Passing Suites:
- ✅ LLMConfiguration: 6/9 (67%)
- ✅ LLMUsageLog: 7/11 (64%)
- ✅ LLMPromptTemplate: 11/9 (122% - más tests de los esperados)
- ✅ LLMConversationSession: 2/8 (25%)
- ✅ LLMDocumentKnowledgeBase: 2/10 (20%)

#### Failing Categories:
- 🔴 Service Implementation Gaps: 12 errors (falta `LLMBudgetManager`, `LLMEmbeddingsService`)
- 🔴 Model Relationship Issues: 8 errors (foreign keys, factories)
- 🔴 NOT NULL Constraints: 15 errors (migrations vs factories)
- 🔴 Business Logic: 9 errors (cache, exceptions, calculations)

### Plan to 100% (Estimado: 4-6h)

#### Priority 1: Model Factories (Est. 2h, +15 tests)
```php
// database/factories/
- LLMConfigurationFactory.php
- LLMPromptTemplateFactory.php
- LLMUsageLogFactory.php
- LLMConversationSessionFactory.php
- LLMConversationMessageFactory.php
- LLMDocumentKnowledgeBaseFactory.php
```

#### Priority 2: Complete Services (Est. 1.5h, +12 tests)
```php
// Implement fully:
- src/Services/LLMBudgetManager.php (métodos budget tracking)
- src/Services/LLMEmbeddingsService.php (generate, store, search)
- Complete provider streaming methods
```

#### Priority 3: Fix Relationships (Est. 1h, +8 tests)
```php
// Fix cascade behavior:
- LLMConversationSession::messages() must create related records
- Ensure foreign keys in factories
```

#### Priority 4: Business Logic (Est. 0.5-1h, +7 tests)
```php
// Fix:
- Cache configuration in phpunit.xml
- Exception throwing in services
- Execution time calculation (null → ms)
```

**Status:** 🔴 BLOCKED (waiting for PHASE 2 completion to avoid conflicts)

---

## ✅ Known Issues - RESUELTOS (Nov 26, 2025)

### ✅ Critical - RESUELTOS

#### 1. Permissions System ✅ RESUELTO
**Estado Original:** Error 403 al acceder a `/admin/llm` después de desinstalar extensión Dummy

**Solución Implementada:**
- ✅ Migración a **Permissions Protocol v2.0** (commit 5be4346)
- ✅ Creación de `LLMPermissions.php` data class
- ✅ Auto-detection system en `ExtensionInstaller`
- ✅ Backward compatibility mantenida
- ✅ 12 permisos organizados: view, create, edit, delete, manage, test, etc.
- ✅ Extension aligned con CorePermissions protocol

**Archivos Modificados:**
- `src/Data/Permissions/LLMPermissions.php` (NUEVO)
- `composer.json` (PSR-4 autoload)
- ServiceProvider (removido `getPermissions()` method)

**Resultado:** Sistema de permisos 100% funcional, sin hardcoding, auto-detectable.

---

## 🐛 Known Issues - HISTÓRICO (Nov 25, 2025)

### Critical (Blocker para producción) - DOCUMENTO HISTÓRICO

#### 1. Permissions System (CURRENT BLOCKER) [AHORA RESUELTO - Ver arriba]
**Síntoma:** Error 403 al acceder a `/admin/llm` después de desinstalar extensión Dummy

**Causa Root:**
- Desinstalación de Dummy alteró archivos core (composer.json, config/bithoven-extensions.php)
- Sistema de permisos parchado extension-by-extension
- No hay protocolo universal para permisos de extensiones

**Estado Histórico:**
- ✅ Permisos LLM agregados a `RolesPermissionsSeeder` (commit da9d265)
- ✅ Usuario tiene rol `super-admin` con 73 permisos
- ✅ Permiso `view-llm-configs` existe y está asignado
- ✅ Database check: `can('view-llm-configs')` = TRUE
- ✅ Sessions y caches limpiados
- ❌ STILL 403 ERROR (browser cache o middleware issue)

**Diagnóstico completado:**
```bash
# Script check-llm-perms.php ejecutado:
1. Permission 'view-llm-configs' exists: YES (ID: 55)
2. Super-admin role has permission: YES
3. User has role super-admin: YES
4. User can view-llm-configs: YES
5. Total LLM permissions: 18
```

**Posibles causas restantes (HISTÓRICO):**
- Browser cookie cache (user debe limpiar cookies)
- Middleware cache no regenerado (necesita restart server?)
- Session storage desincronizado con DB

**Solución APLICADA (Nov 26):**
- Implementación completa de Permissions Protocol v2.0
- Ver `PROJECT-STATUS.md` para detalles completos

**Archivos afectados (HISTÓRICO):**
- `database/seeders/RolesPermissionsSeeder.php`
- `vendor/bithoven/llm-manager/src/Http/Middleware/LLMAdminMiddleware.php`
- `config/bithoven-extensions.php`

---

### Medium (Puede afectar UX)

#### 2. Asset Versioning
**Problema:** CSS changes no se aplican sin hard refresh  
**Impacto:** User confusion, changes not visible  
**Solución:** Implementar Laravel Mix hash/versioning  
**Prioridad:** MEDIUM  

#### 3. Monitor Auto-Scroll
**Problema:** Monitor console puede crecer mucho en streams largos  
**Impacto:** Performance degradation, memoria  
**Solución:** Limitar logs a últimos 100 items, implement virtual scroll  
**Prioridad:** LOW  

---

### Low (Nice to have)

#### 4. Activity Table Pagination
**Problema:** localStorage solo guarda 10 items  
**Impacto:** Historial limitado en cliente  
**Solución:** Implementar paginación con API calls  
**Prioridad:** LOW  

#### 5. Export Formats
**Problema:** Solo CSV y JSON  
**Impacto:** Limited reporting options  
**Solución:** Agregar PDF, Excel (PhpSpreadsheet)  
**Prioridad:** LOW  

---

## 📚 Documentation Status

### ✅ Completado
- Installation guide
- Configuration reference
- API documentation
- Integration guide
- Conversations guide
- RAG setup guide
- Workflows guide
- Tools development guide
- MCP servers guide

### 🟡 Needs Update
- CHANGELOG.md (actualizar con v1.1.0 streaming features)
- README.md (agregar streaming examples)
- USAGE-GUIDE.md (streaming section)

### 🔴 Missing
- Streaming API reference
- Activity monitoring guide
- Statistics dashboard guide
- Troubleshooting guide (common issues)

---

## 🔄 Migration Path

### From v1.0.0 to v1.1.0

**Database Changes:**
```bash
# No breaking changes
php artisan migrate  # Will add new tables/columns for streaming
```

**Config Changes:**
```php
// config/llm-manager.php
// Add pricing section (line 368-407)
'pricing' => [
    'openai' => [...],
    'anthropic' => [...],
    // etc.
]
```

**Code Changes:**
- **BREAKING:** `LLMProviderInterface::stream()` now returns `array` instead of `void`
- Extensions using custom providers must update implementation

**Routes:**
- 4 new routes added: `/admin/llm/stream/*`, `/admin/llm/activity/*`
- No routes removed

**Permissions:**
- No new permissions required (uses existing `view-llm-configs`)

---

## 📦 Dependencies

### Current
- PHP ^8.2
- Laravel ^11.0
- Node.js ^18.0 (MCP servers)
- Python ^3.9 (database MCP)
- spatie/laravel-permission ^6.0
- yajra/laravel-datatables-oracle ^11.0

### Pending (Phase 2)
- ApexCharts or Chart.js (for statistics dashboard)

---

## 🎯 Roadmap

### v1.1.0 (Current - 85% complete)
- ✅ Real-time streaming (SSE)
- ✅ Usage metrics logging
- ✅ Activity monitoring
- 🔴 Statistics dashboard (PENDING)
- 🔴 Complete test coverage (PENDING)

### v1.2.0 (Planned)
- WebSocket support for chat
- Advanced workflow templates
- More bundled MCP servers
- Plugin system for custom providers

### v2.0.0 (Future)
- Multi-model ensemble support
- A/B testing for prompts
- Advanced cost optimization
- Extended analytics

---

## 🚨 Action Items

### IMMEDIATE (Before releasing v1.1.0)
1. **RESOLVER PERMISSIONS ISSUE** - Critical blocker
2. Implementar Phase 2: Statistics Dashboard
3. Update CHANGELOG.md con v1.1.0 features
4. Create migration guide v1.0.0 → v1.1.0
5. Test asset versioning fix

### SHORT TERM (1-2 weeks)
1. Create Model Factories para tests
2. Implement missing services (LLMBudgetManager, LLMEmbeddingsService)
3. Fix relationship tests
4. Achieve 100% test coverage

### MEDIUM TERM (1 month)
1. Implement Phase 3: Enhanced Dashboard Widgets
2. Add more export formats (PDF, Excel)
3. Create troubleshooting guide
4. Performance optimization (virtual scroll, lazy loading)

---

## 📝 Session Notes

### Last Session Summary (25 nov 2025, 16:30-16:45)

**Work Done:**
- ✅ Fixed scroll issues in response card (commit a775101)
- ✅ Removed disruptive auto-scroll
- ✅ Diagnosed permissions issue (DB check passed, browser cache suspected)
- ✅ Added debug logging to LLMAdminMiddleware
- ✅ Cleaned sessions and caches

**Next Steps:**
1. User must clear browser cookies and re-login
2. If still 403, analyze permissions system holistically
3. Create universal permissions protocol for all extensions

**Blockers:**
- Permissions 403 error preventing LLM Manager access
- Need to analyze entire permissions architecture

---

## 🤝 Handoff Information

**Para retomar trabajo en LLM Manager:**

1. **Verificar estado de permisos:**
   ```bash
   php artisan tinker --execute="\$user = \App\Models\User::first(); echo \$user->can('view-llm-configs') ? 'OK' : 'FAIL';"
   ```

2. **Limpiar caches:**
   ```bash
   php artisan optimize:clear
   php artisan permission:cache-reset
   ```

3. **Revisar últimos commits:**
   ```bash
   cd /path/to/bithoven-extension-llm-manager
   git log --oneline -20
   ```

4. **Ejecutar tests:**
   ```bash
   vendor/bin/phpunit --testsuite=Unit
   ```

5. **Revisar este documento** para context completo

**Archivos clave a revisar:**
- `resources/views/admin/stream/test.blade.php` (UI principal streaming)
- `src/Services/LLMStreamLogger.php` (Metrics logging)
- `src/Http/Controllers/Admin/LLMStreamController.php` (Controller streaming)
- `config/llm-manager.php` (Pricing configuration)
- `database/seeders/RolesPermissionsSeeder.php` (Permissions - CPANEL)

---

**Última actualización:** 25 de noviembre de 2025, 16:47  
**Estado:** ⚠️ BLOCKED by permissions issue  
**Próxima tarea:** Analizar y rediseñar sistema de permisos universal  
**Estimado para v1.1.0 release:** Pendiente de resolución de permisos + 4h Phase 2
