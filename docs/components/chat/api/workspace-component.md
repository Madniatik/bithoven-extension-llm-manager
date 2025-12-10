# Workspace Component API

Helper methods del componente Workspace.

---

## isMonitorEnabled()

```php
public function isMonitorEnabled(): bool
{
    return $this->config['features']['monitor']['enabled'];
}
```

### Uso en Blade

```blade
@if($isMonitorEnabled())
    <div class="monitor-panel">...</div>
@endif
```

---

## isMonitorTabEnabled(string $tab)

```php
public function isMonitorTabEnabled(string $tab): bool
{
    return $this->config['features']['monitor']['tabs'][$tab] ?? false;
}
```

### Tabs Disponibles

- `'console'`
- `'request_inspector'`
- `'activity_log'`

### Uso en Blade

```blade
@if($isMonitorTabEnabled('console'))
    @include('llm-manager::components.chat.shared.monitor-console')
@endif
```

---

## isButtonEnabled(string $button)

```php
public function isButtonEnabled(string $button): bool
{
    return $this->config['ui']['buttons'][$button] ?? false;
}
```

### Buttons Disponibles

- `'new_chat'`
- `'clear'`
- `'settings'`
- `'download'`
- `'monitor_toggle'`

### Uso en Blade

```blade
@if($isButtonEnabled('settings'))
    <button>Settings</button>
@endif
```

---

## getMonitorLayout()

```php
public function getMonitorLayout(): string
{
    return $this->config['ui']['layout']['monitor'];
}
```

### Layouts Disponibles

- `'drawer'`
- `'tabs'`
- `'split-horizontal'`
- `'split-vertical'`
- `'sidebar'`

### Uso en Blade

```blade
@if($getMonitorLayout() === 'split-horizontal')
    @include('llm-manager::components.chat.layouts.split-horizontal-layout')
@endif
```

---

## getChatLayout()

```php
public function getChatLayout(): string
{
    return $this->config['ui']['layout']['chat'];
}
```

### Layouts Disponibles

- `'bubble'`
- `'drawer'`
- `'compact'`

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`