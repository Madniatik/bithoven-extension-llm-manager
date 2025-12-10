# Basic Usage

Patrones de uso comunes para el Chat Workspace Configuration System.

---

## Patrón 1: Config Mínimo

```php
$config = [
    'features' => [
        'monitor' => ['enabled' => true],
    ],
];
```

**Resultado:** Monitor habilitado con defaults (3 tabs, split-horizontal).

---

## Patrón 2: Monitor Específico

```php
$config = [
    'features' => [
        'monitor' => [
            'enabled' => true,
            'default_open' => true,
            'tabs' => [
                'console' => true,
                'request_inspector' => false,
                'activity_log' => false,
            ],
        ],
    ],
    'ui' => [
        'layout' => [
            'monitor' => 'sidebar',
        ],
    ],
];
```

**Resultado:** Monitor en sidebar, solo Console tab, abierto por defecto.

---

## Patrón 3: Chat Minimalista

```php
$config = [
    'features' => [
        'monitor' => ['enabled' => false],
        'toolbar' => false,
    ],
    'ui' => [
        'mode' => 'canvas-only',
    ],
];
```

**Resultado:** Solo canvas de chat, sin monitor ni toolbar.

---

## Patrón 4: Merge con Defaults

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

**Resultado:** Todos los defaults EXCEPTO monitor disabled.

---

## Patrón 5: Load User Preferences

```php
use Bithoven\LLMManager\Models\LLMUserWorkspacePreference;

// Cargar preferencias del usuario
$preference = auth()->user()->workspacePreference;

$config = $preference 
    ? $preference->config 
    : ChatWorkspaceConfigValidator::getDefaults();

return view('chat.index', compact('config'));
```

**Resultado:** Carga settings personalizados del usuario desde DB.

---

## Patrón 6: Conditional Features

```php
$config = [
    'features' => [
        'monitor' => [
            'enabled' => auth()->user()->isAdmin(), // Solo admins
            'tabs' => [
                'console' => true,
                'request_inspector' => auth()->user()->isDeveloper(), // Solo devs
                'activity_log' => true,
            ],
        ],
    ],
];
```

**Resultado:** Features según roles de usuario.

---

## Validación de Config

Siempre valida antes de usar:

```php
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

try {
    $validated = ChatWorkspaceConfigValidator::validate($config);
} catch (\InvalidArgumentException $e) {
    // Log error y usar defaults
    report($e);
    $validated = ChatWorkspaceConfigValidator::getDefaults();
}
```

---

## Helper Methods en Blade

```blade
{{-- Blade Component Methods --}}
@if($isMonitorEnabled())
    <div class="monitor-panel">
        @if($isMonitorTabEnabled('console'))
            @include('llm-manager::components.chat.shared.monitor-console')
        @endif
    </div>
@endif

@if($isButtonEnabled('settings'))
    <button>Settings</button>
@endif

{{-- Layout condicional --}}
@if($getMonitorLayout() === 'split-horizontal')
    @include('llm-manager::components.chat.layouts.split-horizontal-layout')
@endif
```

---

## Próximos Pasos

- [Configuration Reference](../configuration/reference.md) - Todas las opciones
- [Examples](../guides/examples.md) - 10+ ejemplos completos
- [API Reference](../api/workspace-component.md) - Helper methods

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived` (Ejemplos de Uso)
