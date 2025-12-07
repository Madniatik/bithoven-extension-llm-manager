# Plans Directory

Este directorio contiene los **planes de implementación** organizados por estado.

---

## 📁 Estructura

```
plans/
├── new/                 # Planes nuevos, no iniciados
├── in-progress/         # Planes en ejecución activa
├── completed/           # Planes completados (referencia histórica)
└── README.md           # Este archivo
```

---

## 🎯 Propósito

**Diferencia entre Plan vs Reporte:**
- **Plan**: Documento de planificación con tareas/fases a ejecutar (futuro)
- **Reporte**: Documento que analiza/reporta resultados de ejecución (pasado)

**Planes incluyen:**
- ✅ Objetivos claros
- ✅ Fases/tareas con checkboxes `[ ]`
- ✅ Estimaciones de tiempo
- ✅ Criterios de éxito
- ✅ Estado actual (NEW, IN-PROGRESS, COMPLETED, BLOCKED)

---

## 📋 Planes Actuales

### new/
- **ACTIVITY-LOG-MIGRATION-PLAN.md** - Migrar Activity Log a database (🔴 Bloqueado por issues críticos)
- **DATABASE-LOGS-CONSOLIDATION-PLAN.md** - Eliminar tabla redundante conversation_logs

### in-progress/
- *(vacío - ningún plan en ejecución activa)*

### completed/
- **CHAT-MONITOR-ENHANCEMENT-PLAN.md** - Upgrade Monitor UI (✅ 8/8 fases completadas)
- **MONITOR-SYSTEM-v2.0-IMPLEMENTATION.md** - Hybrid Adapter + Configurable UI (✅ Completado)

---

## 🔄 Workflow

### 1. Crear nuevo plan
```bash
# Crear en plans/new/
touch plans/new/MY-FEATURE-PLAN.md
```

**Template mínimo:**
```markdown
# Feature Name Plan
**Date:** YYYY-MM-DD  
**Status:** 🔴 NEW  
**Estimated Time:** Xh

## Objetivos
- [ ] Goal 1
- [ ] Goal 2

## Fases
### Phase 1: Title (Xh)
- [ ] Task 1
- [ ] Task 2

## Success Criteria
- ✅ Criterion 1
```

### 2. Iniciar plan
```bash
# Mover a in-progress/
mv plans/new/MY-FEATURE-PLAN.md plans/in-progress/
```

**Actualizar header:**
```markdown
**Status:** 🟡 IN-PROGRESS  
**Started:** YYYY-MM-DD
```

### 3. Completar plan
```bash
# Mover a completed/
mv plans/in-progress/MY-FEATURE-PLAN.md plans/completed/
```

**Actualizar header:**
```markdown
**Status:** ✅ COMPLETED  
**Completed:** YYYY-MM-DD
```

### 4. Bloquear plan
Si un plan encuentra blockers críticos:

```markdown
**Status:** 🔴 BLOCKED  
**Blocker:** Description of blocker
```

Puede permanecer en `new/` o `in-progress/` hasta resolver blocker.

---

## 📚 Referencias

**Carpetas relacionadas:**
- `docs/` - Documentación técnica e instrucciones
- `reports/` - Reportes de análisis y resultados

**Convención de nombres:**
- Usar `UPPERCASE-WITH-DASHES-PLAN.md`
- Incluir `-PLAN` en el nombre
- Ejemplos: `FEATURE-X-PLAN.md`, `REFACTOR-Y-PLAN.md`

---

**Last Updated:** 7 de diciembre de 2025, 03:36
