# DELETE MESSAGE ANALYSIS - Sistema de Logs y Relaciones

**Fecha:** 10 de diciembre de 2025  
**Objetivo:** Analizar impacto de borrar mensajes en el sistema de logs, métricas y estadísticas

---

## 📊 ARQUITECTURA ACTUAL

### Tablas Principales

#### 1. `llm_manager_conversation_sessions`
- **Primary Key:** `id`
- **Foreign Keys:** 
  - `user_id` → `users` (onDelete: cascade)
  - `llm_configuration_id` → `llm_manager_configurations` (onDelete: cascade)
- **Relaciones:**
  - `hasMany` messages
  - `hasMany` usageLogs
  - `hasMany` toolExecutions

#### 2. `llm_manager_conversation_messages`
- **Primary Key:** `id`
- **Foreign Keys:**
  - `session_id` → `llm_manager_conversation_sessions` (onDelete: **CASCADE**)
  - `user_id` → `users` (onDelete: set null)
  - `llm_configuration_id` → `llm_manager_configurations` (onDelete: set null)
- **Datos críticos:**
  - `content`, `metadata`, `raw_response`
  - `tokens`, `response_time`, `cost_usd`
  - Timestamps: `created_at`, `sent_at`, `started_at`, `completed_at`

#### 3. `llm_manager_usage_logs`
- **Primary Key:** `id`
- **Foreign Keys:**
  - `llm_configuration_id` → `llm_manager_configurations` (onDelete: **CASCADE**)
  - `user_id` → `users` (onDelete: set null)
  - `session_id` → **NO CONSTRAINT** (solo index, nullable)
  - `message_id` → **NO CONSTRAINT** (solo index, nullable)
- **Datos críticos:**
  - `prompt`, `response`, `parameters_used`
  - `prompt_tokens`, `completion_tokens`, `total_tokens`
  - `cost_usd`, `currency`, `cost_original`
  - `execution_time_ms`, `status`, `error_message`
- **Propósito:** Auditoría, análisis de costos, métricas de rendimiento

#### 4. `llm_manager_custom_metrics`
- **Foreign Key:** `usage_log_id` → `llm_manager_usage_logs` (onDelete: **CASCADE**)
- **Depende directamente de usage_logs**

#### 5. `llm_manager_tool_executions`
- **Foreign Key:** `usage_log_id` → `llm_manager_usage_logs` (onDelete: set null)
- **No se afecta si se borra usage_log**

---

## 🔍 CASOS DE USO ACTUALES

### Caso 1: Stream Test (`/admin/llm/stream/test`)
```php
// LLMStreamController::stream()
$usageLog = LLMUsageLog::create([
    'llm_configuration_id' => $config->id,
    'user_id' => auth()->id(),
    'session_id' => null,        // ❌ NO usa session
    'message_id' => null,        // ❌ NO usa message
    'extension_slug' => null,
    'prompt' => $validated['prompt'],
    'response' => $fullResponse,
    // ... metrics
]);
```
**Conclusión:** Los logs de Stream Test son **independientes** de conversaciones/mensajes.

### Caso 2: Quick Chat (`/admin/llm/quick-chat`)
```php
// LLMQuickChatController::streamReply()
$usageLog = $this->streamLogger->endSession($session, $fullResponse, $metrics);

// LLMStreamLogger::endSession()
return LLMUsageLog::create([
    'llm_configuration_id' => $session['llm_configuration_id'],
    'user_id' => $session['user_id'],
    'session_id' => $session['session_id'] ?? null,
    'message_id' => $session['message_id'] ?? null,  // ✅ Vinculado a message
    'extension_slug' => $session['extension_slug'] ?? 'llm-manager',
    'prompt' => $session['prompt'],
    'response' => $response,
    // ... metrics
]);
```
**Conclusión:** Los logs de Quick Chat **SÍ vinculan** con `session_id` y `message_id`.

### Caso 3: Borrar Conversación (`conversations.destroy`)
```php
// LLMConversationController::destroy()
$session = LLMConversationSession::findOrFail($id);
$session->delete();  // Trigger cascades
```

**Cascadas automáticas:**
1. ✅ **Messages CASCADE** → Se borran todos los mensajes (`session_id` FK con onDelete cascade)
2. ❌ **Usage Logs NO CASCADE** → Quedan huérfanos (sin `session_id` FK constraint)
3. ❌ **Custom Metrics CASCADE con logs** → Quedan huérfanos indirectamente

---

## ⚠️ PROBLEMAS IDENTIFICADOS

### Problema 1: Inconsistencia en Cascadas
- **Messages:** Se borran automáticamente al borrar session (CASCADE)
- **Usage Logs:** NO se borran automáticamente (sin FK constraint)
- **Resultado:** Logs huérfanos con `session_id` apuntando a sesiones inexistentes

### Problema 2: Pérdida de Datos Históricos
Si borramos logs al borrar mensajes:
- ❌ Se pierde histórico de costos
- ❌ Se pierden métricas de rendimiento
- ❌ Se pierde auditoría de uso de API
- ❌ Imposible calcular estadísticas mensuales/anuales

### Problema 3: Integridad Referencial Débil
- `usage_logs.session_id` y `message_id` son **nullable** sin FK constraint
- No hay garantía de que los IDs apunten a registros existentes
- Queries con JOINs pueden fallar silenciosamente

---

## 💡 OPCIONES DE SOLUCIÓN

### Opción A: SOFT DELETE en Messages (RECOMENDADO ✅)
```php
// En LLMConversationMessage.php
use Illuminate\Database\Eloquent\SoftDeletes;

class LLMConversationMessage extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}
```

**Ventajas:**
- ✅ Los mensajes "borrados" quedan ocultos en UI
- ✅ Los logs mantienen integridad referencial
- ✅ Se preserva histórico de costos y métricas
- ✅ Posibilidad de "restaurar" mensajes
- ✅ Auditoría completa (saber QUÉ se borró y CUÁNDO)

**Desventajas:**
- ⚠️ Base de datos crece indefinidamente
- ⚠️ Necesita tarea de limpieza periódica (ej: borrar después de 6 meses)

**Implementación:**
```php
// Migration
Schema::table('llm_manager_conversation_messages', function (Blueprint $table) {
    $table->softDeletes();
});

// Queries (automático con SoftDeletes)
$messages = Message::all();  // Excluye borrados
$allMessages = Message::withTrashed()->get();  // Incluye borrados
$onlyDeleted = Message::onlyTrashed()->get();  // Solo borrados
```

---

### Opción B: Marcar Logs como "orphan" (sin borrar)
```php
// En LLMConversationMessage::deleting event
protected static function boot()
{
    parent::boot();
    
    static::deleting(function ($message) {
        // Marcar logs como huérfanos en lugar de borrarlos
        LLMUsageLog::where('message_id', $message->id)
            ->update(['message_deleted_at' => now()]);
    });
}
```

**Ventajas:**
- ✅ Se preserva histórico completo
- ✅ Se sabe que el mensaje fue borrado
- ✅ Logs siguen siendo válidos para métricas

**Desventajas:**
- ⚠️ Necesita columna `message_deleted_at` en usage_logs
- ⚠️ Lógica más compleja en queries

---

### Opción C: Borrar Logs en Cascada (❌ NO RECOMENDADO)
```php
// Migration: Agregar FK constraint
Schema::table('llm_manager_usage_logs', function (Blueprint $table) {
    $table->foreign('message_id')
        ->references('id')
        ->on('llm_manager_conversation_messages')
        ->onDelete('cascade');
});
```

**Ventajas:**
- ✅ Limpieza automática
- ✅ No hay datos huérfanos

**Desventajas:**
- ❌ **PÉRDIDA PERMANENTE** de datos de costos
- ❌ **IMPOSIBLE** calcular estadísticas históricas
- ❌ **NO AUDITABLE** (no se sabe cuánto se gastó en mensajes borrados)
- ❌ Viola principio de **separación de concerns** (UI vs Analytics)

---

### Opción D: Borrar solo Message, mantener Logs (HÍBRIDO ⚖️)
```php
// No hacer nada especial, simplemente:
$message->delete();

// Logs quedan con message_id apuntando a mensaje borrado
// Queries JOIN deben usar LEFT JOIN para tolerancia
```

**Ventajas:**
- ✅ Simple, sin cambios en schema
- ✅ Se preserva histórico de costos
- ✅ Logs siguen siendo útiles para analytics

**Desventajas:**
- ⚠️ Integridad referencial débil
- ⚠️ Queries JOIN pueden devolver NULL en message.content
- ⚠️ Confusión en dashboards (logs sin mensaje asociado)

---

## 🎯 RECOMENDACIÓN FINAL

### **OPCIÓN A + OPCIÓN B (Combinado)**

1. **Implementar Soft Delete en Messages** (Opción A)
   - Mensajes "borrados" quedan ocultos pero recuperables
   - Logs mantienen integridad referencial completa
   
2. **Agregar `message_deleted_at` en usage_logs** (Opción B)
   - Redundancia para analytics (saber si el log corresponde a mensaje borrado)
   - Útil para dashboards: "Costos de mensajes activos vs borrados"

### Implementación Paso a Paso

#### 1. Migration: Soft Deletes en Messages
```php
// database/migrations/YYYY_MM_DD_add_soft_deletes_to_messages.php
public function up()
{
    Schema::table('llm_manager_conversation_messages', function (Blueprint $table) {
        $table->softDeletes();
    });
}
```

#### 2. Migration: Columna en Usage Logs
```php
// database/migrations/YYYY_MM_DD_add_message_deleted_at_to_usage_logs.php
public function up()
{
    Schema::table('llm_manager_usage_logs', function (Blueprint $table) {
        $table->timestamp('message_deleted_at')->nullable()->after('message_id');
        $table->index('message_deleted_at');
    });
}
```

#### 3. Modelo: Trait SoftDeletes
```php
// src/Models/LLMConversationMessage.php
use Illuminate\Database\Eloquent\SoftDeletes;

class LLMConversationMessage extends Model
{
    use SoftDeletes;
    
    protected static function boot()
    {
        parent::boot();
        
        // Al hacer soft delete, marcar en logs
        static::deleted(function ($message) {
            if ($message->trashed()) {
                LLMUsageLog::where('message_id', $message->id)
                    ->whereNull('message_deleted_at')
                    ->update(['message_deleted_at' => now()]);
            }
        });
        
        // Al restaurar, quitar marca
        static::restored(function ($message) {
            LLMUsageLog::where('message_id', $message->id)
                ->update(['message_deleted_at' => null]);
        });
    }
}
```

#### 4. Controller: Endpoint Delete
```php
// src/Http/Controllers/MessageController.php
public function destroy(int $id)
{
    $message = LLMConversationMessage::findOrFail($id);
    
    // Verificar permisos (usuario solo puede borrar sus mensajes)
    if ($message->user_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }
    
    // Soft delete
    $message->delete();
    
    return response()->json([
        'success' => true,
        'message' => 'Message deleted successfully',
    ]);
}
```

#### 5. Frontend: Actualizar UI
```javascript
// event-handlers.blade.php
if (target.classList.contains('delete-message-btn')) {
    e.preventDefault();
    const messageId = target.dataset.messageId;
    
    if (!messageId || messageId.startsWith('msg-')) {
        toastr.warning('Cannot delete unsaved messages');
        return;
    }
    
    Swal.fire({
        title: 'Delete Message?',
        text: 'This message will be removed from the conversation',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/llm/messages/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover bubble del DOM
                    const bubble = document.querySelector(`[data-message-id="${messageId}"]`);
                    bubble?.remove();
                    toastr.success('Message deleted');
                }
            });
        }
    });
}
```

---

## 📈 VENTAJAS DE ESTA SOLUCIÓN

1. **Integridad de Datos:**
   - ✅ Logs SIEMPRE tienen referencia válida a message (aunque esté soft-deleted)
   - ✅ Queries con `withTrashed()` funcionan perfectamente
   
2. **Analytics Completos:**
   - ✅ Dashboards pueden calcular costos totales (incluyendo mensajes borrados)
   - ✅ Métricas de "tasa de borrado" (cuántos usuarios borran mensajes)
   - ✅ Auditoría completa: "Usuario X gastó $Y, de los cuales $Z fue en mensajes borrados"

3. **Flexibilidad:**
   - ✅ Posibilidad de restaurar mensajes borrados accidentalmente
   - ✅ Tarea cron para purga definitiva después de N meses
   - ✅ Export de datos históricos sin perder información

4. **UX/UI:**
   - ✅ Mensajes borrados desaparecen instantáneamente del chat
   - ✅ No afecta rendimiento (índices en `deleted_at`)
   - ✅ Usuarios pueden "deshacer" borrado (opcional)

---

## 🧹 LIMPIEZA PERIÓDICA (Opcional)

```php
// app/Console/Commands/PurgeOldDeletedMessages.php
public function handle()
{
    $months = config('llm-manager.purge_deleted_after_months', 6);
    
    $deletedCount = LLMConversationMessage::onlyTrashed()
        ->where('deleted_at', '<', now()->subMonths($months))
        ->forceDelete();  // Borrado permanente
    
    $this->info("Purged {$deletedCount} old deleted messages");
}
```

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('llm:purge-deleted-messages')
        ->monthly()
        ->onlyInProduction();
}
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [ ] Migration: `add_soft_deletes_to_messages`
- [ ] Migration: `add_message_deleted_at_to_usage_logs`
- [ ] Modelo: Trait `SoftDeletes` en `LLMConversationMessage`
- [ ] Modelo: Event listeners `deleted()` y `restored()`
- [ ] Controller: `MessageController::destroy()`
- [ ] Routes: `DELETE /admin/llm/messages/{id}`
- [ ] Frontend: Event listener `delete-message-btn`
- [ ] Frontend: SweetAlert confirmación
- [ ] Frontend: Remover bubble del DOM
- [ ] Tests: Unit tests para soft delete
- [ ] Tests: Feature tests para endpoint destroy
- [ ] Docs: Actualizar README con política de borrado
- [ ] (Opcional) Command: `llm:purge-deleted-messages`
- [ ] (Opcional) Config: `purge_deleted_after_months`

---

**Fin del análisis**
