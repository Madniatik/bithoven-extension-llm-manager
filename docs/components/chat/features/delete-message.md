# Delete Message Feature

**Versión:** v0.3.0  
**Estado:** ✅ Completado (10 dic 2025)  
**Approach:** Two-column delete (message_id + id fallback)

[Ver plans/completed/message-id-refactor/ para detalles completos]

## Overview
Borrar mensajes individuales desde UI con two-column DELETE approach.

## Two-Column Approach
```sql
DELETE FROM llm_messages 
WHERE (message_id = ? OR id = ?) 
AND user_id = ?
```

## Security
- Ownership verification (user_id check)
- Cascade delete en related tables
- Confirmation modal antes de delete

**Documentación Verificada:** plans/completed/message-id-refactor/DELETE-MESSAGE-REFACTOR-SUMMARY.md
