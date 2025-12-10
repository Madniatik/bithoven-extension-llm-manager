# JavaScript API

API JavaScript para interactuar con configuración y preferencias.

---

## Save Settings

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

---

## Load Settings

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

---

## Reset Settings

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

## localStorage Cache

```javascript
// Guardar en cache
function saveToLocalStorage(config) {
    const userId = document.querySelector('meta[name="user-id"]').content;
    const key = `workspace_preferences_${userId}`;
    localStorage.setItem(key, JSON.stringify(config));
}

// Cargar desde cache
function loadFromLocalStorage() {
    const userId = document.querySelector('meta[name="user-id"]').content;
    const key = `workspace_preferences_${userId}`;
    const cached = localStorage.getItem(key);
    return cached ? JSON.parse(cached) : null;
}
```

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`