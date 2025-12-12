# Monitor System v2.0 - Arquitectura Modular

## 🎯 Objetivo

Sistema de monitoreo modular, particionado y multi-instancia para Quick Chat de LLM Manager.

**Características principales:**
- ✅ Código JavaScript separado de Blade
- ✅ Estructura modular por función/propósito
- ✅ Soporte multi-instancia (múltiples chats en misma página)
- ✅ Independiente de CPANEL
- ✅ Export functionality (Clear/Copy/Download)

---

## 📦 Estructura del Proyecto

### JavaScript (Modular)

```
resources/js/monitor/              # 📝 CÓDIGO FUENTE (editar aquí)
├── core/
│   ├── MonitorFactory.js         # Factory singleton - gestión de instancias
│   ├── MonitorInstance.js        # Clase monitor individual por sesión
│   └── MonitorStorage.js         # localStorage management por sesión
├── actions/
│   ├── clear.js                  # Clear logs/history
│   ├── copy.js                   # Copy to clipboard
│   └── download.js               # Download as .txt
├── ui/
│   └── render.js                 # DOM updates (MonitorUI class)
└── monitor.js                    # Entry point (deprecated - no usado)

public/js/monitor/                 # 🌐 PUBLICADO (auto-generado)
├── core/                         # Copia de resources/js/monitor/core/
├── actions/                      # Copia de resources/js/monitor/actions/
├── ui/                           # Copia de resources/js/monitor/ui/
└── monitor.js                    # Copia (deprecated)
```

**Workflow:**
1. Editar en `resources/js/monitor/`
2. Ejecutar `./scripts/copy-monitor-js.sh`
3. Archivos se copian a `public/js/monitor/`
4. Blade carga desde `/vendor/llm-manager/js/monitor/`

### Blade (Solo carga de módulos)

```
resources/views/components/chat/
├── partials/
│   └── scripts/
│       └── monitor-api.blade.php  # 🔌 LOADER - importa módulos JS
└── shared/
    └── monitor.blade.php           # 🎨 UI - botones y HTML
```

**monitor-api.blade.php:**
```blade
<script type="module">
    import MonitorFactory from '/vendor/llm-manager/js/monitor/core/MonitorFactory.js';
    window.LLMMonitorFactory = MonitorFactory;
</script>
```

**monitor.blade.php:**
```blade
<div class="llm-monitor" data-monitor-id="{{ $monitorId }}">
    {{-- Header con botones icon-only --}}
    <button onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.downloadLogs()">
        <i class="ki-duotone ki-file-down fs-2">...</i>
    </button>
    <!-- ... más botones ... -->
</div>
```

---

## 🏗️ Arquitectura de Clases

### 1. MonitorFactory (Singleton)

```javascript
class MonitorFactory {
    instances = {}
    
    create(sessionId)        // Crear nueva instancia
    get(sessionId)           // Obtener instancia existente
    getOrCreate(sessionId)   // Get or create (convenience)
    destroy(sessionId)       // Destruir instancia
    getActiveInstances()     // Listar todas las instancias activas
}
```

**Uso:**
```javascript
const monitor = LLMMonitorFactory.create('session-123');
monitor.init();
```

### 2. MonitorInstance (Clase)

```javascript
class MonitorInstance {
    sessionId
    storage              // MonitorStorage instance
    ui                   // MonitorUI instance
    currentMetrics       // {tokens, chunks, cost, duration, startTime}
    history              // Array de actividades
    durationInterval     // setInterval ID
    
    // Lifecycle
    init()               // Cargar history + UI ready
    start()              // Iniciar streaming
    trackChunk(chunk, tokens)  // Trackear chunk recibido
    complete(provider, model)  // Completar stream
    error(message)       // Manejar error
    refresh()            // Refrescar UI
    reset()              // Reset metrics a 0
    
    // Actions
    clearLogs()          // Limpiar solo console
    clear()              // Limpiar todo (confirm dialog)
    copyLogs()           // Copiar a clipboard
    downloadLogs()       // Descargar como .txt
    
    // Helpers
    emitEvent(name, detail)  // Emitir CustomEvent global
}
```

### 3. MonitorStorage

```javascript
class MonitorStorage {
    sessionId
    storageKey           // 'llm_chat_monitor_history_{sessionId}'
    
    loadHistory()        // Cargar desde localStorage
    saveHistory(history) // Guardar a localStorage
    clearHistory()       // Borrar de localStorage
    addActivity(activity)// Agregar + mantener últimas 10
}
```

### 4. MonitorUI

```javascript
class MonitorUI {
    sessionId
    
    // DOM Helpers
    getElement(baseId)   // Get element por ID dinámico
    
    // Display Updates
    log(message, type)   // Agregar log a console
    updateMetrics({tokens, chunks})  // Update métricas
    updateDuration(duration)         // Update tiempo
    updateCost(cost)                 // Update costo
    updateStatus(text, type)         // Update badge
    renderActivityTable(history)     // Render tabla
    
    // Export Helpers
    clearLogsDisplay()   // Limpiar console visualmente
    getLogsAsText()      // Get logs como string
    hasLogs()            // Check si hay logs
}
```

### 5. Actions (Functions puras)

```javascript
// actions/clear.js
export function clearLogs(sessionId, ui)
export function clearAll(sessionId, storage, ui, resetCallback)

// actions/copy.js
export async function copyLogs(sessionId, ui)

// actions/download.js
export function downloadLogs(sessionId, ui)
```

---

## 🔀 Multi-Instancia

### Escenario: 2 chats en misma página

```html
<!-- Chat 1 -->
<div class="llm-monitor" data-monitor-id="session-123">
    <!-- Monitor para sesión 123 -->
</div>

<!-- Chat 2 -->
<div class="llm-monitor" data-monitor-id="session-456">
    <!-- Monitor para sesión 456 -->
</div>
```

```javascript
// Auto-inicialización
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.llm-monitor').forEach(monitorEl => {
        const sessionId = monitorEl.dataset.monitorId;
        const monitor = LLMMonitorFactory.create(sessionId);
        monitor.init();
    });
});

// Resultado:
// - LLMMonitorFactory.instances['session-123'] = MonitorInstance
// - LLMMonitorFactory.instances['session-456'] = MonitorInstance

// Cada uno opera independientemente:
LLMMonitorFactory.get('session-123').start();
LLMMonitorFactory.get('session-456').start();
```

**Separación:**
- localStorage: `llm_chat_monitor_history_session-123` vs `llm_chat_monitor_history_session-456`
- DOM IDs: `monitor-logs-session-123` vs `monitor-logs-session-456`
- Events: `event.detail.sessionId` identifica la instancia

---

## 🔗 Eventos Globales

Todos los monitores emiten eventos globales con `sessionId`:

```javascript
// Streaming lifecycle
window.addEventListener('llm-streaming-started', (e) => {
    console.log(e.detail); // {sessionId, timestamp}
});

window.addEventListener('llm-streaming-chunk', (e) => {
    console.log(e.detail); // {sessionId, chunk, tokens, totalTokens, totalChunks}
});

window.addEventListener('llm-streaming-completed', (e) => {
    console.log(e.detail); // {sessionId, provider, model, totalTokens, duration, cost}
});

window.addEventListener('llm-streaming-error', (e) => {
    console.log(e.detail); // {sessionId, error, timestamp}
});

// Monitor actions
window.addEventListener('llm-monitor-logs-cleared', (e) => {
    console.log(e.detail); // {sessionId, timestamp}
});

window.addEventListener('llm-monitor-cleared', (e) => {
    console.log(e.detail); // {sessionId, timestamp}
});

window.addEventListener('llm-monitor-logs-copied', (e) => {
    console.log(e.detail); // {sessionId, timestamp, linesCount}
});

window.addEventListener('llm-monitor-logs-downloaded', (e) => {
    console.log(e.detail); // {sessionId, timestamp, filename, linesCount}
});
```

---

## 🎨 UI Components

### Monitor Header (Icon-only buttons)

```blade
<div class="d-flex gap-2">
    {{-- Download (Green) --}}
    <button class="btn btn-sm btn-icon btn-light-success" 
            onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.downloadLogs()"
            data-bs-toggle="tooltip" title="Download logs">
        <i class="ki-duotone ki-file-down fs-2">...</i>
    </button>

    {{-- Copy (Blue) --}}
    <button class="btn btn-sm btn-icon btn-light-primary" 
            onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.copyLogs()"
            data-bs-toggle="tooltip" title="Copy to clipboard">
        <i class="ki-duotone ki-copy fs-2">...</i>
    </button>

    {{-- Clear Logs Only (Orange) --}}
    <button class="btn btn-sm btn-icon btn-light-warning" 
            onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.clearLogs()"
            data-bs-toggle="tooltip" title="Clear console only">
        <i class="ki-duotone ki-eraser fs-2">...</i>
    </button>

    {{-- Clear All (Red) --}}
    <button class="btn btn-sm btn-icon btn-light-danger" 
            onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.clear()"
            data-bs-toggle="tooltip" title="Clear all data">
        <i class="ki-duotone ki-trash fs-2">...</i>
    </button>

    {{-- Refresh (Gray) --}}
    <button class="btn btn-sm btn-icon btn-light" 
            onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.refresh()"
            data-bs-toggle="tooltip" title="Refresh display">
        <i class="ki-duotone ki-arrows-circle fs-2">...</i>
    </button>
</div>
```

**Características:**
- Icon-only (sin texto)
- Tooltips en hover
- Color semántico (success/primary/warning/danger/light)
- Optional chaining (`?.`) para evitar crashes

---

## 📦 Dependencias

**Incluidas en Metronic (0 nuevas instalaciones):**
- SweetAlert2 - Toasts feedback
- Clipboard API - `navigator.clipboard.writeText()`
- Bootstrap Tooltips - `data-bs-toggle="tooltip"`
- KI-Duotone Icons - Sistema de iconos

---

## 🚀 Flujo de Trabajo Completo

### 1. Usuario envía mensaje en Quick Chat

```javascript
// streaming-handler.js
const monitor = LLMMonitorFactory.get(sessionId);
monitor.start();  // Inicia counter, update status
```

### 2. Server envía chunks vía SSE

```javascript
// streaming-handler.js (on chunk received)
monitor.trackChunk(chunk, tokens);  // Update metrics + log
```

### 3. Streaming completa

```javascript
monitor.complete(provider, model);
// - Stop counter
// - Calculate cost
// - Add to history
// - Update status badge
// - Save to localStorage
```

### 4. Usuario exporta logs

```javascript
// Click en botón Download
monitor.downloadLogs();
// - Check hasLogs()
// - Get logs as text
// - Create blob
// - Download as llm-monitor-session-{id}-{timestamp}.txt
// - Toast success
// - Emit event
```

---

## ⚠️ Reglas Críticas

### DO ✅

1. **Editar solo en `resources/js/monitor/`**
   - Código fuente principal
   - Estructura organizada

2. **Ejecutar `./scripts/copy-monitor-js.sh` después de cambios**
   - Copia automática a `public/`
   - Mantiene sincronización

3. **Usar ES6 modules**
   - `import` / `export` nativo
   - No requiere build system

4. **Usar optional chaining en onclick**
   - `?.` evita crashes si monitor no existe
   - Ejemplo: `LLMMonitorFactory.get(id)?.method()`

5. **Incluir `sessionId` en todos los eventos**
   - Permite multi-instancia
   - Facilita debugging

### DON'T ❌

1. **NO editar `public/js/monitor/`**
   - Son copias auto-generadas
   - Se sobrescriben en cada copia

2. **NO poner JavaScript en Blade**
   - Solo carga de módulos
   - Mantener separación

3. **NO asumir monitor siempre existe**
   - Usar `?.` para acceso seguro
   - Check `if (monitor)` cuando sea crítico

4. **NO hardcodear 'default' como sessionId**
   - Usar `$session->id` dinámico
   - Permite múltiples instancias

---

## 🧪 Testing

### Test 1: Logs vacíos

```javascript
// Click Download sin logs
monitor.downloadLogs();
// Resultado: Toast warning "Console is empty"
```

### Test 2: Copy logs

```javascript
// Con logs existentes
monitor.copyLogs();
// Resultado: 
// - Clipboard contiene logs
// - Toast success "Copied!"
// - Event 'llm-monitor-logs-copied' emitido
```

### Test 3: Multi-instancia

```javascript
// 2 monitores en misma página
const m1 = LLMMonitorFactory.get('session-123');
const m2 = LLMMonitorFactory.get('session-456');

m1.downloadLogs();  // Descarga logs de session-123
m2.downloadLogs();  // Descarga logs de session-456 (independiente)
```

### Test 4: Clear actions

```javascript
// Clear logs only
monitor.clearLogs();
// - Console limpio
// - Metrics NO afectadas
// - History NO afectada

// Clear all
monitor.clear();
// - Confirm dialog
// - Console limpio
// - Metrics reset
// - History borrada (localStorage)
```

---

## 📊 Comparación: Antes vs Ahora

| Aspecto | Before (v0.2.2) | After (v2.0) |
|---------|----------------|--------------|
| **Código** | 300 líneas en Blade | 7 archivos JS modulares |
| **Separación** | JS mezclado con HTML | JS separado completamente |
| **Organización** | Monolítico | Por función/propósito |
| **Export** | Solo Clear | Clear/Copy/Download |
| **Multi-instancia** | Soporte básico | Arquitectura robusta |
| **Mantenibilidad** | Difícil | Fácil (modular) |
| **Testing** | Complicado | Simple (functions puras) |

---

## 📝 Archivos Modificados

### Nuevos archivos

```
resources/js/monitor/
├── core/
│   ├── MonitorFactory.js         # +80 líneas
│   ├── MonitorInstance.js        # +180 líneas
│   └── MonitorStorage.js         # +60 líneas
├── actions/
│   ├── clear.js                  # +80 líneas
│   ├── copy.js                   # +70 líneas
│   └── download.js               # +80 líneas
└── ui/
    └── render.js                 # +140 líneas

scripts/
└── copy-monitor-js.sh            # +50 líneas

resources/js/monitor/README.md     # +250 líneas
docs/MONITOR-ARCHITECTURE-v2.md    # Este archivo
```

### Archivos editados

```
resources/views/components/chat/partials/scripts/monitor-api.blade.php
  - Antes: 300 líneas de JS inline
  - Ahora: 20 líneas (solo import modules)
  - Cambio: -280 líneas

resources/views/components/chat/shared/monitor.blade.php
  - Antes: 2 botones (Refresh, Clear)
  - Ahora: 5 botones icon-only (Download, Copy, Clear Logs, Clear All, Refresh)
  - Cambio: +40 líneas
```

**Total:**
- **Código agregado:** ~990 líneas (modular, organizado)
- **Código removido:** ~280 líneas (inline monolítico)
- **Net:** +710 líneas (pero 7x más mantenible)

---

## 🎯 Ventajas de la Nueva Arquitectura

1. **Separación de Responsabilidades**
   - Core (factory, instance, storage)
   - Actions (clear, copy, download)
   - UI (render, DOM updates)

2. **Código Reutilizable**
   - Functions puras
   - ES6 classes
   - Exports modulares

3. **Fácil Testing**
   - Cada módulo testeable independientemente
   - No dependencias circulares

4. **Escalabilidad**
   - Agregar nuevas actions fácil
   - Modificar UI sin tocar lógica
   - Storage intercambiable (ej: API en lugar de localStorage)

5. **Debugging Simple**
   - Stack traces claros
   - Eventos trazables
   - Console.log por módulo

6. **Sin Build System**
   - ES6 modules nativos
   - Script de copia simple
   - No webpack/vite necesario

---

**Versión:** v2.0  
**Fecha:** 4 de diciembre de 2025, 15:52  
**Autor:** Claude (Claude Sonnet 4.5)  
**Proyecto:** LLM Manager Extension v0.3.0
