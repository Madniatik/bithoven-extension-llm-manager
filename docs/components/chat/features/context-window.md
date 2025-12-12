# Context Window Visual Indicator

**Versión:** v0.3.0  
**Estado:** ✅ Completado (10 dic 2025)  
**Tiempo Implementación:** 6.75h (including fixes + toggle + dynamic application)  
**Commits:** 2927a87, 048aba3, 9e60716, f51d4f3, 62a463a, f2e5798, d2d02b2, 07d146e, e7edf38, c6de9b3, 0d17b17

---

## 📋 Overview

Marcador visual en message bubbles que indica qué mensajes están incluidos en el contexto actual (`size_context` setting). Crucial para que el usuario entienda el "alcance de memoria" del asistente LLM.

---

## 🎯 Propósito

### ¿Por qué es necesario?

- **`size_context` define cuántos mensajes previos se envían al LLM como contexto**
- Valor configurable: 5, 10, 20, 50, ALL (0)
- Usuario necesita saber qué ve el LLM para depuración
- Pregunta frecuente: *"¿Por qué el LLM no recuerda esto?"* → mensaje fuera de contexto

### Beneficios

- ✅ **Feedback Visual Claro:** Usuario sabe exactamente qué ve el LLM
- ✅ **Debugging:** Identificar por qué el asistente no recuerda información
- ✅ **Educativo:** Enseña el concepto de context window a usuarios nuevos
- ✅ **Performance:** Ayuda a optimizar uso de tokens (contexto más pequeño = más rápido)

---

## ✨ Características

### Funcionalidad

- ✅ Marcador visual dinámico para mensajes "en contexto"
- ✅ Se actualiza en **tiempo real** al cambiar `size_context` en Settings
- ✅ Toggle enable/disable en Workspace Settings (UX Enhancements)
- ✅ Aplicación dinámica **sin reload** (custom events)
- ✅ Multi-instance support (sessionId scoped localStorage)

### Diseño Implementado

**Opción A (Implementada):** Border Left + Opacity

```css
/* Mensajes EN contexto */
.bubble-content-wrapper.in-context {
    border-left: 3px solid var(--bs-primary-light);
    --bs-border-opacity: 0.3;
}

/* Mensajes FUERA de contexto */
/* No mostrar indicador visual (design decision) */
```

**Decisiones de Diseño:**
1. ✅ Border + opacity (sutil, no satura UI)
2. ✅ Solo indicador para mensajes IN-context (no out-of-context)
3. ✅ Border con 30% opacity (`--bs-border-opacity: 0.3`)
4. ✅ Color: `var(--bs-primary-light)` (Metronic variable)

---

## 🚀 Implementación

### Backend (Controller)

```php
// QuickChatController.php

public function show($sessionId)
{
    $session = ChatSession::findOrFail($sessionId);
    $messages = $session->messages()->orderBy('created_at')->get();
    
    // Obtener context_limit de configuración
    $contextLimit = $session->workspace->configuration->context_limit ?? 0; // 0 = ALL
    
    // Marcar últimos N mensajes como "in context"
    $totalMessages = $messages->count();
    
    if ($contextLimit === 0) {
        // ALL messages
        $messages->each(fn($msg) => $msg->is_in_context = true);
    } else {
        // Últimos N mensajes
        $messages = $messages->map(function($message, $index) use ($totalMessages, $contextLimit) {
            $message->is_in_context = ($totalMessages - $index) <= $contextLimit;
            return $message;
        });
    }
    
    return view('llm-manager::chat.quick-chat', compact('session', 'messages'));
}
```

### Frontend (JavaScript)

```javascript
// event-handlers.blade.php

function updateContextIndicators() {
    // Check if feature is enabled
    const indicatorEnabled = localStorage.getItem(
        `context_indicator_enabled_${sessionId}`
    ) !== 'false'; // Default true
    
    if (!indicatorEnabled) {
        // Remove all indicators
        $('.bubble-content-wrapper').removeClass('in-context');
        return;
    }
    
    // Get context limit from settings
    const contextLimit = parseInt(
        localStorage.getItem(`context_limit_${sessionId}`) || 
        $('#sizeContextSetting').val() || 
        0
    );
    
    // Get all bubbles (reverse order - most recent first)
    const bubbles = $('.bubble-content-wrapper').get().reverse();
    
    // Apply indicators
    if (contextLimit === 0) {
        // ALL messages in context
        $('.bubble-content-wrapper').addClass('in-context');
    } else {
        // Last N messages
        bubbles.forEach((bubble, index) => {
            const $bubble = $(bubble);
            if (index < contextLimit) {
                $bubble.addClass('in-context');
            } else {
                $bubble.removeClass('in-context');
            }
        });
    }
}

// Listener: Context limit changed
$('#sizeContextSetting').on('change', function() {
    const value = $(this).val();
    localStorage.setItem(`context_limit_${sessionId}`, value);
    updateContextIndicators();
    
    const label = value === '0' ? 'All Messages' : `${value} messages`;
    toastr.info(`Context window updated: ${label}`);
});

// Listener: Toggle enabled/disabled
window.addEventListener('context-indicator-toggled', (event) => {
    const enabled = event.detail.enabled;
    localStorage.setItem(`context_indicator_enabled_${sessionId}`, enabled);
    updateContextIndicators();
});

// Initialize on page load
$(document).ready(function() {
    updateContextIndicators();
});

// Update after new message
window.addEventListener('llm-streaming-completed', () => {
    setTimeout(updateContextIndicators, 100);
});
```

### Settings UI (chat-settings.blade.php)

```blade
{{-- Context Limit Dropdown --}}
<div class="menu-item px-3">
    <div class="menu-content px-3 py-3">
        <label class="form-label fw-bold">Context Window</label>
        <select id="sizeContextSetting" 
                class="form-select form-select-sm"
                data-control="select2"
                data-hide-search="true">
            <option value="5">5 messages</option>
            <option value="10" selected>10 messages</option>
            <option value="20">20 messages</option>
            <option value="50">50 messages</option>
            <option value="0">All Messages</option>
        </select>
        <div class="form-text">
            How many previous messages to include as context
        </div>
    </div>
</div>
```

### UX Toggle (ux-enhancements.blade.php)

```blade
{{-- Context Indicator Toggle --}}
<div class="form-check form-switch form-check-custom mb-3">
    <input class="form-check-input" 
           type="checkbox" 
           id="contextIndicatorToggle"
           checked />
    <label class="form-check-label" for="contextIndicatorToggle">
        <span class="fw-bold">Show Context Window Indicator</span>
        <span class="text-muted d-block fs-7">
            Visual border on messages within current context window
        </span>
    </label>
</div>

<script>
document.getElementById('contextIndicatorToggle').addEventListener('change', function() {
    const enabled = this.checked;
    
    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('context-indicator-toggled', {
        detail: { enabled }
    }));
    
    toastr.info(
        enabled 
            ? 'Context indicator enabled' 
            : 'Context indicator disabled'
    );
});
</script>
```

### CSS (split-horizontal-layout.blade.php)

```css
/* Context Window Indicator */
.bubble-content-wrapper.in-context {
    border-left: 3px solid var(--bs-primary-light);
    --bs-border-opacity: 0.3;
}

/* Smooth transition */
.bubble-content-wrapper {
    transition: border-left 0.3s ease, opacity 0.3s ease;
}
```

---

## ⚙️ Configuration

### Defaults (ChatWorkspaceConfigValidator.php)

```php
'context_limit' => 0, // 0 = ALL messages (default changed from 10)
'context_indicator' => [
    'enabled' => true,
    'style' => 'border', // 'border', 'badge', 'icon'
    'opacity' => 0.3,
]
```

### localStorage Keys

```javascript
// Per-session storage
`context_limit_${sessionId}` // "0", "5", "10", "20", "50"
`context_indicator_enabled_${sessionId}` // "true", "false"
```

---

## 🧪 Testing

### Escenarios Probados ✅

1. **Context Limit = 10** ✅
   - Últimos 10 mensajes tienen border azul
   - Mensajes más antiguos sin border
   - Cambiar a 5 → Solo últimos 5 con border
   - Cambiar a ALL (0) → Todos los mensajes con border

2. **Context Limit = 0 (All Messages)** ✅
   - Todos los mensajes tienen border
   - No hay mensajes "fuera de contexto"

3. **Toggle Disabled** ✅
   - Desactivar toggle → Borders desaparecen
   - Activar toggle → Borders vuelven a aparecer
   - Sin page reload

4. **New Message Added** ✅
   - Enviar nuevo mensaje
   - Después de streaming completed
   - Indicadores se actualizan automáticamente
   - Mensaje más antiguo sale de contexto si limit < total

5. **Multiple Sessions** ✅
   - Abrir sesión A con context_limit = 10
   - Abrir sesión B con context_limit = 20
   - Configuraciones independientes (sessionId scoped)

6. **Page Reload** ✅
   - Configuración persiste en localStorage
   - Indicadores se aplican correctamente al cargar

---

## 🎨 Design Variations (Not Implemented)

### Opción B: Badge Indicator

```blade
@if($isInContext)
    <span class="badge badge-light-success badge-sm">
        <i class="ki-outline ki-check-circle fs-6"></i> In Context
    </span>
@else
    <span class="badge badge-light-secondary badge-sm">
        <i class="ki-outline ki-archive fs-6"></i> Archived
    </span>
@endif
```

### Opción C: Icon Indicator

```blade
@if($isInContext)
    <i class="ki-outline ki-eye text-success fs-4" 
       data-bs-toggle="tooltip" 
       title="LLM can see this message"></i>
@else
    <i class="ki-outline ki-eye-slash text-muted fs-4" 
       data-bs-toggle="tooltip" 
       title="Out of context window"></i>
@endif
```

**Decisión:** Opción A (border) es más sutil y no satura la UI con badges/icons extras.

---

## 📊 Performance

### Bundle Size Impact

- CSS: +10 líneas (~0.2 KB)
- JavaScript: +35 líneas (~1.1 KB)
- Total: ~1.3 KB adicionales

### Runtime Performance

- `updateContextIndicators()` ejecuta en <5ms para 100 mensajes
- No afecta rendering de streaming
- Listeners pasivos (no bloquean UI)

---

## 🐛 Bugs Fixed

1. **DOM Selector Incorrecto** (2927a87)
   - Usaba `.message-bubble` en lugar de `.bubble-content-wrapper`
   - Fix: Selector correcto + testing con inspector

2. **Settings Selector No Funciona** (048aba3)
   - Dropdown ID incorrecto
   - Fix: Usar `#sizeContextSetting` consistente

3. **"All Messages" No Parsea Correctamente** (9e60716, f51d4f3)
   - parseInt("All") = NaN
   - Fix: Usar value="0" para "All"

4. **Border Muy Llamativo** (62a463a, f2e5798, d2d02b2)
   - Color muy saturado
   - Fix: `var(--bs-primary-light)` + opacity 30%

---

## 📝 Files Modified

1. `event-handlers.blade.php` - `updateContextIndicators()` function
2. `split-horizontal-layout.blade.php` - CSS rules
3. `chat-settings.blade.php` - Context limit dropdown
4. `settings-manager.blade.php` - localStorage persistence
5. `ux-enhancements.blade.php` - Toggle control
6. `ChatWorkspaceConfigValidator.php` - Defaults y validation

---

## 🔗 Related Features

- **[Chat Settings](../configuration/features.md#chat-settings)** - Configuration options
- **[Monitor Export](./monitor-export.md)** - Export logs with context info
- **[Request Inspector](./request-inspector.md)** - Shows exact context sent to LLM

---

**Documentación Verificada:** PLAN-v0.3.0-chat-ux.md (Context Window Visual Indicator)  
**Última Actualización:** 10 de diciembre de 2025
