# LLM Manager Extension - Documentación

**Versión:** 1.0.0  
**Última actualización:** 3 diciembre 2025

---

## 📚 Índice General

### Guías de Usuario

| Documento | Descripción | Audiencia |
|-----------|-------------|-----------|
| [Installation Guide](INSTALLATION.md) | Instalación y setup inicial | Usuarios nuevos |
| [Usage Guide](USAGE-GUIDE.md) | Uso básico de la extensión | Usuarios generales |
| [Configuration](CONFIGURATION.md) | Configuración de LLM providers | Administradores |
| [Examples](EXAMPLES.md) | Ejemplos de uso comunes | Desarrolladores |
| [FAQ](FAQ.md) | Preguntas frecuentes | Todos |

### Documentación Técnica

| Documento | Descripción | Audiencia |
|-----------|-------------|-----------|
| [API Reference](API-REFERENCE.md) | Referencia completa de API | Desarrolladores |
| [Contributing](CONTRIBUTING.md) | Guía de contribución | Contribuidores |

---

## 🧩 Componentes

### Chat Workspace Component

Componente principal para interfaces de chat LLM con soporte para layouts duales y monitor integrado.

**📖 [Guía Completa de Uso](components/CHAT-WORKSPACE.md)**

**Características:**
- ✅ **Dual Layout System:** Sidebar (vertical) y Split-Horizontal (horizontal resizable)
- ✅ **Monitor Integrado:** Métricas en tiempo real, historial de actividad, console logs
- ✅ **Streaming Support:** Compatible con Server-Sent Events (SSE)
- ✅ **Alpine.js Reactive:** Componentes reactivos sin Vue/React
- ✅ **LocalStorage Persistence:** Guarda preferencias del usuario
- ✅ **Code Partitioning:** Carga condicional para máxima performance (63% reducción)

**Contenido de la guía:**
- Instalación y requisitos
- Props y API reference
- Layouts disponibles (sidebar vs split-horizontal)
- JavaScript API (chatWorkspace, splitResizer, window.LLMMonitor)
- Personalización y ejemplos completos
- Troubleshooting y performance

**Estado:** ✅ v2.1 - Producción  
**Optimización:** 63% reducción de código (740 → 270 líneas)

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
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    monitor-layout="split-horizontal"
/>
```

**Ver:** [USAGE-GUIDE.md](USAGE-GUIDE.md)  
**Referencia completa:** [components/CHAT-WORKSPACE.md](components/CHAT-WORKSPACE.md)

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

1. **[API Reference](API-REFERENCE.md)** - Métodos, clases, eventos
2. **[Chat Workspace Component](components/CHAT-WORKSPACE.md)** - Componente principal
3. **[Contributing](CONTRIBUTING.md)** - Guía de contribución

---

## 🧩 Arquitectura de Componentes

```
LLM Manager Extension
├── Quick Chat (Interfaz principal)
│   └── ChatWorkspace Component
│       ├── Layouts
│       │   ├── Sidebar Layout (60/40 vertical)
│       │   └── Split-Horizontal Layout (70/30 horizontal)
│       ├── Monitor Components
│       │   ├── Full Monitor (métricas + historial + consola)
│       │   └── Console Only (solo consola)
│       └── Alpine.js Components
│           ├── chatWorkspace (global)
│           ├── splitResizer (condicional)
│           └── window.LLMMonitor API (global)
├── Admin Panel
│   ├── Configurations Manager
│   ├── Sessions Manager
│   └── Settings
└── API
    ├── Streaming Endpoint (SSE)
    ├── Chat Endpoint
    └── Session Management
```

---

## 📊 Métricas de Performance

### Chat Workspace Component v2.1

| Métrica | Antes (v1.0) | Después (v2.1) | Mejora |
|---------|--------------|----------------|--------|
| split-horizontal.blade.php | 450 líneas | 150 líneas | **66%** ⬇️ |
| monitor.blade.php | 230 líneas | 100 líneas | **56%** ⬇️ |
| monitor-console.blade.php | 60 líneas | 20 líneas | **66%** ⬇️ |
| **Total componentes** | **740 líneas** | **270 líneas** | **63%** ⬇️ |

**Beneficios:**
- ✅ Código particionado en 7 archivos reutilizables
- ✅ Separación completa HTML/CSS/JS
- ✅ Carga condicional optimizada
- ✅ Testing facilitado (componentes aislados)
- ✅ Mantenibilidad mejorada significativamente

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

**Ver guía completa:** [components/CHAT-WORKSPACE.md#troubleshooting](components/CHAT-WORKSPACE.md#troubleshooting)

---

## 📝 Changelog

### v1.0.6 (3 diciembre 2025)

**ChatWorkspace Component Multi-Instance Support:**
- ✅ Multi-instance architecture with unique Alpine.js scopes
- ✅ window.LLMMonitorFactory pattern for independent monitors
- ✅ LocalStorage isolation per session
- ✅ Custom Events with sessionId discriminator
- ✅ 100% backward compatible
- ✅ Legacy partials cleanup (1,213 lines removed)

### v1.0.5 (3 diciembre 2025)

**ChatWorkspace Component Optimizations:**
- ✅ Monitor code partitioning (56% reduction)
- ✅ Monitor console styles unified
- ✅ window.LLMMonitor API extracted to reusable partial
- ✅ Null-safe DOM checks added
- ✅ Complete usage documentation created

### v1.0.4 (28 noviembre 2025)

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

**Última actualización:** 3 diciembre 2025, 07:20  
**Versión:** 1.0.0
