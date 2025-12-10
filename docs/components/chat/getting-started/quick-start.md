# Quick Start

**Tiempo estimado:** 5 minutos

---

## Paso 1: Configuración Básica

### Controller

```php
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

public function index()
{
    $config = [
        'features' => [
            'monitor' => [
                'enabled' => true,
                'tabs' => [
                    'console' => true,
                    'request_inspector' => false,
                    'activity_log' => true,
                ],
            ],
        ],
    ];
    
    return view('admin.quick-chat.index', compact('config'));
}
```

### View (Blade)

```blade
{{-- resources/views/admin/quick-chat/index.blade.php --}}
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :config="$config"
/>
```

---

## Paso 2: Usar Defaults

```php
// Usar configuración por defecto
$config = ChatWorkspaceConfigValidator::getDefaults();

// O personalizar defaults
$config = array_merge(
    ChatWorkspaceConfigValidator::getDefaults(),
    [
        'ui' => [
            'mode' => 'canvas-only',
        ],
    ]
);
```

---

## Paso 3: Backward Compatibility

Si ya tienes código con legacy props, sigue funcionando:

```blade
{{-- Forma antigua (sigue funcionando) --}}
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :show-monitor="true"
    monitor-layout="split-horizontal"
/>

{{-- Internamente se convierte a config array automáticamente --}}
```

---

## Validación

Para validar tu config antes de usarlo:

```php
try {
    $validated = ChatWorkspaceConfigValidator::validate($config);
} catch (\InvalidArgumentException $e) {
    // Handle validation error
    report($e);
    $validated = ChatWorkspaceConfigValidator::getDefaults();
}
```

---

## Próximos Pasos

- [Basic Usage](basic-usage.md) - Ejemplos completos
- [Configuration Reference](../configuration/reference.md) - Todas las opciones disponibles
- [Examples](../guides/examples.md) - 10+ ejemplos de uso real

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived` (Configuración Básica)
