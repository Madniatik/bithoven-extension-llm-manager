# Reports Directory

Este directorio contiene **reportes de análisis y resultados** organizados por tipo.

---

## 📁 Estructura

```
reports/
├── activity-log/       # Reportes relacionados con Activity Log
├── analysis/           # Análisis técnicos generales
├── fixes/              # Reportes de bug fixes implementados
└── README.md          # Este archivo
```

---

## 🎯 Propósito

**Diferencia entre Reporte vs Plan:**
- **Reporte**: Documento que analiza/reporta resultados de ejecución (pasado/presente)
- **Plan**: Documento de planificación con tareas/fases a ejecutar (futuro)

**Reportes incluyen:**
- ✅ Análisis de problemas/situación actual
- ✅ Resultados de investigación/testing
- ✅ Evidencia (queries, logs, screenshots)
- ✅ Conclusiones y recomendaciones
- ✅ Estado: DRAFT, COMPLETE, ARCHIVED

---

## 📋 Reportes Actuales

### activity-log/
- **ACTIVITY-LOG-MIGRATION-REPORT-2025-12-07.md** - Análisis completo Activity Log + issues críticos

### analysis/
- **OLLAMA-TOKEN-CALCULATION-ANALYSIS.md** - Análisis de cálculo de tokens Ollama
- **openrouter-response-analysis.md** - Análisis de respuesta OpenRouter

### fixes/
- **QUICK-CHAT-MONITOR-FIX-REPORT.md** - Reporte de fixes de integración Quick Chat

### root level/
- **BUGS-ANALYSIS.md** - Análisis de bugs Quick Chat
- **CHAT-MONITOR-ENHANCEMENT-IMPLEMENTATION-REPORT.md** - Reporte de implementación Monitor
- **PROVIDER-RESPONSE-ANALYSIS.md** - Análisis de estructura de respuestas por provider

---

## 🔄 Workflow

### 1. Crear nuevo reporte
```bash
# Elegir carpeta según tipo
touch reports/analysis/MY-ANALYSIS-REPORT.md
touch reports/fixes/BUG-FIX-REPORT.md
touch reports/activity-log/FEATURE-REPORT.md
```

**Template mínimo:**
```markdown
# Report Title
**Date:** YYYY-MM-DD, HH:MM  
**Version:** 1.0  
**Status:** 📝 DRAFT  
**Author:** Name

## Executive Summary
Brief overview...

## Problem/Situation
Detailed description...

## Analysis
Evidence and investigation...

## Conclusions
Key findings...

## Recommendations
Actionable next steps...
```

### 2. Actualizar status
```markdown
**Status:** 📝 DRAFT       # Borrador inicial
**Status:** 🔍 REVIEW      # En revisión
**Status:** ✅ COMPLETE    # Finalizado
**Status:** 📦 ARCHIVED    # Archivado (obsoleto)
```

### 3. Organizar por tipo
- **activity-log/** - Específico de Activity Log feature
- **analysis/** - Análisis técnicos generales (providers, tokens, performance)
- **fixes/** - Reportes de bug fixes implementados
- **root** - Reportes generales que no encajan en categoría específica

---

## 📚 Convenciones

**Nomenclatura:**
- `FEATURE-NAME-REPORT-YYYY-MM-DD.md` - Para reportes con fecha específica
- `TOPIC-ANALYSIS.md` - Para análisis atemporales
- `BUG-FIX-REPORT.md` - Para reportes de fixes

**Contenido:**
- Incluir evidencia (queries SQL, logs, screenshots)
- Incluir timestamps en formato ISO 8601 o legible
- Referenciar archivos/líneas de código específicos
- Agregar sección "References" al final

**Lifecycle:**
1. DRAFT → Creación inicial
2. REVIEW → Revisión de equipo
3. COMPLETE → Finalizado y aprobado
4. ARCHIVED → Obsoleto (mover a `reports/archived/`)

---

## 🗑️ Archiving

Cuando un reporte queda obsoleto:

```bash
# Crear carpeta archived si no existe
mkdir -p reports/archived

# Mover reporte
mv reports/analysis/OLD-REPORT.md reports/archived/

# Actualizar status en reporte
**Status:** 📦 ARCHIVED  
**Reason:** Replaced by NEW-REPORT.md
```

---

## 📚 Referencias

**Carpetas relacionadas:**
- `docs/` - Documentación técnica e instrucciones
- `plans/` - Planes de implementación

**Diferencias clave:**
- docs/ = "Cómo usar/implementar" (presente continuo)
- reports/ = "Qué pasó/se descubrió" (pasado/presente)
- plans/ = "Qué se va a hacer" (futuro)

---

**Last Updated:** 7 de diciembre de 2025, 03:36
