# OpenRouter Response Format

## Overview

OpenRouter is a unified API gateway that **standardizes responses to OpenAI-compatible format** regardless of the underlying model (Claude, GPT-4, Gemini, etc.).

**Version:** 1.0.0  
**Last Updated:** 2025-12-04  
**Source:** OpenRouter Documentation + Testing

---

## Standard Response Structure

```json
{
  "id": "gen-abc123",
  "model": "anthropic/claude-3-opus",
  "object": "chat.completion.chunk",
  "created": 1234567890,
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "Response text"
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 156,
    "completion_tokens": 89,
    "total_tokens": 245,
    "cost": 0.012345
  }
}
```

---

## Field Consistency by Model

| Field | Claude | GPT-4 | Gemini | Always Present? |
|-------|--------|-------|--------|-----------------|
| **id** | `gen-xxx` | `chatcmpl-xxx` | `gen-xxx` | ✅ Yes (format varies) |
| **model** | `anthropic/claude-*` | `openai/gpt-*` | `google/gemini-*` | ✅ Yes |
| **choices[0].message.content** | ✅ | ✅ | ✅ | ✅ Yes |
| **usage.prompt_tokens** | ✅ | ✅ | ✅ | ✅ Yes |
| **usage.completion_tokens** | ✅ | ✅ | ✅ | ✅ Yes |
| **usage.total_tokens** | ✅ | ✅ | ✅ | ✅ Yes |
| **usage.cost** | ✅ | ✅ | ✅ | ✅ Yes (OpenRouter calculated) |
| **finish_reason** | ✅ | ✅ | ✅ | ✅ Yes |
| **system_fingerprint** | ❌ null | ✅ Present | ❌ null | ⚠️ Optional |
| **native_tokens_prompt** | ✅ Present | ❌ null | ❌ null | ⚠️ Optional |
| **native_tokens_completion** | ✅ Present | ❌ null | ❌ null | ⚠️ Optional |

---

## Direct API vs OpenRouter

### Direct Claude API Response
```json
{
  "id": "msg_01XYZ",
  "type": "message",
  "role": "assistant",
  "content": [{"type": "text", "text": "Hello!"}],
  "usage": {
    "input_tokens": 10,
    "output_tokens": 5
  }
}
```

### OpenRouter (Claude) Response
```json
{
  "id": "gen-abc123",
  "model": "anthropic/claude-3-opus",
  "choices": [{
    "message": {
      "role": "assistant",
      "content": "Hello!"
    }
  }],
  "usage": {
    "prompt_tokens": 10,
    "completion_tokens": 5,
    "cost": 0.000123
  }
}
```

**Notice:** OpenRouter converts Claude's native format to OpenAI-compatible!

---

## Implementation Guidelines

### ✅ Safe to Use (Always Present)
```php
// These fields are guaranteed by OpenRouter
$text = $response['choices'][0]['message']['content'];
$inputTokens = $response['usage']['prompt_tokens'];
$outputTokens = $response['usage']['completion_tokens'];
$modelUsed = $response['model'];
$cost = $response['usage']['cost']; // OpenRouter's calculated cost
```

### ⚠️ Check Before Use (Optional)
```php
// These fields may be null depending on model
$systemFingerprint = $response['system_fingerprint'] ?? null; // GPT-4: yes, Claude: no
$nativeTokensPrompt = $response['native_tokens_prompt'] ?? null; // Claude: yes, GPT-4: no
$finishReason = $response['choices'][0]['finish_reason'] ?? 'stop';
```

---

## Our Implementation

See `src/Services/Providers/OpenRouterProvider.php`:

```php
return [
    // Standard fields (always present)
    'usage' => $usage,
    'model' => $finalData['model'] ?? $this->configuration->model,
    'finish_reason' => $finalData['choices'][0]['finish_reason'] ?? 'stop',
    
    // OpenRouter-specific
    'generation_id' => $generationId,
    'created_at' => $finalData['created'] ?? null,
    
    // Optional fields (model-dependent)
    'system_fingerprint' => $finalData['system_fingerprint'] ?? null,
    'cost' => $finalData['usage']['cost'] ?? null,
    
    // Complete raw response
    'raw_response' => $finalData,
];
```

---

## Benefits of OpenRouter Standardization

1. ✅ **Consistent format** across all models
2. ✅ **Easy model switching** without code changes
3. ✅ **Accurate cost tracking** (provider-calculated)
4. ✅ **OpenAI SDK compatibility** (can use same client)
5. ✅ **Single integration point** for multiple providers

---

## Testing Results

**Test Date:** 2025-12-04  
**Model:** anthropic/claude-sonnet-4.5  
**Message ID:** 218

```sql
mysql> SELECT tokens, cost_usd, 
       JSON_EXTRACT(metadata, '$.input_tokens') as meta_input,
       JSON_EXTRACT(metadata, '$.output_tokens') as meta_output
FROM llm_manager_conversation_messages WHERE id = 218;

+--------+----------+------------+-------------+
| tokens | cost_usd | meta_input | meta_output |
+--------+----------+------------+-------------+
|  13033 | 0.056907 |      11549 |        1484 |
+--------+----------+------------+-------------+
```

✅ All fields captured correctly from OpenRouter standardized response!

---

## References

- **OpenRouter Documentation:** https://openrouter.ai/docs/responses
- **Provider Response Analysis:** `/docs/PROVIDER-RESPONSE-ANALYSIS.md`
- **OpenRouter Provider Code:** `/src/Services/Providers/OpenRouterProvider.php`
