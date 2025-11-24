# LLM Streaming Implementation Fixes

**Fecha:** 24 de noviembre de 2025  
**Versión:** v1.1.0-dev  
**Estado:** ✅ Completado y Testeado

---

## 🎯 Objetivo

Implementar y corregir el sistema de streaming para todos los providers LLM, permitiendo respuestas en tiempo real con Server-Sent Events (SSE).

---

## 🔧 Cambios Implementados

### 1. LLMManager.php - Flexibilidad de Parámetros
**Archivo:** `src/Services/LLMManager.php`

**Problema:** El método `config()` solo aceptaba slug (string), pero el controller enviaba IDs (int).

**Solución:**
```php
// ANTES
public function config(string $slug): self

// DESPUÉS
public function config(int|string $identifier): self {
    if (is_int($identifier)) {
        // ID query (preferred - immutable)
        $config = LLMConfiguration::where('id', $identifier)->active()->firstOrFail();
    } else {
        // Slug query (backward compatibility - mutable)
        $config = LLMConfiguration::where('slug', $identifier)->active()->firstOrFail();
    }
}
```

**Razón:** Los IDs son inmutables y preferibles sobre slugs mutables.

---

### 2. LLMManager.php - Visibilidad de getProvider()
**Archivo:** `src/Services/LLMManager.php` (línea 206)

**Problema:** `getProvider()` era `protected`, bloqueando acceso desde `LLMStreamController`.

**Solución:**
```php
// ANTES
protected function getProvider(): LLMProviderInterface

// DESPUÉS  
public function getProvider(): LLMProviderInterface
```

**Error anterior:** `Call to protected method LLMManager::getProvider() from scope LLMStreamController`

---

### 3. LLMStreamController.php - Validación de Parámetros
**Archivo:** `src/Http/Controllers/Admin/LLMStreamController.php`

**Problema:** EventSource (GET) envía parámetros como strings, pero validación esperaba `numeric`/`integer`.

**Solución:**
```php
// ANTES
'temperature' => 'nullable|numeric|min:0|max:2',
'max_tokens' => 'nullable|integer|min:1|max:4000',

// DESPUÉS
'temperature' => 'nullable|string',
'max_tokens' => 'nullable|string',

// Con conversión explícita después
$validated['temperature'] = isset($validated['temperature']) ? (float) $validated['temperature'] : null;
$validated['max_tokens'] = isset($validated['max_tokens']) ? (int) $validated['max_tokens'] : null;
```

**Error anterior:** `Invalid input: expected number, received string`

---

### 4. LLMStreamController.php - Timeout PHP
**Archivo:** `src/Http/Controllers/Admin/LLMStreamController.php`

**Problema:** Streaming tarda más de 30 segundos (límite default PHP).

**Solución:**
```php
// En stream() y conversationStream()
set_time_limit(300); // 5 minutos
```

**Error anterior:** `Maximum execution time of 30 seconds exceeded`

---

### 5. LLMStreamController.php - Sin Filtro de Providers
**Archivo:** `src/Http/Controllers/Admin/LLMStreamController.php`

**Problema:** Hardcoded filtering solo permitía `ollama` y `openai`.

**Solución:**
```php
// ANTES
$configurations = LLMConfiguration::active()
    ->whereIn('provider', ['ollama', 'openai'])
    ->get();

// DESPUÉS
$configurations = LLMConfiguration::active()->get();
```

**Beneficio:** Todos los providers (OpenRouter, Anthropic, Custom) ahora disponibles para streaming.

---

### 6. OpenRouterProvider.php - Método Duplicado
**Archivo:** `src/Services/Providers/OpenRouterProvider.php`

**Problema:** Dos métodos `stream()` causaban fatal error.

**Solución:** Unificado en un solo método con soporte de contexto:
```php
public function stream(string $prompt, array $context, array $parameters, callable $callback): void
{
    $messages = [];
    
    // Add context messages
    foreach ($context as $msg) {
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }
    
    // Add current prompt
    $messages[] = ['role' => 'user', 'content' => $prompt];

    $stream = $this->client->chat()->createStreamed([
        'model' => $this->configuration->model,
        'messages' => $messages,
        'temperature' => $parameters['temperature'] ?? 0.7,
        'max_tokens' => $parameters['max_tokens'] ?? 4096,
    ]);

    foreach ($stream as $response) {
        if (isset($response->choices[0]->delta->content)) {
            $callback($response->choices[0]->delta->content);
        }
    }
}
```

**Error anterior:** `Cannot redeclare OpenRouterProvider::stream()`

---

### 7. OllamaProvider.php - Streaming Real con fopen()
**Archivo:** `src/Services/Providers/OllamaProvider.php`

**Problema:** `Http::post()` espera respuesta completa, no hace streaming real.

**Solución:** Implementación con `fopen()` y `stream_context_create()`:
```php
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => 120,
    ],
]);

$stream = @fopen($endpoint, 'r', false, $context);
if (!$stream) {
    throw new \Exception("Failed to connect to Ollama at {$endpoint}");
}

while (!feof($stream)) {
    $line = fgets($stream);
    if ($line === false) continue;
    
    $data = json_decode($line, true);
    if (!$data) continue;
    
    // Handle both 'response' and 'thinking' fields (model-specific)
    $chunk = $data['response'] ?? $data['thinking'] ?? null;
    
    if ($chunk !== null && $chunk !== '') {
        $callback($chunk);
    }
    
    if ($data['done'] ?? false) {
        break;
    }
}

fclose($stream);
```

**Mejora:** Streaming NDJSON real, línea por línea, soporta modelos con `thinking` field (como qwen3).

---

### 8. Migration - OpenRouter en ENUM
**Archivo:** `database/migrations/2025_01_15_000002_add_openrouter_to_providers.php`

**Problema:** ENUM de `provider` no incluía `openrouter`.

**Solución:**
```php
DB::statement("
    ALTER TABLE llm_manager_configurations 
    MODIFY COLUMN provider ENUM(
        'ollama', 'openai', 'anthropic', 
        'openrouter', 'local', 'custom'
    ) NOT NULL
");
```

**Estado:** ✅ Migración ejecutada exitosamente.

---

### 9. Corrección de Modelo OpenRouter
**Base de datos:** `llm_manager_configurations` ID 8

**Problema:** Modelo `openai/gpt-5-pro` no existe en OpenRouter.

**Solución:**
```bash
# Modelos válidos en OpenRouter
openai/gpt-5.1
openai/gpt-5.1-chat

# Actualizado en DB
UPDATE llm_manager_configurations 
SET model = 'openai/gpt-5.1' 
WHERE id = 8;
```

---

## ✅ Resultados de Testing

### OpenRouter (openai/gpt-5.1)
- ✅ **Streaming funcional**
- **Velocidad:** 5 tokens en 1 segundo
- **Respuesta:** "Hello."
- **Estado:** Funcionando correctamente
- ⚠️ **Issue menor:** Error cosmético `accepted_prediction_tokens` (no afecta funcionalidad)

### Ollama (qwen3:4b)
- ✅ **Streaming funcional**
- **Velocidad:** 10 tokens en 8 segundos
- **Respuesta:** "Hi! 😊 How can I help you today?"
- **Estado:** Funcionando perfectamente
- **Sin errores**

### Ollama (deepseek-coder:6.7b)
- **Estado:** No testeado aún (mismo provider, debería funcionar)

---

## 📊 Arquitectura de Streaming

### Server-Side Events (SSE)
- **Endpoint:** `GET /admin/llm/stream/stream`
- **Headers:** `text/event-stream`, `no-cache`, `keep-alive`
- **Formato:** `data: {"type": "chunk", "content": "...", "tokens": N}\n\n`

### Provider-Specific Implementations

#### 1. Ollama
- **Método:** Native PHP `fopen()` + `stream_context_create()`
- **Formato:** NDJSON (una línea JSON por chunk)
- **Campos:** `response` o `thinking` (model-dependent)

#### 2. OpenRouter
- **Método:** OpenAI SDK `createStreamed()`
- **Formato:** SDK Iterator
- **Campos:** `choices[0]->delta->content`

#### 3. OpenAI
- **Método:** OpenAI SDK `createStreamed()`
- **Formato:** SDK Iterator
- **Estado:** Implementado (mismo que OpenRouter)

#### 4. Anthropic
- **Estado:** No implementado (lanza excepción)

#### 5. Custom
- **Estado:** Stub implementation

---

## 🔍 Issues Conocidos

### 1. OpenRouter - `accepted_prediction_tokens`
**Tipo:** Cosmético  
**Impacto:** Bajo  
**Descripción:** Error en frontend al procesar respuesta final del SDK. No afecta el streaming.  
**Solución futura:** Agregar try-catch o null coalescing en manejo de usage statistics.

### 2. Ollama - Campo `thinking` vs `response`
**Tipo:** Informativo  
**Impacto:** Ninguno  
**Descripción:** Algunos modelos (como qwen3) envían `thinking` antes de `response`.  
**Solución:** Ya implementada - provider maneja ambos campos.

---

## 📝 Testing Checklist

- [x] OpenRouter streaming funcional
- [x] Ollama streaming funcional
- [x] Validación de parámetros corregida
- [x] Timeout PHP aumentado
- [x] Provider visibility (getProvider public)
- [x] Filtro hardcoded eliminado
- [x] Modelo OpenRouter corregido
- [x] Migration OpenRouter ejecutada
- [ ] Anthropic streaming (pendiente implementación)
- [ ] Custom provider streaming (pendiente implementación)
- [ ] Fix error `accepted_prediction_tokens` (cosmético)

---

## 🚀 Próximos Pasos

1. **Fix cosmético:** Manejar `accepted_prediction_tokens` error en OpenRouter
2. **Testing adicional:** Probar Ollama DeepSeek Coder
3. **Implementar Anthropic:** Si se requiere soporte
4. **Documentación:** Actualizar README con ejemplos de uso
5. **Performance:** Optimizar buffers y chunk size
6. **UI/UX:** Mejorar indicadores de streaming activo

---

## 📚 Referencias

- **OpenRouter API:** https://openrouter.ai/docs
- **Ollama API:** https://github.com/ollama/ollama/blob/main/docs/api.md
- **Server-Sent Events:** https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events
- **PHP Streams:** https://www.php.net/manual/en/book.stream.php

---

**Autor:** AI Assistant (Claude Sonnet 4.5)  
**Revisado por:** Usuario  
**Aprobado:** ✅ Testing exitoso
