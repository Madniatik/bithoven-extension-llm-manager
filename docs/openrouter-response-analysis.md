 # OpenRouter Response Analysis

## Campos Actualmente Capturados ✅

En `OpenRouterProvider::stream()` líneas 103-109:

```php
'usage' => [
    'prompt_tokens' => $lastResponse->usage->promptTokens ?? 0,
    'completion_tokens' => $lastResponse->usage->completionTokens ?? 0,
    'total_tokens' => $lastResponse->usage->totalTokens ?? 0,
],
'model' => $lastResponse->model ?? $this->configuration->model,
'finish_reason' => $lastResponse->choices[0]->finishReason ?? 'stop',
```

## Campos Disponibles NO Capturados ❌

### 1. Native Tokens (OpenRouter-specific)
OpenRouter devuelve tokens nativos del modelo que pueden diferir del conteo estándar:

```php
// Disponible en $lastResponse->usage
$lastResponse->usage->native_tokens_prompt ?? null
$lastResponse->usage->native_tokens_completion ?? null
```

**Utilidad:** Tracking preciso de uso real del modelo (especialmente útil en modelos con tokenización diferente).

### 2. Generation ID
```php
$lastResponse->id ?? null  // ID único de la generación
```

**Utilidad:** 
- Debugging y tracking de requests específicos
- Reportar issues a OpenRouter con ID exacto
- Auditoría y logging

### 3. Cost Data (CRÍTICO)
OpenRouter calcula el costo automáticamente basado en pricing actualizado:

```php
// Actualmente NO capturamos esto del response
// Lo calculamos manualmente en LLMStreamLogger::calculateCost()
```

**Problema:** Nuestro cálculo local puede estar desactualizado vs pricing real de OpenRouter.

**Solución:** OpenRouter devuelve el costo en la respuesta o vía usage endpoint.

### 4. System Fingerprint
```php
$lastResponse->system_fingerprint ?? null
```

**Utilidad:** Identificar versión específica del modelo usado.

### 5. Created Timestamp
```php
$lastResponse->created ?? null  // Unix timestamp
```

**Utilidad:** Timestamp exacto de cuando OpenRouter procesó el request.

## Campos de Metadata Adicionales

### En streaming responses:
- `x-ratelimit-*` headers (rate limits)
- `x-request-id` header (request tracking)

### En usage logs endpoint:
- Cost breakdown detallado
- Moderation results (si aplica)

## Recomendaciones de Implementación

### Prioridad ALTA 🔴
1. **Cost Data:** Capturar costo real de OpenRouter (no calcular localmente)
2. **Generation ID:** Útil para debugging

### Prioridad MEDIA 🟡
3. **Native Tokens:** Mejora accuracy de tracking
4. **System Fingerprint:** Útil para auditoría

### Prioridad BAJA 🟢
5. **Created Timestamp:** Ya tenemos nuestros timestamps

## Cambios Propuestos

### 1. OpenRouterProvider::stream()
```php
return [
    'usage' => [
        'prompt_tokens' => $lastResponse->usage->promptTokens ?? 0,
        'completion_tokens' => $lastResponse->usage->completionTokens ?? 0,
        'total_tokens' => $lastResponse->usage->totalTokens ?? 0,
        // NUEVO:
        'native_tokens_prompt' => $lastResponse->usage->native_tokens_prompt ?? null,
        'native_tokens_completion' => $lastResponse->usage->native_tokens_completion ?? null,
    ],
    'model' => $lastResponse->model ?? $this->configuration->model,
    'finish_reason' => $lastResponse->choices[0]->finishReason ?? 'stop',
    // NUEVO:
    'generation_id' => $lastResponse->id ?? null,
    'system_fingerprint' => $lastResponse->system_fingerprint ?? null,
    'created_at' => $lastResponse->created ?? null,
];
```

### 2. Controller metadata
Guardar en `metadata`:
```php
'metadata' => [
    // ... existing fields
    'generation_id' => $metrics['generation_id'] ?? null,
    'native_tokens_prompt' => $metrics['usage']['native_tokens_prompt'] ?? null,
    'native_tokens_completion' => $metrics['usage']['native_tokens_completion'] ?? null,
    'system_fingerprint' => $metrics['system_fingerprint'] ?? null,
]
```

### 3. Cost - Agregar Column a Messages Table
Como `response_time`, agregar `cost_usd DECIMAL(10,6)` para queries rápidos:

```php
// Migration
$table->decimal('cost_usd', 10, 6)->nullable();

// Model
protected $fillable = [..., 'cost_usd'];
protected $casts = [..., 'cost_usd' => 'float'];

// Controller
'cost_usd' => $usageLog->cost_usd,
```

## Siguiente Paso
Implementar captura de estos campos en OpenRouterProvider y guardar en metadata/columns.
