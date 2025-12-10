# Migration Guide

Guía de migración desde legacy props al config array.

---

## Desde Legacy Props → Config Array

### Antes (Legacy Props)

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    layout="bubble"
    :show-monitor="true"
    monitor-layout="split-horizontal"
    mode="full"
    :persist="true"
    :show-toolbar="true"
/>
```

### Después (Config Array - Recomendado)

```php
// Controller
$config = [
    'features' => [
        'monitor' => ['enabled' => true],
        'persistence' => true,
        'toolbar' => true,
    ],
    'ui' => [
        'layout' => [
            'chat' => 'bubble',
            'monitor' => 'split-horizontal',
        ],
        'mode' => 'full',
    ],
];
```

```blade
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :config="$config"
/>
```

---

## Migración Gradual (Sin Breaking Changes)

### Paso 1: Mantén legacy props

```blade
{{-- Esto sigue funcionando (backward compatible) --}}
<x-llm-manager-chat-workspace
    :show-monitor="true"
    monitor-layout="split-horizontal"
/>
```

### Paso 2: Agrega config array

```blade
{{-- Config array tiene prioridad sobre legacy props --}}
<x-llm-manager-chat-workspace
    :show-monitor="true"          {{-- Ignorado --}}
    :config="$config"             {{-- Usado --}}
/>
```

### Paso 3: Elimina legacy props

```blade
{{-- Solo config array (cleanup) --}}
<x-llm-manager-chat-workspace :config="$config" />
```

---

## Mapeo de Props → Config

| Legacy Prop | Config Path | Tipo |
|-------------|-------------|------|
| `$showMonitor` | `features.monitor.enabled` | bool |
| `$monitorOpen` | `features.monitor.default_open` | bool |
| `$layout` | `ui.layout.chat` | string |
| `$monitorLayout` | `ui.layout.monitor` | string |
| `$mode` | `ui.mode` | string |
| `$persist` | `features.persistence` | bool |
| `$showToolbar` | `features.toolbar` | bool |

---

## Script de Migración Automática

```bash
# TODO: Crear script para migrar automáticamente
./scripts/migrate-to-config-array.sh
```

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`