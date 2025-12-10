# Examples

10+ ejemplos de uso real del Chat Workspace Configuration System.

---

## 1. Quick Chat (Monitor Completo)

```php
public function quickChat()
{
    $config = [
        'features' => [
            'monitor' => [
                'enabled' => true,
                'default_open' => true,
                'tabs' => [
                    'console' => true,
                    'request_inspector' => true,
                    'activity_log' => true,
                ],
            ],
        ],
        'ui' => [
            'layout' => [
                'monitor' => 'split-horizontal',
            ],
        ],
    ];
    
    return view('admin.quick-chat.index', compact('config'));
}
```

**Resultado:** Monitor visible, split horizontal, todas las tabs habilitadas.

---

## 2. Conversations (Solo Console)

```php
public function conversations()
{
    $config = [
        'features' => [
            'monitor' => [
                'enabled' => true,
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
    
    return view('conversations.index', compact('config'));
}
```

**Resultado:** Monitor en sidebar, solo Console tab visible.

---

## 3. Embedded Chat (Sin Monitor)

```php
public function embedded()
{
    $config = [
        'features' => [
            'monitor' => ['enabled' => false],
            'toolbar' => false,
        ],
        'ui' => [
            'mode' => 'canvas-only',
        ],
    ];
    
    return view('embedded.chat', compact('config'));
}
```

**Resultado:** Solo canvas de chat, sin monitor ni toolbar.

---

## 4. Developer Mode (Debug Completo)

```php
public function developerMode()
{
    $config = ChatWorkspaceConfigValidator::getDefaults();
    
    $config['advanced']['debug_mode'] = true;
    $config['features']['monitor']['default_open'] = true;
    
    return view('developer.chat', compact('config'));
}
```

**Resultado:** Monitor abierto por defecto, console logs detallados.

---

## 5. Demo Mode (Presentation)

```php
public function demo()
{
    $config = [
        'features' => [
            'monitor' => ['enabled' => false],
            'persistence' => false,
        ],
        'ui' => [
            'mode' => 'demo',
            'buttons' => [
                'download' => false,
                'settings' => false,
            ],
        ],
    ];
    
    return view('demo.chat', compact('config'));
}
```

**Resultado:** Chat sin monitor, sin persistencia, sin botones de control.

---

## 6. Custom CSS & Theming

```php
public function customTheme()
{
    $config = [
        'advanced' => [
            'custom_css_class' => 'dark-theme compact-mode',
        ],
        'ui' => [
            'layout' => [
                'chat' => 'compact',
            ],
        ],
    ];
    
    return view('custom.chat', compact('config'));
}
```

```css
.dark-theme {
    background: #1a1a1a;
    color: #f0f0f0;
}

.compact-mode .chat-message {
    padding: 8px;
    margin: 4px 0;
}
```

**Resultado:** Chat con tema oscuro personalizado.

---

## 7. Multi-Instance (Múltiples Chats)

```php
public function multiChat()
{
    $config1 = [
        'advanced' => ['multi_instance' => true],
        'features' => ['monitor' => ['enabled' => false]],
    ];
    
    $config2 = [
        'advanced' => ['multi_instance' => true],
        'features' => ['monitor' => ['enabled' => true]],
    ];
    
    return view('multi-chat.index', compact('config1', 'config2'));
}
```

```blade
<div class="row">
    <div class="col-md-6">
        <x-llm-manager-chat-workspace :config="$config1" />
    </div>
    <div class="col-md-6">
        <x-llm-manager-chat-workspace :config="$config2" />
    </div>
</div>
```

**Resultado:** Dos chats independientes en la misma página.

---

## 8. Performance Optimizado

```php
public function optimized()
{
    $config = [
        'performance' => [
            'lazy_load_tabs' => true,
            'minify_assets' => true,
            'cache_preferences' => true,
        ],
        'features' => [
            'monitor' => [
                'tabs' => [
                    'console' => false,
                    'request_inspector' => true,
                    'activity_log' => false,
                ],
            ],
        ],
    ];
    
    return view('optimized.chat', compact('config'));
}
```

**Resultado:** Bundle size reducido ~30%, carga más rápida.

---

## 9. Settings Panel Habilitado

```php
public function withSettings()
{
    $preference = auth()->user()->workspacePreference;
    
    $config = $preference ? $preference->config : ChatWorkspaceConfigValidator::getDefaults();
    
    return view('chat.with-settings', compact('config'));
}
```

**Resultado:** Usuario puede personalizar configuración vía Settings Panel.

---

## 10. API/Headless Mode

```php
public function apiMode()
{
    $config = [
        'features' => [
            'monitor' => ['enabled' => false],
            'toolbar' => false,
            'settings_panel' => false,
        ],
        'ui' => [
            'mode' => 'canvas-only',
        ],
    ];
    
    return response()->json([
        'config' => $config,
        'endpoint' => route('api.chat.stream'),
    ]);
}
```

**Resultado:** Chat minimalista para integración API.

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`