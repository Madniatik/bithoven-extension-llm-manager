# Activity Log Migration Plan
**Date:** 7 de diciembre de 2025, 03:35  
**Version:** 1.0  
**Status:** 🔴 NEW - Bloqueado por issues críticos  
**Related Reports:** `reports/activity-log/ACTIVITY-LOG-MIGRATION-REPORT-2025-12-07.md`

---

## 📋 Executive Summary

Plan para migrar Activity Log de **localStorage** (Test Monitor) a **database-driven** (Chat Monitor). Requiere resolver 3 issues críticos antes de implementar.

**Objetivo:** Unificar experiencia de Activity Log en ambos monitores con datos persistentes, cross-device, ilimitados.

---

## 🚨 BLOCKERS CRÍTICOS (Fase 0 - REQUERIDA ANTES)

### 🔴 Blocker #1: session_id/message_id NULL en usage_logs
**Problema:** Todos los registros tienen `session_id` y `message_id` NULL (verificado via MySQL)  
**Impacto:** Activity Log NO puede filtrar por sesión  
**Tiempo:** 1-2 horas  

**Tareas:**
- [ ] Modificar `LLMStreamLogger@startSession()` - Agregar params `?int $sessionId`, `?int $messageId`
- [ ] Modificar `LLMStreamLogger@endSession()` - Incluir session_id/message_id en INSERT
- [ ] Actualizar `LLMQuickChatController@stream()` - Pasar `$session->id`, `$userMessage->id`
- [ ] Actualizar `LLMStreamController@conversationStream()` - Pasar `$session->id`
- [ ] Testing SQL: Verificar nuevos registros tienen IDs (no NULL)

---

### 🟡 Blocker #2: Arquitectura de Endpoints (DECISIÓN REQUERIDA)
**Problema:** 3 endpoints con 80-85% código duplicado  
**Tiempo:** 0-3 horas (según opción elegida)

**Opciones:**
- **A) Mantener 3 separados** - 0h, simple, duplicación continúa
- **B) Unificar en 2 (Test vs Conversations)** - 2-3h, RECOMENDADO, DRY
- **C) Unificar en 1 universal** - 2-3h, máximo DRY, complejo

**Tareas (si Opción B):**
- [ ] Copiar features de QuickChatController a conversationStream
- [ ] Actualizar rutas Quick Chat
- [ ] Deprecar LLMQuickChatController@stream
- [ ] Testing exhaustivo

---

### 🟠 Blocker #3: localStorage Cleanup
**Problema:** Código legacy localStorage duplica datos, inconsistencia cross-browser  
**Tiempo:** 1-2 horas  

**Tareas:**
- [ ] Crear endpoint `getActivityHistory()` en LLMStreamController
- [ ] Crear ruta `GET /admin/llm/stream/activity-history`
- [ ] Crear partial `activity-table.blade.php` con AJAX
- [ ] Eliminar localStorage code de test.blade.php (líneas 289, 723-810)
- [ ] Eliminar `public/js/monitor/storage/storage.js`
- [ ] Eliminar referencias MonitorStorage en monitor-api.blade.php
- [ ] Testing: Activity Log carga desde DB

---

## ✅ FASE 1-6: MIGRATION (Después de Fase 0)

### Phase 1: Backend Endpoint (1h)
- [ ] Crear `getActivityHistory()` en `LLMStreamController`
- [ ] Agregar ruta `GET /admin/llm/stream/activity-history`
- [ ] Implementar query con eager loading (llm_configuration)
- [ ] Ordenar por `executed_at DESC`, limitar a 10-50
- [ ] Testing Postman/curl

### Phase 2: Blade Partial (1-2h)
- [ ] Crear `resources/views/admin/stream/partials/activity-table.blade.php`
- [ ] Implementar función `ActivityHistory.load(sessionId?)`
- [ ] Implementar función `ActivityHistory.render(data)`
- [ ] Agregar empty state ("No activity yet")
- [ ] Testing con 0, 1, 10 items

### Phase 3: Integration (1h)
- [ ] Incluir activity-table.blade.php en monitor-api.blade.php
- [ ] Llamar `ActivityHistory.load()` al cargar página
- [ ] Llamar `ActivityHistory.load()` después de stream complete
- [ ] Remover llamadas a `addToActivityHistory()` de localStorage

### Phase 4: Testing (1-2h)
- [ ] Functional: Crear nuevo stream → verificar aparece en Activity Log
- [ ] Performance: Query <200ms con 1000 registros
- [ ] Cross-browser: Chrome, Safari, Firefox
- [ ] Session filtering: Filtrar por session_id específico

### Phase 5: Documentation (1h)
- [ ] Actualizar README.md con enfoque database
- [ ] Documentar diferencias Test vs Chat monitors
- [ ] Crear troubleshooting guide
- [ ] Actualizar API-REFERENCE.md

### Phase 6: Cleanup & Commit (30min)
- [ ] Remover código localStorage de Chat Monitor
- [ ] Actualizar comentarios
- [ ] Git commit con mensaje descriptivo
- [ ] Update CHANGELOG.md

---

## ⏱️ Time Estimates

| Fase | Tareas | Tiempo | Prioridad |
|------|--------|--------|-----------|
| **Fase 0** | Fix session_id/message_id | 1-2h | 🔴 CRÍTICA |
| **Fase 0** | Decidir + implementar endpoints | 0-3h | 🟡 ALTA |
| **Fase 0** | localStorage cleanup | 1-2h | 🟠 MEDIA |
| **Fase 1** | Backend endpoint | 1h | 🟢 NORMAL |
| **Fase 2** | Blade partial | 1-2h | 🟢 NORMAL |
| **Fase 3** | Integration | 1h | 🟢 NORMAL |
| **Fase 4** | Testing | 1-2h | 🟢 NORMAL |
| **Fase 5** | Documentation | 1h | 🟢 NORMAL |
| **Fase 6** | Cleanup & commit | 30min | 🟢 NORMAL |
| **TOTAL** | - | **8-13h** | - |

---

## 🎯 Success Criteria

**Fase 0 (Blockers):**
- ✅ Todos los nuevos `usage_logs` tienen `session_id` y `message_id` no NULL
- ✅ Test Monitor sigue funcionando (sin session = NULL esperado)
- ✅ Quick Chat guarda session_id/message_id correctamente
- ✅ No hay código localStorage activo (solo comentado como referencia)

**Fase 1-6 (Migration):**
- ✅ Activity Log carga desde database vía AJAX
- ✅ Datos persisten entre sesiones/browsers
- ✅ Performance <200ms para query
- ✅ Empty state funciona correctamente
- ✅ Session filtering funcional
- ✅ Cross-browser compatible
- ✅ Documentación actualizada

---

## 📚 References

**Reports:**
- `reports/activity-log/ACTIVITY-LOG-MIGRATION-REPORT-2025-12-07.md` - Análisis completo

**Files to Modify:**
- `src/Services/LLMStreamLogger.php`
- `src/Http/Controllers/Admin/LLMQuickChatController.php`
- `src/Http/Controllers/Admin/LLMStreamController.php`
- `resources/views/admin/stream/test.blade.php`
- `public/js/monitor/storage/storage.js`
- `resources/views/components/chat/partials/scripts/monitor-api.blade.php`

**Database:**
- Table: `llm_manager_usage_logs` (21 columns)
- Columns: `session_id`, `message_id` (BIGINT UNSIGNED NULL)

---

## 🚦 Current Status

**Estado:** 🔴 NEW - Bloqueado por Fase 0  
**Próximo paso:** Resolver Blocker #2 (Decisión de arquitectura de endpoints)  
**Fecha inicio:** Pendiente  
**Fecha estimada fin:** Pendiente  

**Notas:**
- Plan creado desde reporte de análisis
- Requiere decisión de usuario sobre endpoints (Opción A/B/C)
- No iniciar hasta resolver 3 blockers críticos

---

**Created:** 7 de diciembre de 2025, 03:35  
**Author:** Claude (AI Assistant)  
**Version:** 1.0
