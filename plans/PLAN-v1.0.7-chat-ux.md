# LLM Manager Extension - PLAN v1.0.7 (Chat UX Improvements)

**Fecha de Creación:** 9 de diciembre de 2025  
**Plan Padre:** [PLAN-v1.0.7.md](./PLAN-v1.0.7.md)  
**Estado:** New  
**Prioridad:** Medium  
**Tiempo Estimado:** 8-12 horas

---

## 📋 DESCRIPCIÓN

Plan anexo dedicado a mejoras visuales y de experiencia de usuario (UX) en el componente Quick Chat. Este plan extiende el PLAN-v1.0.7.md para incluir nuevas ideas y corregir bugs UX detectados después de la implementación del Quick Chat Feature y el Smart Auto-Scroll System.

**Relación con Plan Padre:**
- El PLAN v1.0.7 (sección 2) implementó el Quick Chat Feature básico con streaming, monitor, copy/paste, etc.
- Este plan se enfoca en polish, interactividad, y UX avanzado (notificaciones, keyboard shortcuts, indicadores visuales, etc.)

---

## 🎯 OBJETIVOS

1. **Mejorar Feedback Visual:** Indicadores de estado durante streaming (connecting, thinking, typing)
2. **Notificaciones Inteligentes:** Sonido al completar respuesta SOLO si usuario está en otra pestaña
3. **Gestión de Mensajes:** Borrar mensajes individuales desde UI
4. **Atajos de Teclado:** Enter/Shift+Enter configurable para enviar vs nueva línea
5. **Refinamiento UI:** Header de bubbles con segunda línea para acciones, hover effects
6. **Configuración Avanzada:** Panel de administración para fancy animations, sonidos, shortcuts
7. **Bug Fixes:** Scroll inicial invisible, textarea resize, cancel request detection

---

## 📦 IMPLEMENTACIONES UX PENDIENTES

### 1. Notificación Sonora al Completar Respuesta ⏳
**Descripción:** Reproducir sonido cuando el streaming del asistente finaliza.

**Condición:**
- ✅ Solo si la pestaña del navegador NO está activa (usuario en otra tab/ventana)
- ❌ NO reproducir si el usuario está viendo el chat activamente

**Implementación:**
- Usar API `document.visibilityState` para detectar si tab está activa
- Evento trigger: `done` event en streaming (cuando `event.data === '[DONE]'`)
- Sonido: Notificación sutil (ej: `notification.mp3` en `/public/vendor/llm-manager/sounds/`)
- Settings: Habilitar/deshabilitar en Chat Administration

**Archivos:**
- `event-handlers.blade.php` - Listener `done` + visibility check
- `chat-administration.blade.php` - Toggle switch "Notificaciones Sonoras"
- `public/vendor/llm-manager/sounds/` - Audio files

**Tiempo Estimado:** 1.5 horas

---

### 2. Botón "Borrar Mensaje" en Bubble Header ⏳
**Descripción:** Eliminar mensajes individuales desde la UI del chat.

**Funcionalidad:**
- Botón "Delete" en header de cada bubble (usuario y asistente)
- Confirmación modal antes de borrar
- Eliminar de DB + remover del DOM
- Actualizar contador de mensajes en header (-1)

**Endpoint:**
```php
// POST /llm/sessions/{session}/messages/{message}/delete
MessageController::deleteMessage($sessionId, $messageId)
```

**Archivos:**
- `message-bubble.blade.php` - Agregar botón "Delete" en header actions
- `MessageController.php` - Método `deleteMessage()`
- `event-handlers.blade.php` - Listener para borrado (actualizar contador)

**Tiempo Estimado:** 2 horas

---

### 3. Indicador de Streaming Status ⏳
**Descripción:** Mostrar indicador visual cuando el asistente está generando respuesta.

**Estados:**
1. **Connecting...** - Al abrir EventSource (icono spinner circular)
2. **Thinking...** - Primer chunk recibido (icono spinner)
3. **Typing...** - Texto fluyendo (icono blinking dots, estilo WhatsApp)
4. **Completed** - Fade out y desaparecer

**Posiciones Propuestas:**
- **Opción A:** Sticky header en top del messages-container (siempre visible al hacer scroll)
- **Opción B:** Footer flotante debajo de scroll-to-bottom button
- **Opción C:** Inline badge en header del último mensaje del asistente

**Reutilización de Código:**
- Revisar `MonitorAPI.js` y `MonitorInstance.js` - tienen listeners de `open`, `chunk`, `done`
- Posible shared utility: `StreamingStatusIndicator.js` con estados y transiciones

**Archivos:**
- `streaming-status-indicator.blade.php` (nuevo partial)
- `event-handlers.blade.php` - Listeners para cambiar estado
- CSS animations para spinner y blinking dots

**Tiempo Estimado:** 2.5 horas

---

### 4. Refactorización Header del Bubble ⏳
**Descripción:** Reorganizar header de cada bubble con segunda línea para acciones.

**Estructura Actual:**
```
[Avatar] Nombre del Usuario | 12:34 PM
```

**Estructura Nueva:**
```
[Avatar] Nombre del Usuario | 12:34 PM
        Copy | View Raw | Delete
```

**Cambios:**
- Mover botones de iconos a texto (más claro)
- Estilo link pequeño (fs-7, text-muted)
- Añadir botón "Delete" con icono papelera
- Preparar para futuras acciones (Edit, Download, Share, etc.)

**Archivos:**
- `message-bubble.blade.php` - Refactorizar estructura HTML del header
- `split-horizontal.blade.php` - CSS para segunda línea (flex, gap, spacing)

**Tiempo Estimado:** 1.5 horas

---

### 5. Keyboard Shortcuts para Enviar Mensajes ⏳
**Descripción:** Configurar modo de envío con Enter vs Shift+Enter.

**Modos:**
- **Modo A (Default):** Enter = enviar, Shift+Enter = nueva línea
- **Modo B:** Enter = nueva línea, Shift+Enter = enviar

**Implementación:**
- Listener `keydown` en textarea
- Detectar `event.key === 'Enter'` + `event.shiftKey`
- Setting en Chat Administration: Radio buttons para elegir modo
- Guardar preferencia en localStorage o DB (user preference)

**Archivos:**
- `event-handlers.blade.php` - Listener keydown con lógica condicional
- `chat-administration.blade.php` - Radio buttons "Keyboard Shortcuts Mode"

**Tiempo Estimado:** 1 hora

---

### 6. Hover Effects en Bubbles ⏳
**Descripción:** Efectos visuales al pasar el mouse sobre mensajes.

**Efectos:**
- Lift shadow (elevación ligera)
- Resaltar border (color primario sutil)
- Fade in de botones de acción (Copy, Delete, etc.)

**CSS:**
```css
.message-bubble:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
```

**Archivos:**
- `split-horizontal.blade.php` - CSS hover effects

**Tiempo Estimado:** 30 minutos

---

### 7. Efecto Typewriter (Opcional) 🔮
**Descripción:** Simular escritura letra por letra del asistente.

**Estado:** FUTURO (baja prioridad)

**Razones para postergar:**
- Streaming ya proporciona sensación de "typing" natural
- Complexity vs benefit ratio bajo
- Puede parecer más lento que streaming directo

**Si se implementa:**
- Buffer de chunks, revelar caracteres con interval
- Toggle en Chat Administration

**Tiempo Estimado:** 2 horas (si se decide implementar)

---

## 🐞 BUGS UX A CORREGIR

### BUG-1: Scroll Inicial Visible al Cargar Chat 🔴
**Descripción:** Al cargar la página, el scroll automático hacia el final del contenedor es visible para el usuario (efecto de desplazamiento).

**Comportamiento Actual:**
```javascript
setTimeout(() => {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}, 200);
```
- Usuario ve el scroll animándose hacia abajo (200ms delay + smooth scroll)

**Comportamiento Deseado:**
- Aparecer directamente al final sin animación de scroll visible

**Solución Propuesta:**
```javascript
// Opción A: Reducir timeout a 50ms + scrollBehavior instant
setTimeout(() => {
    messagesContainer.scrollTo({
        top: messagesContainer.scrollHeight,
        behavior: 'instant' // Sin animación
    });
}, 50);

// Opción B: CSS inicial (position messages-container al bottom)
#messages-container-{{ $session->id }} {
    display: flex;
    flex-direction: column;
    justify-content: flex-end; /* Iniciar en bottom */
}
```

**Archivos:**
- `event-handlers.blade.php` - Modificar scroll inicial

**Tiempo Estimado:** 30 minutos

---

### BUG-2: Textarea No Restaura Tamaño al Enviar 🟡
**Descripción:** Después de enviar mensaje, el textarea mantiene el tamaño expandido (si era grande, queda grande).

**Comportamiento Actual:**
- Textarea con auto-resize al escribir (event listener `input`)
- Al enviar, solo se limpia el texto pero no se resetea el height

**Comportamiento Deseado:**
- Restaurar a tamaño inicial (1-2 líneas) después de enviar

**Solución:**
```javascript
// En sendMessage() después de limpiar textarea.value
textarea.style.height = 'auto'; // Reset a altura mínima
textarea.style.height = '38px'; // Altura inicial (1 línea)
```

**Archivos:**
- `event-handlers.blade.php` - Función `sendMessage()`

**Tiempo Estimado:** 15 minutos

---

### BUG-3: Bubble de Usuario Sin Iconos Copy/Raw 🟡
**Descripción:** Los bubbles del usuario no muestran los iconos de "Copy" y "View Raw Response" en el header (solo en bubbles del asistente).

**Razón:**
- Código condicional `@if ($message->role === 'assistant')` oculta botones

**Solución:**
- Mostrar "Copy" en AMBOS (usuario y asistente)
- "View Raw" solo para asistente (tiene `raw_response`)
- Verificar que `copyMessageContent()` funciona para mensajes de usuario

**Archivos:**
- `message-bubble.blade.php` - Modificar condicionales de botones

**Tiempo Estimado:** 20 minutos

---

### BUG-4: Cancel Request No Detiene Ollama (Investigación) 🔴
**Descripción:** Al cancelar streaming con Ollama local, la petición parece seguir procesándose en background (sistema relentizado).

**Hipótesis:**
- EventSource.close() solo cierra conexión cliente → servidor
- Backend (Ollama) sigue generando respuesta aunque nadie escuche
- No hay mecanismo de abort/cancel en backend

**Investigación Necesaria:**
1. Verificar si Ollama acepta señal de cancel/abort
2. Revisar si Laravel EventStream puede propagar abort signal
3. Implementar endpoint POST `/cancel` que mate proceso Ollama si es posible

**Archivos a Revisar:**
- `StreamController.php` - Método `streamTest()`
- Documentación Ollama API - Cancel/Abort endpoints

**Tiempo Estimado:** 2 horas (investigación + implementación)

---

### BUG-5: Checkmark "Saved" con Fade Out Innecesario 🟡
**Descripción:** El checkmark animado que aparece al guardar mensaje en DB desaparece después de 2 segundos, pero sería más útil mantenerlo visible permanentemente en nuevos bubbles.

**Comportamiento Actual:**
```javascript
// En showSavedCheckmark()
setTimeout(() => {
    checkmark.classList.remove('show');
    checkmark.classList.add('hide');
    setTimeout(() => {
        checkmark.remove();
    }, 300);
}, 2000); // Desaparece después de 2 segundos
```

**Comportamiento Deseado:**
- El checkmark debe permanecer visible en el footer del bubble
- Solo desaparece cuando usuario recarga página (bubbles antiguos no lo muestran)
- Sirve como indicador visual de que el mensaje está guardado en DB

**Solución:**
```javascript
// Opción A: Eliminar timeouts (más simple)
const showSavedCheckmark = (footer) => {
    // ... código existente ...
    footer.appendChild(checkmark);
    checkmark.classList.add('show');
    // SIN timeouts - queda permanente
};

// Opción B: Agregar clase "permanent" al bubble
const showSavedCheckmark = (footer, permanent = true) => {
    // ... código existente ...
    if (!permanent) {
        setTimeout(() => { /* fade out */ }, 2000);
    }
};
```

**Archivos:**
- `event-handlers.blade.php` - Función `showSavedCheckmark()`

**Tiempo Estimado:** 10 minutos

---

### BUG-6: "New Chat" Sin Advertencia Durante Streaming 🔴
**Descripción:** Si usuario pulsa "New Chat" mientras hay streaming activo, se pierde el progreso sin advertencia.

**Comportamiento Actual:**
- Botón "New Chat" navega directamente a nueva sesión
- No verifica si hay streaming en proceso
- No cancela streaming activo antes de navegar
- Usuario pierde respuesta generándose

**Comportamiento Deseado:**
1. Detectar si hay streaming activo (`eventSource !== null`)
2. Mostrar SweetAlert de advertencia:
   - Título: "⚠️ Streaming in Progress"
   - Mensaje: "You have a response being generated. Are you sure you want to start a new chat?"
   - Botones: "Cancel" (default) / "Continue"
3. Si usuario confirma:
   - Cancelar streaming actual (llamar protocolo de cancelación)
   - Esperar confirmación de cancelación
   - Navegar a nueva sesión

**Solución:**
```javascript
// En listener de newChatBtn
newChatBtn?.addEventListener('click', async (e) => {
    // Prevenir navegación default
    e.preventDefault();
    
    // Check si hay streaming activo
    if (eventSource !== null) {
        const result = await Swal.fire({
            title: '⚠️ Streaming in Progress',
            text: 'You have a response being generated. Are you sure you want to start a new chat?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Stay Here',
            reverseButtons: true
        });
        
        if (!result.isConfirmed) {
            return; // Usuario cancela, no hacer nada
        }
        
        // Usuario confirma - cancelar streaming
        if (eventSource) {
            eventSource.close();
            eventSource = null;
            // Cleanup UI...
        }
    }
    
    // Proceder con "New Chat" (mostrar prompt título)
    const { value: chatTitle } = await Swal.fire({
        title: 'New Chat Session',
        input: 'text',
        inputPlaceholder: 'Enter chat title (optional)',
        showCancelButton: true
    });
    
    if (chatTitle !== undefined) {
        window.location.href = '{{ route("admin.llm.quick-chat.new") }}?title=' + 
            encodeURIComponent(chatTitle || '');
    }
});
```

**Archivos:**
- `event-handlers.blade.php` - Listener de `newChatBtn`

**Tiempo Estimado:** 30 minutos

---

## ⚙️ CONFIGURACIÓN EN CHAT ADMINISTRATION

**Nuevos Settings a Agregar:**

### 1. Fancy Animations Toggle
- Habilitar/deshabilitar efectos visuales avanzados
- Incluye: checkmark bounce, scroll button fade, hover effects
- Default: Enabled

### 2. Notificaciones Sonoras Toggle
- Habilitar/deshabilitar sonido al completar respuesta
- Solo activo si tab no está en foco
- Default: Enabled

### 3. Keyboard Shortcuts Mode
- Radio buttons:
  - Modo A: Enter = enviar, Shift+Enter = nueva línea (Default)
  - Modo B: Enter = nueva línea, Shift+Enter = enviar
- Default: Modo A

**Archivos:**
- `chat-administration.blade.php` - Agregar 3 nuevos settings
- `event-handlers.blade.php` - Leer settings de localStorage/DB
- Guardar en tabla `llm_manager_chat_configurations` (campo JSON `ui_preferences`)

**Tiempo Estimado:** 1.5 horas

---

## 🗂️ ARCHIVOS RELACIONADOS

### Archivos Principales a Modificar
```
resources/views/vendor/llm-manager/chat/
├── partials/
│   ├── message-bubble.blade.php          # Header refactor + delete button
│   ├── event-handlers.blade.php          # Streaming status, keyboard, bugs
│   ├── streaming-status-indicator.blade.php (NUEVO)
│   └── messages-container.blade.php      # Scroll fix
├── layouts/
│   ├── split-horizontal-layout.blade.php # Indicador position
│   └── chat-card.blade.php               # Indicador position
└── styles/
    └── split-horizontal.blade.php        # Hover effects, CSS animations

resources/views/vendor/llm-manager/
└── chat-administration.blade.php         # 3 nuevos settings

public/vendor/llm-manager/
└── sounds/                               # Audio files (NUEVO)
    └── notification.mp3

src/Http/Controllers/
└── MessageController.php                 # deleteMessage() endpoint (NUEVO)
```

### Archivos de Referencia (Reutilización)
```
resources/views/vendor/llm-manager/monitor/
├── partials/
│   ├── MonitorAPI.js                     # Estado streaming events
│   └── MonitorInstance.js                # Listeners open/chunk/done
└── console.blade.php                     # Timeline UI inspiration
```

---

## 🧩 DEPENDENCIAS Y REUTILIZACIÓN

### Código Existente Reutilizable

#### 1. Streaming Events Listeners (Monitor)
**Archivo:** `MonitorAPI.js` (líneas 45-120)
```javascript
eventSource.addEventListener('open', function(e) {
    // Estado: "Connecting..."
});

eventSource.addEventListener('chunk', function(e) {
    // Estado: "Thinking..." → "Typing..."
});

eventSource.addEventListener('done', function(e) {
    // Estado: "Completed" + trigger notificación sonora
});
```

**Reutilización:**
- Extraer lógica a `StreamingStatusIndicator.js`
- Compartir entre MonitorAPI y event-handlers

#### 2. Timeline UI (Context Messages)
**Archivo:** `request-inspector.blade.php` (líneas 180-250)
```blade
<div class="timeline-item">
    <div class="timeline-badge">System</div>
    <div class="timeline-content">...</div>
</div>
```

**Reutilización:**
- Mismo patrón visual para indicador de streaming status
- Badge circular con icono animado + texto de estado

#### 3. Smart Auto-Scroll System
**Archivo:** `event-handlers.blade.php` (líneas 89-128)
```javascript
function isAtBottom() {
    return messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < 100;
}

function toggleScrollButton(show) { ... }
```

**Reutilización:**
- Integrar con streaming status indicator
- Ocultar indicador si usuario hace scroll arriba (igual que badge)

---

## 📐 ARQUITECTURA PROPUESTA

### 1. Streaming Status Indicator Component

**Estructura HTML (Sticky Header):**
```blade
{{-- streaming-status-indicator.blade.php --}}
<div id="streaming-status-{{ $session->id }}" class="streaming-status d-none">
    <div class="status-badge">
        <span class="status-icon spinner-border spinner-border-sm me-2"></span>
        <span class="status-text">Connecting...</span>
    </div>
</div>
```

**Estados y Transiciones:**
```javascript
// event-handlers.blade.php
const streamingStatus = {
    element: document.getElementById('streaming-status-{{ $session->id }}'),
    
    show(state) {
        this.element.classList.remove('d-none');
        this.setState(state);
    },
    
    setState(state) {
        const { icon, text } = this.getStateConfig(state);
        this.element.querySelector('.status-icon').className = icon;
        this.element.querySelector('.status-text').textContent = text;
    },
    
    getStateConfig(state) {
        const configs = {
            connecting: { icon: 'spinner-border spinner-border-sm me-2', text: 'Connecting...' },
            thinking: { icon: 'spinner-border spinner-border-sm me-2', text: 'Thinking...' },
            typing: { icon: 'typing-dots me-2', text: 'Typing...' },
        };
        return configs[state];
    },
    
    hide() {
        this.element.classList.add('fade-out');
        setTimeout(() => this.element.classList.add('d-none'), 300);
    }
};

// Listeners
eventSource.addEventListener('open', () => streamingStatus.show('connecting'));
eventSource.addEventListener('chunk', (e) => {
    const chunkCount = parseInt(e.data.match(/chunk (\d+)/)?.[1] || 0);
    if (chunkCount === 1) streamingStatus.setState('thinking');
    if (chunkCount > 5) streamingStatus.setState('typing');
});
eventSource.addEventListener('done', () => streamingStatus.hide());
```

**CSS Animations:**
```css
.streaming-status {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--bs-light);
    padding: 8px 16px;
    border-bottom: 1px solid var(--bs-border-color);
    transition: opacity 0.3s ease;
}

.typing-dots {
    display: inline-block;
    width: 20px;
    height: 4px;
    background: linear-gradient(90deg, #0d6efd 33%, transparent 0);
    background-size: 6px 4px;
    animation: typingDots 1s infinite;
}

@keyframes typingDots {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
}
```

---

### 2. Sound Notification System

**Implementación:**
```javascript
// event-handlers.blade.php
const soundNotification = {
    enabled: true, // Leer de localStorage/DB
    audio: new Audio('/vendor/llm-manager/sounds/notification.mp3'),
    
    play() {
        if (!this.enabled) return;
        if (document.visibilityState === 'hidden') {
            this.audio.play().catch(err => console.warn('Sound blocked:', err));
        }
    }
};

// Trigger en done event
eventSource.addEventListener('done', function(e) {
    soundNotification.play();
    // ... resto de lógica
});
```

**Settings Toggle:**
```blade
{{-- chat-administration.blade.php --}}
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="enableSounds" checked>
    <label class="form-check-label" for="enableSounds">
        Notificaciones Sonoras
    </label>
</div>

<script>
document.getElementById('enableSounds').addEventListener('change', (e) => {
    localStorage.setItem('llm-sounds-enabled', e.target.checked);
});
</script>
```

---

### 3. Keyboard Shortcuts System

**Implementación:**
```javascript
// event-handlers.blade.php
const keyboardShortcuts = {
    mode: localStorage.getItem('llm-keyboard-mode') || 'A', // A = Enter send, B = Shift+Enter send
    
    handleKeydown(event, textarea) {
        if (event.key !== 'Enter') return;
        
        const shouldSend = (this.mode === 'A' && !event.shiftKey) || 
                          (this.mode === 'B' && event.shiftKey);
        
        if (shouldSend) {
            event.preventDefault();
            sendMessage();
        }
    }
};

// Listener en textarea
textarea.addEventListener('keydown', (e) => {
    keyboardShortcuts.handleKeydown(e, textarea);
});
```

**Settings Radio:**
```blade
{{-- chat-administration.blade.php --}}
<div class="mb-3">
    <label class="form-label">Keyboard Shortcuts Mode</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="keyboardMode" id="modeA" value="A" checked>
        <label class="form-check-label" for="modeA">
            <code>Enter</code> = enviar, <code>Shift+Enter</code> = nueva línea
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="keyboardMode" id="modeB" value="B">
        <label class="form-check-label" for="modeB">
            <code>Enter</code> = nueva línea, <code>Shift+Enter</code> = enviar
        </label>
    </div>
</div>
```

---

## 🧪 TESTING

### Manual Testing Checklist

#### Streaming Status Indicator
- [ ] Aparece "Connecting..." al abrir EventSource
- [ ] Cambia a "Thinking..." en primer chunk
- [ ] Cambia a "Typing..." después de 5 chunks
- [ ] Desaparece con fade out al completar (done event)
- [ ] No interfiere con scroll (sticky position funciona)

#### Sound Notification
- [ ] NO suena si tab está activa (visibilityState === 'visible')
- [ ] SÍ suena si tab NO está activa (visibilityState === 'hidden')
- [ ] Toggle en Chat Administration funciona (localStorage)
- [ ] Audio file carga correctamente (no 404)

#### Keyboard Shortcuts
- [ ] Modo A: Enter envía, Shift+Enter nueva línea
- [ ] Modo B: Enter nueva línea, Shift+Enter envía
- [ ] Cambio de modo persiste (localStorage)
- [ ] No conflicto con auto-resize del textarea

#### Delete Message
- [ ] Botón "Delete" visible en header de bubbles
- [ ] Modal de confirmación aparece
- [ ] Mensaje eliminado de DB
- [ ] Mensaje removido del DOM
- [ ] Contador de mensajes actualizado (-1)

#### Bug Fixes
- [ ] Scroll inicial NO visible (behavior: instant)
- [ ] Textarea restaura tamaño después de enviar
- [ ] Botón "Copy" visible en bubbles de usuario
- [ ] Cancel request detiene Ollama (si implementado)

---

## 📊 PROGRESO

**Estado Actual:** 3/14 items completados (21%)
**Última Actualización:** 9 de diciembre de 2025, 22:50

### Bug Fixes (3/6) ✅
- [x] **BUG-2:** Textarea resize fix (e59259b) - 15 min
- [x] **BUG-3:** User bubble icons (64c0518) - 20 min
- [x] **BUG-1:** Scroll inicial invisible (54b6554) - 30 min
- [ ] **BUG-4:** Cancel request investigation - 2h (APLAZADO)
- [ ] **BUG-5:** Checkmark fade out innecesario - 10 min (NUEVO)
- [ ] **BUG-6:** New Chat sin advertencia durante streaming - 30 min (NUEVO)

### Implementaciones (0/7)
- [ ] Notificación sonora inteligente
- [ ] Botón borrar mensaje
- [ ] Indicador streaming status
- [ ] Header bubble refactor
- [ ] Keyboard shortcuts
- [ ] Hover effects
- [ ] Efecto typewriter (opcional)

### Configuración (0/1)
- [ ] Chat Administration settings (3 nuevos toggles)

---

## 🎯 ORDEN DE IMPLEMENTACIÓN RECOMENDADO

### Fase 1: Bug Fixes (Alta Prioridad) - 1.5 horas ✅ 3/6
1. ✅ **BUG-2:** Textarea resize (15 min) - COMPLETADO (e59259b)
2. ✅ **BUG-3:** User bubble icons (20 min) - COMPLETADO (64c0518)
3. ✅ **BUG-1:** Scroll inicial invisible (30 min) - COMPLETADO (54b6554)
4. ⏸️ **BUG-4:** Cancel request investigation (2 horas) - APLAZADO
5. 🆕 **BUG-5:** Checkmark fade out (10 min) - NUEVO
6. 🆕 **BUG-6:** New Chat warning (30 min) - NUEVO

### Fase 2: Core UX Features - 4 horas
1. ✅ **Keyboard Shortcuts** (1 hora) - Alto impacto, bajo esfuerzo
2. ✅ **Hover Effects** (30 min) - Quick win visual
3. ✅ **Streaming Status Indicator** (2.5 horas) - Feature más complejo
4. ✅ **Sound Notification** (1.5 horas) - Depende de status indicator

### Fase 3: Advanced Features - 3.5 horas
1. ✅ **Header Bubble Refactor** (1.5 horas) - UI cleanup
2. ✅ **Delete Message** (2 horas) - Backend + frontend

### Fase 4: Configuration - 1.5 horas
1. ✅ **Chat Administration** (1.5 horas) - Centralizar settings

**Total:** 10.5 horas (sin BUG-4 investigation)

---

## 🎉 MILESTONE DE COMPLETADO

Este plan se considerará **100% completado** cuando:

✅ **Todas las features implementadas:**
- Streaming status indicator con 3 estados (connecting, thinking, typing)
- Sound notification condicional (solo si tab no activa)
- Keyboard shortcuts configurables (2 modos)
- Delete message funcional (backend + UI)
- Header bubble con segunda línea de acciones
- Hover effects en bubbles

✅ **Todos los bugs corregidos:**
- Scroll inicial invisible
- Textarea resize automático
- User bubble icons visibles
- Checkmark permanente en new bubbles
- New Chat warning durante streaming
- Cancel request investigation documentada (con o sin solución implementada)

✅ **Chat Administration actualizado:**
- 3 nuevos settings (animations, sounds, keyboard)
- Persistencia en localStorage o DB
- UI clara y organizada

✅ **Testing manual completado:**
- Checklist 100% verificado
- No regressions en features existentes
- Performance aceptable (no lag en UI)

---

## 📚 LECCIONES APRENDIDAS ANTICIPADAS

**Lessons to Document:**
1. **Visibility API:** Uso de `document.visibilityState` para notificaciones inteligentes
2. **Keyboard Events:** Manejo de `event.shiftKey` + `event.key` para shortcuts
3. **Streaming State Machine:** Transiciones claras entre estados (connecting → thinking → typing)
4. **Settings Persistence:** localStorage vs DB para preferencias de usuario
5. **Cancel Signal Propagation:** Limitaciones de EventSource + Laravel + Ollama (si BUG-4 no tiene solución)

---

## 🔗 REFERENCIAS

**Documentación Relacionada:**
- [PLAN-v1.0.7.md](./PLAN-v1.0.7.md) - Plan padre
- [PLAN-v1.0.7-HANDOFF-TO-NEXT-COPILOT.md](./archive/PLAN-v1.0.7-HANDOFF-TO-NEXT-COPILOT.md) - Context handoff
- [STREAMING-SYSTEM-DOCUMENTATION.md](/Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager/docs/STREAMING-SYSTEM-DOCUMENTATION.md) - Arquitectura streaming
- [SESSION-VALIDATION-COMPLETE.md](/Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL/dev/copilot/sessions/README.md) - Session achievements

**Archivos Código Clave:**
- `event-handlers.blade.php` - Core streaming + UI logic
- `message-bubble.blade.php` - Message UI component
- `MonitorAPI.js` - Streaming events reference
- `chat-administration.blade.php` - Settings UI

---

## 📝 COMMITS DE IMPLEMENTACIÓN

### FASE 1: Bug Fixes (9 dic 2025)
1. **849c50f** - docs: add Chat UX Improvements plan + handoff document (v1.0.7 annex, 12 pending items)
2. **e59259b** - fix: reset textarea height after send using Metronic autosize.update() (BUG-2)
3. **64c0518** - fix: show Copy button in user bubbles, Raw only in assistant (BUG-3)
4. **54b6554** - fix: invisible initial scroll - instant behavior + 50ms timeout (BUG-1)

**Total:** 4 commits, 3 bug fixes completados

---

**Última Actualización:** 9 de diciembre de 2025, 22:40
**Responsable Actual:** GitHub Copilot (Claude Sonnet 4.5)
**Siguiente Copilot:** Leer [HANDOFF-NEXT-COPILOT-CHAT-UX.md](./archive/HANDOFF-NEXT-COPILOT-CHAT-UX.md)
