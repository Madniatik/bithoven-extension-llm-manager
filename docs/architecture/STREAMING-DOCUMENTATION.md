# LLM Streaming System Documentation

**Versión:** v1.0.7  
**Fecha:** 9 de diciembre de 2025  
**Estado:** Production Ready

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Arquitectura](#arquitectura)
3. [Server-Sent Events (SSE)](#server-sent-events-sse)
4. [Event Types & Formats](#event-types--formats)
5. [Frontend Integration](#frontend-integration)
6. [Backend Implementation](#backend-implementation)
7. [Monitor System Integration](#monitor-system-integration)
8. [Error Handling](#error-handling)
9. [Performance & Optimization](#performance--optimization)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)
12. [Best Practices](#best-practices)

---

## Introducción

El sistema de streaming del LLM Manager Extension permite respuestas en tiempo real mediante **Server-Sent Events (SSE)**. Los usuarios ven chunks de texto a medida que el LLM los genera, proporcionando feedback inmediato y mejor UX.

### Características Clave

- ✅ **Real-time streaming** - Chunks enviados inmediatamente
- ✅ **Multiple event types** - metadata, chunk, done, error, request_data
- ✅ **Monitor integration** - Console logs y Request Inspector en tiempo real
- ✅ **Auto-save to DB** - Mensajes persistidos automáticamente
- ✅ **Stop streaming** - Usuario puede cancelar en cualquier momento
- ✅ **Error recovery** - Manejo robusto de errores
- ✅ **Token tracking** - Input/output tokens rastreados
- ✅ **Cost calculation** - Costo calculado automáticamente

### Flujo Completo

```
User Input → Backend Validation → LLM Provider → SSE Stream → Frontend EventSource
    ↓              ↓                    ↓               ↓                ↓
 Submit      Check session       Ollama/OpenAI    Emit events     Update UI
  Form        & config           /Anthropic       (chunks)        (real-time)
    ↓              ↓                    ↓               ↓                ↓
Database    Create user         Stream chunks   metadata event   Append text
 Session      message           via callback     chunk event      to bubble
    ↓              ↓                    ↓               ↓                ↓
Monitor     Emit request_data   Calculate       done event       Update
Console      event (SSE)         metrics        (final stats)    monitor
```

---

## Arquitectura

### Componentes del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                        Frontend Layer                           │
├─────────────────────────────────────────────────────────────────┤
│  • EventSource (SSE client)                                     │
│  • Event Handlers (metadata, chunk, done, error, request_data) │
│  • UI Updates (message bubbles, monitor console)               │
│  • Progress Indicators (thinking spinner, progress bar)        │
└─────────────────────────────────────────────────────────────────┘
                              ↕
┌─────────────────────────────────────────────────────────────────┐
│                      Controller Layer                           │
├─────────────────────────────────────────────────────────────────┤
│  • LLMQuickChatController::stream()                            │
│  • Request validation                                           │
│  • Session management                                           │
│  • SSE event emission                                           │
└─────────────────────────────────────────────────────────────────┘
                              ↕
┌─────────────────────────────────────────────────────────────────┐
│                       Service Layer                             │
├─────────────────────────────────────────────────────────────────┤
│  • LLMManager (provider resolution)                            │
│  • LLMStreamLogger (metrics tracking)                          │
│  • LLMProviderInterface (streaming API)                        │
└─────────────────────────────────────────────────────────────────┘
                              ↕
┌─────────────────────────────────────────────────────────────────┐
│                      Provider Layer                             │
├─────────────────────────────────────────────────────────────────┤
│  • OllamaProvider::stream()                                     │
│  • OpenAIProvider::stream()                                     │
│  • OpenRouterProvider::stream()                                 │
│  • AnthropicProvider::stream() (pending)                        │
└─────────────────────────────────────────────────────────────────┘
                              ↕
┌─────────────────────────────────────────────────────────────────┐
│                      Database Layer                             │
├─────────────────────────────────────────────────────────────────┤
│  • llm_manager_conversation_messages                            │
│  • llm_manager_conversation_sessions                            │
│  • llm_manager_usage_logs                                       │
└─────────────────────────────────────────────────────────────────┘
```

### Directorio de Archivos

**Backend:**
- `src/Http/Controllers/Admin/LLMQuickChatController.php` - Controller principal
- `src/Services/LLMManager.php` - Resolución de providers
- `src/Services/LLMStreamLogger.php` - Métricas de streaming
- `src/Services/Providers/OllamaProvider.php` - Implementación Ollama
- `src/Services/Providers/OpenAIProvider.php` - Implementación OpenAI
- `src/Services/Providers/OpenRouterProvider.php` - Implementación OpenRouter

**Frontend:**
- `resources/views/components/chat/partials/scripts/event-handlers.blade.php` - EventSource logic
- `resources/views/components/chat/shared/monitor-console.blade.php` - Monitor UI
- `resources/views/components/chat/shared/monitor-request-inspector.blade.php` - Request Inspector

---

## Server-Sent Events (SSE)

### ¿Qué es SSE?

Server-Sent Events es un estándar HTML5 que permite al servidor enviar datos al cliente de forma unidireccional mediante HTTP.

**Ventajas sobre WebSockets:**
- ✅ Más simple (HTTP estándar)
- ✅ Auto-reconexión nativa
- ✅ Event IDs para tracking
- ✅ Compatible con proxies/firewalls
- ✅ No requiere conexión bidireccional

**Desventajas:**
- ❌ Solo servidor → cliente (no cliente → servidor durante stream)
- ❌ Límite de 6 conexiones por dominio (HTTP/1.1)

### Configuración de Headers

```php
// LLMQuickChatController::stream()
return Response::stream(function() {
    // SSE headers OBLIGATORIOS
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no'); // Nginx buffering OFF
    
    // Optional: Keep-alive
    header('Connection: keep-alive');
    
    // Emit events...
}, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
    'X-Accel-Buffering' => 'no',
]);
```

### Formato SSE

**Sintaxis:**
```
data: {JSON payload}\n\n
```

**Con event name:**
```
event: request_data\n
data: {JSON payload}\n\n
```

**Ejemplo real:**
```
data: {"type":"metadata","user_message_id":123,"input_tokens":45}\n\n

data: {"type":"chunk","content":"Hello"}\n\n

data: {"type":"chunk","content":" world"}\n\n

data: {"type":"done","full_response":"Hello world","metrics":{...}}\n\n
```

---

## Event Types & Formats

### 1. metadata Event

**Cuándo:** Antes de streaming, inmediatamente después de crear user message.

**Propósito:** Proporcionar IDs y estimaciones para UI (mostrar tokens durante "Thinking...").

**Formato:**
```json
{
  "type": "metadata",
  "user_message_id": 123,
  "user_prompt": "Original user text",
  "input_tokens": 150,
  "context_size": 10
}
```

**Uso Frontend:**
```javascript
if (data.type === 'metadata') {
    userMessageId = data.user_message_id; // For deletion if stopped early
    savedUserPrompt = data.user_prompt;   // For restoration to input
    
    // Update monitor console
    window.LLMMonitor?.log(monitorId, 'info', `📊 Input: ${data.input_tokens} tokens, Context: ${data.context_size} messages`);
}
```

---

### 2. request_data Event (Named Event)

**Cuándo:** Inmediatamente después de metadata, antes de streaming.

**Propósito:** Poblar Request Inspector tab con datos completos del request.

**Formato:**
```json
{
  "metadata": {
    "provider": "ollama",
    "model": "llama2",
    "endpoint": "http://localhost:11434",
    "timestamp": "2025-12-09T17:00:00+00:00",
    "session_id": 42,
    "message_id": 123
  },
  "parameters": {
    "temperature": 0.7,
    "max_tokens": 2000,
    "top_p": 1.0,
    "context_limit": 20,
    "actual_context_size": 15
  },
  "system_instructions": "You are a helpful assistant.",
  "context_messages": [
    {
      "id": 100,
      "role": "user",
      "content": "Previous message...",
      "tokens": 10,
      "created_at": "2025-12-09T16:55:00+00:00"
    }
  ],
  "current_prompt": "User's current question",
  "full_request_body": {
    "model": "llama2",
    "messages": [...],
    "temperature": 0.7,
    "max_tokens": 2000,
    "stream": true
  }
}
```

**Uso Frontend:**
```javascript
eventSource.addEventListener('request_data', (event) => {
    const data = JSON.parse(event.data);
    
    // Populate Request Inspector
    document.getElementById('request-metadata-provider').textContent = data.metadata.provider;
    document.getElementById('request-metadata-model').textContent = data.metadata.model;
    
    // Render context messages table
    renderContextMessagesTable(data.context_messages);
    
    // Show full request JSON
    document.getElementById('request-full-body').textContent = 
        JSON.stringify(data.full_request_body, null, 2);
});
```

---

### 3. chunk Event

**Cuándo:** Durante streaming, cada vez que el provider envía texto.

**Propósito:** Actualizar UI con texto incremental.

**Formato:**
```json
{
  "type": "chunk",
  "content": "Hello"
}
```

**Características:**
- **Frecuencia:** Variable (depende del provider)
  - Ollama: ~50-100ms por chunk
  - OpenAI: ~20-50ms por chunk
  - OpenRouter: ~30-70ms por chunk
- **Tamaño:** Variable (1-20 palabras por chunk típicamente)
- **Orden:** Secuencial, no garantizado en caso de red lenta

**Uso Frontend:**
```javascript
if (data.type === 'chunk') {
    chunkCount++;
    
    // First chunk: hide "Thinking..." and create assistant bubble
    if (chunkCount === 1) {
        hideThinking();
        assistantBubble = appendMessage('assistant', '', 0, null, false);
    }
    
    // Append chunk to bubble
    fullResponse += data.content;
    const contentDiv = assistantBubble.querySelector('[data-bubble-content]');
    contentDiv.innerHTML = marked.parse(fullResponse); // Markdown rendering
    
    // Syntax highlighting (if code blocks present)
    if (typeof Prism !== 'undefined') {
        Prism.highlightAllUnder(contentDiv);
    }
    
    scrollToBottom();
}
```

---

### 4. done Event

**Cuándo:** Al finalizar streaming exitosamente.

**Propósito:** Proporcionar métricas finales y guardar assistant message en DB.

**Formato:**
```json
{
  "type": "done",
  "assistant_message_id": 124,
  "full_response": "Complete assistant response text",
  "metrics": {
    "prompt_tokens": 150,
    "completion_tokens": 85,
    "total_tokens": 235,
    "cost_usd": 0.00047,
    "processing_time": 3.45,
    "time_to_first_chunk": 0.35
  }
}
```

**Uso Frontend:**
```javascript
if (data.type === 'done') {
    // Update message bubble with final ID
    assistantBubble.dataset.messageId = data.assistant_message_id;
    
    // Update monitor console with metrics
    window.LLMMonitor?.log(monitorId, 'success', 
        `✅ Stream completed: ${data.metrics.total_tokens} tokens, ` +
        `$${data.metrics.cost_usd.toFixed(5)}, ` +
        `${data.metrics.processing_time.toFixed(2)}s`
    );
    
    // Update token breakdown
    updateTokenBreakdown(data.metrics);
    
    // Close EventSource
    eventSource.close();
    eventSource = null;
    
    // Re-enable send button
    sendBtn.disabled = false;
    stopBtn.classList.add('d-none');
}
```

---

### 5. error Event

**Cuándo:** Si ocurre un error durante streaming.

**Propósito:** Notificar al usuario del error y limpiar estado.

**Formato:**
```json
{
  "type": "error",
  "message": "Connection to Ollama failed: Connection refused",
  "code": "PROVIDER_OFFLINE",
  "recoverable": false
}
```

**Errores Comunes:**
- `PROVIDER_OFFLINE` - Ollama/OpenAI no responde
- `API_KEY_INVALID` - API key incorrecta (OpenAI/OpenRouter)
- `RATE_LIMIT_EXCEEDED` - Límite de rate excedido
- `MODEL_NOT_FOUND` - Modelo no existe en provider
- `TIMEOUT` - Request timeout (>60s)

**Uso Frontend:**
```javascript
if (data.type === 'error') {
    hideThinking();
    
    // Show error message bubble
    appendMessage('assistant', 
        `❌ Error: ${data.message}`, 
        0, null, false
    );
    
    // Log to monitor
    window.LLMMonitor?.log(monitorId, 'error', 
        `🚨 ${data.code}: ${data.message}`
    );
    
    // Cleanup
    eventSource?.close();
    eventSource = null;
    sendBtn.disabled = false;
    stopBtn.classList.add('d-none');
}
```

---

## Frontend Integration

### EventSource Setup

```javascript
// Initialize EventSource
const eventSource = new EventSource(streamUrl);

// Named event listener (request_data)
eventSource.addEventListener('request_data', (event) => {
    const data = JSON.parse(event.data);
    // Handle request data...
});

// Default message listener (metadata, chunk, done, error)
eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    
    switch(data.type) {
        case 'metadata':
            // Handle metadata...
            break;
        case 'chunk':
            // Handle chunk...
            break;
        case 'done':
            // Handle done...
            break;
        case 'error':
            // Handle error...
            break;
    }
};

// Error handler (network errors, connection lost)
eventSource.onerror = (error) => {
    console.error('SSE Error:', error);
    eventSource.close();
};
```

### Stream Lifecycle

```javascript
function startStream(prompt) {
    // 1. Disable send button, show stop button
    sendBtn.disabled = true;
    stopBtn.classList.remove('d-none');
    
    // 2. Show "Thinking..." spinner
    showThinking();
    
    // 3. Reset state
    userMessageId = null;
    savedUserPrompt = '';
    chunkCount = 0;
    fullResponse = '';
    assistantBubble = null;
    
    // 4. Create EventSource
    const url = `{{ route('admin.llm.quick-chat.stream') }}?` + new URLSearchParams({
        session_id: sessionId,
        prompt: prompt,
        configuration_id: selectedConfigId,
        temperature: 0.7,
        max_tokens: 2000,
        context_limit: 20
    });
    
    eventSource = new EventSource(url);
    
    // 5. Attach event listeners (see above)
    
    // 6. Monitor console: log stream start
    window.LLMMonitor?.log(monitorId, 'info', '🚀 Stream started');
}

function stopStream() {
    if (!eventSource) return;
    
    // 1. Close EventSource
    eventSource.close();
    eventSource = null;
    
    // 2. Call stop endpoint (delete user message if no chunks received)
    fetch('{{ route("admin.llm.quick-chat.stop") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            session_id: sessionId,
            user_message_id: userMessageId
        })
    });
    
    // 3. Restore user prompt to input
    if (savedUserPrompt && messageInput) {
        messageInput.value = savedUserPrompt;
    }
    
    // 4. Hide thinking, re-enable send button
    hideThinking();
    sendBtn.disabled = false;
    stopBtn.classList.add('d-none');
    
    // 5. Monitor console: log stop
    window.LLMMonitor?.log(monitorId, 'warning', '⏹️ Stream stopped by user');
}
```

---

## Backend Implementation

### Controller: stream() Method

```php
public function stream(Request $request)
{
    // 1. Validate request
    $validated = $request->validate([
        'session_id' => 'required|exists:llm_manager_conversation_sessions,id',
        'prompt' => 'required|string|max:5000',
        'configuration_id' => 'required|exists:llm_manager_configurations,id',
        'temperature' => 'nullable|numeric|min:0|max:2',
        'max_tokens' => 'nullable|integer|min:1|max:128000',
        'context_limit' => 'nullable|integer|min:0|max:100',
    ]);
    
    // 2. Load session and config
    $session = LLMConversationSession::findOrFail($validated['session_id']);
    $configuration = LLMConfiguration::findOrFail($validated['configuration_id']);
    
    // 3. Return SSE stream
    return Response::stream(function() use ($session, $configuration, $validated) {
        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        
        // 4. Create user message
        $userMessage = LLMConversationMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $validated['prompt'],
            'tokens' => str_word_count($validated['prompt']),
        ]);
        
        // 5. Emit metadata event
        echo "data: " . json_encode([
            'type' => 'metadata',
            'user_message_id' => $userMessage->id,
            'user_prompt' => $validated['prompt'],
            'input_tokens' => $estimatedInputTokens,
            'context_size' => count($context),
        ]) . "\n\n";
        
        if (ob_get_level()) ob_flush();
        flush();
        
        // 6. Emit request_data event
        echo "event: request_data\n";
        echo "data: " . json_encode($requestData) . "\n\n";
        
        if (ob_get_level()) ob_flush();
        flush();
        
        // 7. Stream from provider
        $fullResponse = '';
        $tokenCount = 0;
        $startTime = microtime(true);
        
        $metrics = $provider->stream(
            $validated['prompt'],
            $context,
            $params,
            function ($chunk) use (&$fullResponse, &$tokenCount, $startTime) {
                // Emit chunk event
                echo "data: " . json_encode([
                    'type' => 'chunk',
                    'content' => $chunk,
                ]) . "\n\n";
                
                if (ob_get_level()) ob_flush();
                flush();
                
                $fullResponse .= $chunk;
                $tokenCount++;
            }
        );
        
        // 8. Create assistant message
        $assistantMessage = LLMConversationMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $fullResponse,
            'tokens' => $metrics['completion_tokens'] ?? $tokenCount,
            'metadata' => [
                'provider' => $configuration->provider,
                'model' => $configuration->model,
                'cost_usd' => $metrics['cost_usd'] ?? 0,
            ],
        ]);
        
        // 9. Emit done event
        echo "data: " . json_encode([
            'type' => 'done',
            'assistant_message_id' => $assistantMessage->id,
            'full_response' => $fullResponse,
            'metrics' => $metrics,
        ]) . "\n\n";
        
        if (ob_get_level()) ob_flush();
        flush();
        
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
}
```

### Provider: stream() Implementation

**Interface:**
```php
interface LLMProviderInterface
{
    /**
     * Stream response with callback for each chunk
     * 
     * @param string $prompt User prompt
     * @param array $context Previous messages
     * @param array $parameters Temperature, max_tokens, etc.
     * @param callable $callback Function called for each chunk
     * @return array Metrics (prompt_tokens, completion_tokens, cost_usd, etc.)
     */
    public function stream(string $prompt, array $context, array $parameters, callable $callback): array;
}
```

**Example: OllamaProvider**
```php
public function stream(string $prompt, array $context, array $parameters, callable $callback): array
{
    $messages = array_merge($context, [['role' => 'user', 'content' => $prompt]]);
    
    $response = Http::timeout(120)
        ->withHeaders(['Accept' => 'application/x-ndjson'])
        ->post("{$this->endpoint}/api/chat", [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => true,
            'options' => [
                'temperature' => $parameters['temperature'] ?? 0.7,
                'num_predict' => $parameters['max_tokens'] ?? 2000,
            ],
        ]);
    
    $fullResponse = '';
    $tokenCount = 0;
    $startTime = microtime(true);
    $firstChunkTime = null;
    
    // Stream processing (NDJSON format)
    $buffer = '';
    foreach (str_split($response->body()) as $char) {
        $buffer .= $char;
        
        if ($char === "\n") {
            $line = trim($buffer);
            $buffer = '';
            
            if (empty($line)) continue;
            
            $chunk = json_decode($line, true);
            
            if (isset($chunk['message']['content'])) {
                $content = $chunk['message']['content'];
                $fullResponse .= $content;
                $tokenCount++;
                
                if ($firstChunkTime === null) {
                    $firstChunkTime = microtime(true);
                }
                
                // Call callback with chunk
                call_user_func($callback, $content);
            }
        }
    }
    
    $endTime = microtime(true);
    
    return [
        'prompt_tokens' => count($messages) * 10, // Estimate
        'completion_tokens' => $tokenCount,
        'total_tokens' => (count($messages) * 10) + $tokenCount,
        'cost_usd' => 0, // Ollama is free
        'processing_time' => $endTime - $startTime,
        'time_to_first_chunk' => $firstChunkTime ? ($firstChunkTime - $startTime) : 0,
    ];
}
```

---

## Monitor System Integration

### Console Logs

El Monitor Console recibe logs en tiempo real durante streaming:

```javascript
// Stream started
window.LLMMonitor?.log(monitorId, 'info', '🚀 Stream started');

// Metadata received
window.LLMMonitor?.log(monitorId, 'info', `📊 Input: ${inputTokens} tokens, Context: ${contextSize} messages`);

// First chunk received
window.LLMMonitor?.log(monitorId, 'success', `⚡ First chunk received (${ttfc}ms)`);

// Stream completed
window.LLMMonitor?.log(monitorId, 'success', 
    `✅ Stream completed: ${totalTokens} tokens, $${cost.toFixed(5)}, ${time.toFixed(2)}s`
);

// Stream stopped
window.LLMMonitor?.log(monitorId, 'warning', '⏹️ Stream stopped by user');

// Error occurred
window.LLMMonitor?.log(monitorId, 'error', `🚨 ${errorCode}: ${errorMessage}`);
```

### Request Inspector

El Request Inspector tab muestra datos completos del request:

**Sections:**
1. **Metadata** - Provider, model, endpoint, timestamp
2. **Parameters** - Temperature, max_tokens, top_p, context_limit
3. **System Instructions** - System prompt configurado
4. **Context Messages** - Tabla con últimos N mensajes usados como contexto
5. **Current Prompt** - Prompt actual del usuario
6. **Full Request Body** - JSON completo enviado al provider

**Population:**
- Evento `request_data` recibido vía SSE
- Spinners mostrados mientras no hay datos
- ~50ms después del inicio del stream, datos completos disponibles

---

## Error Handling

### Network Errors

**Problema:** EventSource pierde conexión.

**Detección:**
```javascript
eventSource.onerror = (error) => {
    console.error('SSE connection error:', error);
    
    // Check if was a network error vs manual close
    if (eventSource.readyState === EventSource.CLOSED) {
        // Connection closed (could be network issue)
        window.LLMMonitor?.log(monitorId, 'error', 
            '🔌 Connection lost. Please check your network.'
        );
    }
    
    eventSource.close();
};
```

**Recovery:** EventSource auto-reconecta si el servidor sigue disponible.

### Provider Offline

**Problema:** Ollama/OpenAI no responde.

**Detección:** Backend catch exception en `$provider->stream()`.

**Respuesta:**
```php
try {
    $metrics = $provider->stream($prompt, $context, $params, $callback);
} catch (\Exception $e) {
    // Emit error event
    echo "data: " . json_encode([
        'type' => 'error',
        'message' => 'Provider offline: ' . $e->getMessage(),
        'code' => 'PROVIDER_OFFLINE',
        'recoverable' => false,
    ]) . "\n\n";
    
    if (ob_get_level()) ob_flush();
    flush();
    
    return;
}
```

### Timeout

**Problema:** Request tarda más de 120s.

**Prevención:**
```php
// In provider
Http::timeout(120)->post(...);
```

**Detección Frontend:**
```javascript
// Set timeout on EventSource
setTimeout(() => {
    if (eventSource && eventSource.readyState !== EventSource.CLOSED) {
        window.LLMMonitor?.log(monitorId, 'error', '⏱️ Request timeout (120s)');
        eventSource.close();
        sendBtn.disabled = false;
    }
}, 120000); // 120 seconds
```

### Rate Limits

**Problema:** OpenAI/OpenRouter rate limit excedido.

**Detección:** Provider detecta HTTP 429 response.

**Respuesta:**
```json
{
  "type": "error",
  "message": "Rate limit exceeded. Please wait 60 seconds.",
  "code": "RATE_LIMIT_EXCEEDED",
  "recoverable": true,
  "retry_after": 60
}
```

---

## Performance & Optimization

### Buffer Flushing

**Problema:** Nginx/Apache buffer output → chunks no llegan inmediatamente.

**Solución:**
```php
// Force flush after each echo
echo "data: {$json}\n\n";

if (ob_get_level()) ob_flush();
flush();
```

**Nginx Config:**
```nginx
location /admin/llm/quick-chat/stream {
    proxy_buffering off;
    proxy_cache off;
    proxy_set_header X-Accel-Buffering no;
}
```

### Memory Usage

**Problema:** Large responses pueden consumir mucha memoria.

**Solución:**
```php
// Don't store full response in variable if not needed
$fullResponse = '';

foreach ($chunks as $chunk) {
    // Emit chunk immediately
    echo "data: " . json_encode(['type' => 'chunk', 'content' => $chunk]) . "\n\n";
    flush();
    
    // Only concatenate for final save to DB
    $fullResponse .= $chunk;
}
```

### Connection Limits

**Problema:** HTTP/1.1 limita 6 conexiones por dominio.

**Impacto:** Si usuario abre múltiples Quick Chats, pueden bloquearse.

**Solución:**
1. Usar HTTP/2 (no tiene límite de 6)
2. Cerrar EventSource inmediatamente después de `done` event
3. Avisar al usuario si tiene múltiples streams activos

### Token Estimation

**Problema:** Tokens estimados antes de streaming pueden ser inexactos.

**Solución:**
```php
// Use rough estimate in metadata event
$estimatedInputTokens = str_word_count($prompt) + 
                       array_sum(array_map(fn($m) => str_word_count($m['content']), $context));

// Real tokens in done event (from provider metrics)
$actualTokens = $metrics['prompt_tokens'];
```

---

## Testing

### Unit Tests

Ver: `tests/Unit/Services/LLMStreamLoggerTest.php`

### Feature Tests

Ver: `tests/Feature/StreamingTest.php` (14 tests)

**Test Cases:**
- ✅ SSE headers correctos
- ✅ Eventos en formato correcto
- ✅ Metadata antes de chunks
- ✅ Request_data event emitido
- ✅ Chunks enviados
- ✅ Done event al finalizar
- ✅ Mensajes guardados en DB
- ✅ Errors manejados
- ✅ Context limit funciona
- ✅ Validación
- ✅ Unauthorized bloqueado
- ✅ Stop streaming
- ✅ Múltiples sesiones
- ✅ Session activity actualizada

### Manual Testing

**Checklist:**
```bash
# 1. Start Ollama
ollama serve

# 2. Load model
ollama pull llama2

# 3. Test streaming
# - Open Quick Chat
# - Send message
# - Verify chunks appear in real-time
# - Check monitor console logs
# - Check request inspector data
# - Stop stream mid-way
# - Verify message deleted
# - Verify input restored

# 4. Test errors
# - Stop Ollama
# - Send message
# - Verify error event received
# - Verify error message shown

# 5. Test concurrent streams
# - Open 2 Quick Chat tabs
# - Send message in both
# - Verify no interference
```

---

## Troubleshooting

### Chunks No Aparecen

**Síntomas:** "Thinking..." spinner permanece, no chunks.

**Diagnóstico:**
```javascript
// Check EventSource state
console.log('EventSource state:', eventSource.readyState);
// 0 = CONNECTING, 1 = OPEN, 2 = CLOSED

// Check network tab
// DevTools → Network → Filter: EventStream
// Verify connection established and data flowing
```

**Causas comunes:**
1. ❌ Nginx buffering activo → Agregar `X-Accel-Buffering: no`
2. ❌ PHP output buffering → Usar `ob_flush()` y `flush()`
3. ❌ Provider offline → Verificar Ollama/OpenAI accessible
4. ❌ CORS issue → Verificar dominio correcto

### EventSource Desconecta Inmediatamente

**Síntomas:** `onerror` llamado sin recibir datos.

**Diagnóstico:**
```javascript
eventSource.onerror = (error) => {
    console.error('EventSource error:', error);
    console.log('ReadyState:', eventSource.readyState);
    
    // Check HTTP status in Network tab
};
```

**Causas comunes:**
1. ❌ Ruta incorrecta (404) → Verificar `route('admin.llm.quick-chat.stream')`
2. ❌ Validación falla (422) → Verificar parámetros
3. ❌ Unauthorized (403) → Verificar autenticación
4. ❌ Server error (500) → Revisar Laravel logs

### Request Inspector No Se Pobla

**Síntomas:** Spinners permanecen, datos no aparecen.

**Diagnóstico:**
```javascript
// Check if event listener registered
eventSource.addEventListener('request_data', (event) => {
    console.log('request_data received:', event.data);
});

// Check if event emitted in backend
// LLMQuickChatController.php:
\Log::info('[QuickChat] SSE request_data emitted', [...]);
```

**Causas comunes:**
1. ❌ Event listener no registrado → Verificar `event-handlers.blade.php`
2. ❌ Event name incorrecto → Debe ser `event: request_data\n`
3. ❌ JSON inválido → Verificar `json_encode()` sin errores

### Mensajes Duplicados

**Síntomas:** Assistant message aparece 2 veces.

**Causas comunes:**
1. ❌ Multiple EventSource instances → Cerrar anterior antes de crear nuevo
2. ❌ Event listener registrado múltiples veces → Usar `removeEventListener()` antes de re-registrar

**Solución:**
```javascript
// Close previous EventSource
if (eventSource) {
    eventSource.close();
    eventSource = null;
}

// Create new one
eventSource = new EventSource(url);
```

---

## Best Practices

### 1. Siempre Cerrar EventSource

```javascript
// After done event
if (data.type === 'done') {
    eventSource.close();
    eventSource = null;
}

// After error event
if (data.type === 'error') {
    eventSource.close();
    eventSource = null;
}

// On page unload
window.addEventListener('beforeunload', () => {
    if (eventSource) {
        eventSource.close();
    }
});
```

### 2. Track State Correctamente

```javascript
let eventSource = null;
let userMessageId = null;
let savedUserPrompt = '';
let chunkCount = 0;
let fullResponse = '';
let assistantBubble = null;

// Reset on each new stream
function resetState() {
    userMessageId = null;
    savedUserPrompt = '';
    chunkCount = 0;
    fullResponse = '';
    assistantBubble = null;
}
```

### 3. Flush Inmediatamente (Backend)

```php
echo "data: {$json}\n\n";

// SIEMPRE flush después de echo
if (ob_get_level()) ob_flush();
flush();
```

### 4. Manejo Robusto de Errores

```php
try {
    $metrics = $provider->stream($prompt, $context, $params, $callback);
} catch (\Exception $e) {
    // Log error
    \Log::error('[Streaming] Provider error: ' . $e->getMessage());
    
    // Emit error event
    echo "data: " . json_encode([
        'type' => 'error',
        'message' => $e->getMessage(),
        'code' => 'PROVIDER_ERROR',
    ]) . "\n\n";
    
    flush();
    return;
}
```

### 5. Monitor Console Integration

```javascript
// Log all important events
window.LLMMonitor?.log(monitorId, 'info', '🚀 Stream started');
window.LLMMonitor?.log(monitorId, 'success', '✅ Stream completed');
window.LLMMonitor?.log(monitorId, 'error', '🚨 Error occurred');
```

### 6. Syntax Highlighting

```javascript
// After rendering Markdown with chunks
if (typeof Prism !== 'undefined') {
    Prism.highlightAllUnder(contentDiv);
}
```

### 7. Progressive Enhancement

```javascript
// Check if EventSource supported
if (typeof EventSource === 'undefined') {
    alert('Your browser does not support Server-Sent Events. Please use a modern browser.');
    return;
}

// Fallback to polling if needed (not implemented)
```

---

## Referencias

### Documentos Relacionados

- **Architecture:** `docs/architecture/STREAMING-ARCHITECTURE.md`
- **Providers:** `docs/providers/OLLAMA-PROVIDER.md`, `docs/providers/OPENAI-PROVIDER.md`
- **Monitor:** `docs/components/MONITOR-SYSTEM.md`
- **Testing:** `tests/Feature/StreamingTest.php`

### Archivos Clave

**Backend:**
- `src/Http/Controllers/Admin/LLMQuickChatController.php::stream()`
- `src/Services/LLMStreamLogger.php`
- `src/Services/Providers/OllamaProvider.php::stream()`
- `src/Services/Providers/OpenAIProvider.php::stream()`
- `src/Services/Providers/OpenRouterProvider.php::stream()`

**Frontend:**
- `resources/views/components/chat/partials/scripts/event-handlers.blade.php`
- `resources/views/components/chat/shared/monitor-console.blade.php`
- `resources/views/components/chat/shared/monitor-request-inspector.blade.php`

### External Resources

- [MDN: Server-Sent Events](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events)
- [HTML5 SSE Spec](https://html.spec.whatwg.org/multipage/server-sent-events.html)
- [Laravel Response::stream()](https://laravel.com/docs/11.x/responses#streamed-responses)

---

**Autor:** Claude (Claude Sonnet 4.5, Anthropic)  
**Fecha:** 9 de diciembre de 2025  
**Versión:** 1.0.7
