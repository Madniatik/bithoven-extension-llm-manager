# Análisis de Bugs - Quick Chat

## 🔴 Bug 1: "Connection Lost" al finalizar stream

**Síntoma:** Todos los streams terminan con mensaje "Streaming connection lost" (toastr error).

**Causa Raíz:**
El evento `eventSource.onerror` se dispara SIEMPRE al finalizar el stream, incluso cuando termina correctamente con `type: 'done'`.

**Ubicación:** `event-handlers.blade.php` línea 837

```javascript
eventSource.onerror = function(error) {
    // ...
    toastr.error('Streaming connection lost'); // ❌ SIEMPRE se ejecuta
};
```

**Problema:**
- SSE (Server-Sent Events) cierra la conexión tras el último evento
- El navegador interpreta esto como "error" y dispara `onerror`
- Pero NO es un error real - el stream completó exitosamente

**Solución Propuesta:**
Agregar flag `streamCompleted` que se setea en `type: 'done'`:

```javascript
let streamCompleted = false;

// En 'done' event
} else if (data.type === 'done') {
    streamCompleted = true; // ✅ Marcar como completado
    // ... resto del código
}

// En onerror
eventSource.onerror = function(error) {
    if (streamCompleted) {
        // Stream terminó OK, ignorar error cosmético
        eventSource?.close();
        return;
    }
    
    // Solo mostrar error si NO completó
    toastr.error('Streaming connection lost');
    // ...
};
```

**Prioridad:** MEDIA (cosmético, no afecta funcionalidad)

---

## 🔴 Bug 2: OpenRouter metadata null (native_tokens, generation_id)

**Síntoma:**
- `input_tokens` / `output_tokens` = 0
- `native_tokens_prompt` / `native_tokens_completion` = null
- `system_fingerprint` = null

**Causa Raíz 1: Nombres de propiedades incorrectos**

OpenAI SDK usa **camelCase**, pero OpenRouter devuelve **snake_case**:

```php
// ❌ INCORRECTO (OpenRouterProvider.php línea 108-109)
'native_tokens_prompt' => $lastResponse->usage->native_tokens_prompt ?? null,
'native_tokens_completion' => $lastResponse->usage->native_tokens_completion ?? null,

// ✅ CORRECTO (debe ser camelCase para el SDK)
'native_tokens_prompt' => $lastResponse->usage->nativeTokensPrompt ?? null,
'native_tokens_completion' => $lastResponse->usage->nativeTokensCompletion ?? null,
```

**Causa Raíz 2: input_tokens/output_tokens siempre 0**

En Controller línea 235-236:
```php
'output_tokens' => $metrics['usage']['completion_tokens'] ?? 0, // ✅ Bien
'input_tokens' => $metrics['usage']['prompt_tokens'] ?? 0,      // ✅ Bien
```

Pero en Provider solo devolvemos `prompt_tokens`, `completion_tokens` (no input/output):

```php
// OpenRouterProvider.php línea 105-107
'usage' => [
    'prompt_tokens' => $lastResponse->usage->promptTokens ?? 0,
    'completion_tokens' => $lastResponse->usage->completionTokens ?? 0,
    'total_tokens' => $lastResponse->usage->totalTokens ?? 0,
    // ❌ FALTA: input_tokens, output_tokens (son aliases)
],
```

**Solución:**

```php
// OpenRouterProvider.php
'usage' => [
    'prompt_tokens' => $lastResponse->usage->promptTokens ?? 0,
    'completion_tokens' => $lastResponse->usage->completionTokens ?? 0,
    'total_tokens' => $lastResponse->usage->totalTokens ?? 0,
    // Aliases para compatibilidad
    'input_tokens' => $lastResponse->usage->promptTokens ?? 0,
    'output_tokens' => $lastResponse->usage->completionTokens ?? 0,
    // Native tokens (camelCase para OpenAI SDK)
    'native_tokens_prompt' => $lastResponse->usage->nativeTokensPrompt ?? null,
    'native_tokens_completion' => $lastResponse->usage->nativeTokensCompletion ?? null,
],
// ...
'system_fingerprint' => $lastResponse->systemFingerprint ?? null, // camelCase
```

**Prioridad:** ALTA (datos no se guardan correctamente)

---

## 🔴 Bug 3: updated_at = null en metadata

**Síntoma:** Campo `updated_at` en metadata es null.

**Causa Raíz:**
NO estamos guardando `updated_at` en metadata en ningún lado.

**Análisis:**
- Tabla tiene `created_at` (timestamp)
- Metadata tiene `created_at` del Provider (línea 117 OpenRouterProvider)
- Pero NO hay `updated_at` en ningún lugar

**Pregunta:** ¿Qué campo debe tener `updated_at`?
1. ¿Metadata? (timestamp de OpenRouter)
2. ¿Columna de tabla? (Laravel automático)

**Solución Propuesta:**

Si quieres tracking de updates, agregar columna `updated_at` a tabla:

```php
// Migration
$table->timestamp('updated_at')->nullable();

// Model - Laravel auto-maneja created_at/updated_at si existe
public $timestamps = true; // Cambiar de false a true
```

**Prioridad:** BAJA (solo si necesitas tracking de updates)

---

## 🔴 Bug 4: Nuevo bubble no muestra modelo en título

**Síntoma:** Tras streaming, el bubble solo muestra "AI Assistant" sin badges de provider/modelo.

**Causa Raíz:**
`appendMessage()` NO incluye badges de modelo en el HTML (línea 67-69):

```javascript
// event-handlers.blade.php línea 67-69
<span class="text-gray-600 fw-semibold fs-8">
    ${role === 'user' ? '{{ auth()->user()->name ?? "User" }}' : 'Assistant'}
</span>
// ❌ FALTAN badges aquí
<span class="text-gray-500 fw-semibold fs-8 ms-2">${timestamp}</span>
```

**Comparar con chat-messages.blade.php (históricos) línea 23-27:**

```blade
@if ($message->role === 'assistant' && $message->llmConfiguration)
    <span class="badge badge-light-primary badge-sm ms-2">
        {{ ucfirst($message->llmConfiguration->provider) }}
    </span>
    <span class="badge badge-light-info badge-sm">
        {{ $message->llmConfiguration->model }}
    </span>
@endif
```

**Solución:**

Agregar badges en `appendMessage()` usando config del selector:

```javascript
// Obtener config seleccionada
const configSelect = document.getElementById('llm-configuration-select-{{ $session?->id ?? "default" }}');
const selectedOption = configSelect?.options[configSelect.selectedIndex];
const provider = selectedOption?.dataset.provider || 'Unknown';
const model = selectedOption?.dataset.model || 'Unknown';

// En el HTML del bubble assistant
${role === 'assistant' ? `
    <span class="text-gray-600 fw-semibold fs-8">Assistant</span>
    <span class="badge badge-light-primary badge-sm ms-2">${provider}</span>
    <span class="badge badge-light-info badge-sm">${model}</span>
` : `
    <span class="text-gray-600 fw-semibold fs-8">{{ auth()->user()->name ?? "User" }}</span>
`}
```

**Prioridad:** MEDIA (UX inconsistency)

---

## 🔴 Bug 5: "Thinking" podría mostrar modelo

**Síntoma:** Indicator solo dice "Thinking..." sin info del modelo.

**Mejora Sugerida:**

```blade
{{-- chat-messages.blade.php línea 166 --}}
<span class="text-muted fw-semibold fs-7">
    <span id="thinking-model-info-{{ $session?->id ?? 'default' }}">Thinking</span>
    <span class="streaming-cursor">|</span>
</span>
```

```javascript
// event-handlers.blade.php - Al iniciar stream
const configSelect = document.getElementById('llm-configuration-select-{{ $session?->id ?? "default" }}');
const selectedOption = configSelect?.options[configSelect.selectedIndex];
const provider = selectedOption?.dataset.provider || 'Unknown';
const model = selectedOption?.dataset.model || 'Unknown';

const thinkingModelInfo = document.getElementById('thinking-model-info-{{ $session?->id ?? "default" }}');
if (thinkingModelInfo) {
    thinkingModelInfo.textContent = `${provider} / ${model} thinking`;
}
```

**Prioridad:** BAJA (nice to have)

---

## 📋 Resumen de Prioridades

### ALTA (Fix Inmediato)
- ✅ Bug 2: OpenRouter metadata (camelCase + aliases)

### MEDIA (Fix Pronto)
- ✅ Bug 1: Connection lost falso positivo
- ✅ Bug 4: Badges de modelo en nuevo bubble

### BAJA (Opcional)
- ⚪ Bug 3: updated_at (solo si necesario)
- ⚪ Bug 5: Thinking con modelo (UX enhancement)

---

## 🔧 Plan de Implementación

### Paso 1: Fix OpenRouter metadata (Bug 2)
```php
// src/Services/Providers/OpenRouterProvider.php
'usage' => [
    'prompt_tokens' => $lastResponse->usage->promptTokens ?? 0,
    'completion_tokens' => $lastResponse->usage->completionTokens ?? 0,
    'total_tokens' => $lastResponse->usage->totalTokens ?? 0,
    'input_tokens' => $lastResponse->usage->promptTokens ?? 0,
    'output_tokens' => $lastResponse->usage->completionTokens ?? 0,
    'native_tokens_prompt' => $lastResponse->usage->nativeTokensPrompt ?? null,
    'native_tokens_completion' => $lastResponse->usage->nativeTokensCompletion ?? null,
],
'system_fingerprint' => $lastResponse->systemFingerprint ?? null,
```

### Paso 2: Fix connection lost (Bug 1)
```javascript
// resources/views/.../event-handlers.blade.php
let streamCompleted = false;

// En done event
streamCompleted = true;

// En onerror
if (streamCompleted) {
    eventSource?.close();
    return;
}
```

### Paso 3: Fix badges en nuevo bubble (Bug 4)
```javascript
// Obtener provider/model de selector
// Agregar badges en appendMessage() HTML
```

### Paso 4 (Opcional): Thinking con modelo (Bug 5)
```javascript
// Actualizar texto de thinking con provider/model
```

---

## ✅ Confirmación Requerida

¿Procedo con:
1. ✅ Fix Bug 2 (OpenRouter metadata) - ALTA prioridad
2. ✅ Fix Bug 1 (Connection lost) - MEDIA prioridad
3. ✅ Fix Bug 4 (Badges modelo) - MEDIA prioridad
4. ⚪ Fix Bug 5 (Thinking texto) - BAJA prioridad
5. ⚪ Fix Bug 3 (updated_at) - Solo si confirmas que lo necesitas

**¿Confirmas el plan?**
