# Configuration Reference

Referencia completa de todas las opciones de configuración.

---

## Estructura Completa

```php
$config = [
    'features' => [...],
    'ui' => [...],
    'performance' => [...],
    'advanced' => [...],
];
```

---

## Features

Control de características principales.

### monitor

```php
'monitor' => [
    'enabled' => true,              // Master toggle (bool)
    'default_open' => false,        // Estado inicial open/closed (bool)
    'tabs' => [
        'console' => true,          // Tab Console (logs tiempo real) (bool)
        'request_inspector' => true, // Tab Request Inspector (debugging) (bool)
        'activity_log' => true,     // Tab Activity Log (historial) (bool)
    ],
]
```

### settings_panel

```php
'settings_panel' => true  // Mostrar panel de configuración (bool)
```

### persistence

```php
'persistence' => true  // Guardar mensajes en DB (bool)
```

### toolbar

```php
'toolbar' => true  // Mostrar barra de herramientas (bool)
```

---

## UI Elements

Control granular de elementos visuales.

### layout

```php
'layout' => [
    'chat' => 'bubble',             // Layout del chat
                                    // Valores: 'bubble', 'drawer', 'compact'
    
    'monitor' => 'split-horizontal', // Layout del monitor
                                    // Valores: 'drawer', 'tabs', 
                                    //          'split-horizontal', 
                                    //          'split-vertical', 'sidebar'
]
```

### buttons

```php
'buttons' => [
    'new_chat' => true,             // Botón New Chat (bool)
    'clear' => true,                // Botón Clear Chat (bool)
    'settings' => true,             // Botón Settings (bool)
    'download' => true,             // Botón Download History (bool)
    'monitor_toggle' => true,       // Botón Toggle Monitor (bool)
]
```

### mode

```php
'mode' => 'full'  // Modo del componente
                  // Valores: 'full', 'demo', 'canvas-only'
```

---

## Performance

Optimizaciones de carga y rendimiento.

```php
'performance' => [
    'lazy_load_tabs' => true,       // Cargar tabs solo cuando se activan (bool)
    'minify_assets' => false,       // Minificar JS/CSS (solo production) (bool)
    'cache_preferences' => true,    // Cache en localStorage (bool)
]
```

---

## Advanced

Opciones avanzadas para casos especiales.

```php
'advanced' => [
    'multi_instance' => false,      // Múltiples chats en misma página (bool)
    'custom_css_class' => '',       // CSS class personalizada (string)
    'debug_mode' => false,          // Logs detallados en console (bool)
]
```

---

## Defaults Table

| Sección | Opción | Default | Tipo |
|---------|--------|---------|------|
| `features.monitor.enabled` | Monitor habilitado | `true` | bool |
| `features.monitor.default_open` | Monitor abierto al inicio | `false` | bool |
| `features.monitor.tabs.console` | Tab Console | `true` | bool |
| `features.monitor.tabs.request_inspector` | Tab Request Inspector | `true` | bool |
| `features.monitor.tabs.activity_log` | Tab Activity Log | `true` | bool |
| `features.settings_panel` | Panel Settings | `true` | bool |
| `features.persistence` | Guardar mensajes | `true` | bool |
| `features.toolbar` | Mostrar toolbar | `true` | bool |
| `ui.layout.chat` | Layout chat | `'bubble'` | string |
| `ui.layout.monitor` | Layout monitor | `'split-horizontal'` | string |
| `ui.buttons.*` | Todos los botones | `true` | bool |
| `ui.mode` | Modo componente | `'full'` | string |
| `performance.lazy_load_tabs` | Lazy loading | `true` | bool |
| `performance.minify_assets` | Minificación | `false` | bool |
| `performance.cache_preferences` | Cache local | `true` | bool |
| `advanced.multi_instance` | Multi instancia | `false` | bool |
| `advanced.custom_css_class` | CSS class | `''` | string |
| `advanced.debug_mode` | Debug mode | `false` | bool |

---

## Validación

El validador `ChatWorkspaceConfigValidator` verifica:

- **Tipos:** bool, string, array según corresponda
- **Valores permitidos:** Layouts válidos, modes válidos
- **Reglas lógicas:** 
  - Monitor tabs disabled cuando monitor.enabled = false
  - Settings button disabled cuando toolbar = false

---

## Próximos Pasos

- [Features](features.md) - Detalle de cada feature
- [Examples](../guides/examples.md) - Ejemplos de uso
- [Best Practices](../guides/best-practices.md) - Recomendaciones

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived` (Referencia de Configuración)
