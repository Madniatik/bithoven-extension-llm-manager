# Common Issues

Problemas comunes y soluciones.

---

## Error: "Invalid chat workspace configuration"

### Causa
Config no pasa validación.

### Solución

```php
try {
    $config = ChatWorkspaceConfigValidator::validate($config);
} catch (\InvalidArgumentException $e) {
    // Ver mensaje de error específico
    dd($e->getMessage());
    
    // Usar defaults como fallback
    $config = ChatWorkspaceConfigValidator::getDefaults();
}
```

---

## Error: "Monitor tabs cannot be enabled when monitor.enabled is false"

### Causa
Monitor disabled pero tabs enabled (regla lógica).

### Solución

```php
// ✅ FIX: Disable tabs cuando monitor disabled
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

## Error: "Settings button cannot be enabled when toolbar is disabled"

### Causa
Toolbar disabled pero settings button enabled.

### Solución

```php
// ✅ FIX: Enable toolbar O disable settings button
$config = [
    'features' => ['toolbar' => true],
    'ui' => ['buttons' => ['settings' => true]],
];
```

---

## Monitor No Aparece

### Diagnóstico

```blade
{{-- Blade debugging --}}
@dump($config['features']['monitor']['enabled'])
@dump($isMonitorEnabled())
@dump($showMonitor)
```

### Causas Comunes

1. `monitor.enabled => false`
2. Legacy prop `show-monitor` en `false`
3. Config no pasó a componente

---

## Settings Panel No Guarda

### Diagnóstico

```javascript
// Console browser (F12)
console.log('Config antes de save:', this.config);

fetch('/admin/llm/workspace/preferences/save', {...})
    .then(res => res.json())
    .then(data => console.log('Server response:', data));
```

### Causas Comunes

1. CSRF token missing
2. Ruta no registrada
3. Validación falla en server

---

## Performance Lento

### Diagnóstico

```bash
# Medir bundle size actual
./scripts/benchmark-conditional-loading.sh
```

### Soluciones

1. Disable tabs no usadas
2. Enable lazy loading
3. Enable minification (production)
4. Check cache preferences

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`
