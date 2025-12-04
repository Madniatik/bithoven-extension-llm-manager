# Monitor System - JavaScript Architecture

## 📁 Estructura Modular

```
resources/js/monitor/
├── core/
│   ├── MonitorFactory.js      # Factory singleton (multi-instance)
│   ├── MonitorInstance.js     # Clase monitor individual
│   └── MonitorStorage.js      # localStorage management
├── actions/
│   ├── clear.js               # Clear logs/history actions
│   ├── copy.js                # Copy to clipboard
│   └── download.js            # Download logs as .txt
├── ui/
│   └── render.js              # UI updates y DOM management
└── monitor.js                 # Entry point (DEPRECATED - not used)
```

## 🔄 Workflow de Desarrollo

### 1. Editar archivos en `resources/js/monitor/`

Los archivos fuente están en `resources/js/monitor/` organizados por función.

### 2. Copiar a `public/js/monitor/`

```bash
# Copiar todos los módulos
./scripts/copy-monitor-js.sh

# O copiar manualmente:
cp -r resources/js/monitor/core/* public/js/monitor/core/
cp -r resources/js/monitor/actions/* public/js/monitor/actions/
cp -r resources/js/monitor/ui/* public/js/monitor/ui/
```

### 3. Cargar en Blade

```blade
{{-- resources/views/components/chat/partials/scripts/monitor-api.blade.php --}}
<script type="module">
    import MonitorFactory from '/vendor/llm-manager/js/monitor/core/MonitorFactory.js';
    window.LLMMonitorFactory = MonitorFactory;
</script>
```

## 🏗️ Arquitectura

### Core (Núcleo)

**MonitorFactory** (Singleton)
- Gestiona múltiples instancias de monitores
- Una instancia por sessionId
- Métodos: `create(id)`, `get(id)`, `getOrCreate(id)`, `destroy(id)`

**MonitorInstance** (Clase)
- Monitor individual para una sesión de chat
- Maneja metrics, logs, history
- Métodos principales:
  - `init()` - Inicializar monitor
  - `start()` - Iniciar streaming
  - `trackChunk(chunk, tokens)` - Trackear chunk
  - `complete(provider, model)` - Completar stream
  - `error(message)` - Manejar error
  - `refresh()` - Refrescar UI
  - `reset()` - Reset metrics

**MonitorStorage**
- Gestión de localStorage por sesión
- Clave: `llm_chat_monitor_history_{sessionId}`
- Mantiene últimas 10 actividades

### Actions (Acciones de Usuario)

**clear.js**
- `clearLogs(sessionId, ui)` - Limpiar solo console
- `clearAll(sessionId, storage, ui, resetCallback)` - Limpiar todo

**copy.js**
- `copyLogs(sessionId, ui)` - Copiar logs al clipboard
- Usa Clipboard API
- SweetAlert2 feedback

**download.js**
- `downloadLogs(sessionId, ui)` - Descargar logs como .txt
- Nombre: `llm-monitor-session-{id}-{timestamp}.txt`
- Blob + createElement('a') pattern

### UI (Interfaz)

**render.js (MonitorUI)**
- Clase para manipulación del DOM
- Métodos:
  - `getElement(baseId)` - Get elemento por ID dinámico
  - `log(message, type)` - Agregar log
  - `updateMetrics(metrics)` - Actualizar tokens/chunks
  - `updateDuration(duration)` - Actualizar tiempo
  - `updateCost(cost)` - Actualizar costo
  - `updateStatus(text, type)` - Actualizar badge status
  - `renderActivityTable(history)` - Renderizar tabla
  - `clearLogsDisplay()` - Limpiar display de logs
  - `getLogsAsText()` - Obtener logs como texto
  - `hasLogs()` - Check si hay logs

## 🎯 Multi-Instancia

El sistema soporta **múltiples monitores en la misma página**:

```javascript
// Crear monitor para sesión 1
const monitor1 = LLMMonitorFactory.create('session-123');
monitor1.init();

// Crear monitor para sesión 2
const monitor2 = LLMMonitorFactory.create('session-456');
monitor2.init();

// Ambos operan independientemente
monitor1.start();
monitor2.start();
```

Cada monitor:
- Tiene su propio localStorage (`llm_chat_monitor_history_{sessionId}`)
- Tiene sus propios elementos DOM (`monitor-logs-{sessionId}`)
- Emite eventos globales con `sessionId` en `event.detail`

## 🔗 Eventos Globales

Todos los eventos incluyen `sessionId` en `detail`:

```javascript
// Streaming events
'llm-streaming-started'     // {sessionId, timestamp}
'llm-streaming-chunk'       // {sessionId, chunk, tokens, totalTokens, totalChunks}
'llm-streaming-completed'   // {sessionId, provider, model, totalTokens, duration, cost}
'llm-streaming-error'       // {sessionId, error, timestamp}

// Monitor events
'llm-monitor-logs-cleared'  // {sessionId, timestamp}
'llm-monitor-cleared'       // {sessionId, timestamp}
'llm-monitor-logs-copied'   // {sessionId, timestamp, linesCount}
'llm-monitor-logs-downloaded' // {sessionId, timestamp, filename, linesCount}
```

## 📦 Dependencias

- **SweetAlert2** - Toasts de feedback (success/error/warning)
- **Clipboard API** - `navigator.clipboard.writeText()`
- **Bootstrap Tooltips** - `data-bs-toggle="tooltip"`
- **KI-Duotone Icons** - Iconos del sistema

Todas las dependencias están incluidas en Metronic (no requiere instalación).

## 🚀 Uso en Blade Components

```blade
{{-- monitor.blade.php --}}
<button onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.downloadLogs()">
    Download
</button>
<button onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.copyLogs()">
    Copy
</button>
<button onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.clearLogs()">
    Clear Logs
</button>
<button onclick="window.LLMMonitorFactory.get('{{ $monitorId }}')?.clear()">
    Clear All
</button>
```

## ⚠️ Importante

1. **NO editar archivos en `public/js/monitor/`** - Son copias auto-generadas
2. **Siempre editar en `resources/js/monitor/`** - Código fuente principal
3. **Ejecutar `./scripts/copy-monitor-js.sh`** después de cambios
4. **Usar ES6 modules** - `import`/`export` nativo del navegador
5. **Mantener backwards compatibility** - `window.LLMMonitor` = default instance

## 🔍 Troubleshooting

**Error: Module not found**
```bash
# Asegurarse que los archivos están en public/
./scripts/copy-monitor-js.sh
```

**Monitor no se inicializa**
```javascript
// Verificar que el elemento tiene data-monitor-id
<div class="llm-monitor" data-monitor-id="{{ $monitorId }}">
```

**Funciones no existen**
```javascript
// Usar optional chaining
LLMMonitorFactory.get('session-id')?.downloadLogs()
// No crashea si monitor no existe
```

---

**Versión:** v1.0.7  
**Última actualización:** 4 de diciembre de 2025, 15:14
