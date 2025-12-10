# ChatWorkspaceConfigValidator API

Service para validar y mergear configuraciones.

---

## validate(array $config)

Valida y mergea config con defaults.

```php
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

$validated = ChatWorkspaceConfigValidator::validate([
    'features' => ['monitor' => ['enabled' => false]],
]);

// Returns: Array completo con defaults mergeados
```

### Throws

`InvalidArgumentException` si validación falla.

### Validaciones

- **Tipos:** bool, string, array según corresponda
- **Valores permitidos:** Layouts válidos, modes válidos
- **Reglas lógicas:**
  - Monitor tabs disabled cuando monitor.enabled = false
  - Settings button disabled cuando toolbar = false

---

## getDefaults()

Retorna configuración por defecto completa.

```php
$defaults = ChatWorkspaceConfigValidator::getDefaults();

// Returns:
[
    'features' => [...],
    'ui' => [...],
    'performance' => [...],
    'advanced' => [...],
]
```

---

## Ejemplo Completo

```php
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

try {
    // Validar config del usuario
    $validated = ChatWorkspaceConfigValidator::validate($userConfig);
} catch (\InvalidArgumentException $e) {
    // Log error
    report($e);
    
    // Fallback a defaults
    $validated = ChatWorkspaceConfigValidator::getDefaults();
}

// Usar config validado
return view('chat.index', ['config' => $validated]);
```

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`