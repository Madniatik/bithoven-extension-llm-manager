# Plan: Fix Providers Connection in Admin Models

**Status:** NEW  
**Priority:** HIGH  
**Estimated Time:** 2-3 hours  
**Created:** 2025-12-07  
**Assignee:** Claude (AI Agent)

---

## 📋 Resumen del Problema

En la sección de administración de modelos LLM (`/admin/llm/models/{model}`), específicamente en el **Edit Tab** (`_edit-tab.blade.php`), los proveedores LLM no se están conectando correctamente y el botón "Load Models" no funciona como esperado.

### Síntomas Identificados:

1. **Botón "Load Models" no se muestra:** Aunque existe en el código Blade (`loadDynamicModels()`), no aparece en el renderizado HTML inicial
2. **No hay conexión con proveedores LLM:** La función `loadDynamicModels()` intenta hacer `fetch()` directo a endpoints externos, lo cual falla por CORS y falta de autenticación
3. **Arquitectura inconsistente:** El componente Chat (`chat-workspace.blade.php`) usa una arquitectura diferente, cargando modelos desde la BD (`$configurations`) en lugar de endpoints externos

### Comparación Chat vs Admin Models:

**✅ Chat (Funciona):**
- Carga modelos desde BD (`LLMConfiguration::where('is_active', true)->get()`)
- Select pre-poblado con modelos configurados
- No hace llamadas HTTP directas a proveedores

**❌ Admin Models (No funciona):**
- Intenta cargar modelos dinámicamente desde endpoints externos
- Hace `fetch()` directo a `https://api.openai.com/v1/models` (CORS fail)
- No usa modelos ya configurados en BD

---

## 🎯 Objetivos

1. **Mostrar correctamente el botón "Load Models"** en el estado inicial del formulario
2. **Implementar carga dinámica de modelos** vía backend (proxy) en lugar de frontend directo
3. **Reutilizar arquitectura de test de conexión** existente en `LLMConfigurationController::testConnection()`
4. **Añadir endpoint dedicado** para cargar modelos de proveedores
5. **Mejorar UX** con estados de carga, errores y modelos pre-seleccionados

---

## 📐 Arquitectura Propuesta

### 1. Backend: Nuevo Endpoint `loadModels()`

**Ubicación:** `LLMConfigurationController.php`

```php
public function loadModels(Request $request)
{
    $validated = $request->validate([
        'provider' => 'required|string',
        'api_endpoint' => 'nullable|string',
        'api_key' => 'nullable|string',
    ]);
    
    try {
        $provider = $validated['provider'];
        $providerConfig = config("llm-manager.providers.{$provider}");
        
        if (!$providerConfig || !$providerConfig['supports_dynamic_models']) {
            return response()->json([
                'success' => false,
                'message' => 'Provider does not support dynamic model loading',
                'models' => []
            ]);
        }
        
        // Build endpoint
        $baseEndpoint = $validated['api_endpoint'] ?? $providerConfig['endpoint'];
        $modelsPath = $providerConfig['endpoints']['models'];
        $fullUrl = rtrim($baseEndpoint, '/') . $modelsPath;
        
        // Prepare headers
        $apiKey = $validated['api_key'] ?? '';
        $headers = ['Accept: application/json'];
        
        if ($apiKey && $providerConfig['requires_api_key']) {
            $headers[] = "Authorization: Bearer {$apiKey}";
        }
        
        // Make request via cURL
        $ch = curl_init($fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return response()->json([
                'success' => false,
                'message' => "Connection error: {$error}",
                'models' => []
            ]);
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            return response()->json([
                'success' => false,
                'message' => "Failed to load models (HTTP {$httpCode})",
                'models' => []
            ]);
        }
        
        // Parse response
        $data = json_decode($response, true);
        $models = [];
        
        // Handle different response formats
        if (isset($data['data']) && is_array($data['data'])) {
            // OpenAI/OpenRouter format: { data: [ {id: "..."}, ... ] }
            foreach ($data['data'] as $model) {
                $models[] = [
                    'id' => $model['id'] ?? $model['name'] ?? $model,
                    'name' => $model['id'] ?? $model['name'] ?? $model
                ];
            }
        } elseif (isset($data['models']) && is_array($data['models'])) {
            // Ollama format: { models: [ {name: "..."}, ... ] }
            foreach ($data['models'] as $model) {
                $models[] = [
                    'id' => $model['name'] ?? $model['id'] ?? $model,
                    'name' => $model['name'] ?? $model['id'] ?? $model
                ];
            }
        } elseif (is_array($data)) {
            // Plain array format
            foreach ($data as $model) {
                if (is_string($model)) {
                    $models[] = ['id' => $model, 'name' => $model];
                } else {
                    $models[] = [
                        'id' => $model['id'] ?? $model['name'] ?? 'unknown',
                        'name' => $model['name'] ?? $model['id'] ?? 'unknown'
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => count($models) . ' models loaded',
            'models' => $models
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Error: {$e->getMessage()}",
            'models' => []
        ]);
    }
}
```

### 2. Routes: Nuevo Endpoint

**Ubicación:** `routes/web.php`

```php
Route::post('configurations/load-models', [LLMConfigurationController::class, 'loadModels'])
    ->name('configurations.load-models');
```

### 3. Frontend: Actualizar `loadDynamicModels()`

**Ubicación:** `_edit-tab.blade.php`

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
    
    // Guardar el modelo actual para pre-seleccionarlo después
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
    
    // Call backend proxy endpoint
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
            api_key: apiKey || null
        })
    })
    .then(response => response.json())
    .then(data => {
        // Restore button
        if (loadButton) {
            loadButton.disabled = false;
            loadButton.innerHTML = 'Load Models';
        }
        
        if (!data.success) {
            hintDiv.innerHTML = `<span class="text-danger">${data.message}</span>`;
            return;
        }
        
        const models = data.models || [];
        
        if (models.length === 0) {
            hintDiv.innerHTML = '<span class="text-warning">No models found</span>';
            return;
        }
        
        // Populate select
        selectField.innerHTML = '<option value="">Select a model...</option>';
        
        let modelFound = false;
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name;
            
            // Pre-select current model if exists
            if (model.id === currentModel) {
                option.selected = true;
                modelFound = true;
            }
            
            selectField.appendChild(option);
        });
        
        // Switch to select
        selectField.style.display = '';
        inputField.style.display = 'none';
        selectField.required = true;
        inputField.required = false;
        
        // Update hint
        if (modelFound) {
            hintDiv.innerHTML = `${models.length} models loaded <span class="badge badge-success ms-2">Current model found</span>`;
        } else if (currentModel) {
            hintDiv.innerHTML = `${models.length} models loaded <span class="badge badge-warning ms-2">Current model "${currentModel}" not in list</span>`;
        } else {
            hintDiv.textContent = `${models.length} models loaded`;
        }
    })
    .catch(error => {
        console.error('Error loading models:', error);
        if (loadButton) {
            loadButton.disabled = false;
            loadButton.innerHTML = 'Load Models';
        }
        hintDiv.innerHTML = '<span class="text-danger">Failed to load models. Check API key and endpoint.</span>';
    });
}
```

### 4. Frontend: Fix Initial State Rendering

**Problema:** El botón "Load Models" no se muestra en el estado inicial porque la condición Blade solo muestra texto, no HTML con botón.

**Solución:** Actualizar la parte HTML del formulario:

```blade
<div class="form-text" id="model-hint">
    @if($providerConfig['supports_dynamic_models'] ?? false)
        <span class="me-2">Click to load available models from provider</span>
        <button type="button" id="load-models-btn" class="btn btn-sm btn-light-primary" onclick="loadDynamicModels()">
            Load Models
        </button>
    @else
        Enter the model identifier
    @endif
</div>
```

---

## 🔧 Pasos de Implementación

### Fase 1: Backend Endpoint (30 min)
1. ✅ Crear método `loadModels()` en `LLMConfigurationController.php`
2. ✅ Agregar ruta en `routes/web.php`
3. ✅ Probar endpoint con Postman/Thunder Client

### Fase 2: Frontend Update (45 min)
4. ✅ Actualizar HTML en `_edit-tab.blade.php` para mostrar botón
5. ✅ Reescribir función `loadDynamicModels()` para usar backend proxy
6. ✅ Agregar ID al botón (`load-models-btn`) para estados de carga
7. ✅ Mejorar feedback visual (spinners, badges)

### Fase 3: Testing (30 min)
8. ✅ Probar con Ollama (local, no API key)
9. ✅ Probar con OpenAI (requiere API key)
10. ✅ Probar con OpenRouter (requiere API key)
11. ✅ Verificar edge cases (sin API key, endpoint inválido, provider sin dynamic models)

### Fase 4: Polish & Documentation (15 min)
12. ✅ Agregar comentarios en código
13. ✅ Actualizar este plan con resultados
14. ✅ Crear commit descriptivo

---

## 🧪 Casos de Prueba

### Test 1: Ollama (Local)
- **Provider:** ollama
- **Endpoint:** `http://localhost:11434`
- **API Key:** N/A
- **Esperado:** Lista de modelos desde `/api/tags`

### Test 2: OpenAI
- **Provider:** openai
- **Endpoint:** `https://api.openai.com/v1`
- **API Key:** Required
- **Esperado:** Lista de modelos desde `/models`

### Test 3: OpenRouter
- **Provider:** openrouter
- **Endpoint:** `https://openrouter.ai/api/v1`
- **API Key:** Required
- **Esperado:** Lista de modelos desde `/models`

### Test 4: Anthropic (No Dynamic)
- **Provider:** anthropic
- **Endpoint:** N/A
- **Esperado:** Select con modelos hardcoded, sin botón "Load Models"

### Test 5: Sin API Key
- **Provider:** openai
- **Endpoint:** `https://api.openai.com/v1`
- **API Key:** Empty
- **Esperado:** Error 401/403, mensaje claro "API Key required"

---

## 📝 Notas Técnicas

### CORS y Seguridad
- **Problema:** Frontend directo a APIs externas = CORS fail
- **Solución:** Backend proxy (Laravel cURL) = Sin CORS, headers correctos
- **Ventaja adicional:** API keys no expuestas en frontend

### Formatos de Respuesta Soportados

**OpenAI/OpenRouter:**
```json
{
  "data": [
    {"id": "gpt-4", "object": "model", ...},
    {"id": "gpt-3.5-turbo", "object": "model", ...}
  ]
}
```

**Ollama:**
```json
{
  "models": [
    {"name": "llama3.2", "size": 123456, ...},
    {"name": "codellama", "size": 234567, ...}
  ]
}
```

**Array plano:**
```json
["model-1", "model-2", "model-3"]
```

### Estado del Selector de Modelos

**Estados posibles:**
1. **Hardcoded models (Anthropic):** Select pre-poblado, sin botón Load
2. **Dynamic models (OpenAI, Ollama):** Input + botón "Load Models"
3. **Loading state:** Spinner en botón + hint
4. **Loaded state:** Select poblado, modelo actual pre-seleccionado
5. **Error state:** Mensaje de error en hint

---

## 🎨 Mejoras UX Propuestas

### 1. Pre-selección Inteligente
Si el modelo actual (`{{ $model->model }}`) existe en la lista cargada, pre-seleccionarlo automáticamente.

### 2. Badge de Estado
- ✅ **Verde:** "Current model found"
- ⚠️ **Amarillo:** "Current model not in list" (permite editar)
- ❌ **Rojo:** "Failed to load models"

### 3. Botón con Estados
```html
<!-- Initial -->
<button id="load-models-btn">Load Models</button>

<!-- Loading -->
<button id="load-models-btn" disabled>
    <span class="spinner-border spinner-border-sm"></span> Loading...
</button>

<!-- Success -->
<button id="load-models-btn">Reload Models</button>
```

---

## ✅ Checklist de Implementación

- [ ] Backend: `loadModels()` creado
- [ ] Route: `configurations.load-models` agregada
- [ ] Frontend: Botón visible en HTML
- [ ] Frontend: `loadDynamicModels()` actualizado
- [ ] Testing: Ollama probado
- [ ] Testing: OpenAI probado
- [ ] Testing: OpenRouter probado
- [ ] Testing: Edge cases probados
- [ ] Commit: Código commiteado
- [ ] Documentation: Plan actualizado

---

## 🚀 Próximos Pasos (Futuro)

1. **Cache de modelos:** Cachear lista de modelos por 10 minutos (usar `cache_ttl` de config)
2. **Auto-refresh:** Botón "Reload Models" después de primer load
3. **Favoritos:** Marcar modelos favoritos en select (estrella)
4. **Búsqueda:** Search box en select para filtrar modelos largos
5. **Preview:** Tooltip con detalles del modelo (context window, pricing)

---

## 📚 Referencias

- **Config:** `config/llm-manager.php` (líneas 31-147)
- **Controller:** `LLMConfigurationController.php` (línea 54: `testConnection()`)
- **View:** `_edit-tab.blade.php` (línea 323: `loadDynamicModels()`)
- **Chat Component:** `select-models.blade.php` (enfoque alternativo)

---

**ESTADO:** Listo para implementación  
**APROBACIÓN:** Pendiente usuario
