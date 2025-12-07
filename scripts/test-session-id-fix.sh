#!/bin/bash
# Test script for Blocker #1: session_id/message_id fix
# Usage: ./scripts/test-session-id-fix.sh

echo "============================================"
echo "Testing Blocker #1: session_id/message_id Fix"
echo "============================================"
echo ""

DB_USER="root"
DB_PASS="M070k0!27"
DB_NAME="bithoven_laravel"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "📊 BEFORE Fix (expected: all NULL)"
echo "-----------------------------------"
mysql -u $DB_USER -p$DB_PASS -D $DB_NAME -e "
SELECT id, session_id, message_id, total_tokens, created_at 
FROM llm_manager_usage_logs 
ORDER BY id DESC LIMIT 5;
"

echo ""
echo "⚠️  Now test by creating a new Quick Chat message at:"
echo "    http://localhost:8000/admin/llm/quick-chat"
echo ""
echo "After sending a message, press Enter to check results..."
read

echo ""
echo "📊 AFTER Fix (expected: session_id and message_id NOT NULL for Quick Chat)"
echo "--------------------------------------------------------------------------"
mysql -u $DB_USER -p$DB_PASS -D $DB_NAME -e "
SELECT id, session_id, message_id, total_tokens, created_at 
FROM llm_manager_usage_logs 
ORDER BY id DESC LIMIT 5;
"

echo ""
echo "✅ SUCCESS CRITERIA:"
echo "   - Test Monitor (no session): session_id = NULL, message_id = NULL ✅"
echo "   - Quick Chat: session_id = [number], message_id = [number] ✅"
echo "   - Conversation Stream: session_id = [number], message_id = NULL (or number if message created before stream)"
echo ""
