# 🔧 Protocolo de Trabajo - OPCIÓN B v1.1.0 Development

**Fecha:** 28 de noviembre de 2025  
**Status:** 🟢 LISTO PARA INSTALAR  
**Objetivo:** Completar v1.1.0 sin afectar repositorio GitHub

---

## 📋 Flujo de Trabajo Acordado

### 1. Instalación en CPANEL (LOCAL DEV MODE)

**Paso 1: Verificar estructura actual**
```bash
# En CPANEL
ls -la vendor/bithoven/  # ¿existe llm-manager?
```

**Paso 2: Instalar extension en modo symlink (desarrollo)**
```bash
# En CPANEL
php artisan bithoven:extension:install llm-manager \
    --path=/Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager \
    --symlink

# O manualmente si el comando no lo soporta:
# composer config repositories.llm-manager path ../EXTENSIONS/bithoven-extension-llm-manager
# composer require bithoven/llm-manager:@dev
```

**Resultado esperado:**
```
vendor/bithoven/llm-manager -> ../../../EXTENSIONS/bithoven-extension-llm-manager (symlink)
```

**Paso 3: Ejecutar migraciones y seeders**
```bash
php artisan migrate
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\DatabaseSeeder
```

---

### 2. Versionado y Control de Cambios

#### Version Policy
- ✅ **extension.json** permanece en `"version": "1.0.0"` en repositorio oficial
- ✅ **extension.json** → `"updated_at"` se actualiza cuando hay cambios (para tracking)
- ✅ Los cambios de código se hacen directamente en `/EXTENSIONS/bithoven-extension-llm-manager`
- ✅ El symlink refleja cambios automáticamente en CPANEL

#### Tracking de Cambios
```json
// extension.json - Usar SOLO para tracking, NO para version bumps
{
    "version": "1.0.0",           // NUNCA CAMBIAR AQUÍ
    "updated_at": "2025-11-28T14:35:00Z"  // ACTUALIZAR SIEMPRE que hay cambios
}
```

#### Para Saber si Cambió en GitHub
```bash
# Comparar con repositorio oficial
git remote add origin https://github.com/Madniatik/bithoven-extension-llm-manager.git
git fetch origin main

# Ver si hay cambios
git diff origin/main extension.json | grep updated_at

# Si el updated_at en GitHub es más antiguo → tu versión local es más nueva
```

---

### 3. Estructura de Trabajo

```
Durante OPCIÓN B:
├── CPANEL/
│   └── vendor/bithoven/llm-manager/ → SYMLINK
│       └── (apunta a EXTENSIONS/bithoven-extension-llm-manager)
│
└── EXTENSIONS/
    └── bithoven-extension-llm-manager/  ← EDITAR AQUÍ
        ├── src/
        ├── resources/
        ├── routes/
        ├── database/
        └── tests/
```

**Ventajas:**
- ✅ Los cambios en `/EXTENSIONS` se reflejan automáticamente en CPANEL
- ✅ No necesitas reinstalar después de cambios
- ✅ Puedes testear en CPANEL sin commit
- ✅ Commits y Git history quedan en `/EXTENSIONS` (repositorio original)

---

### 4. Flujo de Desarrollo para TAREA 1, 2, 3

#### Paso A: Hacer cambios
```bash
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager

# Editar archivos necesarios
# - Crear rutas
# - Actualizar controller
# - Modificar vistas
# - Crear JavaScript
```

#### Paso B: Testear en CPANEL
```bash
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL

# Limpiar cache (importante)
php artisan optimize:clear

# Testear en navegador
http://localhost:8000/admin/llm/conversations
```

#### Paso C: Ver cambios reflejados
```bash
# Los archivos en vendor/bithoven/llm-manager apuntan a la carpeta fuente
# Cambios son automáticos sin reinstalar

# Si no se ven cambios:
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

#### Paso D: Versionar cambios
```bash
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager

# 1. Actualizar updated_at en extension.json
# 2. Hacer commits
git add .
git commit -m "feat(streaming): integrate conversations UI"

# 3. NO hacer git tag ni push a main todavía
# (Eso se hace en TAREA 4)
```

---

### 5. Checklist Pre-Instalación

Antes de instalar, asegúrate de que:

```
[ ] CPANEL está funcionando (php artisan serve running)
[ ] Base de datos accesible
[ ] Permisos en /EXTENSIONS/bithoven-extension-llm-manager son correctos (755)
[ ] No hay conflictos de permisos en vendor/
[ ] Ollama está disponible en http://localhost:11434 (para testing)
```

---

### 6. Instalación de Extensión - Comandos Exactos

**Opción A: Via bithoven:extension:install (si soporta symlink)**
```bash
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL

php artisan bithoven:extension:install llm-manager \
    --path=/Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager \
    --dev
```

**Opción B: Manual (si A no funciona)**
```bash
# 1. Editar composer.json
cat > /tmp/composer-repo.json << 'EOF'
{
    "repositories": [
        {
            "type": "path",
            "url": "../EXTENSIONS/bithoven-extension-llm-manager",
            "options": {
                "symlink": true
            }
        }
    ]
}
EOF

# 2. Mergetear con composer.json existente
# (O editarlo manualmente en VS Code)

# 3. Instalar
composer require bithoven/llm-manager:@dev

# 4. Link si no se creó automáticamente
ln -s ../../../EXTENSIONS/bithoven-extension-llm-manager vendor/bithoven/llm-manager
```

**Opción C: Manual simplificado**
```bash
cd vendor/bithoven
ln -s ../../../EXTENSIONS/bithoven-extension-llm-manager llm-manager
```

---

### 7. Verificación Post-Instalación

```bash
# 1. Confirmar symlink
ls -la vendor/bithoven/llm-manager

# Output esperado:
# lrwxr-xr-x  1 user  staff  ... llm-manager -> ../../../EXTENSIONS/bithoven-extension-llm-manager

# 2. Verificar rutas
php artisan route:list | grep llm

# 3. Verificar permisos
php artisan permission:show | grep llm

# 4. Acceder a admin
# http://localhost:8000/admin/llm
```

---

### 8. Durante Desarrollo - Quick Reference

**Flujo rápido de edición y testing:**
```bash
# Terminal 1: Editar código
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager
# (editor VS Code abierto)

# Terminal 2: Limpiar caches y recargar
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL
php artisan optimize:clear && php artisan serve

# Browser: Recargar página
http://localhost:8000/admin/llm/conversations
```

**Si cambios no aparecen:**
```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

---

### 9. Commits Durante OPCIÓN B

**Formato de commits:**
```bash
# Feature implementation
git commit -m "feat(streaming-conversations): integrate SSE in conversation UI"

# Bug fixes
git commit -m "fix(streaming): handle missing configuration gracefully"

# Documentation
git commit -m "docs(streaming): add real-time streaming guide"

# Tests
git commit -m "test(streaming): add feature tests for conversation streaming"
```

**DO NOT commit:**
- ❌ version bumps (extension.json version field)
- ❌ git tags (hasta TAREA 4)

---

### 10. Transición a TAREA 4 (Release)

**Cuando TAREA 1, 2, 3 estén completas:**

```bash
# 1. Actualizar version
# extension.json: "version": "1.0.0" → "1.1.0"

# 2. Crear git tag
git tag -a v1.1.0 -m "Release v1.1.0: Streaming support"

# 3. Push a GitHub
git push origin main
git push origin v1.1.0

# 4. Crear GitHub release con notas
```

---

## 📊 Resumen del Protocolo

| Aspecto | Política |
|--------|----------|
| **Directorio de trabajo** | `/EXTENSIONS/bithoven-extension-llm-manager` |
| **Instalación en CPANEL** | Symlink (desarrollo) |
| **Version en extension.json** | `1.0.0` (NO cambiar hasta TAREA 4) |
| **updated_at en extension.json** | Actualizar con cada cambio |
| **Git tags** | Crear en TAREA 4 ONLY |
| **Caching durante dev** | Limpiar con `php artisan optimize:clear` |
| **Commits** | Hacer frecuentes, push cuando complete cada TAREA |
| **Push a GitHub** | Solo después de TAREA 4 |

---

## ✅ Next Steps

1. **Usuario instala extension** con symlink en CPANEL
2. **Confirma instalación** exitosa (route:list + permisos)
3. **Notifica cuando listo** para comenzar TAREA 1

---

**Creado:** 28 de noviembre de 2025, 14:45  
**Estado:** 📋 PROTOCOLO LISTO - Aguardando instalación

