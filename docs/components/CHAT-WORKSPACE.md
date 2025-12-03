# Chat Workspace Component - Guía de Uso

**Versión:** 2.1  
**Estado:** ✅ Producción  
**Última actualización:** 3 diciembre 2025

---

## 📖 Índice

1. [Descripción General](#descripción-general)
2. [Instalación](#instalación)
3. [Uso Básico](#uso-básico)
4. [Propiedades (Props)](#propiedades-props)
5. [Layouts Disponibles](#layouts-disponibles)
6. [API JavaScript](#api-javascript)
7. [Personalización](#personalización)
8. [Ejemplos Completos](#ejemplos-completos)
9. [Troubleshooting](#troubleshooting)
10. [Performance](#performance)

---

## Descripción General

El **Chat Workspace Component** es un componente Blade optimizado para interfaces de chat LLM con soporte para:

- ✅ **Dual Layout System:** Sidebar (vertical) y Split-Horizontal (horizontal)
- ✅ **Monitor Integrado:** Métricas en tiempo real, historial de actividad, console logs
- ✅ **Streaming Support:** Compatible con Server-Sent Events (SSE)
- ✅ **Alpine.js Reactive:** Componentes reactivos sin Vue/React
- ✅ **LocalStorage Persistence:** Guarda preferencias del usuario
- ✅ **Mobile Responsive:** Adaptativo a pantallas pequeñas
- ✅ **Code Partitioning:** Carga condicional para máxima performance

### Arquitectura

```
ChatWorkspace Component
├── Layouts (intercambiables)
│   ├── Sidebar Layout (60/40 vertical)
│   └── Split-Horizontal Layout (70/30 horizontal resizable)
├── Monitor Components
│   ├── Full Monitor (métricas + historial + consola)
│   └── Console Only (solo consola para split)
└── Alpine.js Components
    ├── chatWorkspace (global)
    ├── splitResizer (condicional)
    └── window.LLMMonitor API (global)
```

---

## Instalación

### Requisitos

- Laravel 11+
- Alpine.js 3.x
- Bootstrap 5.x
- LLM Manager Extension instalada

### Registro del Componente

El componente ya está registrado en `LLMManagerServiceProvider.php`:

```php
use Bithoven\LLMManager\View\Components\Chat\ChatWorkspace;

Blade::component('llm-manager-chat-workspace', ChatWorkspace::class);
```

---

## Uso Básico

### Ejemplo Mínimo

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
/>
```

### Ejemplo con Monitor

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :show-monitor="true"
    :monitor-open="true"
    monitor-layout="split-horizontal"
/>
```

---

## Propiedades (Props)

### Props Requeridas

| Prop | Tipo | Descripción |
|------|------|-------------|
| `session` | `LLMConversationSession\|null` | Sesión de conversación actual |
| `configurations` | `Collection` | Configuraciones LLM disponibles |

### Props Opcionales

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `show-monitor` | `bool` | `true` | Mostrar/ocultar monitor |
| `monitor-open` | `bool` | `true` | Estado inicial del monitor |
| `monitor-layout` | `string` | `'sidebar'` | Layout del monitor: `'sidebar'` o `'split-horizontal'` |

### Props Generadas Automáticamente

Estas props se generan en la clase `ChatWorkspace.php`:

| Prop | Tipo | Descripción |
|------|------|-------------|
| `messages` | `Collection` | Mensajes de la sesión actual |
| `monitorId` | `string` | ID único del monitor (basado en session) |

---

## Layouts Disponibles

### 1. Sidebar Layout (Vertical)

Monitor fijo a la derecha (40% ancho en desktop).

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    monitor-layout="sidebar"
/>
```

**Características:**
- Chat: 60% izquierda
- Monitor: 40% derecha (fijo)
- Colapsa a 100% en móvil
- Monitor toggle cierra completamente la columna

**Cuándo usar:**
- Interfaces con espacio horizontal abundante
- Cuando el monitor debe estar siempre visible
- Pantallas anchas (>1400px)

---

### 2. Split-Horizontal Layout (Horizontal)

Monitor dividido horizontalmente con resize drag.

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    monitor-layout="split-horizontal"
/>
```

**Características:**
- Chat: 70% superior (ajustable)
- Monitor console: 30% inferior (ajustable)
- Drag & drop para redimensionar (20%-80%)
- Header y footer siempre visibles
- Tamaños persisten en localStorage

**Cuándo usar:**
- Interfaces con espacio vertical abundante
- Cuando se necesita más espacio horizontal para mensajes
- Desarrollo/debugging (console logs importantes)
- Pantallas verticales

---

## API JavaScript

### 1. Alpine.js: chatWorkspace Component

**Ubicación:** `partials/scripts/chat-workspace.blade.php`

#### Propiedades Reactivas

```javascript
{
    monitorOpen: boolean,      // Estado del monitor (abierto/cerrado)
    isMobile: boolean,         // Detección de móvil
    showMobileModal: boolean   // Modal en móvil
}
```

#### Métodos Públicos

```javascript
// Toggle monitor (abrir/cerrar)
toggleMonitor()

// Ejemplo de uso en HTML
<button @click="toggleMonitor()">Toggle Monitor</button>
```

#### Ejemplo de Extensión

```blade
<div x-data="chatWorkspace(true, true, 'sidebar')">
    {{-- Acceso a propiedades --}}
    <div x-show="monitorOpen">Monitor abierto</div>
    
    {{-- Llamar métodos --}}
    <button @click="toggleMonitor()">Toggle</button>
</div>
```

---

### 2. Alpine.js: splitResizer Component

**Ubicación:** `partials/scripts/split-resizer.blade.php`  
**Solo cargado cuando:** `monitor-layout="split-horizontal"`

#### Propiedades Reactivas

```javascript
{
    chatHeight: number,        // Altura chat (%) - default 70
    monitorHeight: number,     // Altura monitor (%) - default 30
    isResizing: boolean,       // Drag activo
    startY: number,            // Posición Y inicial del drag
    startChatHeight: number    // Altura inicial al drag
}
```

#### Métodos Públicos

```javascript
// Iniciar drag
startResize(event)

// Durante drag
resize(event)

// Finalizar drag
stopResize()

// Resetear tamaños
resetSizes()
```

#### Constraints

- Altura mínima chat: **20%**
- Altura máxima chat: **80%**
- Valores persisten en `localStorage` key: `llm_chat_split_sizes`

#### Ejemplo de Personalización

```javascript
// Cambiar tamaños por defecto
document.addEventListener('alpine:init', () => {
    Alpine.data('splitResizer', () => ({
        chatHeight: 60,      // 60% chat
        monitorHeight: 40,   // 40% monitor
        // ... resto de métodos
    }))
})
```

---

### 3. JavaScript: window.LLMMonitor API

**Ubicación:** `partials/scripts/monitor-api.blade.php`  
**Scope:** Global (disponible en todo el documento)

#### Propiedades

```javascript
window.LLMMonitor = {
    currentMetrics: {
        tokens: number,
        chunks: number,
        cost: number,
        duration: number,
        startTime: number|null
    },
    history: Array<Activity>,
    durationInterval: number|null
}
```

#### Métodos Públicos

```javascript
// Inicializar monitor (automático en DOMContentLoaded)
window.LLMMonitor.init()

// Iniciar tracking de stream
window.LLMMonitor.start()

// Trackear chunk recibido
window.LLMMonitor.trackChunk(chunk, tokens = 0)

// Stream completado
window.LLMMonitor.complete(provider, model)

// Error en stream
window.LLMMonitor.error(message)

// Log a consola
window.LLMMonitor.log(message, type = 'info')
// types: 'info', 'success', 'error', 'warning'

// Refrescar vista
window.LLMMonitor.refresh()

// Limpiar datos
window.LLMMonitor.clear()

// Resetear métricas actuales
window.LLMMonitor.reset()
```

#### Ejemplo de Uso con Streaming

```javascript
// Al iniciar stream
window.LLMMonitor.start();

// Por cada chunk recibido
eventSource.onmessage = (event) => {
    const chunk = event.data;
    const tokens = calculateTokens(chunk);
    window.LLMMonitor.trackChunk(chunk, tokens);
};

// Al completar
eventSource.addEventListener('done', () => {
    window.LLMMonitor.complete('OpenAI', 'gpt-4');
});

// Si hay error
eventSource.onerror = () => {
    window.LLMMonitor.error('Stream connection failed');
};
```

#### Activity History Structure

```javascript
{
    timestamp: "2025-12-03T07:00:00.000Z",
    provider: "OpenAI",
    model: "gpt-4",
    tokens: 1250,
    cost: 0.0025,
    duration: 15  // segundos
}
```

---

## Personalización

### 1. Custom Styles

```blade
{{-- Sobrescribir estilos del componente --}}
@push('styles')
<style>
    /* Chat messages customization */
    .llm-chat-workspace .message-content {
        font-size: 15px;
        line-height: 1.8;
    }
    
    /* Monitor console customization */
    .monitor-console-dark {
        background-color: #0d1117 !important;
        font-size: 14px !important;
    }
    
    /* Split resizer customization */
    .split-resizer {
        height: 10px !important;
        background: linear-gradient(to bottom, #2563eb, #1d4ed8) !important;
    }
</style>
@endpush
```

### 2. Custom Scripts

```blade
@push('scripts')
<script>
    // Extender window.LLMMonitor
    const originalLog = window.LLMMonitor.log;
    window.LLMMonitor.log = function(message, type) {
        // Custom logging logic
        console.log(`[LLM Monitor] ${message}`);
        
        // Call original
        originalLog.call(this, message, type);
    };
    
    // Custom event listeners
    document.addEventListener('DOMContentLoaded', () => {
        // Listen to monitor toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-monitor-toggle]')) {
                console.log('Monitor toggled');
            }
        });
    });
</script>
@endpush
```

### 3. Custom Layouts

Para crear un layout personalizado:

1. Crear archivo en `resources/views/components/chat/layouts/my-custom-layout.blade.php`
2. Usar partials existentes:

```blade
{{-- my-custom-layout.blade.php --}}
<div class="my-custom-layout">
    {{-- Chat section --}}
    <div class="chat-section">
        @include('llm-manager::components.chat.partials.chat-card')
    </div>
    
    {{-- Monitor section --}}
    @if($showMonitor && $monitorOpen)
        <div class="monitor-section">
            @include('llm-manager::components.chat.shared.monitor')
        </div>
    @endif
</div>

{{-- Cargar scripts necesarios --}}
@include('llm-manager::components.chat.partials.scripts.chat-workspace')
@include('llm-manager::components.chat.partials.scripts.monitor-api')
```

3. Modificar `chat-workspace.blade.php` para incluir nuevo layout:

```blade
@if($monitorLayout === 'my-custom')
    @include('llm-manager::components.chat.layouts.my-custom-layout')
@elseif($monitorLayout === 'split-horizontal')
    @include('llm-manager::components.chat.layouts.split-horizontal-layout')
@else
    @include('llm-manager::components.chat.layouts.sidebar-layout')
@endif
```

---

## Ejemplos Completos

### Ejemplo 1: Chat Simple (Sin Monitor)

```blade
{{-- Controller --}}
public function index(Request $request)
{
    $session = LLMConversationSession::find($request->session_id);
    $configurations = LLMConfiguration::active()->get();
    
    return view('chat.simple', compact('session', 'configurations'));
}

{{-- Vista: chat/simple.blade.php --}}
<x-default-layout>
    @section('title', 'Simple Chat')
    
    <div class="container-fluid">
        <x-llm-manager-chat-workspace
            :session="$session"
            :configurations="$configurations"
            :show-monitor="false"
        />
    </div>
</x-default-layout>
```

---

### Ejemplo 2: Chat con Monitor Sidebar

```blade
{{-- Vista: chat/with-monitor.blade.php --}}
<x-default-layout>
    @section('title', 'Chat with Monitor')
    
    <div class="container-fluid">
        <x-llm-manager-chat-workspace
            :session="$session"
            :configurations="$configurations"
            :show-monitor="true"
            :monitor-open="true"
            monitor-layout="sidebar"
        />
    </div>
</x-default-layout>
```

---

### Ejemplo 3: Chat con Split-Horizontal + Custom Logging

```blade
<x-default-layout>
    @section('title', 'Development Chat')
    
    <div class="container-fluid">
        <x-llm-manager-chat-workspace
            :session="$session"
            :configurations="$configurations"
            :show-monitor="true"
            :monitor-open="true"
            monitor-layout="split-horizontal"
        />
    </div>
    
    @push('scripts')
    <script>
        // Custom logging para desarrollo
        document.addEventListener('DOMContentLoaded', () => {
            // Intercept streaming responses
            const originalFetch = window.fetch;
            window.fetch = async function(...args) {
                window.LLMMonitor.log('API Request: ' + args[0], 'info');
                
                try {
                    const response = await originalFetch.apply(this, args);
                    window.LLMMonitor.log('API Response: ' + response.status, 'success');
                    return response;
                } catch (error) {
                    window.LLMMonitor.error('API Error: ' + error.message);
                    throw error;
                }
            };
        });
    </script>
    @endpush
</x-default-layout>
```

---

### Ejemplo 4: Chat con Tamaños Personalizados

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    monitor-layout="split-horizontal"
/>

@push('scripts')
<script>
    // Cambiar tamaños default del split
    document.addEventListener('alpine:init', () => {
        const originalData = Alpine.data('splitResizer');
        
        Alpine.data('splitResizer', function() {
            const data = originalData();
            return {
                ...data,
                chatHeight: 80,      // 80% para chat
                monitorHeight: 20,   // 20% para monitor
            };
        });
    });
</script>
@endpush
```

---

## Troubleshooting

### Problema 1: Monitor no aparece

**Síntoma:** Monitor no se muestra aunque `show-monitor="true"`

**Soluciones:**
```blade
{{-- Verificar que session existe --}}
@if($session)
    <x-llm-manager-chat-workspace :session="$session" ... />
@else
    <p>No hay sesión activa</p>
@endif

{{-- Verificar cache de Laravel --}}
```bash
php artisan view:clear
php artisan optimize:clear
```

---

### Problema 2: Split resizer no funciona

**Síntoma:** No se puede arrastrar el separador horizontal

**Soluciones:**

1. Verificar que Alpine.js está cargado:
```javascript
// En consola del navegador
console.log(typeof Alpine); // debe ser 'object'
```

2. Verificar que el layout es `split-horizontal`:
```blade
monitor-layout="split-horizontal"  {{-- Correcto --}}
monitor-layout="sidebar"           {{-- No carga resizer --}}
```

3. Limpiar localStorage:
```javascript
localStorage.removeItem('llm_chat_split_sizes');
location.reload();
```

---

### Problema 3: window.LLMMonitor no definido

**Síntoma:** `Uncaught ReferenceError: LLMMonitor is not defined`

**Causa:** Scripts no cargados o ejecutados antes de DOMContentLoaded

**Solución:**
```javascript
// Siempre usar dentro de DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    window.LLMMonitor.start();
});

// O verificar existencia
if (typeof window.LLMMonitor !== 'undefined') {
    window.LLMMonitor.log('Test', 'info');
}
```

---

### Problema 4: Monitor toggle no cierra columna en sidebar

**Síntoma:** Monitor se oculta pero el espacio permanece

**Causa:** Bug antiguo (v2.0.0), resuelto en v2.0.1

**Solución:**

Verificar que `sidebar-layout.blade.php` usa `:class` binding:

```blade
{{-- ✅ Correcto (v2.0.1+) --}}
<div :class="monitorOpen ? 'col-lg-4 d-none d-lg-block' : 'd-none'">

{{-- ❌ Incorrecto (v2.0.0) --}}
<div x-show="monitorOpen" class="col-lg-4 d-none d-lg-block">
```

Actualizar extensión a v2.1+:
```bash
cd vendor/bithoven/llm-manager
git pull origin main
```

---

### Problema 5: Estilos no aplicados

**Síntoma:** Componente sin estilos o estilos rotos

**Soluciones:**

1. Verificar que Bootstrap 5 está cargado:
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
```

2. Verificar publicación de assets:
```bash
php artisan vendor:publish --tag=llm-manager-assets --force
```

3. Limpiar cache de Blade:
```bash
php artisan view:clear
```

---

## 📡 Custom Events API

### Overview

El componente ChatWorkspace emite **eventos custom JavaScript** que permiten integraciones externas sin modificar el código del componente. Cualquier aplicación puede escuchar estos eventos para reaccionar a cambios en el chat, streaming, o monitor.

**Beneficios:**
- ✅ Desacoplamiento total - el componente no conoce a los listeners
- ✅ Extensibilidad - agrega funcionalidad sin tocar el componente
- ✅ Testing simplificado - verifica que se emiten los eventos correctos
- ✅ Integraciones de terceros - plugins, analytics, dashboards externos

---

### Event Structure

Todos los eventos siguen este formato:

```javascript
// Alpine.js events (dentro del componente)
this.$dispatch('event-name', {
    // detail object
    property1: value1,
    property2: value2
});

// Vanilla JS events (window.LLMMonitor)
window.dispatchEvent(new CustomEvent('event-name', {
    detail: {
        property1: value1,
        property2: value2
    }
}));
```

**Escuchar eventos:**

```javascript
// Alpine events (desde el elemento del componente hacia arriba)
document.addEventListener('event-name', (event) => {
    console.log(event.detail);
});

// Window events (globales)
window.addEventListener('event-name', (event) => {
    console.log(event.detail);
});
```

---

### Message Events

#### `llm-message-sent`

Emitido cuando el usuario envía un mensaje.

**Detail:**
```javascript
{
    content: string,        // Texto del mensaje
    sessionId: number,      // ID de la sesión
    timestamp: number       // Unix timestamp (ms)
}
```

**Ejemplo:**
```javascript
document.addEventListener('llm-message-sent', (event) => {
    console.log('Usuario envió:', event.detail.content);
    
    // Analytics
    analytics.track('Message Sent', {
        sessionId: event.detail.sessionId,
        length: event.detail.content.length
    });
});
```

---

#### `llm-response-received`

Emitido cuando se recibe la respuesta completa del LLM.

**Detail:**
```javascript
{
    content: string,        // Respuesta completa
    sessionId: number,      // ID de la sesión
    provider: string,       // 'OpenAI', 'Anthropic', etc.
    model: string,          // 'gpt-4', 'claude-3', etc.
    tokens: number,         // Total de tokens
    duration: number,       // Duración en milisegundos
    cost: number           // Costo estimado
}
```

**Ejemplo:**
```javascript
document.addEventListener('llm-response-received', (event) => {
    const { provider, tokens, duration, cost } = event.detail;
    
    // Actualizar dashboard externo
    updateDashboardStats({
        provider,
        tokens,
        avgResponseTime: duration,
        totalCost: cost
    });
    
    // Notificación si respuesta larga
    if (duration > 30000) { // >30 segundos
        showNotification(`Respuesta tardó ${duration/1000}s`);
    }
});
```

---

### Streaming Events

#### `llm-streaming-started`

Emitido cuando comienza el streaming de una respuesta.

**Detail:**
```javascript
{
    sessionId: number,      // ID de la sesión
    provider: string,       // Provider LLM
    model: string,          // Modelo usado
    timestamp: number       // Unix timestamp
}
```

**Ejemplo:**
```javascript
window.addEventListener('llm-streaming-started', (event) => {
    console.log('Streaming iniciado:', event.detail);
    
    // Mostrar indicador de carga global
    showGlobalLoadingIndicator();
    
    // Deshabilitar envío de nuevos mensajes
    disableChatInput();
});
```

---

#### `llm-streaming-chunk`

Emitido por cada chunk recibido durante el streaming.

**Detail:**
```javascript
{
    chunk: string,          // Texto del chunk
    tokens: number,         // Tokens en este chunk
    totalTokens: number,    // Tokens acumulados
    totalChunks: number,    // Chunks acumulados
    sessionId: number       // ID de la sesión
}
```

**Ejemplo:**
```javascript
window.addEventListener('llm-streaming-chunk', (event) => {
    const { totalTokens, totalChunks } = event.detail;
    
    // Actualizar contador en tiempo real
    updateTokenCounter(totalTokens);
    
    // Progress bar
    updateProgressBar(totalChunks);
});
```

---

#### `llm-streaming-completed`

Emitido cuando el streaming termina exitosamente.

**Detail:**
```javascript
{
    sessionId: number,      // ID de la sesión
    provider: string,       // Provider usado
    model: string,          // Modelo usado
    totalTokens: number,    // Total de tokens
    totalChunks: number,    // Total de chunks
    duration: number,       // Duración total (ms)
    cost: number           // Costo total
}
```

**Ejemplo:**
```javascript
window.addEventListener('llm-streaming-completed', (event) => {
    const { totalTokens, duration, cost } = event.detail;
    
    // Ocultar indicador de carga
    hideGlobalLoadingIndicator();
    
    // Habilitar input
    enableChatInput();
    
    // Notificación
    showNotification(`Completado: ${totalTokens} tokens en ${duration/1000}s ($${cost.toFixed(4)})`);
    
    // Auto-save
    saveConversation(event.detail.sessionId);
});
```

---

#### `llm-streaming-error`

Emitido cuando ocurre un error durante el streaming.

**Detail:**
```javascript
{
    sessionId: number,      // ID de la sesión
    error: string,          // Mensaje de error
    code: string,           // Código de error
    timestamp: number       // Unix timestamp
}
```

**Ejemplo:**
```javascript
window.addEventListener('llm-streaming-error', (event) => {
    const { error, code } = event.detail;
    
    console.error('Streaming error:', error);
    
    // Mostrar error al usuario
    showErrorNotification(error);
    
    // Log para analytics
    logError({
        type: 'streaming_error',
        code: code,
        message: error
    });
    
    // Reintentar automáticamente
    if (code === 'NETWORK_ERROR') {
        retryStreaming(event.detail.sessionId);
    }
});
```

---

### Monitor Events

#### `llm-monitor-toggled`

Emitido cuando el usuario abre/cierra el monitor.

**Detail:**
```javascript
{
    isOpen: boolean,        // Estado del monitor
    layout: string,         // 'sidebar' o 'split-horizontal'
    sessionId: number       // ID de la sesión
}
```

**Ejemplo:**
```javascript
document.addEventListener('llm-monitor-toggled', (event) => {
    const { isOpen, layout } = event.detail;
    
    // Guardar preferencia de usuario
    saveUserPreference('monitor_open', isOpen);
    saveUserPreference('monitor_layout', layout);
    
    // Analytics
    analytics.track('Monitor Toggled', {
        isOpen,
        layout
    });
});
```

---

#### `llm-monitor-cleared`

Emitido cuando el usuario limpia los datos del monitor.

**Detail:**
```javascript
{
    sessionId: number,      // ID de la sesión
    itemsCleared: number,   // Cantidad de items eliminados
    timestamp: number       // Unix timestamp
}
```

**Ejemplo:**
```javascript
window.addEventListener('llm-monitor-cleared', (event) => {
    console.log('Monitor limpiado:', event.detail.itemsCleared, 'items');
    
    // Notificación
    showNotification(`Monitor limpiado (${event.detail.itemsCleared} items)`);
});
```

---

#### `llm-layout-changed`

Emitido cuando cambia el layout del monitor (sidebar ↔ split-horizontal).

**Detail:**
```javascript
{
    oldLayout: string,      // Layout anterior
    newLayout: string,      // Layout nuevo
    sessionId: number       // ID de la sesión
}
```

**Ejemplo:**
```javascript
document.addEventListener('llm-layout-changed', (event) => {
    const { oldLayout, newLayout } = event.detail;
    
    console.log(`Layout cambiado: ${oldLayout} → ${newLayout}`);
    
    // Ajustar UI externa
    if (newLayout === 'split-horizontal') {
        adjustExternalUIForSplitMode();
    }
});
```

---

### Session Events

#### `llm-session-created`

Emitido cuando se crea una nueva sesión de conversación.

**Detail:**
```javascript
{
    sessionId: number,      // ID de la nueva sesión
    provider: string,       // Provider seleccionado
    model: string,          // Modelo seleccionado
    timestamp: number       // Unix timestamp
}
```

**Ejemplo:**
```javascript
document.addEventListener('llm-session-created', (event) => {
    const { sessionId, provider } = event.detail;
    
    // Actualizar UI externa
    updateSessionList();
    
    // Analytics
    analytics.track('Session Created', {
        sessionId,
        provider
    });
});
```

---

#### `llm-session-cleared`

Emitido cuando se limpia/elimina una sesión.

**Detail:**
```javascript
{
    sessionId: number,      // ID de la sesión eliminada
    messageCount: number,   // Cantidad de mensajes eliminados
    timestamp: number       // Unix timestamp
}
```

**Ejemplo:**
```javascript
document.addEventListener('llm-session-cleared', (event) => {
    console.log('Sesión eliminada:', event.detail.sessionId);
    
    // Actualizar lista de sesiones
    removeSessionFromList(event.detail.sessionId);
});
```

---

### Example: Complete Integration

```javascript
// analytics-integration.js
class LLMAnalytics {
    constructor() {
        this.initListeners();
    }
    
    initListeners() {
        // Track message activity
        document.addEventListener('llm-message-sent', (e) => {
            this.trackEvent('Message Sent', {
                sessionId: e.detail.sessionId,
                length: e.detail.content.length
            });
        });
        
        // Track streaming performance
        window.addEventListener('llm-streaming-completed', (e) => {
            this.trackEvent('Streaming Completed', {
                provider: e.detail.provider,
                tokens: e.detail.totalTokens,
                duration: e.detail.duration,
                cost: e.detail.cost
            });
        });
        
        // Track errors
        window.addEventListener('llm-streaming-error', (e) => {
            this.trackError('Streaming Error', {
                code: e.detail.code,
                message: e.detail.error
            });
        });
        
        // Track monitor usage
        document.addEventListener('llm-monitor-toggled', (e) => {
            this.trackEvent('Monitor Toggled', {
                isOpen: e.detail.isOpen,
                layout: e.detail.layout
            });
        });
    }
    
    trackEvent(name, properties) {
        // Send to analytics service
        if (typeof analytics !== 'undefined') {
            analytics.track(name, properties);
        }
    }
    
    trackError(name, properties) {
        // Send to error tracking service
        if (typeof Sentry !== 'undefined') {
            Sentry.captureMessage(name, {
                level: 'error',
                extra: properties
            });
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    new LLMAnalytics();
});
```

---

### Example: Auto-save Plugin

```javascript
// auto-save-plugin.js
class ChatAutoSave {
    constructor(intervalMs = 30000) { // 30 segundos
        this.interval = intervalMs;
        this.sessionId = null;
        this.hasChanges = false;
        this.initListeners();
        this.startAutoSave();
    }
    
    initListeners() {
        // Detectar cambios
        document.addEventListener('llm-message-sent', (e) => {
            this.sessionId = e.detail.sessionId;
            this.hasChanges = true;
        });
        
        document.addEventListener('llm-response-received', (e) => {
            this.sessionId = e.detail.sessionId;
            this.hasChanges = true;
        });
    }
    
    startAutoSave() {
        setInterval(() => {
            if (this.hasChanges && this.sessionId) {
                this.saveConversation();
            }
        }, this.interval);
    }
    
    async saveConversation() {
        try {
            await fetch(`/api/sessions/${this.sessionId}/save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            
            this.hasChanges = false;
            console.log('Conversation auto-saved');
        } catch (error) {
            console.error('Auto-save failed:', error);
        }
    }
}

// Initialize
new ChatAutoSave();
```

---

### Example: Real-time Dashboard

```javascript
// dashboard-integration.js
class LLMDashboard {
    constructor() {
        this.stats = {
            totalMessages: 0,
            totalTokens: 0,
            totalCost: 0,
            avgResponseTime: 0,
            errorCount: 0
        };
        
        this.initListeners();
        this.renderDashboard();
    }
    
    initListeners() {
        // Update stats on message sent
        document.addEventListener('llm-message-sent', () => {
            this.stats.totalMessages++;
            this.updateDashboard();
        });
        
        // Update stats on streaming completed
        window.addEventListener('llm-streaming-completed', (e) => {
            this.stats.totalTokens += e.detail.totalTokens;
            this.stats.totalCost += e.detail.cost;
            this.stats.avgResponseTime = (
                (this.stats.avgResponseTime * (this.stats.totalMessages - 1) + e.detail.duration) 
                / this.stats.totalMessages
            );
            this.updateDashboard();
        });
        
        // Track errors
        window.addEventListener('llm-streaming-error', () => {
            this.stats.errorCount++;
            this.updateDashboard();
        });
    }
    
    updateDashboard() {
        document.getElementById('total-messages').textContent = this.stats.totalMessages;
        document.getElementById('total-tokens').textContent = this.stats.totalTokens.toLocaleString();
        document.getElementById('total-cost').textContent = '$' + this.stats.totalCost.toFixed(4);
        document.getElementById('avg-response-time').textContent = (this.stats.avgResponseTime / 1000).toFixed(2) + 's';
        document.getElementById('error-count').textContent = this.stats.errorCount;
    }
    
    renderDashboard() {
        // Initial render
        this.updateDashboard();
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    new LLMDashboard();
});
```

---

### Best Practices

1. **Event Naming:** Todos los eventos usan prefijo `llm-` para evitar colisiones
2. **Detail Structure:** Siempre incluye `sessionId` para identificar la conversación
3. **Error Handling:** Listeners deben tener try-catch para evitar romper el flujo
4. **Performance:** No realizar operaciones pesadas en listeners de alta frecuencia (`llm-streaming-chunk`)
5. **Cleanup:** Remover listeners cuando ya no son necesarios

```javascript
// Ejemplo de cleanup
const listener = (e) => console.log(e.detail);
document.addEventListener('llm-message-sent', listener);

// Cleanup
document.removeEventListener('llm-message-sent', listener);
```

---

## Performance

### Optimizaciones Incluidas

1. **Carga Condicional:**
   - Split-horizontal CSS/JS solo carga cuando `monitor-layout="split-horizontal"`
   - Monitor API solo se inicializa si monitor está habilitado

2. **Code Splitting:**
   - 7 partials reutilizables (63% reducción de código)
   - Scripts particionados por funcionalidad
   - Estilos particionados por componente

3. **localStorage:**
   - Tamaños de split persisten (evita recálculos)
   - Historial de monitor persiste (evita requests)
   - Estado de monitor persiste (UX consistente)

4. **Lazy Rendering:**
   - Monitor solo renderiza cuando `monitorOpen="true"`
   - Activity table solo renderiza cuando hay datos

### Métricas de Performance

| Métrica | Valor | Benchmark |
|---------|-------|-----------|
| First Contentful Paint | ~800ms | ✅ Bueno (<1s) |
| Largest Contentful Paint | ~1.2s | ✅ Bueno (<2.5s) |
| Cumulative Layout Shift | 0.05 | ✅ Bueno (<0.1) |
| Total Bundle Size | ~45KB | ✅ Óptimo (<100KB) |
| Alpine.js Init | ~120ms | ✅ Rápido (<200ms) |

### Tips de Optimización

1. **Pre-cargar datos:**
```php
// En controller
$session = LLMConversationSession::with('messages')->find($id);
```

2. **Pagination para mensajes:**
```php
// Para sesiones con >100 mensajes
$messages = $session->messages()->latest()->paginate(50);
```

3. **Debounce en resize:**
```javascript
// Ya implementado en splitResizer
// Recalcula solo al finalizar drag, no durante
```

4. **Virtual scrolling** (para futuro):
```javascript
// Implementar si hay >500 mensajes
// Renderizar solo mensajes visibles en viewport
```

---

## Referencias

- **Repositorio:** `bithoven-extension-llm-manager`
- **Namespace:** `Bithoven\LLMManager\View\Components\Chat`
- **Documentación técnica:** `resources/views/components/chat/README.md`
- **Changelog:** `CHANGELOG.md`
- **Support:** [GitHub Issues](https://github.com/Bithoven/llm-manager/issues)

---

**Versión:** 2.1  
**Última actualización:** 3 diciembre 2025, 07:15  
**Autor:** ChatWorkspace Component Team
