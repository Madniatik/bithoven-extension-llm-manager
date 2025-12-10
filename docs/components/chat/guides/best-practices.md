# Best Practices

Recomendaciones para usar el Chat Workspace Configuration System.

---

## 1. Usar Defaults como Base

```php
// ✅ GOOD: Merge con defaults
$config = array_merge(
    ChatWorkspaceConfigValidator::getDefaults(),
    [
        'features' => [
            'monitor' => ['enabled' => false],
        ],
    ]
);

// ❌ BAD: Config incompleto (puede romper validación)
$config = [
    'features' => ['monitor' => ['enabled' => false]],
];
```

---

## 2. Validar Siempre

```php
// ✅ GOOD: Validar antes de usar
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

try {
    $validated = ChatWorkspaceConfigValidator::validate($config);
} catch (\InvalidArgumentException $e) {
    report($e);
    $validated = ChatWorkspaceConfigValidator::getDefaults();
}
```

---

## 3. Persistir Preferencias de Usuario

```php
// ✅ GOOD: Guardar en DB
use Bithoven\LLMManager\Models\LLMUserWorkspacePreference;

$preference = LLMUserWorkspacePreference::updateOrCreate(
    ['user_id' => auth()->id()],
    ['config' => $validated]
);

// Cargar preferencias
$config = $preference->config ?? ChatWorkspaceConfigValidator::getDefaults();
```

---

## 4. Conditional Features

```php
// ✅ GOOD: Deshabilitar features no usadas (mejor performance)
$config = [
    'features' => [
        'monitor' => [
            'tabs' => [
                'console' => true,
                'request_inspector' => false,  // ❌ Disabled: No carga JS
                'activity_log' => false,       // ❌ Disabled: No carga JS
            ],
        ],
    ],
];

// Bundle size: -30% aproximadamente
```

---

## 5. Respetar Reglas Lógicas

```php
// ❌ BAD: Monitor disabled pero tabs enabled
$config = [
    'features' => [
        'monitor' => [
            'enabled' => false,
            'tabs' => [
                'console' => true,  // ❌ ERROR: Inconsistente
            ],
        ],
    ],
];

// ✅ GOOD: Monitor disabled → tabs disabled
$config = [
    'features' => [
        'monitor' => [
            'enabled' => false,
            'tabs' => [
                'console' => false,
                'request_inspector' => false,
                'activity_log' => false,
            ],
        ],
    ],
];
```

---

## 6. Usar Helper Methods en Blade

```blade
{{-- ✅ GOOD: Usar helper methods --}}
@if($isMonitorEnabled())
    <div class="monitor-panel">
        @if($isMonitorTabEnabled('console'))
            @include('llm-manager::components.chat.shared.monitor-console')
        @endif
    </div>
@endif

{{-- ❌ BAD: Acceso directo a $config --}}
@if($config['features']['monitor']['enabled'])
    {{-- Propenso a errores si estructura cambia --}}
@endif
```

---

## 7. Documentar Config Custom

```php
/**
 * Custom config para chat de soporte
 * 
 * Features:
 * - Monitor con solo Console tab
 * - Layout sidebar
 * - Sin persistence (demo mode)
 */
$supportChatConfig = [
    'features' => [
        'monitor' => [
            'enabled' => true,
            'tabs' => ['console' => true, 'request_inspector' => false, 'activity_log' => false],
        ],
        'persistence' => false,
    ],
    'ui' => ['layout' => ['monitor' => 'sidebar']],
];
```

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`