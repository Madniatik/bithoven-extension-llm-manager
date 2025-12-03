# 🧩 Chat Workspace Component - ACTIVO

**Estado:** ✅ Componente registrado y en uso activo  
**Versión:** v2.0 (Optimizado con código particionado)  
**Última actualización:** 3 diciembre 2025, 06:45

---

## 📁 Estructura de Directorios

```
components/chat/
├── chat-workspace.blade.php          # Componente principal (orquestador)
├── ChatWorkspace.blade.php          # Clase PHP del componente
├── README.md                        # Este archivo
│
├── layouts/                         # Layouts intercambiables
│   ├── sidebar-layout.blade.php     # Monitor sidebar derecha (60/40)
│   └── split-horizontal-layout.blade.php  # Monitor split inferior (70/30)
│
├── partials/                        # Partials reutilizables
│   ├── chat-card.blade.php          # Card principal del chat
│   ├── chat-messages.blade.php      # Renderizado de mensajes
│   ├── input-form.blade.php         # Formulario de input
│   ├── messages-container.blade.php # Container con scroll de mensajes
│   │
│   ├── scripts/                     # Scripts particionados (Alpine.js)
│   │   ├── chat-workspace.blade.php    # ✨ Alpine: chatWorkspace component
│   │   ├── split-resizer.blade.php     # ✨ Alpine: splitResizer component
│   │   ├── clipboard-utils.blade.php   # Utilidades de portapapeles
│   │   ├── event-handlers.blade.php    # Event handlers globales
│   │   ├── message-renderer.blade.php  # Renderizado de markdown
│   │   └── settings-manager.blade.php  # Gestión de configuración
│   │
│   ├── styles/                      # Estilos particionados
│   │   ├── split-horizontal.blade.php  # ✨ Estilos del split layout
│   │   ├── buttons.blade.php           # Estilos de botones
│   │   ├── dependencies.blade.php      # Dependencias externas
│   │   ├── markdown.blade.php          # Estilos markdown
│   │   └── responsive.blade.php        # Media queries
│   │
│   ├── buttons/                     # Componentes de botones
│   ├── drafts/                      # Borradores/helpers
│   └── modals/                      # Modales (raw message, etc.)
│
└── shared/                          # Componentes compartidos
    ├── monitor.blade.php            # Monitor completo (métricas + historial + consola)
    ├── monitor-console.blade.php    # Solo consola (para split)
    ├── streaming-handler.js         # Handler de SSE streaming
    └── metrics-calculator.js        # Calculadora de métricas
```

---

## 🎯 Principios de Organización

### 1. **Separación de Responsabilidades**
- **Layouts** → Estructuras de página completas
- **Partials** → Fragmentos reutilizables
- **Scripts** → Lógica JavaScript/Alpine.js particionada
- **Styles** → CSS particionado por funcionalidad
- **Shared** → Componentes usados por múltiples layouts

### 2. **Carga Condicional**
```blade
{{-- En chat-workspace.blade.php --}}

{{-- Styles: Solo carga split-horizontal si es necesario --}}
@if($monitorLayout === 'split-horizontal')
    @include('llm-manager::components.chat.partials.styles.split-horizontal')
@endif

{{-- Scripts: Solo carga splitResizer si es necesario --}}
@if($monitorLayout === 'split-horizontal')
    @include('llm-manager::components.chat.partials.scripts.split-resizer')
@endif
```

### 3. **Reutilización de Código**

#### ❌ ANTES (Código duplicado - v1.0)
```blade
{{-- split-horizontal-layout.blade.php: 450 líneas --}}
<div>HTML completo</div>
@push('styles')<style>100+ líneas CSS inline</style>@endpush
@push('scripts')<script>150+ líneas JS inline</script>@endpush
```

#### ✅ DESPUÉS (Código particionado - v2.0)
```blade
{{-- split-horizontal-layout.blade.php: 150 líneas --}}
<div>Solo HTML estructura</div>
{{-- Styles y scripts en partials --}}

{{-- Partials creados: --}}
- partials/styles/split-horizontal.blade.php (100 líneas CSS)
- partials/scripts/split-resizer.blade.php (100 líneas Alpine.js)
- partials/scripts/chat-workspace.blade.php (50 líneas Alpine.js)
```

**Beneficios:**
- ✅ **66% reducción** en `split-horizontal-layout.blade.php` (450 → 150 líneas)
- ✅ **CSS reutilizable** independiente del layout
- ✅ **Alpine components** aislados y testables
- ✅ **Mantenibilidad** - cambios en un solo lugar
- ✅ **Carga condicional** - solo lo necesario

---

## 📦 Componentes Alpine.js

### 1. `chatWorkspace` (Global)
**Archivo:** `partials/scripts/chat-workspace.blade.php`

```javascript
Alpine.data('chatWorkspace', (showMonitor, monitorOpen, layout) => ({
    // Gestiona toggle monitor
    // Persiste estado en localStorage
    // Maneja modal en móvil
    // Compatible con ambos layouts
}))
```

**Usado en:** Todos los layouts (sidebar y split-horizontal)

### 2. `splitResizer` (Condicional)
**Archivo:** `partials/scripts/split-resizer.blade.php`

```javascript
Alpine.data('splitResizer', () => ({
    // Maneja drag & drop del separador horizontal
    // Calcula tamaños dinámicamente (20%-80%)
    // Persiste posiciones en localStorage
    // Feedback visual durante drag
}))
```

**Usado en:** Solo `split-horizontal-layout.blade.php`

---

## 🎨 Estilos Particionados

| Archivo | Propósito | Usado en | Líneas |
|---------|-----------|----------|--------|
| `dependencies.blade.php` | Dependencias externas (highlight.js, etc.) | Todos | ~50 |
| `markdown.blade.php` | Estilos de contenido markdown | Todos | ~80 |
| `buttons.blade.php` | Botones de acción (copy, regenerate, etc.) | Todos | ~60 |
| `responsive.blade.php` | Media queries mobile | Todos | ~40 |
| `split-horizontal.blade.php` | Layout split específico | Solo split-horizontal | ~100 |

---

## 🚀 Uso del Componente

### Sidebar Layout (Monitor derecha 40%)
```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :show-monitor="true"
    :monitor-open="true"
    monitor-layout="sidebar"
/>
```

### Split Horizontal Layout (Monitor abajo 30%)
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

## 📊 Métricas de Optimización

### Antes (v1.0 - Código mezclado)
- `split-horizontal-layout.blade.php`: **450 líneas**
- CSS inline: **100+ líneas**
- JS inline (Alpine.js): **200+ líneas**
- **Total:** ~750 líneas mezcladas (HTML + CSS + JS)

### Después (v2.0 - Código particionado)
- `split-horizontal-layout.blade.php`: **150 líneas** (solo HTML)
- `styles/split-horizontal.blade.php`: **100 líneas** (CSS puro)
- `scripts/split-resizer.blade.php`: **100 líneas** (Alpine.js puro)
- `scripts/chat-workspace.blade.php`: **50 líneas** (Alpine.js puro)
- **Total:** ~400 líneas **particionadas y reutilizables**

### Mejoras Cuantificadas
- ✅ **46% reducción** de código total (750 → 400 líneas)
- ✅ **66% reducción** en layout principal (450 → 150 líneas)
- ✅ **Separación completa** HTML/CSS/JS
- ✅ **3 componentes reutilizables** creados
- ✅ **Carga condicional** implementada
- ✅ **Testeable** (componentes aislados)

---

## 🔧 Registro del Componente

**Archivo:** `src/LLMManagerServiceProvider.php`

```php
Blade::component(
    'llm-manager-chat-workspace', 
    \Bithoven\LLMManager\View\Components\Chat\ChatWorkspace::class
);
```

**Estado:** ✅ Registrado y funcional

---

## 🐛 Fixes Aplicados

### 1. Sidebar Monitor Collapse (v2.0.1)
**Problema:** Al colapsar monitor en sidebar, columna permanecía en DOM (solo oculta visualmente)

**Fix:**
```blade
{{-- ANTES --}}
<div x-show="monitorOpen" class="col-lg-4 d-none d-lg-block">

{{-- DESPUÉS --}}
<div :class="monitorOpen ? 'col-lg-4 d-none d-lg-block' : 'd-none'">
```

**Resultado:** Chat expande al 100% cuando monitor se cierra

### 2. Split Structure (v2.0.0)
**Problema:** Split envolvía toda la card (header + body + footer)

**Fix:** Split solo afecta al body (mensajes + console), header y footer fuera

**Resultado:** Textarea siempre visible, header siempre visible

---

## 🔮 Próximas Mejoras Sugeridas

### 1. Extraer lógica del monitor
**Pendiente:** `shared/monitor.blade.php` tiene ~200 líneas de JS inline

**Plan:**
- Mover a `partials/scripts/monitor-api.blade.php`
- Reutilizar entre `monitor.blade.php` y `monitor-console.blade.php`

### 2. Unificar monitor y monitor-console
**Objetivo:** Evitar duplicación de lógica de logging

**Propuesta:**
```blade
{{-- Usar slots/props para customizar --}}
<x-monitor :type="console|full" />
```

### 3. Tests unitarios
- Alpine.js components (chatWorkspace, splitResizer)
- Drag & resize logic (20%-80% constraints)
- localStorage persistence
- Mobile responsiveness

### 4. Documentación de eventos
- Custom events emitidos
- Listeners externos
- Integración con streaming API

---

## 📝 Commits Relacionados

| Commit | Fecha | Descripción |
|--------|-------|-------------|
| `5c4caa1` | 03/12/2025 06:45 | refactor: partition split-horizontal code into partials |
| `30a000a` | 03/12/2025 06:40 | fix: sidebar monitor collapse hides column completely (d-none) |
| `7b3ea99` | 03/12/2025 06:30 | fix: split only affects card body, footer always visible |
| `...` | ... | Initial component creation |

---

**Versión:** 2.0.1  
**Última actualización:** 3 diciembre 2025, 06:45  
**Mantenedor:** ChatWorkspace Component Team
