# LLM Manager Extension - Documentación

**Versión:** 1.0.7  
**Última actualización:** 10 diciembre 2025, 13:10

> 📋 **Quick Index:** Ver [QUICK-INDEX.json](../QUICK-INDEX.json) para navegación optimizada de Copilot

---

## 🚀 Quick Start

| Link | Descripción |
|------|-------------|
| [Installation](guides/INSTALLATION.md) | Setup inicial (5 min) |
| [Usage Guide](guides/USAGE-GUIDE.md) | Uso básico |
| [API Reference](reference/API-REFERENCE.md) | Referencia completa |
| [FAQ](guides/FAQ.md) | Preguntas frecuentes |

---

## 📁 Estructura de Documentación

### 📘 Guías (guides/)
Documentación de usuario para instalación, configuración y uso básico.

- [INSTALLATION.md](guides/INSTALLATION.md) - Instalación y setup inicial
- [USAGE-GUIDE.md](guides/USAGE-GUIDE.md) - Uso básico de la extensión
- [CONFIGURATION.md](guides/CONFIGURATION.md) - Configuración de providers
- [EXAMPLES.md](guides/EXAMPLES.md) - Ejemplos prácticos
- [FAQ.md](guides/FAQ.md) - Preguntas frecuentes

### 📗 Referencias (reference/)
Documentación técnica de API y componentes.

- [API-REFERENCE.md](reference/API-REFERENCE.md) - Referencia completa de API
- [components/CHAT-WORKSPACE.md](reference/components/CHAT-WORKSPACE.md) - Chat Workspace Component (legacy)

### 🎯 Componentes (components/)
Documentación modular de componentes del sistema.

#### Chat Component
Documentación completa del sistema de configuración del Chat Workspace.

- **[Chat README](components/chat/README.md)** - Índice principal y referencia rápida

**Getting Started:**
- [Introduction](components/chat/getting-started/introduction.md) - Qué es el Chat Workspace Configuration System
- [Quick Start](components/chat/getting-started/quick-start.md) - Implementación en 5 minutos
- [Basic Usage](components/chat/getting-started/basic-usage.md) - Patrones de uso comunes

**Configuration:**
- [Overview](components/chat/configuration/overview.md) - Visión general del sistema de configuración
- [Reference](components/chat/configuration/reference.md) - Referencia completa de todas las opciones
- [Features](components/chat/configuration/features.md) - Detalle de cada feature disponible
- [Persistence](components/chat/configuration/persistence.md) - Sistema de guardado de preferencias en DB

**Guides:**
- [Examples](components/chat/guides/examples.md) - 10+ ejemplos de uso real
- [Migration Guide](components/chat/guides/migration.md) - Migración desde legacy props
- [Best Practices](components/chat/guides/best-practices.md) - Recomendaciones y patrones
- [Performance Tips](components/chat/guides/performance.md) - Optimizaciones (15-39% bundle reduction)

**API Reference:**
- [Workspace Component](components/chat/api/workspace-component.md) - Helper methods del componente
- [Config Validator](components/chat/api/config-validator.md) - ChatWorkspaceConfigValidator API
- [JavaScript API](components/chat/api/javascript-api.md) - API JavaScript para settings

**Features:**
- [Monitor Export](components/chat/features/monitor-export.md) - Export en CSV/JSON/SQL
- [Context Window](components/chat/features/context-window.md) - Visual indicator de context window
- [Request Inspector](components/chat/features/request-inspector.md) - Tab de debugging
- [Delete Message](components/chat/features/delete-message.md) - Feature de borrado de mensajes
- [Auto-Scroll](components/chat/features/auto-scroll.md) - Sistema smart auto-scroll
- [Notifications](components/chat/features/notifications.md) - Browser + Sound notifications

**Troubleshooting:**
- [Common Issues](components/chat/troubleshooting/common-issues.md) - Problemas comunes y soluciones
- [Testing](components/chat/troubleshooting/testing.md) - Suite de tests (27/27 passing)

### 🏗️ Arquitectura (architecture/)
Documentación de diseño interno y arquitecturas de sistemas.

- [MONITOR-ARCHITECTURE-v2.md](architecture/MONITOR-ARCHITECTURE-v2.md) - Sistema Monitor v2.0
- [OPENROUTER-RESPONSE-FORMAT.md](architecture/OPENROUTER-RESPONSE-FORMAT.md) - Formato OpenRouter

### 🔧 Debug (debug/)
Herramientas y guías de troubleshooting.

- [MONITOR-DEBUG-CHECKLIST.md](debug/MONITOR-DEBUG-CHECKLIST.md) - Checklist debugging Monitor
- [QUICK-DEBUG.js](debug/QUICK-DEBUG.js) - Snippets de debugging

### 🌐 Providers (providers/)
Documentación específica de providers LLM.

- [PROVIDER-COMPARISON.md](providers/PROVIDER-COMPARISON.md) - Comparación de providers

### 🤝 Contribución
- [CONTRIBUTING.md](CONTRIBUTING.md) - Guía para contribuidores

---

### 🧩 Componentes

#### Chat Workspace Component

Componente principal para interfaces de chat LLM con soporte para layouts duales, monitor integrado y sistema de configuración granular.

**📖 Documentación:**
- **[Chat Configuration System](components/chat/README.md)** - Sistema completo de configuración (v0.3.0)
- **[Legacy Guide](reference/components/CHAT-WORKSPACE.md)** - Guía legacy del componente (v2.1)

**Características principales:**
- ✅ **Config Array System:** Configuración granular mediante array único (v0.3.0)
- ✅ **Dual Layout System:** Sidebar (vertical) y Split-Horizontal (horizontal resizable)
- ✅ **Monitor Integrado:** 3 tabs (Console, Request Inspector, Activity Log)
- ✅ **Monitor Export:** CSV/JSON/SQL con session filtering (v0.3.0)
- ✅ **UX Enhancements:** Context Window Indicator, Auto-Scroll, Notifications (v0.3.0)
- ✅ **Settings Panel:** Personalización de UI con DB persistence (v0.3.0)
- ✅ **Streaming Support:** Compatible con Server-Sent Events (SSE)
- ✅ **Alpine.js Reactive:** Componentes reactivos sin Vue/React
- ✅ **Code Partitioning:** Carga condicional para máxima performance (15-39% reducción)
- ✅ **Backward Compatible:** Legacy props siguen funcionando (v0.3.0)

**Quick Links:**
- [Introduction](components/chat/getting-started/introduction.md) - Beneficios y arquitectura
- [Quick Start](components/chat/getting-started/quick-start.md) - Setup en 5 minutos
- [Examples](components/chat/guides/examples.md) - 10+ ejemplos de uso real
- [Configuration Reference](components/chat/configuration/reference.md) - Todas las opciones
- [Performance Tips](components/chat/guides/performance.md) - Optimizaciones (bundle reduction)

**Estado:** ✅ v0.3.0 - Production Ready (97% completado)  
**Testing:** 27/27 tests passing ✅

---

## 🚀 Quick Start

### 1. Instalación

```bash
composer require bithoven/llm-manager
php artisan vendor:publish --tag=llm-manager-config
php artisan vendor:publish --tag=llm-manager-assets
php artisan migrate
```

**Ver:** [INSTALLATION.md](INSTALLATION.md)

---

### 2. Configuración Básica

```env
# .env
LLM_DEFAULT_PROVIDER=openai
OPENAI_API_KEY=your-api-key
```

**Ver:** [CONFIGURATION.md](CONFIGURATION.md)

---

### 3. Primer Chat

```blade
{{-- Forma moderna (Config Array - Recomendado) --}}
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :config="$config"
/>

{{-- Forma legacy (sigue funcionando) --}}
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    monitor-layout="split-horizontal"
/>
```

**Ver:** [USAGE-GUIDE.md](guides/USAGE-GUIDE.md)  
**Config System:** [components/chat/README.md](components/chat/README.md)  
**Quick Start:** [components/chat/getting-started/quick-start.md](components/chat/getting-started/quick-start.md)

---

## 📖 Documentación por Tema

### Para Usuarios Nuevos

1. **[Instalación](INSTALLATION.md)** - Setup completo paso a paso
2. **[Guía de Uso](USAGE-GUIDE.md)** - Funcionalidades básicas
3. **[FAQ](FAQ.md)** - Respuestas a dudas comunes

### Para Administradores

1. **[Configuración](CONFIGURATION.md)** - LLM providers, settings, permisos
2. **[Ejemplos](EXAMPLES.md)** - Casos de uso reales

### Para Desarrolladores

1. **[API Reference](reference/API-REFERENCE.md)** - Métodos, clases, eventos
2. **[Chat Configuration System](components/chat/README.md)** - Sistema de configuración completo
3. **[Chat Workspace Component](reference/components/CHAT-WORKSPACE.md)** - Componente legacy (v2.1)
4. **[Contributing](CONTRIBUTING.md)** - Guía de contribución

---

## 🧩 Arquitectura de Componentes

```
LLM Manager Extension
├── Quick Chat (Interfaz principal)
│   └── ChatWorkspace Component (v0.3.0)
│       ├── Config Array System
│       │   ├── Features (monitor, settings_panel, persistence, toolbar)
│       │   ├── UI Elements (layouts, buttons, mode)
│       │   ├── Performance (lazy_load, minify, cache)
│       │   └── Advanced (multi_instance, custom_css, debug)
│       ├── Layouts
│       │   ├── Sidebar Layout (60/40 vertical)
│       │   └── Split-Horizontal Layout (70/30 horizontal)
│       ├── Monitor Components
│       │   ├── Full Monitor (3 tabs: console, request inspector, activity log)
│       │   └── Console Only (solo consola)
│       ├── UX Enhancements (v0.3.0)
│       │   ├── Context Window Indicator
│       │   ├── Smart Auto-Scroll
│       │   ├── Browser Notifications
│       │   └── Delete Message
│       └── Alpine.js Components
│           ├── chatWorkspace (global)
│           ├── splitResizer (condicional)
│           └── window.LLMMonitor API (global)
├── Admin Panel
│   ├── Configurations Manager
│   ├── Sessions Manager
│   ├── Settings Panel (v0.3.0)
│   └── Settings
└── API
    ├── Streaming Endpoint (SSE)
    ├── Chat Endpoint
    ├── Session Management
    └── Workspace Preferences (v0.3.0)
        ├── Save Settings
        ├── Get Settings
        └── Reset to Defaults
```

---

## 📊 Métricas de Performance

### Chat Workspace Component v0.3.0

**Code Partitioning (v2.1):**

| Métrica | Antes (v1.0) | Después (v2.1) | Mejora |
|---------|--------------|----------------|--------|
| split-horizontal.blade.php | 450 líneas | 150 líneas | **66%** ⬇️ |
| monitor.blade.php | 230 líneas | 100 líneas | **56%** ⬇️ |
| monitor-console.blade.php | 60 líneas | 20 líneas | **66%** ⬇️ |
| **Total componentes** | **740 líneas** | **270 líneas** | **63%** ⬇️ |

**Bundle Size Optimization (v0.3.0):**

| Configuración | Bundle Size | Reducción |
|---------------|-------------|-----------|
| **ALL ENABLED** | 119 KB | 0% (baseline) |
| **Monitor (1 tab)** | 102 KB | -15% |
| **No Monitor** | 85 KB | -29% |
| **Minimal** | 74 KB | -39% |

**Beneficios:**
- ✅ Código particionado en 7 archivos reutilizables
- ✅ Separación completa HTML/CSS/JS
- ✅ Carga condicional optimizada (15-39% reducción)
- ✅ Testing facilitado (componentes aislados)
- ✅ Mantenibilidad mejorada significativamente
- ✅ Config Array System con validación (v0.3.0)
- ✅ Settings Panel con DB persistence (v0.3.0)

**Ver:** [Performance Tips](components/chat/guides/performance.md)

---

## 🔧 Troubleshooting

### Problemas Comunes

**1. Monitor no aparece**
```bash
php artisan view:clear
php artisan optimize:clear
```

**2. Split resizer no funciona**
```javascript
localStorage.removeItem('llm_chat_split_sizes');
location.reload();
```

**3. window.LLMMonitor no definido**
```javascript
// Usar dentro de DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    window.LLMMonitor.start();
});
```

**Ver guía completa:** [components/chat/troubleshooting/common-issues.md](components/chat/troubleshooting/common-issues.md)

---

## 📝 Changelog

### v0.3.0 (9 diciembre 2025)

**Chat Workspace Configuration System:**
- ✅ Config Array System implementado (configuración granular)
- ✅ ChatWorkspaceConfigValidator con validación completa
- ✅ Workspace.php + ChatWorkspace.php refactorizados
- ✅ Backward compatibility 100% (legacy props funcionan)
- ✅ Settings Panel UI con DB persistence
- ✅ Conditional resource loading (15-39% bundle reduction)
- ✅ WorkspacePreferencesController (save/reset/get)
- ✅ Testing suite completo (27/27 passing)
- ✅ Helper methods en componentes
- ✅ Documentation modular completa (23 archivos, 3376 líneas)

**UX Enhancements (21 items - PLAN-v0.3.0-chat-ux.md):**
- ✅ Monitor Export (CSV/JSON/SQL con session filtering)
- ✅ Context Window Visual Indicator (border + opacity)
- ✅ Smart Auto-Scroll System (6 features ChatGPT-style)
- ✅ Browser + Sound Notifications
- ✅ Delete Message Feature (two-column approach)
- ✅ Request Inspector Tab (hybrid architecture)
- ✅ Message ID Refactor (centralized system)

### v0.2.2 (3 diciembre 2025)

**ChatWorkspace Component Multi-Instance Support:**
- ✅ Multi-instance architecture with unique Alpine.js scopes
- ✅ window.LLMMonitorFactory pattern for independent monitors
- ✅ LocalStorage isolation per session
- ✅ Custom Events with sessionId discriminator
- ✅ 100% backward compatible
- ✅ Legacy partials cleanup (1,213 lines removed)

### v0.2.1 (3 diciembre 2025)

**ChatWorkspace Component Optimizations:**
- ✅ Monitor code partitioning (56% reduction)
- ✅ Monitor console styles unified
- ✅ window.LLMMonitor API extracted to reusable partial
- ✅ Null-safe DOM checks added
- ✅ Complete usage documentation created

### v0.2.0 (28 noviembre 2025)

**Streaming Support & Permissions:**
- ✅ Split-horizontal code partitioning (66% reduction)
- ✅ Alpine.js components extracted (chatWorkspace, splitResizer)
- ✅ Conditional loading implemented
- ✅ Sidebar collapse fix (d-none binding)
- ✅ Monitor toggle consolidated to footer
- ✅ Streaming API implementation
- ✅ Permissions system integration

**Ver:** [CHANGELOG.md](../CHANGELOG.md)

---

## 🤝 Contribuir

¿Quieres contribuir? Lee nuestra [Guía de Contribución](CONTRIBUTING.md).

### Quick Links

- **Issues:** [GitHub Issues](https://github.com/Bithoven/llm-manager/issues)
- **Pull Requests:** [GitHub PRs](https://github.com/Bithoven/llm-manager/pulls)
- **Discusiones:** [GitHub Discussions](https://github.com/Bithoven/llm-manager/discussions)

---

## 📞 Soporte

- **Email:** support@bithoven.com
- **Documentación:** [docs/](.)
- **Issues:** [GitHub Issues](https://github.com/Bithoven/llm-manager/issues)

---

## 📄 Licencia

Este proyecto está licenciado bajo [MIT License](../LICENSE).

---

**Última actualización:** 10 diciembre 2025, 13:10  
**Versión:** 1.0.7
