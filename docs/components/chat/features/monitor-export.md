# Monitor Export Feature

**Versión:** v1.0.7  
**Estado:** ✅ Completado (10 dic 2025)  
**Tiempo Implementación:** 3.5h  
**Commits:** f43aee6

---

## 📋 Overview

Sistema de exportación de Activity Logs del Monitor en múltiples formatos (CSV, JSON, SQL) con filtrado por sesión.

---

## ✨ Características

### Formatos de Exportación

1. **CSV (Comma-Separated Values)**
   - Full conversation text con metadata
   - Compatible con Excel, Google Sheets
   - Headers: Session ID, Message ID, Role, Model, Content, Tokens, Duration, Created At

2. **JSON (JavaScript Object Notation)**
   - Estructura completa de metadatos
   - Ideal para procesamiento programático
   - Formato: Array de objetos con todas las propiedades

3. **SQL (INSERT Statements)**
   - INSERT statements listos para ejecutar
   - Permite replicación exacta en otra base de datos
   - Incluye valores escapados correctamente

### Filtrado

- **Session Filtering:** Export solo sesión actual o todas las sesiones
- **Ownership Verification:** 403 Unauthorized si usuario intenta exportar datos de otro usuario
- **Dynamic Filenames:** `activity-log-{session_id}-{timestamp}.{format}` o `activity-log-all-{timestamp}.{format}`

---

## 🚀 Uso

### Backend (Controller)

```php
// Admin/MonitorController.php

public function exportActivityLog(Request $request)
{
    $format = $request->get('format', 'csv'); // csv, json, sql
    $sessionId = $request->get('session_id'); // opcional
    
    // Obtener logs (con ownership verification)
    $logs = $this->getActivityLogs($sessionId);
    
    // Generar export según formato
    return match($format) {
        'csv' => $this->exportCSV($logs, $sessionId),
        'json' => $this->exportJSON($logs, $sessionId),
        'sql' => $this->exportSQL($logs, $sessionId),
        default => response()->json(['error' => 'Invalid format'], 400)
    };
}

private function exportCSV($logs, $sessionId)
{
    $filename = $sessionId 
        ? "activity-log-{$sessionId}-" . now()->format('Ymd-His') . ".csv"
        : "activity-log-all-" . now()->format('Ymd-His') . ".csv";
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\""
    ];
    
    $callback = function() use ($logs) {
        $file = fopen('php://output', 'w');
        
        // Headers
        fputcsv($file, [
            'Session ID', 'Message ID', 'Role', 'Model', 
            'Content', 'Tokens', 'Duration (s)', 'Created At'
        ]);
        
        // Data
        foreach ($logs as $log) {
            fputcsv($file, [
                $log->session_id,
                $log->message_id,
                $log->role,
                $log->model_name,
                $log->content,
                $log->input_tokens + $log->output_tokens,
                $log->processing_time,
                $log->created_at
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}

private function exportJSON($logs, $sessionId)
{
    $filename = $sessionId 
        ? "activity-log-{$sessionId}-" . now()->format('Ymd-His') . ".json"
        : "activity-log-all-" . now()->format('Ymd-His') . ".json";
    
    $data = $logs->map(function($log) {
        return [
            'session_id' => $log->session_id,
            'message_id' => $log->message_id,
            'role' => $log->role,
            'model' => $log->model_name,
            'provider' => $log->provider_name,
            'content' => $log->content,
            'input_tokens' => $log->input_tokens,
            'output_tokens' => $log->output_tokens,
            'total_tokens' => $log->input_tokens + $log->output_tokens,
            'processing_time' => $log->processing_time,
            'created_at' => $log->created_at->toISOString(),
        ];
    });
    
    return response()->json($data)
        ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
}

private function exportSQL($logs, $sessionId)
{
    $filename = $sessionId 
        ? "activity-log-{$sessionId}-" . now()->format('Ymd-His') . ".sql"
        : "activity-log-all-" . now()->format('Ymd-His') . ".sql";
    
    $sql = "-- Activity Log Export\n";
    $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
    $sql .= "-- Session: " . ($sessionId ?: 'ALL') . "\n\n";
    
    foreach ($logs as $log) {
        $sql .= sprintf(
            "INSERT INTO llm_manager_usage_logs (session_id, message_id, role, model_name, provider_name, content, input_tokens, output_tokens, processing_time, created_at) VALUES (%s, %s, %s, %s, %s, %s, %d, %d, %.3f, %s);\n",
            $this->quote($log->session_id),
            $this->quote($log->message_id),
            $this->quote($log->role),
            $this->quote($log->model_name),
            $this->quote($log->provider_name),
            $this->quote($log->content),
            $log->input_tokens,
            $log->output_tokens,
            $log->processing_time,
            $this->quote($log->created_at)
        );
    }
    
    return response($sql, 200, [
        'Content-Type' => 'text/plain',
        'Content-Disposition' => "attachment; filename=\"{$filename}\""
    ]);
}

private function quote($value)
{
    return "'" . addslashes($value) . "'";
}
```

### Frontend (Blade)

```blade
{{-- Monitor panel con botones de export --}}
<div class="monitor-export-controls">
    <div class="btn-group">
        <button type="button" 
                class="btn btn-sm btn-light-primary export-activity-log"
                data-format="csv"
                data-session-id="{{ $sessionId }}">
            <i class="ki-outline ki-file-down fs-3"></i>
            Export CSV
        </button>
        
        <button type="button" 
                class="btn btn-sm btn-light-info export-activity-log"
                data-format="json"
                data-session-id="{{ $sessionId }}">
            <i class="ki-outline ki-code fs-3"></i>
            Export JSON
        </button>
        
        <button type="button" 
                class="btn btn-sm btn-light-success export-activity-log"
                data-format="sql"
                data-session-id="{{ $sessionId }}">
            <i class="ki-outline ki-data fs-3"></i>
            Export SQL
        </button>
    </div>
    
    <div class="form-check form-check-sm ms-3">
        <input class="form-check-input" 
               type="checkbox" 
               id="export-all-sessions" 
               value="1">
        <label class="form-check-label" for="export-all-sessions">
            Export todas las sesiones
        </label>
    </div>
</div>
```

### JavaScript

```javascript
// Event handler para export buttons
document.querySelectorAll('.export-activity-log').forEach(button => {
    button.addEventListener('click', async function() {
        const format = this.dataset.format;
        const sessionId = document.getElementById('export-all-sessions').checked 
            ? null 
            : this.dataset.sessionId;
        
        const url = new URL('/admin/llm/monitor/export', window.location.origin);
        url.searchParams.append('format', format);
        if (sessionId) {
            url.searchParams.append('session_id', sessionId);
        }
        
        try {
            // Fetch con download
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error('Export failed');
            }
            
            // Trigger download
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = response.headers.get('Content-Disposition')
                .split('filename=')[1]
                .replace(/"/g, '');
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(downloadUrl);
            
            toastr.success(`Activity log exported as ${format.toUpperCase()}`);
        } catch (error) {
            console.error('[Export] Failed:', error);
            toastr.error('Export failed. Please try again.');
        }
    });
});
```

---

## 🧪 Testing

### Testing Manual (7/7 scenarios ✅)

1. **CSV Export (Session)** ✅
   - Export CSV de sesión actual
   - Verificar headers correctos
   - Verificar datos completos (8 columnas)
   - Abrir en Excel → formato correcto

2. **CSV Export (All Sessions)** ✅
   - Marcar checkbox "Export todas las sesiones"
   - Verificar filename `activity-log-all-{timestamp}.csv`
   - Verificar datos de múltiples sesiones

3. **JSON Export (Session)** ✅
   - Export JSON de sesión actual
   - Verificar estructura de array de objetos
   - Verificar todas las propiedades (11 campos)

4. **JSON Export (All Sessions)** ✅
   - Export JSON de todas las sesiones
   - Verificar agrupación correcta

5. **SQL Export (Session)** ✅
   - Export SQL de sesión actual
   - Verificar INSERTstatements válidos
   - Ejecutar en DB → sin errores

6. **SQL Export (All Sessions)** ✅
   - Export SQL de todas las sesiones
   - Verificar escape correcto de comillas/caracteres especiales

7. **Ownership Verification** ✅
   - Intentar exportar logs de otro usuario
   - Verificar respuesta 403 Unauthorized

---

## 📊 Formato de Archivos

### CSV Example

```csv
Session ID,Message ID,Role,Model,Content,Tokens,Duration (s),Created At
sess_123,msg_456,user,gpt-4o-mini,"How do I export data?",125,0.000,2025-12-10 12:30:00
sess_123,msg_457,assistant,gpt-4o-mini,"You can export using the Monitor Export feature...",450,2.350,2025-12-10 12:30:03
```

### JSON Example

```json
[
  {
    "session_id": "sess_123",
    "message_id": "msg_456",
    "role": "user",
    "model": "gpt-4o-mini",
    "provider": "openai",
    "content": "How do I export data?",
    "input_tokens": 125,
    "output_tokens": 0,
    "total_tokens": 125,
    "processing_time": 0.000,
    "created_at": "2025-12-10T12:30:00.000000Z"
  },
  {
    "session_id": "sess_123",
    "message_id": "msg_457",
    "role": "assistant",
    "model": "gpt-4o-mini",
    "provider": "openai",
    "content": "You can export using the Monitor Export feature...",
    "input_tokens": 125,
    "output_tokens": 450,
    "total_tokens": 575,
    "processing_time": 2.350,
    "created_at": "2025-12-10T12:30:03.000000Z"
  }
]
```

### SQL Example

```sql
-- Activity Log Export
-- Generated: 2025-12-10 12:35:00
-- Session: sess_123

INSERT INTO llm_manager_usage_logs (session_id, message_id, role, model_name, provider_name, content, input_tokens, output_tokens, processing_time, created_at) VALUES ('sess_123', 'msg_456', 'user', 'gpt-4o-mini', 'openai', 'How do I export data?', 125, 0, 0.000, '2025-12-10 12:30:00');
INSERT INTO llm_manager_usage_logs (session_id, message_id, role, model_name, provider_name, content, input_tokens, output_tokens, processing_time, created_at) VALUES ('sess_123', 'msg_457', 'assistant', 'gpt-4o-mini', 'openai', 'You can export using the Monitor Export feature...', 125, 450, 2.350, '2025-12-10 12:30:03');
```

---

## 🔒 Security

### Ownership Verification

```php
private function getActivityLogs($sessionId = null)
{
    $query = LLMUsageLog::where('user_id', auth()->id());
    
    if ($sessionId) {
        $query->where('session_id', $sessionId);
    }
    
    return $query->orderBy('created_at', 'desc')->get();
}
```

### XSS Prevention

- Uso de `addslashes()` en SQL export
- JSON encode automático (sin raw output)
- CSV escaping automático via `fputcsv()`

---

## 📝 Commits

- **f43aee6** - feat: monitor export feature (CSV/JSON/SQL) with session filtering

**Files Modified:**
- `src/Http/Controllers/Admin/MonitorController.php` (3 métodos export + helper)
- `resources/views/admin/monitor/index.blade.php` (export controls UI)
- `resources/js/custom/monitor-export.js` (event handlers)

**Testing:** 7/7 scenarios passed ✅

---

**Documentación Verificada:** PLAN-v1.0.7-chat-ux.md (Monitor Export Feature)  
**Última Actualización:** 10 de diciembre de 2025
