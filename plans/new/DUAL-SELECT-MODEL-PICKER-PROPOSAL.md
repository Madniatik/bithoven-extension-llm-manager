# Feature Proposal: Dual-Select Model Picker (Provider + Model)

**Feature:** Modo de selección alternativo para componente Chat  
**Fecha:** 2025-12-07  
**Status:** PROPUESTA  
**Priority:** MEDIUM (Post-Fix Providers Connection)  
**Autor:** Claude (AI Agent)

---

## 📋 Contexto Actual

### Estado Actual del Componente Chat

**Ubicación:** `resources/views/components/chat/`

**Arquitectura:**
```
Chat Workspace Component
├─ Workspace.php (Component Class)
│  └─ $configurations: Collection (LLMConfiguration::where('is_active', true))
│
├─ select-models.blade.php (Form Element)
│  └─ Single SELECT: configuration_id
│     └─ Options: "{{ $config->name }} ({{ $config->provider }})"
│
└─ input-form.blade.php (Container)
   └─ @include('select-models')
```

**Selector Actual:**
```blade
<select id="quick-chat-model-selector-{{ $session->id }}" name="configuration_id">
    @foreach ($configurations as $config)
        <option value="{{ $config->id }}" 
                data-provider="{{ $config->provider }}"
                data-model="{{ $config->model }}">
            {{ $config->name }} ({{ ucfirst($config->provider) }})
        </option>
    @endforeach
</select>
```

**Ejemplo de opciones:**
```
- Ollama Local (Ollama)
- GPT-4 Turbo (Openai)
- Claude 3.5 Sonnet (Anthropic)
- OpenRouter Mix (Openrouter)
```

**Características:**
- ✅ **Pre-configurado:** Solo muestra configs activas en BD
- ✅ **Simple:** Un solo select
- ✅ **Persistente:** Guarda `configuration_id` en localStorage
- ✅ **Session-aware:** Pre-selecciona config de sesión actual
- ⚠️ **Limitado:** Requiere crear config en admin primero

---

## 🎯 Propuesta: Dual-Select Mode

### Modo Propuesto: Provider → Model (2 Selects)

**SELECT 1: Provider**
```
- Ollama (local)
- OpenAI
- Anthropic
- OpenRouter
- Custom
```

**SELECT 2: Model (dinámico según provider)**
```
// Si Ollama seleccionado:
- llama3.2
- codellama
- mistral
- gemma2

// Si OpenAI seleccionado:
- gpt-4o
- gpt-4-turbo
- gpt-3.5-turbo
- gpt-4o-mini
```

### Ventajas vs Modo Actual

| Aspecto | Modo Actual (Single Select) | Modo Propuesto (Dual Select) |
|---------|----------------------------|------------------------------|
| **Flexibilidad** | ⚠️ Solo configs pre-creadas | ✅ Cualquier provider+model |
| **Setup requerido** | ❌ Crear config en admin primero | ✅ Directo, sin config previa |
| **Descubrimiento** | ⚠️ Limitado a configs activas | ✅ Ve TODOS los modelos disponibles |
| **UX** | ✅ Simple (1 click) | ⚠️ Más pasos (2 clicks) |
| **Persistencia** | ✅ Via configuration_id | ⚠️ Requiere guardar provider+model |
| **Validación** | ✅ Solo configs válidas | ⚠️ User puede elegir combo inválido |

---

## 🏗️ Diseño de Implementación

### Opción A: Prop de Configuración (Recomendado ✅)

**Modificar:** `Workspace.php`

```php
class Workspace extends Component
{
    // ... propiedades existentes ...
    
    /**
     * Model selection mode: 'single' (default) or 'dual'
     *
     * @var string
     */
    public string $modelSelectionMode;
    
    /**
     * Show only enabled configurations or all providers
     *
     * @var bool
     */
    public bool $showAllProviders;

    public function __construct(
        // ... parámetros existentes ...
        string $modelSelectionMode = 'single',
        bool $showAllProviders = false
    ) {
        // ... código existente ...
        $this->modelSelectionMode = $modelSelectionMode;
        $this->showAllProviders = $showAllProviders;
    }
}
```

**Uso:**
```blade
{{-- Modo actual (default) --}}
<x-llm-chat-workspace :session="$session" />

{{-- Modo dual-select --}}
<x-llm-chat-workspace 
    :session="$session" 
    model-selection-mode="dual"
    show-all-providers="true" />
```

### Nueva Vista: `select-models-dual.blade.php`

**Crear:** `resources/views/components/chat/partials/form-elements/select-models-dual.blade.php`

```blade
{{-- Dual Model Selection (Provider + Model) --}}
<div class="d-flex gap-2 flex-grow-1">
    {{-- Provider Select --}}
    <div class="flex-grow-1">
        <select id="provider-selector-{{ $session?->id ?? 'default' }}" 
                name="provider"
                class="form-select form-select-sm form-select-solid" 
                data-control="select2"
                onchange="loadProviderModels_{{ $session?->id ?? 'default' }}(this.value)">
            <option value="">Select Provider...</option>
            @foreach (config('llm-manager.providers') as $key => $config)
                @if($showAllProviders || LLMConfiguration::where('provider', $key)->where('is_active', true)->exists())
                    <option value="{{ $key }}" 
                            data-requires-api-key="{{ $config['requires_api_key'] ? 'true' : 'false' }}">
                        {{ ucfirst($key) }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>
    
    {{-- Model Select (populated dynamically) --}}
    <div class="flex-grow-1">
        <select id="model-selector-{{ $session?->id ?? 'default' }}" 
                name="model"
                class="form-select form-select-sm form-select-solid" 
                data-control="select2"
                disabled>
            <option value="">Select Model...</option>
        </select>
    </div>
</div>

@push('scripts')
<script>
    // Load models for selected provider
    function loadProviderModels_{{ $session?->id ?? 'default' }}(provider) {
        const modelSelect = document.getElementById('model-selector-{{ $session?->id ?? 'default' }}');
        const sessionId = '{{ $session?->id ?? 'default' }}';
        
        if (!provider) {
            modelSelect.disabled = true;
            modelSelect.innerHTML = '<option value="">Select Model...</option>';
            return;
        }
        
        // Show loading state
        modelSelect.disabled = false;
        modelSelect.innerHTML = '<option value="">Loading models...</option>';
        
        // Get provider config to determine if we need API key
        const providerConfig = @json(config('llm-manager.providers'));
        const config = providerConfig[provider] || {};
        
        // Try to get API key from existing configuration
        let apiKey = null;
        const existingConfig = @json($configurations->where('provider', $provider)->first());
        if (existingConfig) {
            apiKey = existingConfig.api_key;
        }
        
        // Call backend to load models
        fetch("{{ route('admin.llm.configurations.load-models') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                provider: provider,
                api_key: apiKey
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.models || data.models.length === 0) {
                // Fallback to hardcoded models
                const hardcodedModels = config.available_models || [];
                
                if (hardcodedModels.length > 0) {
                    populateModels(hardcodedModels.map(m => ({id: m, name: m})));
                } else {
                    modelSelect.innerHTML = '<option value="">No models available</option>';
                    
                    if (config.requires_api_key && !apiKey) {
                        modelSelect.innerHTML = '<option value="">API key required (configure in admin)</option>';
                    }
                }
            } else {
                populateModels(data.models);
            }
        })
        .catch(error => {
            console.error('Error loading models:', error);
            modelSelect.innerHTML = '<option value="">Error loading models</option>';
        });
        
        function populateModels(models) {
            modelSelect.innerHTML = '<option value="">Select Model...</option>';
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model.id;
                option.textContent = model.name;
                modelSelect.appendChild(option);
            });
        }
    }
</script>
@endpush
```

### Modificar: `input-form.blade.php`

```blade
{{-- Model Selection (flex-grow para ocupar espacio disponible) --}}
<div class="flex-grow-1">
    @if($modelSelectionMode === 'dual')
        @include('llm-manager::components.chat.partials.form-elements.select-models-dual')
    @else
        @include('llm-manager::components.chat.partials.form-elements.select-models')
    @endif
</div>
```

### Modificar: `event-handlers.blade.php` (Send Message)

**Actual:**
```javascript
const modelSelector = document.getElementById(`quick-chat-model-selector-${sessionId}`);
const configurationId = modelSelector.value;

// EventSource params
{
    configuration_id: configurationId,
    // ...
}
```

**Nuevo (detectar modo):**
```javascript
// Detect selection mode
const modelSelector = document.getElementById(`quick-chat-model-selector-${sessionId}`);
const providerSelector = document.getElementById(`provider-selector-${sessionId}`);
const modelSelectorDual = document.getElementById(`model-selector-${sessionId}`);

let params = {};

if (providerSelector && modelSelectorDual) {
    // Dual mode: Use provider + model
    params = {
        provider: providerSelector.value,
        model: modelSelectorDual.value,
        // No configuration_id
    };
} else {
    // Single mode: Use configuration_id
    params = {
        configuration_id: modelSelector.value,
    };
}

// EventSource
const url = `{{ route('admin.llm.quick-chat.stream') }}?` + new URLSearchParams(params);
```

### Backend: Modificar `LLMQuickChatController::stream()`

**Actual:**
```php
$validated = $request->validate([
    'configuration_id' => 'required|exists:llm_manager_configurations,id',
    // ...
]);

$config = LLMConfiguration::findOrFail($validated['configuration_id']);
```

**Nuevo (soportar ambos modos):**
```php
// Validate either configuration_id OR provider+model
$validated = $request->validate([
    'configuration_id' => 'nullable|exists:llm_manager_configurations,id',
    'provider' => 'nullable|required_without:configuration_id|string',
    'model' => 'nullable|required_with:provider|string',
    // ...
]);

if (isset($validated['configuration_id'])) {
    // Single mode: Use existing configuration
    $config = LLMConfiguration::findOrFail($validated['configuration_id']);
} else {
    // Dual mode: Find or create temporary configuration
    $config = LLMConfiguration::firstOrCreate(
        [
            'provider' => $validated['provider'],
            'model' => $validated['model'],
            'user_id' => auth()->id(),
        ],
        [
            'name' => ucfirst($validated['provider']) . ' - ' . $validated['model'],
            'slug' => \Str::slug($validated['provider'] . '-' . $validated['model']),
            'is_active' => true,
            // Get API key from first active config of same provider
            'api_key' => LLMConfiguration::where('provider', $validated['provider'])
                ->where('is_active', true)
                ->value('api_key'),
        ]
    );
}
```

---

## 🔄 Flujo de Usuario (Dual Mode)

### Escenario 1: Usuario con configs pre-creadas

1. **Abre Quick Chat**
2. **Ve selector dual:** Provider (vacío) + Model (deshabilitado)
3. **Selecciona Provider:** "OpenAI"
4. **Models se cargan automáticamente** (via `loadModels()` Service)
5. **Selecciona Model:** "gpt-4o"
6. **Envía mensaje**
7. **Backend:** Busca config existente con `provider=openai` + `model=gpt-4o`
   - ✅ Si existe: Usa esa config
   - ⚠️ Si NO existe: Crea config temporal (reutiliza API key de otra config OpenAI)

### Escenario 2: Usuario sin configs pre-creadas (Ollama)

1. **Abre Quick Chat**
2. **Selecciona Provider:** "Ollama"
3. **Models se cargan** desde `http://localhost:11434/api/tags`
4. **Ve lista completa:** llama3.2, codellama, mistral, etc.
5. **Selecciona Model:** "llama3.2"
6. **Envía mensaje**
7. **Backend:** Crea config temporal automáticamente (Ollama no requiere API key)

### Escenario 3: Provider requiere API key NO configurada

1. **Selecciona Provider:** "OpenAI"
2. **No hay configs OpenAI activas** (sin API key)
3. **Intenta cargar models:** Falla (401 Unauthorized)
4. **Fallback:** Muestra modelos hardcoded de config
5. **Usuario selecciona model**
6. **Envía mensaje:** Falla en backend (no hay API key)
7. **Error message:** "Configure OpenAI API key in admin panel first"

---

## ⚠️ Consideraciones & Limitaciones

### 1. API Keys

**Problema:** Dual mode necesita API key para cargar modelos dinámicamente

**Soluciones:**

**A) Reutilizar API key existente** (Recomendado ✅)
```php
'api_key' => LLMConfiguration::where('provider', $provider)
    ->where('is_active', true)
    ->value('api_key')
```

**B) Pedir API key inline** (UX compleja ❌)
```blade
<input type="password" id="api-key-input" placeholder="API Key (optional)">
```

**C) Fallback a hardcoded models** (Limitado ⚠️)
```php
if (!$apiKey) {
    return config("llm-manager.providers.{$provider}.available_models");
}
```

### 2. Persistencia de Selección

**Problema:** ¿Cómo guardar provider+model en localStorage?

**Opción 1: Guardar provider+model separados**
```javascript
localStorage.setItem('llm_chat_provider_' + sessionId, provider);
localStorage.setItem('llm_chat_model_' + sessionId, model);
```

**Opción 2: Crear configuration_id temporal**
```php
// Backend crea config temporal y devuelve ID
$tempConfig = LLMConfiguration::create([...]);
return ['configuration_id' => $tempConfig->id];

// Frontend guarda ID como siempre
localStorage.setItem('llm_chat_config_' + sessionId, configId);
```

### 3. Validación de Combos

**Problema:** Usuario puede elegir provider+model inválido

**Ejemplo inválido:**
- Provider: "Anthropic"
- Model: "llama3.2" (modelo de Ollama)

**Solución:**
```javascript
// Validar que model pertenece a provider
const config = providerConfig[provider];
const validModels = loadedModels; // De loadModels() response

if (!validModels.find(m => m.id === selectedModel)) {
    alert('Invalid model for selected provider');
    return;
}
```

### 4. Performance

**Problema:** Cada cambio de provider → request HTTP

**Optimizaciones:**

**A) Cache en frontend**
```javascript
const modelsCache = {};

function loadProviderModels(provider) {
    if (modelsCache[provider]) {
        populateModels(modelsCache[provider]);
        return;
    }
    
    fetch(...).then(data => {
        modelsCache[provider] = data.models;
        populateModels(data.models);
    });
}
```

**B) Pre-load all providers** (al cargar página)
```javascript
// On page load
['ollama', 'openai', 'anthropic'].forEach(provider => {
    loadProviderModels(provider); // Cache all
});
```

**C) Backend cache** (ya implementado en Service)
```php
// LLMProviderService::loadModels() ya cachea 10 min
Cache::remember("llm_models_{$provider}", 600, fn() => ...);
```

---

## 📝 Implementación Step-by-Step

### Fase 1: Componente Dual Select (2 horas)

1. ✅ Crear `select-models-dual.blade.php`
2. ✅ Modificar `Workspace.php` → prop `$modelSelectionMode`
3. ✅ Modificar `input-form.blade.php` → condición `@if($modelSelectionMode)`
4. ✅ Agregar JS `loadProviderModels()` con cache

### Fase 2: Backend Support (1.5 horas)

5. ✅ Modificar `LLMQuickChatController::stream()`
   - Validar `provider+model` OR `configuration_id`
   - Crear/buscar config automáticamente
6. ✅ Agregar método `findOrCreateConfig()` helper
7. ✅ Agregar validación de combos válidos

### Fase 3: Persistencia & UX (1 hora)

8. ✅ Implementar localStorage dual mode
9. ✅ Restaurar selección al reload
10. ✅ Agregar loading states & error handling
11. ✅ Agregar tooltips/hints para API keys

### Fase 4: Testing & Polish (1 hora)

12. ✅ Probar ambos modos (single/dual)
13. ✅ Probar con Ollama (sin API key)
14. ✅ Probar con OpenAI (con API key)
15. ✅ Edge cases (sin configs, offline, etc.)

**Tiempo Total:** ~5.5 horas

---

## ✅ Respuesta a tu Pregunta

### ¿Es posible hacer esto después de terminar el plan actual?

**Respuesta:** **SÍ, totalmente viable** ✅

**Dependencias del Plan Actual:**

1. ✅ **`LLMProviderService::loadModels()`** - CRÍTICO
   - Dual mode necesita este método para cargar modelos dinámicamente
   - Sin esto, solo podríamos usar modelos hardcoded

2. ✅ **Route `configurations.load-models`** - CRÍTICO
   - Frontend llama a este endpoint al cambiar provider
   - Sin esto, no hay forma de obtener modelos desde backend

3. ⚠️ **Cache system** - OPCIONAL pero recomendado
   - Mejora performance del dual mode
   - Evita requests repetidos al cambiar providers

**Orden Recomendado:**

```
1. ✅ Fix Providers Connection (Plan actual)
   ├─ LLMProviderService::loadModels()
   ├─ Route configurations.load-models
   └─ Cache implementation
   
2. ✅ Dual-Select Mode (Este feature)
   ├─ Reutiliza loadModels() del paso 1
   ├─ Reutiliza cache del paso 1
   └─ Añade UI dual + backend support
```

**Beneficio de hacerlo después:**
- ✅ Service `loadModels()` ya probado y funcional
- ✅ Cache system ya configurado
- ✅ No duplicar lógica de carga de modelos
- ✅ Testing más fácil (componentes aislados)

---

## 🎨 Mockup Visual

### Modo Single (Actual)
```
┌─────────────────────────────────────────────┐
│  Model Selection                            │
│  ┌───────────────────────────────────────┐  │
│  │ Ollama Local (Ollama)              ▼ │  │
│  └───────────────────────────────────────┘  │
│                                     [Send]  │
└─────────────────────────────────────────────┘
```

### Modo Dual (Propuesto)
```
┌─────────────────────────────────────────────┐
│  Provider & Model Selection                 │
│  ┌──────────────────┐ ┌──────────────────┐  │
│  │ OpenAI        ▼ │ │ gpt-4o        ▼ │  │
│  └──────────────────┘ └──────────────────┘  │
│                                     [Send]  │
└─────────────────────────────────────────────┘
```

### Con Estado Loading
```
┌─────────────────────────────────────────────┐
│  Provider & Model Selection                 │
│  ┌──────────────────┐ ┌──────────────────┐  │
│  │ Anthropic     ▼ │ │ ⏳ Loading... │  │
│  └──────────────────┘ └──────────────────┘  │
│                                     [Send]  │
└─────────────────────────────────────────────┘
```

---

## 🚀 Próximos Pasos

1. **Completar Plan Actual** (Fix Providers Connection)
2. **Revisar este documento** para aprobar/modificar propuesta
3. **Implementar Dual-Select Mode** (si aprobado)
4. **Testing cross-browser** (Select2, Alpine, etc.)
5. **Documentation** para usuarios

---

## 📚 Referencias

- **Service Layer:** `src/Services/LLMProviderService.php` (por crear)
- **Component:** `src/View/Components/Chat/Workspace.php`
- **View Actual:** `resources/views/components/chat/partials/form-elements/select-models.blade.php`
- **Event Handlers:** `resources/views/components/chat/partials/scripts/event-handlers.blade.php`

---

**ESTADO:** ✅ Propuesta completa - Viable post-Fix  
**DEPENDENCIAS:** LLMProviderService::loadModels() (del plan actual)  
**TIEMPO ESTIMADO:** ~5.5 horas después del plan actual
