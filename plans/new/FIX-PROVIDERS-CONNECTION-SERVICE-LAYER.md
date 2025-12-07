# Plan: Fix Providers Connection - Service Layer Implementation

**Status:** IN PROGRESS  
**Priority:** HIGH  
**Estimated Time:** 2 horas 15 min  
**Created:** 2025-12-07  
**Updated:** 2025-12-08  
**Assignee:** Claude (AI Agent)  
**Architecture:** Service Layer (Opción A - Aprobada)  
**Restore Point:** Commit `710ec29` - Pre-implementation

---

## 📋 Contexto

**Problema:** En `/admin/llm/models/{model}` (Edit Tab), el botón "Load Models" no funciona y no hay conexión con proveedores LLM.

**Causa:** Frontend hace `fetch()` directo a APIs externas → CORS fail + API keys expuestas

**Solución:** Implementar **Service Layer** (`LLMProviderService`) como proxy backend con cache

**Referencia completa:** `reports/analysis/PROVIDER-CONNECTION-ARCHITECTURE-ANALYSIS.md`

---

## 🎯 Objetivos

1. ✅ Crear `LLMProviderService` reutilizable
2. ✅ Refactorizar `testConnection()` para usar Service
3. ✅ Implementar `loadModels()` con cache
4. ✅ Actualizar Controller para usar Service
5. ✅ Fix frontend `loadDynamicModels()` para llamar backend
6. ✅ Crear componentes Blade parciales reutilizables

---

## 🏗️ Arquitectura: Service Layer

```
┌────────────────────────────────────────────────┐
│  ARQUITECTURA SERVICE LAYER                    │
├────────────────────────────────────────────────┤
│                                                 │
│  LLMProviderService (NEW) ✨                   │
│    ├─ testConnection($provider, ...)           │
│    ├─ loadModels($provider, ..., $cache)  ✨   │
│    ├─ parseModelsResponse($data)               │
│    └─ makeRequest($url, $method, ...)          │
│                                                 │
│  LLMConfigurationController (UPDATED)          │
│    ├─ testConnection() → Service::test()       │
│    └─ loadModels()  ✨  → Service::load()      │
│                                                 │
│  _edit-tab.blade.php (UPDATED)                 │
│    └─ loadDynamicModels() → AJAX backend       │
│                                                 │
│  Cache Layer (Laravel)                         │
│    └─ TTL: 10 min (configurable)               │
│                                                 │
└────────────────────────────────────────────────┘
```

---

## 📦 Fase 1: Service Layer (45 min)

### 1.1 Crear `LLMProviderService`

**Archivo:** `src/Services/LLMProviderService.php`

**Métodos:**

#### `testConnection(string $provider, ?string $endpoint, ?string $apiKey): array`
- Prueba conexión con proveedor
- Reutiliza lógica de `LLMConfigurationController::testConnection()`
- Retorna: `['success' => bool, 'message' => string, 'metadata' => array]`

#### `loadModels(string $provider, ?string $endpoint, ?string $apiKey, bool $useCache = true): array`
- Carga modelos desde API del proveedor
- Cache: 10 min (configurable via `llm-manager.cache.ttl`)
- Parsing flexible (OpenAI, Ollama, OpenRouter formats)
- Retorna: `['success' => bool, 'models' => array, 'cached' => bool]`

#### `parseModelsResponse(array $data, string $provider): array`
- Parsea diferentes formatos de respuesta
- OpenAI/OpenRouter: `{data: [{id: "..."}]}`
- Ollama: `{models: [{name: "..."}]}`
- Plain array: `["model1", "model2"]`
- Retorna: `[['id' => string, 'name' => string], ...]`

#### `makeRequest(string $url, string $method, array $headers, ?array $body = null): array`
- HTTP request vía cURL
- Timeout: 10s
- Retorna: `['success' => bool, 'data' => array, 'execution_time_ms' => float]`

#### `clearModelsCache(string $provider): bool`
- Limpia cache de modelos para un provider específico

**Checklist:**
- [ ] Crear archivo `src/Services/LLMProviderService.php`
- [ ] Implementar `makeRequest()` (base method)
- [ ] Implementar `testConnection()` (refactor de Controller)
- [ ] Implementar `loadModels()` con cache
- [ ] Implementar `parseModelsResponse()` (multi-format)
- [ ] Implementar `clearModelsCache()`
- [ ] Añadir DocBlocks completos
- [ ] Unit tests básicos (opcional)

---

## 📦 Fase 2: Controller Integration (30 min)

### 2.1 Actualizar `LLMConfigurationController`

**Archivo:** `src/Http/Controllers/Admin/LLMConfigurationController.php`

**Cambios:**

#### Refactorizar `testConnection()`
```php
use Bithoven\LLMManager\Services\LLMProviderService;

public function testConnection(Request $request, LLMProviderService $service)
{
    $validated = $request->validate([
        'provider' => 'required|string',
        'api_endpoint' => 'nullable|string',
        'api_key' => 'nullable|string',
    ]);
    
    $result = $service->testConnection(
        $validated['provider'],
        $validated['api_endpoint'] ?? null,
        $validated['api_key'] ?? null
    );
    
    return response()->json($result);
}
```

#### Crear `loadModels()` (NUEVO)
```php
public function loadModels(Request $request, LLMProviderService $service)
{
    $validated = $request->validate([
        'provider' => 'required|string',
        'api_endpoint' => 'nullable|string',
        'api_key' => 'nullable|string',
        'use_cache' => 'nullable|boolean',
    ]);
    
    $result = $service->loadModels(
        $validated['provider'],
        $validated['api_endpoint'] ?? null,
        $validated['api_key'] ?? null,
        $validated['use_cache'] ?? true
    );
    
    return response()->json($result);
}
```

**Checklist:**
- [ ] Refactorizar `testConnection()` → usar Service
- [ ] Crear método `loadModels()`
- [ ] Validar parámetros correctamente
- [ ] Manejar excepciones

### 2.2 Añadir Route

**Archivo:** `routes/web.php`

```php
// Dentro de Route::prefix('admin/llm')->group(...)
Route::post('configurations/load-models', [LLMConfigurationController::class, 'loadModels'])
    ->name('configurations.load-models');
```

**Checklist:**
- [ ] Añadir route `configurations.load-models`
- [ ] Verificar middleware (`auth`, `llm.admin`)

---

## 📦 Fase 3: Frontend Update (30 min)

### 3.1 Fix HTML Inicial - Botón Visible

**Archivo:** `resources/views/admin/models/partials/_edit-tab.blade.php`

**Problema actual (línea ~70):**
```blade
@if($providerConfig['supports_dynamic_models'] ?? false)
    Click to load available models
    <button type="button" class="btn btn-sm btn-light-primary ms-2" onclick="loadDynamicModels()">
        Load Models
    </button>
@else
    Enter the model identifier
@endif
```

**Fix:**
```blade
@if($providerConfig['supports_dynamic_models'] ?? false)
    <span class="me-2">Click to load available models from provider</span>
    <button type="button" id="load-models-btn" class="btn btn-sm btn-light-primary" onclick="loadDynamicModels()">
        <i class="ki-duotone ki-cloud-download fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        Load Models
    </button>
@else
    Enter the model identifier
@endif
```

### 3.2 Reescribir `loadDynamicModels()`

**Actual:** Hace `fetch()` directo a APIs externas (CORS fail)

**Nuevo:** Llama a backend proxy

```javascript
function loadDynamicModels() {
    const provider = document.getElementById('provider-select').value;
    const providers = @json($providers);
    const providerConfig = providers[provider] || {};
    
    if (!providerConfig.supports_dynamic_models) {
        return;
    }
    
    const inputField = document.getElementById('model-input');
    const selectField = document.getElementById('model-select');
    const hintDiv = document.getElementById('model-hint');
    const loadButton = document.getElementById('load-models-btn');
    
    // Guardar modelo actual para pre-selección
    const currentModel = inputField.value || selectField.value || '{{ $model->model }}';
    
    // UI feedback
    if (loadButton) {
        loadButton.disabled = true;
        loadButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    }
    hintDiv.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading models from provider...';
    
    // Get current form values
    const apiEndpoint = document.querySelector('input[name="api_endpoint"]')?.value || '';
    const apiKey = document.querySelector('input[name="api_key"]')?.value || '';
    
    // Call backend proxy
    fetch("{{ route('admin.llm.configurations.load-models') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            provider: provider,
            api_endpoint: apiEndpoint || null,
            api_key: apiKey || null,
            use_cache: true
        })
    })
    .then(response => response.json())
    .then(data => {
        // Restore button
        if (loadButton) {
            loadButton.disabled = false;
            loadButton.innerHTML = '<i class="ki-duotone ki-cloud-download fs-2"><span class="path1"></span><span class="path2"></span></i> Load Models';
        }
        
        if (!data.success) {
            hintDiv.innerHTML = `<span class="text-danger"><i class="ki-duotone ki-cross-circle fs-2"><span class="path1"></span><span class="path2"></span></i> ${data.message}</span>`;
            return;
        }
        
        const models = data.models || [];
        
        if (models.length === 0) {
            hintDiv.innerHTML = '<span class="text-warning"><i class="ki-duotone ki-information fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> No models found</span>';
            return;
        }
        
        // Populate select
        selectField.innerHTML = '<option value="">Select a model...</option>';
        
        let modelFound = false;
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name;
            
            if (model.id === currentModel) {
                option.selected = true;
                modelFound = true;
            }
            
            selectField.appendChild(option);
        });
        
        // Switch to select mode
        selectField.style.display = '';
        inputField.style.display = 'none';
        selectField.required = true;
        inputField.required = false;
        
        // Update hint with badge
        if (modelFound) {
            hintDiv.innerHTML = `<span class="text-muted">${models.length} models loaded</span> <span class="badge badge-success ms-2"><i class="ki-duotone ki-check fs-3"><span class="path1"></span><span class="path2"></span></i> Current model found</span>`;
        } else if (currentModel) {
            hintDiv.innerHTML = `<span class="text-muted">${models.length} models loaded</span> <span class="badge badge-warning ms-2"><i class="ki-duotone ki-information fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Current model "${currentModel}" not in list</span>`;
        } else {
            hintDiv.innerHTML = `<span class="text-muted">${models.length} models loaded${data.cached ? ' <span class="badge badge-light-primary ms-1">cached</span>' : ''}</span>`;
        }
    })
    .catch(error => {
        console.error('Error loading models:', error);
        if (loadButton) {
            loadButton.disabled = false;
            loadButton.innerHTML = '<i class="ki-duotone ki-cloud-download fs-2"><span class="path1"></span><span class="path2"></span></i> Load Models';
        }
        hintDiv.innerHTML = '<span class="text-danger"><i class="ki-duotone ki-cross-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Failed to load models. Check API key and endpoint.</span>';
    });
}
```

**Checklist:**
- [ ] Fix HTML botón visible en render inicial
- [ ] Añadir ID `load-models-btn` al botón
- [ ] Reescribir `loadDynamicModels()` para usar backend
- [ ] Añadir loading states (spinner en botón + hint)
- [ ] Añadir error handling completo
- [ ] Añadir badges de estado (success/warning/cached)
- [ ] Iconos Metronic (ki-duotone)

### 3.3 Componentes Blade Parciales (Opcional)

**Crear:** `resources/views/admin/models/partials/_model-field-dual.blade.php`

Componente reutilizable para select/input de modelos (preparado para dual-select futuro)

**Checklist:**
- [ ] Evaluar si crear componente parcial
- [ ] Mantener código inline por ahora (más simple)

---

## 📦 Fase 4: Testing (30 min)

### 4.1 Testing Manual

**Test 1: Ollama (Local, sin API key)**
```
- Provider: ollama
- Endpoint: http://localhost:11434
- API Key: N/A
- Expected: Lista de modelos desde /api/tags
```

**Test 2: OpenAI (Requiere API key)**
```
- Provider: openai
- Endpoint: https://api.openai.com/v1
- API Key: sk-...
- Expected: Lista de modelos desde /models
```

**Test 3: Anthropic (Hardcoded, no dynamic)**
```
- Provider: anthropic
- Expected: Select con modelos hardcoded, botón Load Models NO visible
```

**Test 4: Sin API Key (Error handling)**
```
- Provider: openai
- API Key: Empty
- Expected: Error 401, mensaje claro "API Key required"
```

**Test 5: Cache Validation**
```
1. Load models → Request HTTP
2. Load models again → Desde cache (badge "cached")
3. Wait 10+ min → Request HTTP nuevo
```

**Checklist:**
- [ ] Probar con Ollama local
- [ ] Probar con OpenAI (API key)
- [ ] Probar con Anthropic (hardcoded)
- [ ] Probar sin API key (error)
- [ ] Validar cache funcionando (TTL 10 min)
- [ ] Validar pre-selección de modelo actual
- [ ] Cross-browser (Chrome, Safari, Firefox)

### 4.2 Edge Cases

- [ ] Endpoint offline (timeout)
- [ ] Respuesta vacía (`{models: []}`)
- [ ] Respuesta formato inválido
- [ ] Provider sin `supports_dynamic_models`
- [ ] API key inválida (403/401)

---

## 📝 Checklist General

### Archivos a Crear
- [ ] `src/Services/LLMProviderService.php` (NUEVO)

### Archivos a Modificar
- [ ] `src/Http/Controllers/Admin/LLMConfigurationController.php`
- [ ] `routes/web.php`
- [ ] `resources/views/admin/models/partials/_edit-tab.blade.php`

### Testing
- [ ] Unit tests `LLMProviderService` (opcional)
- [ ] Manual testing (Ollama, OpenAI, Anthropic)
- [ ] Edge cases validation
- [ ] Cache TTL validation

### Documentation
- [ ] DocBlocks en Service
- [ ] Comentarios en código crítico
- [ ] Actualizar este plan con resultados

---

## 🚀 Orden de Implementación

```
1. ✅ Commit punto de restauración (DONE: 710ec29)
2. ⏳ Fase 1: Service Layer (45 min)
   ├─ LLMProviderService::makeRequest()
   ├─ LLMProviderService::testConnection()
   ├─ LLMProviderService::loadModels()
   ├─ LLMProviderService::parseModelsResponse()
   └─ LLMProviderService::clearModelsCache()
3. ⏳ Fase 2: Controller (30 min)
   ├─ Refactor testConnection()
   ├─ Crear loadModels()
   └─ Añadir route
4. ⏳ Fase 3: Frontend (30 min)
   ├─ Fix HTML botón
   ├─ Reescribir loadDynamicModels()
   └─ UX improvements (loading, errors, badges)
5. ⏳ Fase 4: Testing (30 min)
   ├─ Manual tests (providers)
   ├─ Edge cases
   └─ Cache validation
6. 📝 Commit final + actualizar plan
```

**Tiempo Total Estimado:** 2 horas 15 min

---

## 🎯 Criterios de Éxito

- ✅ Botón "Load Models" visible en render inicial
- ✅ Click en botón carga modelos desde backend (sin CORS)
- ✅ Cache funciona (TTL 10 min)
- ✅ Modelo actual pre-seleccionado si existe en lista
- ✅ Loading states + error handling completos
- ✅ Ollama funciona sin API key
- ✅ OpenAI funciona con API key
- ✅ Service reutilizable por otros componentes
- ✅ Código limpio con DocBlocks

---

## 📚 Referencias

- **Análisis completo:** `reports/analysis/PROVIDER-CONNECTION-ARCHITECTURE-ANALYSIS.md`
- **Config providers:** `config/llm-manager.php` (líneas 31-147)
- **Controller actual:** `src/Http/Controllers/Admin/LLMConfigurationController.php`
- **Vista Edit Tab:** `resources/views/admin/models/partials/_edit-tab.blade.php`
- **Dual-Select (futuro):** `plans/new/DUAL-SELECT-MODEL-PICKER-PROPOSAL.md`

---

**ESTADO:** ✅ Plan actualizado - Listo para Fase 1  
**PRÓXIMO PASO:** Implementar `LLMProviderService`
