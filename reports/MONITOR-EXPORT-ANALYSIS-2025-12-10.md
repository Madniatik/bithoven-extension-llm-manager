# Monitor Export Functionality - Analysis & Implementation Plan

**Fecha:** 10 de diciembre de 2025  
**Contexto:** Integrar botones Export CSV/JSON del admin al Monitor (Activity Logs tab)

---

## 📊 Análisis del Sistema Existente

### 1. **Funcionalidad Actual (Admin Activity Page)**

**Ubicación:** `/admin/llm/activity`

**Rutas existentes:**
```php
// routes/web.php
Route::get('activity-export/csv', [LLMActivityController::class, 'export'])
    ->name('activity.export');
Route::get('activity-export/json', [LLMActivityController::class, 'exportJson'])
    ->name('activity.export-json');
```

**Controlador:** `LLMActivityController.php`

**Métodos:**
- `export()` - Export CSV con filtros
- `exportJson()` - Export JSON con filtros

**Filtros soportados:**
- `provider` - Filtrar por proveedor (openai, anthropic, etc.)
- `status` - Filtrar por estado (success, error)
- `date_from` - Fecha desde
- `date_to` - Fecha hasta
- `search` - Búsqueda en prompt/response

**Implementación:**
```php
public function export(Request $request)
{
    $query = LLMUsageLog::with(['configuration', 'user'])
        ->latest('executed_at');

    // Apply filters (provider, status, date_from, date_to)
    
    $logs = $query->get();
    
    // Generate CSV with headers
    // Stream response with filename: llm-activity-{timestamp}.csv
}
```

### 2. **Sistema de Activity History (Monitor)**

**Endpoint actual:** `route('admin.llm.stream.activity-history')`

**Controlador:** `LLMStreamController@getActivityHistory`

**Filtros actuales:**
```php
$validated = $request->validate([
    'session_id' => 'nullable|integer|exists:llm_manager_conversation_sessions,id',
    'limit' => 'nullable|integer|min:1|max:100',
]);

$query = LLMUsageLog::with('configuration')
    ->where('user_id', auth()->id());

// Filter by session_id if provided
if (isset($validated['session_id'])) {
    $query->where('session_id', $validated['session_id']);
}
```

**Datos devueltos:**
```javascript
{
    timestamp: "2025-12-10T10:30:00+00:00",
    provider: "openai",
    model: "gpt-4",
    tokens: 150,
    cost: 0.003,
    duration: 2.5,
    status: "success",
    prompt: "Texto del prompt...",
    response: "Respuesta del modelo...",
    log_id: 123
}
```

---

## 🎯 Propuesta de Integración

### **Objetivo:**
Agregar botones "Export CSV" y "Export JSON" en el tab Activity Logs del Monitor para exportar **solo los logs relacionados con el workspace actual** (session_id específico o global si no hay sesión).

### **Ubicación de Botones:**

**Monitor Tab: Activity Logs**
```
Header: [Dynamic Title] [Refresh] [Load More] [Export CSV ▼] [Fullscreen] [Close]
                                               └─ Export JSON
```

**Implementación propuesta:**
- Botón "Export" con dropdown (CSV / JSON)
- O dos botones separados (más claro)

---

## 🔧 Plan de Implementación

### **Opción 1: Reutilizar Endpoints Existentes** ✅ RECOMENDADO

**Ventajas:**
- ✅ Código ya probado y funcionando
- ✅ No duplicar lógica de exportación
- ✅ Mantiene consistencia con admin
- ✅ Implementación rápida (~30 min)

**Desventajas:**
- ⚠️ Necesita agregar filtro `session_id` a los endpoints existentes

**Cambios necesarios:**

1. **Modificar `LLMActivityController::export()`**
```php
public function export(Request $request)
{
    $query = LLMUsageLog::with(['configuration', 'user'])
        ->latest('executed_at');

    // NUEVO: Filter by session_id (Monitor context)
    if ($request->filled('session_id')) {
        $query->where('session_id', $request->session_id);
    }
    
    // NUEVO: Filter by user (solo logs del usuario actual)
    if ($request->filled('user_only')) {
        $query->where('user_id', auth()->id());
    }

    // Existing filters (provider, status, date_from, date_to)
    // ...

    $logs = $query->get();
    
    // Generate CSV...
}
```

2. **Modificar `LLMActivityController::exportJson()`**
```php
public function exportJson(Request $request)
{
    // Same logic as export()
    // Return JSON instead of CSV
}
```

3. **Agregar botones en Monitor**
```blade
{{-- monitor-activity-logs.blade.php --}}
@if($variant === 'table')
    {{-- Monitor context: show export buttons --}}
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('admin.llm.activity.export', ['session_id' => $sessionId, 'user_only' => 1]) }}" 
           class="btn btn-sm btn-light-success">
            <i class="ki-outline ki-file-down fs-5"></i>
            Export CSV
        </a>
        <a href="{{ route('admin.llm.activity.export-json', ['session_id' => $sessionId, 'user_only' => 1]) }}" 
           class="btn btn-sm btn-light-info">
            <i class="ki-outline ki-file-down fs-5"></i>
            Export JSON
        </a>
    </div>
@endif
```

**O integrarlo en header buttons:**

4. **Agregar props al componente de botones**
```blade
{{-- monitor-header-buttons.blade.php --}}
@php
    $showExport = $showExport ?? false;
    $sessionId = $sessionId ?? null;
@endphp

@if($showExport)
    {{-- Export Dropdown --}}
    <div class="btn-group" role="group">
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-success dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="Export activity logs">
            {!! getIcon('ki-file-down', $iconSize, '', 'i') !!}
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" 
                   href="{{ route('admin.llm.activity.export', ['session_id' => $sessionId, 'user_only' => 1]) }}">
                    <i class="ki-outline ki-file-down fs-5 me-2"></i>
                    Export CSV
                </a>
            </li>
            <li>
                <a class="dropdown-item" 
                   href="{{ route('admin.llm.activity.export-json', ['session_id' => $sessionId, 'user_only' => 1]) }}">
                    <i class="ki-outline ki-file-down fs-5 me-2"></i>
                    Export JSON
                </a>
            </li>
        </ul>
    </div>
@endif
```

5. **Actualizar configuración de botones en split-horizontal-layout**
```blade
{{-- Activity Logs Tab Buttons --}}
<div x-show="activeTab === 'activity'" style="display: none;">
    @include('llm-manager::components.chat.shared.monitor.monitor-header-buttons', [
        'monitorId' => $monitorId,
        'showRefresh' => true,
        'showLoadMore' => true,
        'showExport' => true,          // NUEVO
        'sessionId' => $session?->id,  // NUEVO
        'showFullscreen' => true,
        'showClose' => true,
        'size' => 'sm'
    ])
</div>
```

---

### **Opción 2: Crear Endpoints Específicos del Monitor** ❌ NO RECOMENDADO

**Ventajas:**
- ✅ Lógica separada y específica
- ✅ No afecta endpoints admin

**Desventajas:**
- ❌ Duplicación de código (export logic)
- ❌ Más tiempo de implementación (~2h)
- ❌ Mantener dos implementaciones sincronizadas
- ❌ Más archivos y rutas

**NO PROCEDER CON ESTA OPCIÓN**

---

## 📋 Comportamiento Esperado

### **Caso 1: Monitor con Sesión (Chat Workspace)**
```
User clicks "Export CSV" en Activity Logs tab
↓
Request: /admin/llm/activity-export/csv?session_id=39&user_only=1
↓
Backend filtra: session_id=39 AND user_id=auth()->id()
↓
Download: llm-activity-session-39-2025-12-10-143022.csv
```

**Contenido:** Solo logs de la sesión 39 del usuario actual

### **Caso 2: Monitor sin Sesión (Quick Chat)**
```
User clicks "Export CSV" en Activity Logs tab
↓
Request: /admin/llm/activity-export/csv?user_only=1
↓
Backend filtra: user_id=auth()->id() (todos los logs del usuario)
↓
Download: llm-activity-user-2025-12-10-143022.csv
```

**Contenido:** Todos los logs del usuario actual (sin filtro de sesión)

### **Caso 3: Admin Page (sin cambios)**
```
User clicks "Export CSV" en /admin/llm/activity
↓
Request: /admin/llm/activity-export/csv?provider=openai&status=success
↓
Backend filtra: provider=openai AND status=success (sin user_only)
↓
Download: llm-activity-2025-12-10-143022.csv
```

**Contenido:** Todos los logs filtrados (admin puede ver todos)

---

## 🔍 Consideraciones Técnicas

### **1. Seguridad**

**Problema:** ¿Qué pasa si un usuario malicioso pasa `session_id` de otra sesión?

**Solución:**
```php
// LLMActivityController::export()
if ($request->filled('session_id')) {
    $session = LLMConversationSession::findOrFail($request->session_id);
    
    // Verificar que la sesión pertenece al usuario
    if ($session->user_id !== auth()->id()) {
        abort(403, 'Unauthorized: This session does not belong to you');
    }
    
    $query->where('session_id', $request->session_id);
}
```

**Validación adicional:**
```php
$validated = $request->validate([
    'session_id' => 'nullable|integer|exists:llm_manager_conversation_sessions,id',
    'user_only' => 'nullable|boolean',
    'provider' => 'nullable|string',
    'status' => 'nullable|in:success,error',
    'date_from' => 'nullable|date',
    'date_to' => 'nullable|date|after_or_equal:date_from',
]);
```

### **2. Filename Convención**

**Propuesta:**
```php
// Con sesión
$filename = "llm-activity-session-{$sessionId}-" . date('Y-m-d-His') . '.csv';

// Sin sesión (usuario)
$filename = "llm-activity-user-" . date('Y-m-d-His') . '.csv';

// Admin (sin contexto)
$filename = "llm-activity-" . date('Y-m-d-His') . '.csv';
```

### **3. Límite de Registros**

**Problema:** Exportar 10,000 logs puede ser lento

**Solución:**
```php
// Opcional: Limitar exportación a 1000 registros máximo
if ($query->count() > 1000) {
    return response()->json([
        'error' => 'Too many records to export. Please apply filters to reduce the dataset.'
    ], 400);
}
```

**O mejor:** Sin límite, pero con notificación
```php
// No limitar, pero agregar header con count
$logsCount = $query->count();
$headers['X-Total-Records'] = $logsCount;

// Frontend puede mostrar warning si > 1000
```

### **4. Formato CSV Mejorado**

**Campos actuales (admin):**
- ID, Date/Time, Provider, Model, User, Prompt (200 chars), Response (200 chars), Tokens, Cost, Duration, Status, Error

**Propuesta Monitor (más completo):**
```php
fputcsv($file, [
    'ID',
    'Session ID',           // NUEVO
    'Date/Time',
    'Provider',
    'Model',
    'User',
    'Prompt (Full)',        // Full text (no truncar)
    'Response (Full)',      // Full text (no truncar)
    'Prompt Tokens',
    'Completion Tokens',
    'Total Tokens',
    'Cost USD',
    'Duration (ms)',
    'Duration (s)',         // NUEVO (más legible)
    'Status',
    'Error Message',
]);
```

---

## 🎨 Diseño UI Propuesto

### **Opción A: Dropdown en Header (RECOMENDADO)**

```
┌─────────────────────────────────────────────────────────────┐
│ Activity History                                             │
│ [Refresh] [Load More] [Export ▼] [Fullscreen] [Close]      │
│                          ├─ CSV                              │
│                          └─ JSON                             │
├─────────────────────────────────────────────────────────────┤
│ # │ Time │ Provider │ Model │ Tokens │ Cost │ Duration │... │
│ 1 │ 10:30│ OpenAI   │ GPT-4 │ 150    │$0.003│ 2.5s     │... │
│ 2 │ 10:25│ Anthropic│Claude │ 200    │$0.005│ 3.2s     │... │
└─────────────────────────────────────────────────────────────┘
```

**Ventajas:**
- ✅ Integrado en header (consistente)
- ✅ Ahorra espacio
- ✅ Dropdown agrupa formatos

### **Opción B: Botones Separados sobre Tabla**

```
┌─────────────────────────────────────────────────────────────┐
│ Activity History                                             │
│ [Refresh] [Load More] [Fullscreen] [Close]                  │
├─────────────────────────────────────────────────────────────┤
│ [Export CSV] [Export JSON]                                   │
├─────────────────────────────────────────────────────────────┤
│ # │ Time │ Provider │ Model │ Tokens │ Cost │ Duration │... │
│ 1 │ 10:30│ OpenAI   │ GPT-4 │ 150    │$0.003│ 2.5s     │... │
└─────────────────────────────────────────────────────────────┘
```

**Ventajas:**
- ✅ Más visible
- ✅ Más espacio para tooltips

**Desventajas:**
- ❌ Ocupa más espacio vertical

---

## 📝 Checklist de Implementación

### **Backend (LLMActivityController)**
- [ ] Agregar filtro `session_id` a `export()` método
- [ ] Agregar filtro `user_only` a `export()` método
- [ ] Agregar validación de ownership de sesión
- [ ] Agregar filtro `session_id` a `exportJson()` método
- [ ] Agregar filtro `user_only` a `exportJson()` método
- [ ] Mejorar filename con contexto (session-XX vs user)
- [ ] Opcional: Agregar campos adicionales al CSV (session_id, full text)

### **Frontend (Blade Components)**
- [ ] Agregar prop `$showExport` a `monitor-header-buttons.blade.php`
- [ ] Agregar prop `$sessionId` a `monitor-header-buttons.blade.php`
- [ ] Implementar dropdown Export con CSS/JS (Bootstrap dropdown)
- [ ] Actualizar configuración en `split-horizontal-layout.blade.php`
- [ ] Agregar iconos `ki-file-down` (ya usados en admin)

### **Testing**
- [ ] Test: Export CSV con session_id (debe filtrar correctamente)
- [ ] Test: Export JSON con session_id
- [ ] Test: Export sin session_id (quick chat - todos los logs del user)
- [ ] Test: Seguridad - intentar acceder session_id de otro usuario (debe fallar 403)
- [ ] Test: Filename correcto (session-XX vs user)
- [ ] Test: Dropdown funciona en monitor
- [ ] Test: Botones visibles solo en tab Activity Logs

---

## ⏱️ Estimación de Tiempo

| Tarea | Tiempo |
|-------|--------|
| Backend: Modificar export() y exportJson() | 30 min |
| Frontend: Agregar dropdown a botones | 45 min |
| Testing: Validación completa | 30 min |
| **TOTAL** | **1h 45min** |

---

## 🚀 Recomendación Final

### ✅ **PROCEDER CON OPCIÓN 1**

**Razones:**
1. Reutiliza código probado del admin
2. Implementación rápida (~2h total)
3. Mantiene consistencia en toda la app
4. Fácil de mantener (un solo lugar para lógica export)
5. Dropdown UI es limpio y profesional

**Próximos pasos:**
1. Confirmar UI (Dropdown vs Botones separados)
2. Implementar backend (filtros session_id + user_only)
3. Implementar frontend (dropdown component)
4. Testing exhaustivo
5. Commit y documentación

**¿Aprobado para implementación?**
