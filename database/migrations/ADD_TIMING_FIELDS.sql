-- ================================================================
-- SCRIPT SQL: Agregar campos de timing a conversation_messages
-- Fecha: 2025-11-29
-- Propósito: Actualizar tabla existente sin crear nueva migración
-- ================================================================

USE bithoven_laravel;

-- Agregar campos de timing
ALTER TABLE llm_manager_conversation_messages
ADD COLUMN sent_at TIMESTAMP NULL AFTER created_at,
ADD COLUMN started_at TIMESTAMP NULL AFTER sent_at,
ADD COLUMN completed_at TIMESTAMP NULL AFTER started_at;

-- Agregar índices para mejorar queries
ALTER TABLE llm_manager_conversation_messages
ADD INDEX llm_cm_started_idx (started_at),
ADD INDEX llm_cm_completed_idx (completed_at);

-- Verificar estructura actualizada
DESCRIBE llm_manager_conversation_messages;

-- ================================================================
-- OPCIONAL: Poblar datos existentes con valores estimados
-- ================================================================

-- Para mensajes de usuario: sent_at = created_at
UPDATE llm_manager_conversation_messages
SET sent_at = created_at
WHERE role = 'user';

-- Para mensajes de assistant: estimar tiempos basados en tokens
-- MySQL usa MICROSECOND (1 millisecond = 1000 microseconds)
UPDATE llm_manager_conversation_messages
SET 
    started_at = DATE_ADD(created_at, INTERVAL (FLOOR(RAND() * 500) + 100) * 1000 MICROSECOND),
    completed_at = DATE_ADD(
        DATE_ADD(created_at, INTERVAL (FLOOR(RAND() * 500) + 100) * 1000 MICROSECOND),
        INTERVAL (FLOOR(tokens / 100) + FLOOR(RAND() * 2000) + 500) * 1000 MICROSECOND
    )
WHERE role = 'assistant';

-- Verificar datos actualizados
SELECT 
    id, 
    role, 
    tokens,
    created_at,
    sent_at,
    started_at,
    completed_at,
    TIMESTAMPDIFF(MICROSECOND, started_at, completed_at) / 1000000 as response_time_seconds
FROM llm_manager_conversation_messages
LIMIT 10;

-- ================================================================
-- FIN DEL SCRIPT
-- ================================================================
