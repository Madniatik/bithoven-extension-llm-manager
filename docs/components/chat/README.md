# Chat Workspace Documentation

**Versión:** v0.3.0  
**Fecha:** 10 de diciembre de 2025  
**Estado:** Production Ready

---

## 📋 Índice de Documentación

Esta documentación cubre todas las funcionalidades del componente Chat Workspace del LLM Manager Extension, organizada de manera modular para facilitar la navegación y mantenimiento.

---

## 🚀 Getting Started

### Para Usuarios Nuevos
1. **[Introduction](./getting-started/introduction.md)** - Qué es el Chat Workspace y sus beneficios
2. **[Quick Start](./getting-started/quick-start.md)** - Comienza a usar el chat en 5 minutos
3. **[Basic Usage](./getting-started/basic-usage.md)** - Guía paso a paso de uso básico

---

## ⚙️ Configuration

### Sistema de Configuración
1. **[Overview](./configuration/overview.md)** - Visión general del sistema de configuración
2. **[Reference](./configuration/reference.md)** - Referencia completa de todas las opciones
3. **[Features Configuration](./configuration/features.md)** - Configuración de features específicas
4. **[Persistence](./configuration/persistence.md)** - Sistema de guardado de preferencias

---

## 📚 Guides

### Guías Prácticas
1. **[Examples](./guides/examples.md)** - Ejemplos completos de configuraciones
2. **[Migration Guide](./guides/migration.md)** - Migración desde versiones anteriores
3. **[Best Practices](./guides/best-practices.md)** - Mejores prácticas y patrones recomendados
4. **[Performance Optimization](./guides/performance.md)** - Tips para optimizar rendimiento

---

## 🔧 API Reference

### Referencias Técnicas
1. **[Workspace Component](./api/workspace-component.md)** - API del componente Blade
2. **[Config Validator](./api/config-validator.md)** - Sistema de validación
3. **[JavaScript API](./api/javascript-api.md)** - API JavaScript del frontend

---

## ✨ Features

### Funcionalidades v0.3.0
1. **[Monitor Export](./features/monitor-export.md)** - Export Activity Logs (CSV/JSON/SQL)
2. **[Context Window](./features/context-window.md)** - Indicador visual de contexto
3. **[Request Inspector](./features/request-inspector.md)** - Debugging de requests completos
4. **[Delete Message](./features/delete-message.md)** - Borrar mensajes individuales
5. **[Auto-Scroll System](./features/auto-scroll.md)** - Smart scroll ChatGPT-style
6. **[Notifications](./features/notifications.md)** - System notifications + sonidos

---

## 🐛 Troubleshooting

### Resolución de Problemas
1. **[Common Issues](./troubleshooting/common-issues.md)** - Problemas frecuentes y soluciones
2. **[Testing Guide](./troubleshooting/testing.md)** - Cómo testear configuraciones

---

## 📊 Quick Reference

### Configuración Rápida

```blade
{{-- Configuración mínima --}}
<x-llm-manager::workspace
    sessionId="{{ $sessionId }}"
/>

{{-- Configuración completa --}}
<x-llm-manager::workspace
    sessionId="{{ $sessionId }}"
    :config="[
        'layout' => [
            'type' => 'split-horizontal',
            'ratio' => '60-40'
        ],
        'features' => [
            'chat' => [
                'input' => [
                    'placeholder' => 'Custom placeholder...',
                    'autofocus' => true
                ]
            ],
            'monitor' => [
                'tabs' => [
                    'activity_log' => true,
                    'request_inspector' => true,
                    'console_log' => false
                ],
                'export' => [
                    'enabled' => true,
                    'formats' => ['csv', 'json', 'sql']
                ]
            ]
        ],
        'ui' => [
            'theme' => 'dark',
            'animations' => true
        ]
    ]"
/>
```

---

## 🎯 Características Principales

### Quick Chat System
- ✅ Streaming en tiempo real (SSE)
- ✅ Monitor panel con Activity Log, Request Inspector, Console Log
- ✅ Export CSV/JSON/SQL con session filtering
- ✅ Context Window visual indicator
- ✅ Delete individual messages
- ✅ Smart auto-scroll system
- ✅ System notifications + sonidos
- ✅ Keyboard shortcuts (Enter/Shift+Enter configurable)
- ✅ Copy/Paste/Resend mensajes
- ✅ Responsive design (móvil + desktop)

### Configuration System
- ✅ Single array-based configuration
- ✅ Backward compatible con legacy props
- ✅ Validación centralizada con type checking
- ✅ Persistence en base de datos por usuario
- ✅ Conditional resource loading (bundle size optimization)
- ✅ Extensible sin breaking changes

---

## 📈 Versión History

### v0.3.0 (10 dic 2025)
- Monitor Export Feature (CSV/JSON/SQL)
- Context Window Indicator (6.75h, 100% completo)
- Request Inspector Tab (hybrid population)
- Delete Message (two-column approach)
- Smart Auto-Scroll System (6 features)
- Notificaciones + Sonidos
- Chat UX System (21 items, 24h)

### v0.2.2
- Quick Chat Feature básico
- Monitor System v2.0
- Provider Service Layer
- Activity Log DB Migration

---

## 🤝 Contribución

Para contribuir a la documentación:
1. Lee [CONTRIBUTING.md](../../CONTRIBUTING.md)
2. Sigue la estructura modular actual
3. Mantén consistencia en formato y estilo
4. Agrega ejemplos prácticos cuando sea posible

---

## 📞 Soporte

- **Issues:** https://github.com/Madniatik/bithoven-extension-llm-manager/issues
- **Discussions:** https://github.com/Madniatik/bithoven-extension-llm-manager/discussions
- **Email:** dev@bithoven.com

---

**Última Actualización:** 10 de diciembre de 2025  
**Documentación Versión:** 2.0 (Modular)  
**Extension Versión:** v0.3.0
