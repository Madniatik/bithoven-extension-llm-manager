# Provider Response Format Comparison

**Quick Answer:** No, cada provider tiene un formato completamente diferente.

**Version:** 0.4.0-dev  
**Last Updated:** 12 de diciembre de 2025

---

## 🔍 Comparativa Rápida

| Aspecto | OpenRouter | Ollama | Anthropic | OpenAI |
|---------|------------|--------|-----------|--------|
| **Formato Base** | OpenAI-compatible | Ollama nativo | Anthropic nativo | OpenAI nativo |
| **Token Fields** | `prompt_tokens`, `completion_tokens` | `prompt_eval_count`, `eval_count` | `input_tokens`, `output_tokens` | `prompt_tokens`, `completion_tokens` |
| **Cost Included?** | ✅ Yes (`usage.cost`) | ❌ No (local) | ❌ No | ❌ No |
| **Streaming Format** | SSE chunks | JSON chunks | SSE events | SSE chunks |
| **Usage in Stream?** | ✅ Yes (final chunk) | ✅ Yes (final chunk) | ⚠️ Partial (events) | ⚠️ Varies |
| **Unique Metadata** | `native_tokens_*`, `generation_id` | Durations, `context` array | `stop_reason`, event types | `system_fingerprint` |

---

## 📊 Response Structure Details

### 1. OpenRouter (OpenAI-compatible)

```json
{
  "id": "gen-1764826472-VaXunwHtXQNvgDwANzwO",
  "model": "anthropic/claude-sonnet-4.5",
  "object": "chat.completion.chunk",
  "created": 1764826472,
  "choices": [{
    "index": 0,
    "delta": {"role": "assistant", "content": ""},
    "finish_reason": "stop"
  }],
  "usage": {
    "prompt_tokens": 11549,
    "completion_tokens": 1484,
    "total_tokens": 13033,
    "cost": 0.056907
  }
}
```

**Características:**
- ✅ **Estandarizado** a formato OpenAI
- ✅ **Cost incluido** por OpenRouter
- ✅ **Compatible** con OpenAI SDK
- ⚠️ Campos opcionales varían por modelo subyacente

**Nuestro mapeo:**
```php
'usage' => [
    'prompt_tokens' => $finalData['usage']['prompt_tokens'],
    'completion_tokens' => $finalData['usage']['completion_tokens'],
],
'cost' => $finalData['usage']['cost'], // ← Directo de OpenRouter
```

---

### 2. Ollama (Formato Nativo)

```json
{
  "model": "deepseek-coder:6.7b",
  "created_at": "2025-12-04T04:06:54.123Z",
  "response": "Hello! How can I help?",
  "done": true,
  "context": [1234, 5678, 9012],
  "total_duration": 3448000000,
  "load_duration": 123000000,
  "prompt_eval_count": 156,
  "prompt_eval_duration": 810000000,
  "eval_count": 89,
  "eval_duration": 1200000000
}
```

**Características:**
- ❌ **NO compatible** con formato OpenAI
- ✅ **Duraciones detalladas** (load, eval, total)
- ✅ **Context array** (tokens del prompt)
- ❌ **NO cost** (es local/gratuito)
- ⚠️ Campos diferentes: `prompt_eval_count` vs `prompt_tokens`

**Nuestro mapeo:**
```php
'usage' => [
    'prompt_tokens' => $finalData['prompt_eval_count'] ?? 0, // ← Mapeo manual
    'completion_tokens' => $finalData['eval_count'] ?? 0,    // ← Mapeo manual
],
'durations' => [ // ← Metadata única de Ollama
    'total' => $finalData['total_duration'],
    'load' => $finalData['load_duration'],
    'eval' => $finalData['eval_duration'],
],
```

---

### 3. Anthropic (SSE Events)

```
event: message_start
data: {"type":"message_start","message":{"id":"msg_01XYZ","usage":{"input_tokens":156}}}

event: content_block_delta
data: {"type":"content_block_delta","delta":{"text":"Hello"}}

event: message_delta
data: {"type":"message_delta","usage":{"output_tokens":89},"delta":{"stop_reason":"end_turn"}}
```

**Características:**
- ❌ **NO compatible** con formato OpenAI
- ✅ **Event-based streaming** (diferente a chunks)
- ⚠️ Campos diferentes: `input_tokens` vs `prompt_tokens`
- ❌ **NO cost** incluido
- ✅ **stop_reason** más descriptivo

**Nuestro mapeo:**
```php
'usage' => [
    'prompt_tokens' => $inputTokens,  // ← De event message_start
    'completion_tokens' => $outputTokens, // ← De event message_delta
],
'stop_reason' => $stopReason, // ← end_turn, max_tokens, stop_sequence
```

---

### 4. OpenAI (Referencia)

```json
{
  "id": "chatcmpl-abc123",
  "object": "chat.completion.chunk",
  "created": 1677652288,
  "model": "gpt-4",
  "choices": [{
    "delta": {"content": "Hello"},
    "finish_reason": null
  }],
  "usage": {
    "prompt_tokens": 156,
    "completion_tokens": 89,
    "total_tokens": 245
  }
}
```

**Características:**
- ✅ **Formato estándar** (usado por OpenRouter)
- ✅ `system_fingerprint` (único de OpenAI)
- ❌ **NO cost** incluido
- ✅ Compatible con SDK oficial

---

## 🔧 Diferencias Críticas

### Token Field Names

| Provider | Input Field | Output Field | Total Field |
|----------|-------------|--------------|-------------|
| **OpenRouter** | `prompt_tokens` | `completion_tokens` | `total_tokens` |
| **Ollama** | `prompt_eval_count` | `eval_count` | ❌ (calculado) |
| **Anthropic** | `input_tokens` | `output_tokens` | ❌ (calculado) |
| **OpenAI** | `prompt_tokens` | `completion_tokens` | `total_tokens` |

### Streaming Differences

| Provider | Format | Usage in Stream? | How to Extract |
|----------|--------|------------------|----------------|
| **OpenRouter** | SSE chunks | ✅ Final chunk | `$finalData['usage']` |
| **Ollama** | JSON lines | ✅ Final chunk (`done: true`) | `$finalData['prompt_eval_count']` |
| **Anthropic** | SSE events | ⚠️ Multiple events | Accumulate from `message_start` + `message_delta` |
| **OpenAI** | SSE chunks | ⚠️ Varies | Sometimes in final chunk |

### Cost Tracking

| Provider | Cost Included? | How to Calculate |
|----------|----------------|------------------|
| **OpenRouter** | ✅ Yes | `$response['usage']['cost']` |
| **Ollama** | ❌ No (local) | `0.0` (free) |
| **Anthropic** | ❌ No | Manual: tokens × pricing |
| **OpenAI** | ❌ No | Manual: tokens × pricing |

---

## 💡 Implementation Strategy

### Our Normalization Layer

Cada provider tiene su propio `Provider.php` que normaliza a este formato común:

```php
return [
    'usage' => [
        'prompt_tokens' => ...,      // Normalizado
        'completion_tokens' => ...,  // Normalizado
        'total_tokens' => ...,       // Normalizado
    ],
    'model' => ...,
    'finish_reason' => ...,
    'cost' => ...,                   // null si no disponible
    'raw_response' => ...,           // Response original completo
];
```

### Provider-Specific Metadata

Además del formato común, cada provider guarda metadata única:

**OpenRouter:**
```php
'generation_id' => $generationId,
'native_tokens_prompt' => ...,
'native_tokens_completion' => ...,
```

**Ollama:**
```php
'durations' => [
    'total' => ...,
    'load' => ...,
    'eval' => ...,
],
'context' => [...],
```

**Anthropic:**
```php
'stop_reason' => ..., // end_turn, max_tokens, etc.
'stop_sequence' => ...,
```

---

## 📝 Key Takeaways

1. ❌ **NO hay formato universal** entre providers
2. ✅ **Cada provider** requiere mapeo específico
3. ✅ **OpenRouter es el más compatible** (usa formato OpenAI)
4. ✅ **raw_response** guarda datos originales para análisis
5. ⚠️ **Cost solo OpenRouter** lo incluye directamente
6. ✅ **Nuestro código normaliza** todo a formato común

---

## 🔗 Referencias

- **OpenRouter:** [docs/OPENROUTER-RESPONSE-FORMAT.md](./OPENROUTER-RESPONSE-FORMAT.md)
- **Provider Analysis:** [docs/PROVIDER-RESPONSE-ANALYSIS.md](./PROVIDER-RESPONSE-ANALYSIS.md)
- **Code:** `src/Services/Providers/`
  - `OpenRouterProvider.php` - OpenAI-compatible
  - `OllamaProvider.php` - Ollama nativo
  - `AnthropicProvider.php` - SSE events
  - `OpenAIProvider.php` - OpenAI SDK
