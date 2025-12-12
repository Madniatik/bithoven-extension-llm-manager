# 📊 Versioning Migration Analysis: v1.0.x → v0.1.x

**Date:** 12 de diciembre de 2025, 05:00  
**Extension:** bithoven-extension-llm-manager  
**Current Version:** v1.0.7 (in development towards v1.0.8)  
**Proposed Change:** Migrate to v0.1.x pre-release versioning

---

## 📋 Executive Summary

**Feasibility:** ✅ Completamente posible  
**Recommended:** ✅ **SÍ, altamente recomendable**  
**Risk Level:** 🟡 Medio (require planificación cuidadosa)  
**Effort:** ~4-6 horas (historia git + archivos + testing)

---

## 🎯 Current Versioning Status

### Git Tags Existentes
```bash
checkpoint-pre-message-refactor      # Checkpoint, no version
v1.0.0                                # "First release" (no fue)
v1.0.0-pre-installation               # Pre-release marker
v1.0.6                                # Released
v1.0.7                                # Released
v1.0.7-pre-refactor                   # Checkpoint
v1.0.8-fase1-complete                 # Work in progress
v1.0.8-fase2-complete                 # Work in progress
```

### Archivos con Versiones
- **extension.json:** `"version": "1.0.7"`
- **composer.json:** No tiene version (correcto, composer usa git tags)
- **CHANGELOG.md:** Documenta v1.0.0 - v1.0.7 + unreleased v1.0.8
- **ROADMAP.md:** Referencias a v1.0.0 - v1.0.8
- **README.md:** Badge `v1.0.8-dev`
- **PROJECT-STATUS.md:** `v1.0.8-dev (33% complete)`
- **Docblock comments:** `@version 1.0.0` en varios archivos PHP

---

## 🔍 Análisis de Implementaciones por Versión

### v1.0.0 - Core Platform (Nov 18, 2025)
**Estado:** ❌ **NO fue release oficial, fue desarrollo interno**

**Features Implementadas:**
- ✅ Multi-provider support (Ollama, OpenAI, Anthropic, Custom)
- ✅ Per-extension configurations
- ✅ Budget tracking (cost limits)
- ✅ Prompt Templates con variable substitution
- ✅ Parameter Override (runtime config)
- ✅ Conversations (persistent sessions + context)
- ✅ Complete Admin UI (6 modules)
- ✅ 16 database tables con migrations
- ✅ 100% manual testing coverage (33/33 features)

**Métricas:**
- Development Time: ~80 hours
- Lines of Code: ~15,000 PHP + Blade
- Database Tables: 16 tables
- Test Coverage: Manual 100%

**Equivalente en v0.x:** Debería ser **v0.5.0** o **v0.6.0** (major milestone en pre-release)

---

### v1.0.4 - Real-Time Streaming (Nov 28, 2025)
**Estado:** ❌ **Desarrollo interno**

**Features Implementadas:**
- ✅ Server-Sent Events (SSE) streaming infrastructure
- ✅ OllamaProvider NDJSON streaming
- ✅ OpenAIProvider SDK streaming
- ✅ Interactive streaming test UI
- ✅ Usage metrics logging (tokens, cost, execution time)
- ✅ Permissions Protocol v2.0 migration
- ✅ LLMPermissions data class (12 permissions)

**Métricas:**
- Development Time: ~20 hours
- New Features: 3 major
- Database Logs: 57+ streaming sessions captured

**Equivalente en v0.x:** **v0.6.0** o **v0.7.0**

---

### v1.0.5 - ChatWorkspace Optimizations (Dec 3, 2025)
**Estado:** ❌ **Desarrollo interno**

**Features Implementadas:**
- ✅ Split-horizontal layout partitioning (66% code reduction)
- ✅ Monitor components optimization
- ✅ 10 reusable partials created
- ✅ Conditional loading implementation

**Métricas:**
- Code Reduction: 63% (740 → 270 lines)
- Impact: Improved maintainability

**Equivalente en v0.x:** **v0.7.1** (patch optimization)

---

### v1.0.6 - Multi-Instance Support (Dec 3, 2025)
**Estado:** ❌ **Desarrollo interno**

**Features Implementadas:**
- ✅ Alpine.js scopes únicos por sesión
- ✅ DOM IDs dinámicos
- ✅ Factory pattern: `window.LLMMonitorFactory`
- ✅ LocalStorage isolation per session
- ✅ 100% backward compatible

**Use Cases Enabled:**
- Dual-chat comparison (GPT-4 vs Claude)
- Model A/B testing
- Multi-user dashboards

**Equivalente en v0.x:** **v0.7.2** (patch feature)

---

### v1.0.7 - Monitor Export + Chat UX (Dec 10, 2025)
**Estado:** ✅ **Versión actual (development)**

**Features Implementadas:**
- ✅ Monitor Export (CSV/JSON/SQL) con session filtering
- ✅ Activity Log DB migration (cross-device persistence)
- ✅ Request Inspector tab
- ✅ Chat Workspace Configuration System (23 docs)
- ✅ Delete Message functionality
- ✅ Context Window visual indicator
- ✅ Message ID refactor (request/response columns)

**Métricas:**
- Development Time: ~72 hours
- Commits: 132+
- Documentation: 3,376+ lines
- Features Complete: 21/21

**Equivalente en v0.x:** **v0.8.0** (major pre-release milestone)

---

### v1.0.8 - Architecture Refactoring + Provider Repositories (In Progress)
**Estado:** 🔄 **33% complete (FASE 1-2 done)**

**Features Implementadas:**
- ✅ FASE 1: Service Layer (2h)
  - LLMConfigurationService (343 lines, 15 methods)
  - Cache layer (90% DB query reduction)
  - 25 tests (20 unit + 5 integration)
  
- ✅ FASE 2: Core Import System (3h)
  - ProviderRepositoryValidator (226 lines)
  - ImportProviderConfigs command
  - ListProviderPackages command
  - 19 tests (8 unit + 11 integration)

**Pendientes:**
- ⏳ FASE 3: First Provider Package (4h) - Ollama configs
- ⏳ FASE 4: Additional Providers (8h) - Anthropic, OpenRouter
- ⏳ FASE 5: Advanced Features (6h) - Export, validation v2.0
- ⏳ FASE 6: Marketplace & Community (8h) - UI, ratings

**Equivalente en v0.x:** **v0.9.0** (final major milestone antes de 1.0)

---

## 🎯 Propuesta de Migración: v1.0.x → v0.1.x

### Mapeo de Versiones

| Versión Actual | Versión Propuesta | Justificación |
|----------------|-------------------|---------------|
| v1.0.0 | **v0.5.0** | Core platform completo (major milestone) |
| v1.0.4 | **v0.6.0** | Streaming (major feature) |
| v1.0.5 | **v0.6.1** | Optimizations (patch) |
| v1.0.6 | **v0.6.2** | Multi-instance (patch) |
| v1.0.7 | **v0.7.0** | Monitor Export + Chat UX (major features) |
| v1.0.8 | **v0.8.0** | Provider Repositories (major architecture change) |
| Future v1.0.9 | **v0.9.0** | Final pre-release before 1.0 |
| True 1.0 | **v1.0.0** | First official public release |

### Semantic Versioning en Pre-Release (0.x)

**0.MAJOR.MINOR:**
- **MAJOR (0.X.0):** Grandes features, cambios arquitectónicos, breaking changes tolerables
- **MINOR (0.x.Y):** Pequeñas features, patches, bug fixes

**Ejemplos:**
- v0.5.0 → v0.6.0: Streaming añadido (major feature)
- v0.6.0 → v0.6.1: Optimizaciones (patch)
- v0.7.0 → v0.8.0: Provider Repositories (arquitectura)
- v0.9.0 → v1.0.0: Release oficial (stable API)

---

## ⚠️ Conflictos Potenciales y Soluciones

### 1. Git History Rewrite (git tags)
**Conflicto:** Tags v1.0.x ya existen en repository

**Opciones:**

#### Opción A: Forzar reescritura (⚠️ Destructivo)
```bash
# Eliminar tags remotos v1.0.x
git push origin :refs/tags/v1.0.0
git push origin :refs/tags/v1.0.4
git push origin :refs/tags/v1.0.6
git push origin :refs/tags/v1.0.7
git push origin :refs/tags/v1.0.7-pre-refactor
git push origin :refs/tags/v1.0.8-fase1-complete
git push origin :refs/tags/v1.0.8-fase2-complete

# Eliminar tags locales
git tag -d v1.0.0 v1.0.4 v1.0.6 v1.0.7 v1.0.7-pre-refactor v1.0.8-fase1-complete v1.0.8-fase2-complete

# Crear nuevos tags v0.x
git tag v0.5.0 <commit-hash-v1.0.0>
git tag v0.6.0 <commit-hash-v1.0.4>
git tag v0.6.1 <commit-hash-v1.0.5>
git tag v0.6.2 <commit-hash-v1.0.6>
git tag v0.7.0 <commit-hash-v1.0.7>
git tag v0.8.0-fase1-complete <commit-hash-v1.0.8-fase1>
git tag v0.8.0-fase2-complete <commit-hash-v1.0.8-fase2>

# Push nuevos tags
git push origin --tags
```

**⚠️ Riesgos:**
- Si alguien más tiene clones, verá conflictos
- GitHub releases apuntarán a tags inexistentes
- Cualquier referencia externa se romperá

**✅ Mitigación:**
- Proyecto es privado/no tiene usuarios externos → **SEGURO**
- Crear backup del repo antes: `git clone --mirror`
- Notificar a colaboradores (si existen)

#### Opción B: Deprecar v1.0.x, continuar desde v0.x (✅ Recomendado)
```bash
# Mantener tags v1.0.x como están (deprecados)
# Crear nuevos tags v0.x para commits FUTUROS
git tag v0.8.0  # Cuando se complete v1.0.8
git tag v0.9.0  # Próxima major milestone
git tag v1.0.0  # Release oficial
```

**✅ Ventajas:**
- No rompe historia existente
- Tags v1.0.x quedan como "internal development versions"
- Futuro limpio con v0.x → v1.0.0

**❌ Desventajas:**
- Historia tiene "versiones incorrectas" (v1.0.0-v1.0.7)
- Puede confundir si se mira git log

**🎯 Recomendación:** **Opción B** si ya hay trabajo compartido, **Opción A** si es solo tú.

---

### 2. Archivos con Versión Hardcoded

**Archivos a Modificar:**

#### extension.json
```json
{
    "version": "1.0.7",  // → "0.7.0"
}
```

#### README.md
```markdown
![Version](https://img.shields.io/badge/version-1.0.8--dev-blue)
// → ![Version](https://img.shields.io/badge/version-0.8.0--dev-blue)
```

#### PROJECT-STATUS.md
```markdown
**Version:** v1.0.8-dev (33% complete)
// → **Version:** v0.8.0-dev (33% complete)
```

#### ROADMAP.md
- Toda la tabla de versiones necesita actualización
- Referencias a v1.0.0 - v1.0.8 → v0.5.0 - v0.8.0

#### CHANGELOG.md
- Headers de versión: `## [1.0.7]` → `## [0.7.0]`
- Referencias internas actualizadas

#### Docblock Comments (@version)
```php
/**
 * @version 1.0.0  // → 0.8.0 (versión actual)
 */
```

**Conflicto:** Archivos versionados pueden tener merge conflicts si hay ramas activas

**Solución:**
1. Hacer commit de cambios pendientes
2. Ejecutar script de migración en main branch
3. Merge otras ramas después (resolver conflicts manualmente)

---

### 3. Composer Package Version

**Conflicto:** `composer.json` NO tiene version (correcto), pero Packagist usa git tags

**Solución:**
- **Si ya publicado en Packagist:** Tags v1.0.x ya están "released" → **No se puede revertir**
- **Si NO publicado:** Git tags v0.x funcionarán correctamente
- **Packagist Version Detection:** Usa tags semánticos (v1.0.0, v0.5.0, etc.)

**⚠️ Verificar:**
```bash
# ¿Está publicado en Packagist?
curl https://packagist.org/packages/bithoven/llm-manager.json
```

**Si está publicado:**
- Opción A: Deprecar v1.0.x, continuar con v0.8.0 (nueva serie)
- Opción B: Mantener v1.0.x, considerar v1.0.8 como v0.8.0 internamente

---

### 4. Extension Manager Compatibility

**Conflicto:** CPANEL Extension Manager puede tener lógica de versión

**Verificar:**
- ¿Extension Manager valida version syntax?
- ¿Depende de versión >= 1.0?
- ¿Tiene migración automática basada en version?

**Solución:**
- Probar instalación con `"version": "0.8.0"` en extension.json
- Verificar que Extension Manager acepta v0.x
- Actualizar validación si rechaza pre-release versions

---

### 5. Database Migrations

**Conflicto:** Nombres de migraciones NO tienen versión (correcto Laravel style)

**Archivos:**
```
database/migrations/2025_11_18_000001_create_llm_manager_providers_table.php
database/migrations/2025_11_18_000002_create_llm_manager_provider_configurations_table.php
```

**✅ No requiere cambios** - Las migraciones usan timestamp, no versión

---

### 6. Documentation Links

**Conflicto:** Referencias a "v1.0.0" en documentación

**Archivos Afectados:**
- `docs/guides/INSTALLATION.md`
- `docs/guides/CONFIGURATION.md`
- `docs/guides/USAGE-GUIDE.md`
- `docs/guides/EXAMPLES.md`
- `docs/guides/API-REFERENCE.md`
- `docs/guides/FAQ.md`

**Solución:**
- Buscar y reemplazar "v1.0" → "v0." en docs/
- Actualizar ejemplos de version checking

---

## 🛠️ Plan de Migración Paso a Paso

### Fase 1: Preparación (30 min)

1. **Backup completo:**
   ```bash
   cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager
   git clone --mirror . ../llm-manager-backup.git
   ```

2. **Crear rama de migración:**
   ```bash
   git checkout -b migration/v1-to-v0
   ```

3. **Documentar estado actual:**
   ```bash
   git log --oneline --decorate > migration-git-log-before.txt
   git tag -l > migration-tags-before.txt
   ```

---

### Fase 2: Actualización de Archivos (2 horas)

**Script automatizado:**

```bash
#!/bin/bash
# migration-v1-to-v0.sh

echo "🔄 Migrando versiones v1.0.x → v0.x..."

# 1. extension.json
sed -i '' 's/"version": "1.0.7"/"version": "0.7.0"/' extension.json

# 2. README.md
sed -i '' 's/version-1\.0\.8--dev/version-0.8.0--dev/' README.md
sed -i '' 's/v1\.0\.8-dev/v0.8.0-dev/g' README.md

# 3. PROJECT-STATUS.md
sed -i '' 's/v1\.0\.8-dev/v0.8.0-dev/g' PROJECT-STATUS.md

# 4. ROADMAP.md (mapeo específico)
sed -i '' 's/v1\.0\.0/v0.5.0/g' ROADMAP.md
sed -i '' 's/v1\.0\.4/v0.6.0/g' ROADMAP.md
sed -i '' 's/v1\.0\.5/v0.6.1/g' ROADMAP.md
sed -i '' 's/v1\.0\.6/v0.6.2/g' ROADMAP.md
sed -i '' 's/v1\.0\.7/v0.7.0/g' ROADMAP.md
sed -i '' 's/v1\.0\.8/v0.8.0/g' ROADMAP.md

# 5. CHANGELOG.md
sed -i '' 's/## \[1\.0\.0\]/## [0.5.0]/' CHANGELOG.md
sed -i '' 's/## \[1\.0\.4\]/## [0.6.0]/' CHANGELOG.md
sed -i '' 's/## \[1\.0\.5\]/## [0.6.1]/' CHANGELOG.md
sed -i '' 's/## \[1\.0\.6\]/## [0.6.2]/' CHANGELOG.md
sed -i '' 's/## \[1\.0\.7\]/## [0.7.0]/' CHANGELOG.md
sed -i '' 's/v1\.0\.8/v0.8.0/g' CHANGELOG.md

# 6. Docblocks PHP (@version)
find src/ -type f -name "*.php" -exec sed -i '' 's/@version 1\.0\.0/@version 0.8.0/' {} \;
find src/ -type f -name "*.php" -exec sed -i '' 's/@version 1\.0\.7/@version 0.8.0/' {} \;

# 7. Documentación (docs/)
find docs/ -type f -name "*.md" -exec sed -i '' 's/Version.*1\.0\./Version 0.8.0/' {} \;
find docs/ -type f -name "*.md" -exec sed -i '' 's/v1\.0\./v0./g' {} \;

# 8. Plans (referencia)
find plans/ -type f -name "*.md" -exec sed -i '' 's/PLAN-v1\.0\.8/PLAN-v0.8.0/' {} \;

echo "✅ Archivos actualizados correctamente"
echo "📝 Revisar cambios con: git diff"
```

**Ejecutar:**
```bash
chmod +x migration-v1-to-v0.sh
./migration-v1-to-v0.sh
```

**Validar cambios:**
```bash
git diff --stat
git diff | less
```

---

### Fase 3: Git Tags (Elegir Opción A o B)

#### Opción A: Reescribir Historia (Solo si repo es privado/personal)

```bash
# 1. Obtener commit hashes
V100_HASH=$(git rev-list -n 1 v1.0.0)
V104_HASH=$(git rev-list -n 1 v1.0.4)
V106_HASH=$(git rev-list -n 1 v1.0.6)
V107_HASH=$(git rev-list -n 1 v1.0.7)
V108F1_HASH=$(git rev-list -n 1 v1.0.8-fase1-complete)
V108F2_HASH=$(git rev-list -n 1 v1.0.8-fase2-complete)

# 2. Eliminar tags remotos (si existe remote)
git push origin :refs/tags/v1.0.0
git push origin :refs/tags/v1.0.4
git push origin :refs/tags/v1.0.6
git push origin :refs/tags/v1.0.7
git push origin :refs/tags/v1.0.7-pre-refactor
git push origin :refs/tags/v1.0.8-fase1-complete
git push origin :refs/tags/v1.0.8-fase2-complete

# 3. Eliminar tags locales
git tag -d v1.0.0 v1.0.4 v1.0.6 v1.0.7 v1.0.7-pre-refactor v1.0.8-fase1-complete v1.0.8-fase2-complete

# 4. Crear nuevos tags v0.x
git tag -a v0.5.0 $V100_HASH -m "Core Platform (formerly v1.0.0)"
git tag -a v0.6.0 $V104_HASH -m "Real-Time Streaming (formerly v1.0.4)"
git tag -a v0.6.2 $V106_HASH -m "Multi-Instance Support (formerly v1.0.6)"
git tag -a v0.7.0 $V107_HASH -m "Monitor Export + Chat UX (formerly v1.0.7)"
git tag -a v0.8.0-fase1-complete $V108F1_HASH -m "Service Layer Complete"
git tag -a v0.8.0-fase2-complete $V108F2_HASH -m "Core Import System Complete"

# 5. Push nuevos tags
git push origin --tags --force
```

#### Opción B: Deprecar v1.0.x, Continuar desde v0.x (Recomendado)

```bash
# 1. NO eliminar tags v1.0.x existentes (quedan como historia)

# 2. Añadir nota en CHANGELOG
cat >> CHANGELOG.md << 'EOF'

---

## Migration Note: v1.0.x → v0.x (December 12, 2025)

**Why the change?**
Versions v1.0.0 through v1.0.7 were internal development versions, not official public releases.
To reflect the pre-release nature of this project, we've migrated to v0.x semantic versioning.

**Version Mapping:**
- v1.0.0 → v0.5.0 (Core Platform)
- v1.0.4 → v0.6.0 (Real-Time Streaming)
- v1.0.5 → v0.6.1 (ChatWorkspace Optimizations)
- v1.0.6 → v0.6.2 (Multi-Instance Support)
- v1.0.7 → v0.7.0 (Monitor Export + Chat UX)
- v1.0.8 → v0.8.0 (Provider Repositories)

**True v1.0.0** will be the first official public release with stable API.

EOF

# 3. Commit migración
git add .
git commit -m "chore: migrate versioning from v1.0.x to v0.x pre-release"

# 4. Crear tag actual
git tag -a v0.8.0-dev -m "Current development version (migrated from v1.0.8-dev)"

# 5. Push
git push origin migration/v1-to-v0
git push origin v0.8.0-dev
```

---

### Fase 4: Testing (1 hora)

**Tests a ejecutar:**

1. **Extension Installation:**
   ```bash
   cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL
   php artisan bithoven:extension:uninstall llm-manager
   php artisan bithoven:extension:install llm-manager
   ```

2. **Version Check:**
   ```bash
   # Verificar que extension.json muestra v0.7.0
   cat vendor/bithoven/llm-manager/extension.json | grep version
   
   # Verificar que Extension Manager reconoce versión
   php artisan bithoven:extension:list | grep llm-manager
   ```

3. **Functionality:**
   - Abrir http://localhost:8000/admin/llm
   - Probar Quick Chat
   - Probar Knowledge Base
   - Probar Tool Definitions
   - Verificar Activity Logs

4. **Composer:**
   ```bash
   composer show bithoven/llm-manager
   # Debería mostrar version correcta si está local
   ```

---

### Fase 5: Merge y Deploy (30 min)

```bash
# 1. Merge a main
git checkout main
git merge migration/v1-to-v0

# 2. Tag final
git tag -a v0.8.0-dev -m "Architecture Refactoring + Provider Repositories (33% complete)"

# 3. Push
git push origin main
git push origin v0.8.0-dev

# 4. Eliminar rama de migración
git branch -d migration/v1-to-v0
git push origin --delete migration/v1-to-v0
```

---

## 📊 Checklist de Archivos a Modificar

### Archivos Críticos (Manual Review)
- [ ] `extension.json` - version field
- [ ] `README.md` - version badge
- [ ] `PROJECT-STATUS.md` - version references
- [ ] `ROADMAP.md` - version table
- [ ] `CHANGELOG.md` - version headers

### Archivos Secundarios (Script Automático)
- [ ] `docs/guides/INSTALLATION.md`
- [ ] `docs/guides/CONFIGURATION.md`
- [ ] `docs/guides/USAGE-GUIDE.md`
- [ ] `docs/guides/EXAMPLES.md`
- [ ] `docs/guides/API-REFERENCE.md`
- [ ] `docs/guides/FAQ.md`
- [ ] `docs/providers/PROVIDER-COMPARISON.md`
- [ ] `plans/new/PLAN-v1.0.8/README.md`
- [ ] `src/**/*.php` - @version docblocks

### Archivos que NO cambiar
- [ ] `composer.json` - No tiene version (correcto)
- [ ] `database/migrations/*` - Usan timestamps
- [ ] `tests/**/*.php` - Tests no dependen de version

---

## ✅ Recomendaciones Finales

### ¿Es Recomendable Hacerlo?

**✅ SÍ, altamente recomendable porque:**

1. **Honestidad de Versioning:** v1.0.x implica "stable release", pero esto es desarrollo activo
2. **Expectativas Claras:** v0.x comunica "pre-release, API puede cambiar"
3. **Semantic Versioning Correcto:** v0.8.0 → v1.0.0 tiene significado (major milestone)
4. **Flexibilidad:** v0.x permite breaking changes sin culpa
5. **Marketing:** Verdadero v1.0.0 puede ser anunciado como "launch oficial"

### ¿Cuándo NO Hacerlo?

❌ **NO migrar si:**
- Ya hay usuarios en producción dependiendo de v1.0.x
- Package publicado en Packagist con descargas
- Contratos/SLAs que referencian v1.0.x
- Equipo grande con muchas ramas activas

### ¿Cuándo SÍ Hacerlo?

✅ **SÍ migrar si:**
- Proyecto privado/interno (tu caso) ✅
- No hay usuarios externos ✅
- Repo no publicado en Packagist ✅
- Equipo pequeño o solo tú ✅

---

## 🎯 Recomendación Final

**Opción Recomendada:** **Opción B - Deprecar v1.0.x, Continuar desde v0.x**

**Razones:**
1. ✅ No rompe historia git existente
2. ✅ Tags v1.0.x quedan como "internal milestones"
3. ✅ Futuro limpio: v0.8.0 → v0.9.0 → v1.0.0
4. ✅ Menos riesgo de errores
5. ✅ Fácil rollback si hay problemas

**Esfuerzo Estimado:**
- Preparación: 30 min
- Script migración: 2 horas
- Testing: 1 hora
- Deploy: 30 min
- **Total: 4 horas**

**Próximos Pasos:**
1. Revisar este análisis
2. Decidir: Opción A (reescribir) vs Opción B (deprecar)
3. Ejecutar Fase 1 (backup)
4. Ejecutar script de migración
5. Testing exhaustivo
6. Commit y push

---

## 📞 ¿Preguntas?

¿Necesitas ayuda con alguna fase específica? Puedo:
- Generar el script de migración completo
- Ejecutar los comandos paso a paso
- Validar los cambios antes de commit
- Resolver conflicts específicos

**Ready when you are!** 🚀

---

**Created By:** GitHub Copilot (Claude Sonnet 4.5, Anthropic)  
**Date:** 12 de diciembre de 2025, 05:00
