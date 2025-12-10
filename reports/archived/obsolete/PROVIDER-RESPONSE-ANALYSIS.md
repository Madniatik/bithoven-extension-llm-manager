# Provider Response Structure Analysis
**Fecha:** 4 de diciembre de 2025  
**Propósito:** Analizar qué datos devuelve cada provider (raw) y cómo los procesamos

---

## 🎯 Problema Actual

**Estamos perdiendo datos** porque:
1. Solo guardamos datos procesados (nuestro array normalizado)
2. No guardamos la respuesta RAW completa del provider
3. Diferentes providers devuelven estructuras distintas
4. No sabemos qué metadatos únicos ofrece cada provider

---

## 📊 Análisis por Provider

### 1. **OpenAI** (via OpenAI PHP SDK)

#### Non-Streaming Response
```json
{
  "id": "chatcmpl-123",
  "object": "chat.completion",
  "created": 1677652288,
  "model": "gpt-4-turbo",
  "system_fingerprint": "fp_44709d6fcb",
  "choices": [{
    "index": 0,
    "message": {
      "role": "assistant",
      "content": "Hello! How can I help you?"
    },
    "logprobs": null,
    "finish_reason": "stop"
  }],
  "usage": {
    "prompt_tokens": 56,
    "completion_tokens": 31,
    "total_tokens": 87,
    "completion_tokens_details": {
      "reasoning_tokens": 0,
      "accepted_prediction_tokens": 0,
      "rejected_prediction_tokens": 0
    }
  }
}
```

#### Streaming Response (último chunk)
```php
// SDK devuelve objeto CreateStreamedResponse
$lastResponse->id                    // "chatcmpl-123"
$lastResponse->model                 // "gpt-4-turbo"
$lastResponse->created               // 1677652288
$lastResponse->systemFingerprint     // "fp_44709d6fcb"
$lastResponse->usage->promptTokens   // 56 ✅
$lastResponse->usage->completionTokens // 31 ✅
$lastResponse->usage->totalTokens    // 87 ✅
$lastResponse->choices[0]->finishReason // "stop"
```

**Metadatos únicos OpenAI:**
- ✅ `system_fingerprint` - Huella del sistema
- ✅ `completion_tokens_details` - Tokens de reasoning, predictions
- ✅ `created` - Timestamp Unix

---

### 2. **OpenRouter** (via OpenAI PHP SDK)

#### Non-Streaming Response
```json
{
  "id": "gen-1764824848-rKblVd0t2QQTXTDbMQNe",
  "model": "anthropic/claude-sonnet-4.5",
  "object": "chat.completion",
  "created": 1764824848,
  "choices": [{
    "index": 0,
    "message": {
      "role": "assistant",
      "content": "Hello! How can I help you?"
    },
    "finish_reason": "stop"
  }],
  "usage": {
    "prompt_tokens": 56,
    "completion_tokens": 31,
    "total_tokens": 87,
    "native_tokens_prompt": 58,        // ⭐ OPENROUTER-SPECIFIC
    "native_tokens_completion": 29     // ⭐ OPENROUTER-SPECIFIC
  }
}
```

#### Streaming Response (último chunk) - **PROBLEMA**
```php
// SDK devuelve objeto CreateStreamedResponse
$lastResponse->id                    // "gen-..." ✅
$lastResponse->model                 // "anthropic/claude-sonnet-4.5" ✅
$lastResponse->created               // 1764824848 ✅
$lastResponse->systemFingerprint     // null (no usa este campo)
$lastResponse->usage                 // ❌ NULL - AQUÍ ESTÁ EL PROBLEMA
$lastResponse->choices[0]->finishReason // "stop" ✅
```

**Metadatos únicos OpenRouter:**
- ⭐ `native_tokens_prompt` - Tokens reales del modelo subyacente (ej: Claude usa más tokens)
- ⭐ `native_tokens_completion` - Tokens reales de respuesta del modelo
- ⭐ `id` formato: `gen-{timestamp}-{hash}` (diferente a OpenAI)
- ❌ NO envía `usage` en último chunk de stream (DIFERENCIA CLAVE)

**Headers HTTP adicionales (no capturamos actualmente):**
```
X-RateLimit-Requests-Limit: 200
X-RateLimit-Requests-Remaining: 199
X-RateLimit-Tokens-Limit: 10000000
X-RateLimit-Tokens-Remaining: 9999943
```

---

### 3. **Anthropic** (via HTTP directo)

#### Non-Streaming Response
```json
{
  "id": "msg_01XYZ123abc",
  "type": "message",
  "role": "assistant",
  "content": [
    {
      "type": "text",
      "text": "Hello! How can I help you?"
    }
  ],
  "model": "claude-3-opus-20240229",
  "stop_reason": "end_turn",
  "stop_sequence": null,
  "usage": {
    "input_tokens": 156,
    "output_tokens": 89
  }
}
```

#### Streaming Response Events
```
event: message_start
data: {"type":"message_start","message":{"id":"msg_01XYZ","usage":{"input_tokens":156}}}

event: content_block_delta
data: {"type":"content_block_delta","delta":{"text":"Hello"}}

event: message_delta
data: {"type":"message_delta","usage":{"output_tokens":89},"delta":{"stop_reason":"end_turn"}}

event: message_stop
data: {"type":"message_stop"}
```

**Metadatos únicos Anthropic:**
- ✅ `id` formato: `msg_01XYZ...`
- ✅ `stop_reason`: end_turn, max_tokens, stop_sequence
- ✅ `stop_sequence`: String que causó el stop (si aplica)
- ❌ NO tiene `system_fingerprint`
- ❌ NO tiene `created` timestamp
- ✅ Streaming por eventos SSE (diferente estructura)

---

### 4. **Ollama** (via HTTP directo)

#### Non-Streaming Response
```json
{
  "model": "deepseek-coder:6.7b",
  "created_at": "2025-12-04T04:06:54.123Z",
  "response": "Hello! How can I help you?",
  "done": true,
  "context": [1234, 5678, ...],  // Array de tokens
  "total_duration": 3448000000,
  "load_duration": 123000000,
  "prompt_eval_count": 156,
  "prompt_eval_duration": 810000000,
  "eval_count": 89,
  "eval_duration": 1200000000
}
```

#### Streaming Response (chunks)
```json
// Chunks intermedios
{"model":"deepseek-coder:6.7b","created_at":"...","response":"Hello","done":false}
{"model":"deepseek-coder:6.7b","created_at":"...","response":"!","done":false}

// Último chunk
{
  "model": "deepseek-coder:6.7b",
  "created_at": "2025-12-04T04:06:54.123Z",
  "response": "",
  "done": true,
  "context": [1234, 5678, ...],
  "total_duration": 3448000000,
  "load_duration": 123000000,
  "prompt_eval_count": 156,
  "prompt_eval_duration": 810000000,
  "eval_count": 89,
  "eval_duration": 1200000000
}
```

**Metadatos únicos Ollama:**
- ⭐ `context` - Array de tokens completo (para continuar conversación)
- ⭐ `total_duration` - Nanosegundos totales
- ⭐ `load_duration` - Tiempo cargando modelo
- ⭐ `prompt_eval_duration` - Tiempo procesando prompt
- ⭐ `eval_duration` - Tiempo generando respuesta
- ✅ `done` flag - Indica último chunk
- ✅ Timestamps en formato ISO 8601

---

## 🔍 Comparativa de Campos

| Campo | OpenAI | OpenRouter | Anthropic | Ollama |
|-------|--------|------------|-----------|--------|
| **ID único** | ✅ chatcmpl-* | ✅ gen-* | ✅ msg_* | ❌ |
| **Timestamp** | ✅ created | ✅ created | ❌ | ✅ created_at |
| **Model usado** | ✅ | ✅ | ✅ | ✅ |
| **Tokens input** | ✅ prompt_tokens | ✅ prompt_tokens | ✅ input_tokens | ✅ prompt_eval_count |
| **Tokens output** | ✅ completion_tokens | ✅ completion_tokens | ✅ output_tokens | ✅ eval_count |
| **Tokens totales** | ✅ total_tokens | ✅ total_tokens | ❌ (calc) | ❌ (calc) |
| **Finish reason** | ✅ stop/length/... | ✅ stop/length/... | ✅ end_turn/max_tokens/... | ❌ |
| **System fingerprint** | ✅ | ❌ | ❌ | ❌ |
| **Native tokens** | ❌ | ⭐ Sí | ❌ | ❌ |
| **Performance metrics** | ❌ | ❌ | ❌ | ⭐ Sí (durations) |
| **Context array** | ❌ | ❌ | ❌ | ⭐ Sí |
| **Streaming usage** | ✅ En último chunk | ❌ NULL | ✅ En evento message_delta | ✅ En último chunk |

---

## ❌ Datos que Estamos PERDIENDO Actualmente

### OpenAI
- `completion_tokens_details.reasoning_tokens`
- `completion_tokens_details.accepted_prediction_tokens`
- `completion_tokens_details.rejected_prediction_tokens`

### OpenRouter
- ❌ **CRÍTICO:** `usage` completo en streaming (viene NULL)
- `native_tokens_prompt`
- `native_tokens_completion`
- Rate limit headers

### Anthropic
- Eventos completos de streaming
- `stop_sequence` (cuál fue)

### Ollama
- `context` array completo
- `load_duration`
- `prompt_eval_duration`
- `eval_duration`
- `total_duration`

---

## 💡 Propuesta de Solución

### 1. **Agregar campo `raw_response` a tabla messages**
```sql
ALTER TABLE llm_manager_conversation_messages 
ADD COLUMN raw_response JSON NULL COMMENT 'Complete raw response from provider';
```

### 2. **Capturar respuesta completa en cada provider**
```php
// En cada Provider::stream()
return [
    'usage' => [...],
    'raw_response' => $lastResponse, // ⭐ NUEVO: Respuesta completa
];
```

### 3. **Guardar en DB**
```php
$assistantMessage = LLMConversationMessage::create([
    // ... campos actuales
    'raw_response' => $metrics['raw_response'] ?? null, // ⭐ NUEVO
    'metadata' => [
        // ... metadatos procesados actuales
    ],
]);
```

### 4. **Normalizar acceso a tokens**
Crear método en cada provider:
```php
public function extractTokenUsage($rawResponse): array
{
    // Cada provider implementa su lógica
    // OpenRouter: llamar endpoint generation si es null
    // Anthropic: parsear eventos
    // Ollama: leer eval_count
}
```

---

## 🎯 Plan de Implementación

**Fase 1: Captura (sin romper nada)**
1. ✅ Agregar migración `raw_response` column
2. ✅ Modificar cada provider para devolver `raw_response`
3. ✅ Guardar en DB sin procesar

**Fase 2: Análisis**
1. Recopilar datos reales de producción
2. Analizar qué campos únicos tiene cada provider
3. Documentar diferencias entre modelos del mismo provider

**Fase 3: Optimización**
1. Implementar `extractTokenUsage()` inteligente
2. Agregar fallback para OpenRouter (HTTP call si usage=null)
3. Normalizar metadata keys

**Fase 4: Monitoring**
1. Dashboard de metadatos únicos
2. Alertas si faltan tokens
3. Comparativas de performance por provider

---

## 🚀 Siguiente Paso

**¿Empezamos con Fase 1?**
- Crear migración `raw_response`
- Modificar providers para capturar todo
- Guardar sin procesar (debugging completo)

Esto nos dará visibilidad total de qué estamos recibiendo realmente.
