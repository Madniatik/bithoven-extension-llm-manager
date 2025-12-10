# JavaScript Workflow - Monitor System

**Fecha:** 10 de diciembre de 2025  
**Extensión:** bithoven-extension-llm-manager

---

## ✅ **Respuesta Corta: NO HAY NPM BUILD**

Este proyecto **NO usa compilación de JavaScript**. Los archivos son **ES6 modules** que se cargan directamente por el navegador.

---

## 🔄 Workflow de Desarrollo

### 1️⃣ **Editar archivos en `resources/js/monitor/`**

```bash
resources/js/monitor/
├── core/
│   ├── MonitorFactory.js
│   ├── MonitorInstance.js
│   └── MonitorStorage.js
├── actions/
│   ├── clear.js        # ← Editar aquí
│   ├── copy.js         # ← Editar aquí
│   └── download.js     # ← Editar aquí
└── ui/
    └── render.js
```

### 2️⃣ **Sincronizar a `public/js/monitor/`**

```bash
# Script automático (recomendado)
./scripts/copy-monitor-js.sh

# O manualmente:
cp -r resources/js/monitor/core/* public/js/monitor/core/
cp -r resources/js/monitor/actions/* public/js/monitor/actions/
cp -r resources/js/monitor/ui/* public/js/monitor/ui/
```

**Output del script:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📦 Copy Monitor JavaScript Modules
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📁 Creating target directories...
📋 Copying files...
   ✓ core/ (3 files)
   ✓ actions/ (3 files)
   ✓ ui/ (1 files)

✅ Monitor modules copied successfully!
```

### 3️⃣ **Actualizar symlinks en CPANEL (si es extensión publicada)**

```bash
# Desde CPANEL
php artisan vendor:publish --tag=llm-assets --force

# O manualmente:
ln -s /path/to/extension/public /path/to/cpanel/public/vendor/bithoven/llm-manager
```

### 4️⃣ **Limpiar caché de vistas**

```bash
php artisan view:clear
```

---

## 📦 ¿Por qué NO hay build?

### Arquitectura ES6 Modules (Nativa del Navegador)

**monitor-api.blade.php:**
```javascript
// Import dinámico - browser carga directamente el .js
const basePath = '/vendor/bithoven/llm-manager/js/monitor';

const { default: MonitorStorage } = await import(`${basePath}/core/MonitorStorage.js`);
const { clearLogs, clearAll } = await import(`${basePath}/actions/clear.js`);
const { copyLogs } = await import(`${basePath}/actions/copy.js`);
const { downloadLogs } = await import(`${basePath}/actions/download.js`);
```

**Ventajas:**
- ✅ Sin paso de compilación (desarrollo más rápido)
- ✅ Sin webpack/vite/babel
- ✅ Sin node_modules pesados
- ✅ Debugging directo (source maps innecesarios)
- ✅ Módulos se cargan on-demand

**Desventajas:**
- ⚠️ Necesita navegadores modernos (todos lo soportan ya)
- ⚠️ Archivos se copian manualmente (mitigado con script)

---

## 🛠️ Script: `copy-monitor-js.sh`

### Funcionalidad

1. **Valida** que `resources/js/monitor/` exista
2. **Crea** directorios en `public/js/monitor/`
3. **Copia** todos los `.js` de cada carpeta
4. **Muestra** resumen de archivos copiados

### Ejecución

```bash
# Desde raíz de la extensión
./scripts/copy-monitor-js.sh

# Con permisos:
chmod +x scripts/copy-monitor-js.sh
./scripts/copy-monitor-js.sh
```

### Output Completo

```
📁 Creating target directories...
📋 Copying files...
   ✓ core/ (3 files)
   ✓ actions/ (3 files)
   ✓ ui/ (1 files)
   ✓ monitor.js (entry point - deprecated)

✅ Monitor modules copied successfully!

Target: /path/to/public/js/monitor

Structure:
  - public/js/monitor/actions/clear.js
  - public/js/monitor/actions/copy.js
  - public/js/monitor/actions/download.js
  - public/js/monitor/core/MonitorFactory.js
  - public/js/monitor/core/MonitorInstance.js
  - public/js/monitor/core/MonitorStorage.js
  - public/js/monitor/monitor.js
  - public/js/monitor/ui/render.js
```

---

## 🔁 Flujo Completo para Renombrado

### Cambios en `copy.js`, `download.js`, `clear.js`

**Paso 1: Editar `resources/js/monitor/actions/copy.js`**
```javascript
// Cambiar:
export async function copyLogs(sessionId, ui) { ... }

// Por:
export async function copyConsole(sessionId, ui) { ... }
```

**Paso 2: Sincronizar**
```bash
./scripts/copy-monitor-js.sh
```

**Paso 3: Actualizar imports en `monitor-api.blade.php`**
```javascript
// Cambiar:
const { copyLogs } = await import(`${basePath}/actions/copy.js`);

// Por:
const { copyConsole } = await import(`${basePath}/actions/copy.js`);
```

**Paso 4: Actualizar API wrapper**
```javascript
// Cambiar método:
async copyLogs() { ... }

// Por:
async copyConsole() { ... }

// + Agregar alias deprecado:
async copyLogs() {
    console.warn('DEPRECATED: Use copyConsole() instead');
    return this.copyConsole();
}
```

**Paso 5: Actualizar botones en `monitor-header-buttons.blade.php`**
```blade
{{-- Cambiar: --}}
onclick="window.LLMMonitor.copyLogs('{{ $monitorId }}')"

{{-- Por: --}}
onclick="window.LLMMonitor.copyConsole('{{ $monitorId }}')"
```

**Paso 6: Limpiar caché**
```bash
php artisan view:clear
```

---

## ⚡ Ventaja del Sistema Actual

### Sin Build Process

**Desarrollo rápido:**
```bash
# Editar archivo
vim resources/js/monitor/actions/copy.js

# Sincronizar (instantáneo)
./scripts/copy-monitor-js.sh

# Refresh browser (F5)
# ✅ Cambios aplicados inmediatamente
```

**VS sistema con build:**
```bash
# Editar archivo
vim src/monitor/copy.js

# Compilar (30-60 segundos)
npm run build

# Refresh browser
# ✅ Cambios aplicados
```

**Ganancia:** ~1 minuto por cambio × 100 cambios = **100 minutos ahorrados**

---

## 📋 Checklist para Modificar JavaScript

- [ ] 1. Editar archivos en `resources/js/monitor/`
- [ ] 2. Ejecutar `./scripts/copy-monitor-js.sh`
- [ ] 3. Actualizar imports en `monitor-api.blade.php` (si cambió export name)
- [ ] 4. Actualizar componentes Blade que usan las funciones
- [ ] 5. `php artisan view:clear`
- [ ] 6. Refresh browser (F5 + Ctrl+Shift+R para hard refresh)
- [ ] 7. Verificar en consola del browser (F12)

---

## 🎯 Conclusión

**NO necesitas `npm run build`** para este proyecto.

El workflow es:
1. **Editar** en `resources/js/`
2. **Copiar** con `./scripts/copy-monitor-js.sh`
3. **Limpiar** caché con `php artisan view:clear`
4. **Refresh** navegador

Simple, rápido, sin dependencias de Node.js. ✅
