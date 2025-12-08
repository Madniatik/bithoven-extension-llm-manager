# Plans Directory

Este directorio contiene los **planes de implementación** organizados por estado.

---

## 📁 Estructura

```
plans/
├── new/                 # Planes nuevos, no iniciados
├── in-progress/         # Planes en ejecución activa
├── completed/           # Planes completados (referencia histórica)
├── archive/             # Planes archivados (integrados en master plan)
├── PLAN-v1.0.7.md      # Plan maestro v1.0.7 (85% completado)
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

### Master Plan
- **PLAN-v1.0.7.md** - Plan maestro consolidado (85% completado, 110+ commits)
  - Quick Chat Feature (100%)
  - Monitor System v2.0 (100%)
  - Provider Connection Service Layer (100%)
  - UI/UX Optimizations (92%)
  - Testing Suite (Pendiente)
  - Streaming Documentation (Pendiente)
  - GitHub Release (Pendiente)

### new/
- **DUAL-SELECT-MODEL-PICKER-PROPOSAL.md** - Propuesta para selector dual Provider+Model

### in-progress/
- *(vacío - ningún plan en ejecución activa)*

### completed/
- **ACTIVITY-LOG-MIGRATION-PLAN.md** - Database-driven Activity History (✅ Completado 7 dic 2025, 21:45)
- **CHAT-MONITOR-ENHANCEMENT-PLAN.md** - Upgrade Monitor UI (✅ 8/8 fases completadas)
- **MONITOR-SYSTEM-v2.0-IMPLEMENTATION.md** - Hybrid Adapter + Configurable UI (✅ Completado)
- **DATABASE-LOGS-CONSOLIDATION-PLAN.md** - Eliminar tabla redundante conversation_logs
- **FIX-PROVIDERS-CONNECTION-SERVICE-LAYER.md** - Service Layer para conexión LLM (✅ Completado 8 dic 2025)
- **FIX-PROVIDERS-CONNECTION-IN-ADMIN-MODELS.md** - Fix Load Models en Admin (✅ Completado 8 dic 2025)

### archive/
- **QUICK-CHAT-IMPLEMENTATION-PLAN.md** - Plan detallado Quick Chat (integrado en PLAN-v1.0.7.md)
- **PLAN-v1.0.7-HANDOFF-TO-NEXT-COPILOT.md** - Handoff documentation (integrado en PLAN-v1.0.7.md)

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

**Last Updated:** 8 de diciembre de 2025, 16:32
