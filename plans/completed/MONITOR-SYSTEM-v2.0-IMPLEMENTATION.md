# Monitor System v2.0 - Implementation Guide

**Date:** 4 de diciembre de 2025  
**Version:** 2.0 (Hybrid Adapter + Configurable UI)  
**Status:** ✅ IMPLEMENTED

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Key Features](#key-features)
4. [Usage Examples](#usage-examples)
5. [Configuration](#configuration)
6. [API Reference](#api-reference)
7. [Testing](#testing)
8. [Troubleshooting](#troubleshooting)

---

## Overview

Monitor System v2.0 introduce dos mejoras críticas:

### 1. **Hybrid Adapter Pattern (Opción 3)**
- Soporte multi-session (múltiples chats simultáneos)
- sessionId opcional (backward compatible)
- Auto-detection de instancias
- Console debugging integrado

### 2. **Configurable UI Components**
- Presets predefinidos (`full`, `console-only`, `metrics-only`, `history-only`)
- Props individuales (`showMetrics`, `showHistory`, `showConsole`, `showButtons`)
- Layouts flexibles según necesidades

---

## Architecture

### File Structure

```
resources/views/components/chat/
├── shared/
│   ├── monitor.blade.php           # ✅ Configurable monitor component
│   ├── monitor-console.blade.php   # Legacy (console only)
│   └── streaming-handler.blade.php # ✅ Updated with sessionId support
├── partials/
│   └── scripts/
│       ├── monitor-api.blade.php   # ✅ Hybrid Adapter implementation
│       └── chat-workspace.blade.php
└── layouts/
    ├── sidebar.blade.php
    └── split-horizontal.blade.php
```

### Component Hierarchy

```
window.LLMMonitorFactory (Singleton)
    ├── create(sessionId) → MonitorInstance
    ├── get(sessionId) → MonitorInstance | null
    └── instances = Map<sessionId, MonitorInstance>

window.LLMMonitor (Hybrid Adapter)
    ├── _currentSessionId (fallback)
    ├── setSession(sessionId)
    ├── _getMonitor(sessionId?) → MonitorInstance
    ├── start(sessionId?)
    ├── trackChunk(chunk, tokens, sessionId?)
    ├── complete(provider, model, sessionId?)
    ├── error(message, sessionId?)
    ├── clearLogs(sessionId?)
    ├── copyLogs(sessionId?)
    ├── downloadLogs(sessionId?)
    ├── refresh(sessionId?)
    ├── clear(sessionId?)
    └── getInstance(sessionId?)
```

---

## Key Features

### ✅ Multi-Session Support

```javascript
// Chat 1
window.LLMMonitor.start('quick-chat-001');
window.LLMMonitor.trackChunk('Hello', 5, 'quick-chat-001');

// Chat 2 (simultáneo)
window.LLMMonitor.start('project-chat-002');
window.LLMMonitor.trackChunk('World', 3, 'project-chat-002');

// Ambos funcionan independientemente
```

### ✅ Backward Compatible

```javascript
// Sin sessionId (usa fallback)
window.LLMMonitor.setSession('default');
window.LLMMonitor.start();

// Con sessionId explícito (override)
window.LLMMonitor.start('chat-123');
```

### ✅ Configurable UI

```blade
{{-- Full monitor (default) --}}
@include('llm-manager::components.chat.shared.monitor')

{{-- Console only --}}
@include('llm-manager::components.chat.shared.monitor', ['preset' => 'console-only'])

{{-- Custom configuration --}}
@include('llm-manager::components.chat.shared.monitor', [
    'preset' => 'custom',
    'showMetrics' => true,
    'showConsole' => true,
    'showHistory' => false,
    'showButtons' => true
])
```

### ✅ Debug Mode

```javascript
// Activar debug (ver logs en console)
window.LLMMonitor._debugMode = true;

// Logs automáticos:
// [LLMMonitor] Session set to: quick-chat-001
// [LLMMonitor] Started: quick-chat-001
// [LLMMonitor] Completed: quick-chat-001
```

---

## Usage Examples

### Example 1: Quick Chat (Split Horizontal + Console Only)

```blade
{{-- admin/quick-chat/index.blade.php --}}
<x-default-layout>
    <x-llm-manager-chat-workspace
        :session="$session"
        :configurations="$configurations"
        :show-monitor="true"
        :monitor-open="false"
        monitor-layout="split-horizontal"
        monitor-preset="console-only"
    />
</x-default-layout>
```

### Example 2: Dashboard (Sidebar + Metrics Only)

```blade
{{-- admin/dashboard.blade.php --}}
<x-llm-manager-chat-workspace
    :session="$session"
    monitor-layout="sidebar"
    monitor-preset="metrics-only"
/>
```

### Example 3: Two Chats (Same Page)

```blade
<div class="row">
    {{-- Chat 1: Quick Chat --}}
    <div class="col-6">
        @include('llm-manager::components.chat.layouts.split-horizontal', [
            'session' => $session1,
            'monitorId' => 'quick-chat-001',
            'monitorPreset' => 'full'
        ])
    </div>
    
    {{-- Chat 2: Project Chat --}}
    <div class="col-6">
        @include('llm-manager::components.chat.layouts.split-horizontal', [
            'session' => $session2,
            'monitorId' => 'project-chat-002',
            'monitorPreset' => 'console-only'
        ])
    </div>
</div>
```

### Example 4: Custom Layout (History Only)

```blade
{{-- Custom metrics dashboard --}}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('llm-manager::components.chat.shared.monitor', [
                'monitorId' => 'metrics-dashboard',
                'preset' => 'history-only',
                'showButtons' => false
            ])
        </div>
    </div>
</div>
```

---

## Configuration

### Presets

| Preset | Metrics | History | Console | Buttons |
|--------|---------|---------|---------|---------|
| `full` | ✅ | ✅ | ✅ | ✅ |
| `console-only` | ❌ | ❌ | ✅ | ✅ |
| `metrics-only` | ✅ | ❌ | ❌ | ❌ |
| `history-only` | ❌ | ✅ | ❌ | ✅ |
| `custom` | props | props | props | props |

### Props Reference

```php
@include('llm-manager::components.chat.shared.monitor', [
    'monitorId' => 'unique-session-id',      // Required: Unique identifier
    'showCloseButton' => false,              // Show/hide close button
    'preset' => 'full',                      // Preset config (see table above)
    'showMetrics' => null,                   // Override preset (true|false|null)
    'showHistory' => null,                   // Override preset (true|false|null)
    'showConsole' => null,                   // Override preset (true|false|null)
    'showButtons' => null,                   // Override preset (true|false|null)
])
```

---

## API Reference

### Adapter Methods

#### `setSession(sessionId: string): void`
```javascript
window.LLMMonitor.setSession('quick-chat-001');
```
Set fallback session ID para llamadas sin sessionId explícito.

#### `start(sessionId?: string): void`
```javascript
window.LLMMonitor.start('quick-chat-001');
```
Iniciar monitoreo de sesión.

#### `trackChunk(chunk: string, tokens: number, sessionId?: string): void`
```javascript
window.LLMMonitor.trackChunk('Hello world', 5, 'quick-chat-001');
```
Trackear chunk de streaming.

#### `complete(provider: string, model: string, sessionId?: string): void`
```javascript
window.LLMMonitor.complete('github', 'claude-sonnet-4.5', 'quick-chat-001');
```
Completar sesión y calcular costo.

#### `error(message: string, sessionId?: string): void`
```javascript
window.LLMMonitor.error('Connection timeout', 'quick-chat-001');
```
Registrar error.

#### `clearLogs(sessionId?: string): void`
```javascript
window.LLMMonitor.clearLogs('quick-chat-001');
```
Limpiar console logs.

#### `copyLogs(sessionId?: string): void`
```javascript
window.LLMMonitor.copyLogs('quick-chat-001');
```
Copiar logs al clipboard.

#### `downloadLogs(sessionId?: string): void`
```javascript
window.LLMMonitor.downloadLogs('quick-chat-001');
```
Descargar logs como archivo .txt.

#### `getInstance(sessionId?: string): MonitorInstance | null`
```javascript
const monitor = window.LLMMonitor.getInstance('quick-chat-001');
```
Obtener instancia directa (uso avanzado).

---

## Testing

### Test 1: Multi-Chat Support

1. Abrir Quick Chat (`/admin/llm/quick-chat`)
2. Abrir segunda pestaña: `/admin/llm/quick-chat` (session diferente)
3. Enviar mensaje en Chat 1
4. Enviar mensaje en Chat 2
5. **Verificar:** Ambos monitores actualizan métricas independientemente

### Test 2: Adapter Fallback

```javascript
// En browser console
window.LLMMonitor.setSession('default');
window.LLMMonitor.start(); // Debe usar 'default'
window.LLMMonitor.start('chat-123'); // Debe override a 'chat-123'
```

### Test 3: Configuración UI

```blade
{{-- Crear vista de prueba --}}
@include('llm-manager::components.chat.shared.monitor', ['preset' => 'console-only'])
```

**Verificar:** Solo console visible, no metrics ni history.

### Test 4: Export Buttons

1. Enviar mensaje en chat (generar logs)
2. Click en "Copy" → Verificar clipboard
3. Click en "Download" → Verificar archivo descargado
4. Click en "Clear Logs" → Verificar console limpia (metrics intactas)
5. Click en "Clear All" → Verificar todo limpio

---

## Troubleshooting

### Issue: Metrics no actualizan

**Síntoma:** Stats (tokens, chunks, duration) permanecen en 0.

**Causa:** `sessionId` no se pasa al streaming handler.

**Solución:**
```javascript
// Verificar en browser console
window.LLMMonitor._debugMode = true;
// Luego enviar mensaje y buscar warnings:
// [LLMMonitor] No monitor instance found for session: undefined
```

**Fix:** Asegurar que `params.sessionId` se pasa en `LLMStreamingHandler.start()`.

---

### Issue: Export buttons no funcionan

**Síntoma:** Click no hace nada.

**Causa:** `window.LLMMonitor` no existe o SweetAlert2 no cargado.

**Solución:**
```javascript
// En browser console
window.LLMMonitor; // Debe retornar objeto
window.Swal; // Debe retornar función
```

**Fix:** Verificar que `monitor-api.blade.php` se carga ANTES que `monitor.blade.php`.

---

### Issue: Múltiples chats interfieren

**Síntoma:** Métricas de Chat 1 aparecen en Chat 2.

**Causa:** `sessionId` no único o no se pasa explícitamente.

**Solución:**
```javascript
// Verificar sessionId único por chat
document.querySelectorAll('.llm-monitor').forEach(el => {
    console.log(el.dataset.monitorId);
});
```

**Fix:** Generar `sessionId` único (UUID o timestamp + random).

---

### Issue: Console logs no se muestran

**Síntoma:** Console vacío aunque hay logs.

**Causa:** Preset `showConsole => false` o IDs incorrectos.

**Solución:**
```blade
{{-- Verificar preset --}}
@include('llm-manager::components.chat.shared.monitor', ['preset' => 'console-only'])
```

```javascript
// Verificar IDs en DOM
document.getElementById('monitor-logs-' + sessionId);
```

---

## Next Steps

### Phase 1: Testing (CURRENT)
- [ ] Test multi-chat en producción
- [ ] Verificar export buttons funcionan
- [ ] Validar métricas actualizan correctamente

### Phase 2: Advanced Features
- [ ] WebSocket support (alternativa a EventSource)
- [ ] Exportar history a CSV
- [ ] Gráficos de performance (Chart.js)

### Phase 3: Optimizations
- [ ] Lazy load monitor modules
- [ ] Virtual scrolling para logs largos
- [ ] IndexedDB para persistencia offline

---

## References

- **Hybrid Adapter:** `resources/views/components/chat/partials/scripts/monitor-api.blade.php`
- **Configurable UI:** `resources/views/components/chat/shared/monitor.blade.php`
- **Streaming Integration:** `resources/views/components/chat/shared/streaming-handler.blade.php`
- **Alpine Component:** `resources/views/components/chat/partials/scripts/chat-workspace.blade.php`

---

**✅ Implementation Status:** COMPLETE  
**🚀 Ready for Testing:** YES  
**📝 Documentation:** UP TO DATE
