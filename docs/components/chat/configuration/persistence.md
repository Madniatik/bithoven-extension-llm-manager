# Persistence

Sistema de persistencia de preferencias de usuario en base de datos.

---

## Modelo

```php
use Bithoven\LLMManager\Models\LLMUserWorkspacePreference;

// Schema
Schema::create('llm_user_workspace_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->json('config');
    $table->timestamps();
    
    $table->unique('user_id');
});
```

---

## Controller

### Save Preferences

```php
use Bithoven\LLMManager\Models\LLMUserWorkspacePreference;
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

public function save(Request $request)
{
    $config = $request->input('config');
    
    // Validar
    $validated = ChatWorkspaceConfigValidator::validate($config);
    
    // Guardar en DB
    $preference = LLMUserWorkspacePreference::updateOrCreate(
        ['user_id' => auth()->id()],
        ['config' => $validated]
    );
    
    return response()->json([
        'success' => true,
        'message' => 'Settings saved successfully',
        'needs_reload' => false,
        'config' => $validated,
    ]);
}
```

### Get Preferences

```php
public function get(Request $request)
{
    $preference = auth()->user()->workspacePreference;
    
    $config = $preference 
        ? $preference->config 
        : ChatWorkspaceConfigValidator::getDefaults();
    
    return response()->json([
        'success' => true,
        'config' => $config,
    ]);
}
```

### Reset to Defaults

```php
public function reset(Request $request)
{
    $preference = auth()->user()->workspacePreference;
    
    if ($preference) {
        $preference->delete();
    }
    
    $defaults = ChatWorkspaceConfigValidator::getDefaults();
    
    return response()->json([
        'success' => true,
        'message' => 'Settings reset to defaults',
        'config' => $defaults,
    ]);
}
```

---

## Frontend (JavaScript)

### Save Settings

```javascript
async function saveSettings(config) {
    const response = await fetch('/admin/llm/workspace/preferences/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ config }),
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Settings saved:', data.config);
        
        if (data.needs_reload) {
            window.location.reload();
        }
    }
}
```

### Load Settings

```javascript
async function loadSettings() {
    const response = await fetch('/admin/llm/workspace/preferences/get');
    const data = await response.json();
    
    if (data.success) {
        console.log('Settings loaded:', data.config);
        return data.config;
    }
}
```

### Reset Settings

```javascript
async function resetSettings() {
    const response = await fetch('/admin/llm/workspace/preferences/reset', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Settings reset to defaults:', data.config);
        window.location.reload();
    }
}
```

---

## Rutas

```php
// routes/admin.php
Route::prefix('llm/workspace/preferences')->group(function () {
    Route::post('save', [WorkspacePreferencesController::class, 'save'])
        ->name('admin.llm.workspace.preferences.save');
    
    Route::get('get', [WorkspacePreferencesController::class, 'get'])
        ->name('admin.llm.workspace.preferences.get');
    
    Route::post('reset', [WorkspacePreferencesController::class, 'reset'])
        ->name('admin.llm.workspace.preferences.reset');
});
```

---

## Performance Cache

### localStorage Cache

```php
'performance' => [
    'cache_preferences' => true,
]
```

**Beneficio:** Settings Panel carga instantáneamente sin DB query.

### Implementación

```javascript
// Cache en localStorage al guardar
function saveToLocalStorage(config) {
    const userId = document.querySelector('meta[name="user-id"]').content;
    const key = `workspace_preferences_${userId}`;
    localStorage.setItem(key, JSON.stringify(config));
}

// Cargar desde localStorage primero
function loadFromLocalStorage() {
    const userId = document.querySelector('meta[name="user-id"]').content;
    const key = `workspace_preferences_${userId}`;
    const cached = localStorage.getItem(key);
    return cached ? JSON.parse(cached) : null;
}
```

---

## Relación Eloquent

```php
// User model
public function workspacePreference()
{
    return $this->hasOne(LLMUserWorkspacePreference::class);
}

// Uso
$config = auth()->user()->workspacePreference?->config 
    ?? ChatWorkspaceConfigValidator::getDefaults();
```

---

## Próximos Pasos

- [API Reference](../api/workspace-component.md) - Helper methods
- [Examples](../guides/examples.md) - Ejemplos de uso
- [Troubleshooting](../troubleshooting/common-issues.md) - Problemas comunes

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived` (API Reference + Persistence)
