# Configuration Overview

El sistema de configuración se organiza en 4 secciones principales:

---

## 1. Features

Control de características principales del componente.

```php
'features' => [
    'monitor' => [...],           // Monitor de streaming
    'settings_panel' => true,     // Panel de configuración
    'persistence' => true,        // Guardar mensajes en DB
    'toolbar' => true,            // Barra de herramientas
]
```

**Ver:** [Configuration Reference](reference.md#features)

---

## 2. UI Elements

Control granular de elementos visuales.

```php
'ui' => [
    'layout' => [...],            // Layouts (chat + monitor)
    'buttons' => [...],           // Botones de toolbar
    'mode' => 'full',             // Modo del componente
]
```

**Ver:** [Configuration Reference](reference.md#ui-elements)

---

## 3. Performance

Optimizaciones de carga y rendimiento.

```php
'performance' => [
    'lazy_load_tabs' => true,     // Lazy loading de tabs
    'minify_assets' => false,     // Minificar JS/CSS
    'cache_preferences' => true,  // Cache en localStorage
]
```

**Ver:** [Performance Tips](../guides/performance.md)

---

## 4. Advanced

Opciones avanzadas para casos especiales.

```php
'advanced' => [
    'multi_instance' => false,    // Múltiples chats en misma página
    'custom_css_class' => '',     // CSS class personalizada
    'debug_mode' => false,        // Logs detallados en console
]
```

**Ver:** [Configuration Reference](reference.md#advanced)

---

## Defaults

Para ver todos los defaults actuales:

```php
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

$defaults = ChatWorkspaceConfigValidator::getDefaults();
dd($defaults);
```

---

## Merge con Defaults

Siempre es recomendable mergear con defaults:

```php
$config = array_merge(
    ChatWorkspaceConfigValidator::getDefaults(),
    [
        'features' => [
            'monitor' => ['enabled' => false],
        ],
    ]
);
```

---

## Próximos Pasos

- [Configuration Reference](reference.md) - Referencia completa de todas las opciones
- [Features](features.md) - Detalle de cada feature
- [Persistence](persistence.md) - Guardado de preferencias en DB

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived` (Referencia de Configuración)
