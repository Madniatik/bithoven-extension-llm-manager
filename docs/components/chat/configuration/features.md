# Features

Detalle de cada feature del Chat Workspace.

---

## Monitor de Streaming

### Descripción

Panel de debugging con 3 tabs para monitorear el streaming en tiempo real.

### Configuración

```php
'features' => [
    'monitor' => [
        'enabled' => true,
        'default_open' => false,
        'tabs' => [
            'console' => true,
            'request_inspector' => true,
            'activity_log' => true,
        ],
    ],
]
```

### Tabs Disponibles

#### 1. Console Tab
- Logs de streaming en tiempo real
- Chunks recibidos del LLM
- Errores y warnings
- **Ver:** [Monitor Export](../features/monitor-export.md)

#### 2. Request Inspector Tab
- Payload del request
- Headers
- Context messages
- Timeline de mensajes
- **Ver:** [Request Inspector](../features/request-inspector.md)

#### 3. Activity Log Tab
- Historial de acciones
- Timestamps
- User actions

### Layouts

| Layout | Descripción |
|--------|-------------|
| `drawer` | Panel lateral deslizable |
| `tabs` | Tabs horizontales |
| `split-horizontal` | Split 50/50 horizontal |
| `split-vertical` | Split 50/50 vertical |
| `sidebar` | Sidebar fijo |

---

## Settings Panel

### Descripción

Panel de configuración donde usuarios pueden personalizar su experiencia.

### Configuración

```php
'features' => [
    'settings_panel' => true,
]
```

### Opciones Personalizables

- Monitor enabled/disabled
- Monitor tabs activas
- Monitor layout
- UX Enhancements (context window indicator, etc.)

### Persistence

Los settings se guardan automáticamente en DB por usuario.

**Ver:** [Persistence](persistence.md)

---

## Persistence

### Descripción

Guardar mensajes en base de datos.

### Configuración

```php
'features' => [
    'persistence' => true,
]
```

### Comportamiento

- `true`: Mensajes se guardan en `llm_messages` table
- `false`: Mensajes solo en memoria (demo mode)

---

## Toolbar

### Descripción

Barra de herramientas con acciones principales.

### Configuración

```php
'features' => [
    'toolbar' => true,
],
'ui' => [
    'buttons' => [
        'new_chat' => true,
        'clear' => true,
        'settings' => true,
        'download' => true,
        'monitor_toggle' => true,
    ],
]
```

### Botones Disponibles

| Botón | Descripción |
|-------|-------------|
| `new_chat` | Crear nueva sesión |
| `clear` | Limpiar chat actual |
| `settings` | Abrir panel de settings |
| `download` | Descargar historial |
| `monitor_toggle` | Toggle monitor on/off |

---

## UX Enhancements

Features adicionales de UX:

### Context Window Indicator
- **Ver:** [Context Window](../features/context-window.md)

### Smart Auto-Scroll
- **Ver:** [Auto-Scroll](../features/auto-scroll.md)

### Notifications
- **Ver:** [Notifications](../features/notifications.md)

### Delete Message
- **Ver:** [Delete Message](../features/delete-message.md)

---

## Próximos Pasos

- [Configuration Reference](reference.md) - Referencia completa
- [Examples](../guides/examples.md) - Ejemplos de configuración
- [Persistence](persistence.md) - Guardado de preferencias

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived` + `PLAN-v0.3.0-chat-ux.md`
