# Changelog

All notable changes to the LLM Manager extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] - Work in Progress Towards v1.0.7

### 🔍 Request Inspector Tab (9 diciembre 2025, 02:20)

**Visual debugging tool for LLM requests implemented!** ✅

**Total:** 5 commits (20d41ac → 4329429), ~3 hours

**What Changed:**
- ✅ New Request Inspector tab in Monitor panel
- ✅ Hybrid population: Immediate form data + SSE update with context
- ✅ 6 collapsible sections: Metadata, Parameters, System Instructions, Context Messages, Current Prompt, Full JSON
- ✅ Spinners for SSE-pending data (Top P, Actual Context Size, Context Messages)
- ✅ Copy/Download buttons for prompt and JSON
- ✅ Timeline visualization for context messages with role badges and tokens

**Implementation Details:**

#### Phase 1 - Initial Population (commit 20d41ac)
- **Created** `monitor-request-inspector.blade.php` (240 líneas) - UI component
- **Created** `request-inspector.blade.php` (145 líneas) - JavaScript functions
- **Modified** `event-handlers.blade.php` - Build requestData from form, populate immediately
- **Modified** `select-models.blade.php` - Added `data-endpoint` attribute
- **Strategy:** Populate partial data BEFORE streaming starts (metadata, parameters, current_prompt)

#### Phase 2 - SSE Update + Context Fix (commit 130227f)
- **Fixed** Context limit bug: Was taking FIRST N messages → Now takes LAST N (most recent)
- **Fixed** Context includes current message → Now excludes it with `where('id', '!=', $userMessage->id)`
- **Modified** `LLMQuickChatController.php` - Use `slice(-$contextLimit)` for recent messages
- **Modified** `LLMQuickChatController.php` - Map `$contextMessages` directly (not `skip($idx)`)
- **Strategy:** Backend emits `request_data` SSE event with complete context_messages

#### Phase 3 - Visual Feedback (commit 60c45cc)
- **Added** Spinners in `monitor-request-inspector.blade.php` for SSE-pending fields
- **Spinners in:** Top P, Actual Context Size, Context Messages badge/list
- **Purpose:** Visual indicator of data loading from SSE event

#### Phase 4 - DOM Visibility Fix (commit 85e3abb)
- **Changed** `x-show` with `x-cloak` → `x-show` without `x-cloak`
- **Reason:** DOM must always exist (just hidden) for JavaScript to populate
- **Result:** Tab switching works correctly, data populates in background

#### Phase 5 - SSE Listener Fix (commit 4329429)
- **Added** `request_data` event listener in `event-handlers.blade.php`
- **Reason:** EventSource created in event-handlers, not using streaming-handler.js class
- **Result:** SSE event now received correctly, spinners replaced with real data

**Benefits:**
- ✅ Complete request visibility (what's being sent to model)
- ✅ Debug context issues (verify last N messages included)
- ✅ Verify parameters (temperature, max_tokens, context_limit)
- ✅ Instant feedback (~5ms partial, ~50ms complete)
- ✅ Copy/Download for testing/debugging

**Files Modified:**
- NEW: `resources/views/components/chat/shared/monitor-request-inspector.blade.php`
- NEW: `resources/views/components/chat/partials/scripts/request-inspector.blade.php`
- MODIFIED: `resources/views/components/chat/partials/scripts/event-handlers.blade.php`
- MODIFIED: `resources/views/components/chat/layouts/split-horizontal-layout.blade.php`
- MODIFIED: `resources/views/components/chat/partials/form-elements/select-models.blade.php`
- MODIFIED: `src/Http/Controllers/Admin/LLMQuickChatController.php`

**Testing:**
- ✅ Ollama: 6 context messages loaded correctly
- ✅ Context limit 20: Last 20 messages (not first 20)
- ✅ Context limit 0 (All): All messages without duplicating current
- ✅ Spinners appear/disappear in ~50ms
- ✅ Copy/Download buttons functional

---

### 🎉 Activity Log Migration Complete (7 diciembre 2025, 21:45)

**Database-driven Activity History implemented successfully!** ✅

**Total:** 9 commits (230ba0a → b8ef595), 6 hours, 3 bugs fixed

**What Changed:**
- ✅ Test Monitor now loads Activity History from database (replaces localStorage)
- ✅ `session_id` and `message_id` now properly saved in `llm_manager_usage_logs`
- ✅ Auto-refresh after stream completion
- ✅ Cross-device persistence, unlimited history
- ✅ Server-side filtering by session_id (optional)

**Implementation Details:**

#### Blocker #1 - session_id/message_id NULL Fix (commit 230ba0a)
- **Modified** `LLMStreamLogger@startSession()` - Added optional `$sessionId`, `$messageId` params
- **Modified** `LLMStreamLogger@endSession()` - Save real DB IDs to usage_logs
- **Modified** `LLMStreamLogger@logError()` - Save real DB IDs on errors
- **Updated** `LLMQuickChatController@stream()` - Pass `$session->id`, `$userMessage->id`
- **Updated** `LLMStreamController@conversationStream()` - Pass `$session->id`
- **Preserved** `LLMStreamController@stream()` - Keeps NULL (Test Monitor, no session)

#### Blocker #2 - Endpoint Architecture Decision
- **Decision:** Keep 3 separate endpoints (Opción A)
- **Reason:** Quick Chat has unique complex features (TTFT, error handling, metadata events)
- **Result:** No duplicación crítica, código DRY dentro de cada endpoint

#### Blocker #3 + Phases 1-3 - Database Migration (commits d3a9108, 3dd6bf4)
- **Added** `LLMStreamController@getActivityHistory()` endpoint
- **Added** Route `GET /admin/llm/stream/activity-history`
- **Created** `activity-table.blade.php` partial with ActivityHistory JavaScript API
- **Integrated** Database-driven AJAX loading in test.blade.php
- **Deprecated** localStorage functions (addToActivityHistory, renderActivityTable)
- **Fixed** Model import and relation name (`configuration` not `llmConfiguration`)

**Benefits:**
- ✅ Cross-device persistence
- ✅ Unlimited history (DB storage)
- ✅ Server-side filtering by session_id
- ✅ No localStorage limitations (5MB cap, browser-specific)
- ✅ Clean, maintainable code

#### Phase 4 - Quick Chat Integration (commits 1458cce, d81afea, 28087be, e2d963a)
- **Replaced** Hardcoded Activity table in split-horizontal-layout.blade.php with @include partial
- **Added** sessionId filtering for Quick Chat (shows only current conversation logs)
- **Fixed** sessionId filter not working - pass sessionId to ActivityHistory.load()
- **Fixed** Auto-refresh not working - add llm-streaming-completed event listener
- **Fixed** Event listener not capturing - change document to window.addEventListener
- **Verified** Test Monitor shows all logs (no sessionId filter)
- **Verified** Quick Chat shows only session logs (sessionId filter working)

**Testing:** Manual testing 100% successful (5/5 criteria passed)

**Related:** `plans/completed/ACTIVITY-LOG-MIGRATION-PLAN.md`

**Commits:**
- `17c2c82` - Punto de restauración
- `230ba0a` - Blocker #1 fix
- `d3a9108` - Blocker #3 + Phases 1-3
- `3dd6bf4` - Hotfix 500 error
- `716a3ea` - Test Monitor integration (localStorage deprecated)
- `1458cce` - Quick Chat integration (replace hardcoded table)
- `d81afea` - Fix sessionId filter
- `28087be` - Fix auto-refresh
- `e2d963a` - Fix event listener (window vs document)

---

### ⚠️ CRITICAL UPDATE (6 diciembre 2025) - DB Persistence Revert

**7 commits revertidos** (cc94a7d-f8fb81c) por implementación incorrecta de DB persistence para Activity Logs.

**Root Cause:** Uso de tabla incorrecta (`llm_manager_conversation_logs` en lugar de `llm_manager_usage_logs`)

**Lesson Learned (#16):** SIEMPRE analizar arquitectura existente completamente antes de implementar features similares. Referencia correcta: `/admin/llm/stream/test` usa `llm_manager_usage_logs`.

**Current State (commit 1bd668e):**
- ✅ Activity Logs tab funcional con localStorage (dual-button system)
- ⏳ DB persistence pendiente (requiere análisis de /stream/test endpoints)
- ✅ Documentation updated (HANDOFF, PROJECT-STATUS, session achievements)

**Progreso General:** **75%** (no 95% - ajustado por revert)

---

### Activity Logs Tab System (commit f24d957, docs update 1bd668e)

**NEW FEATURE:** Monitor now supports dual-tab system (Console + Activity Logs)

#### Features Implemented
- **Added** Dual-button tab system in monitor:
  - Console tab (existing functionality)
  - Activity Logs tab (NEW - localStorage-based)
- **Added** Alpine.js tab switching (`activeTab` state, `x-show` directives)
- **Added** `openMonitorTab(tab)` method for programmatic tab control
- **Added** Activity Logs localStorage persistence:
  - Max 10 logs, auto-cleanup oldest
  - Stores: timestamp, event, details, sessionId, messageId
  - Survives page refresh
- **Simplified** Modal monitor (Console only, no Activity Logs tab)
- **Enhanced** Split-horizontal layout with tab UI in monitor section

#### Files Modified
- `resources/views/components/chat/layouts/split-horizontal-layout.blade.php` - Tab buttons + activeTab state
- `resources/views/components/chat/partials/modals/modal-monitor.blade.php` - Simplified (Console only)
- `public/js/monitor/ui/render.js` - renderActivityTable() for split view
- `public/js/monitor/core/MonitorInstance.js` - localStorage-based init/complete methods

#### Documentation
- `plans/PLAN-v1.0.7-HANDOFF-TO-NEXT-COPILOT.md` - Lesson 16 added, revert documented
- `PROJECT-STATUS.md` - Progress updated to 75%, 7 commits listed

---

### Quick Chat Feature Enhancements (90% Complete)

**30+ commits implementados** trabajando hacia v1.0.7:

#### Enhanced Data Capture (commits 721e271, 0cd80d4)
- **Added** `model` field to messages table - Captures actual model used (not just config)
- **Added** `raw_response` field (JSON) - Complete provider response for analysis
- **Enhanced** Raw Data Modal with Tabs UI:
  - "Formatted JSON" tab with syntax highlighting
  - "Raw Text" tab for debugging
  - Copy buttons for each tab
  - Modal now shows complete provider metadata

#### Thinking Tokens Display (commit 0cd80d4)
- **Improved** Token display from start of streaming (not just completion)
- **Added** `input_tokens` display from metadata event (before first chunk)
- **Enhanced** Progress bar shows real-time token accumulation
- **Removed** "Streaming complete" toast (less disruptive UX)

#### Stop Stream Feature (commits multiple)
- **Added** Intelligent stream cancellation with cleanup:
  - DELETE orphaned user messages if stopped before first chunk
  - Restore prompt to input for retry
  - Preserve conversation context if stopped during streaming
- **Added** Global scope variables for cross-function access:
  - `userMessageId`, `savedUserPrompt`, `chunkCount`
- **Fixed** Bubble removal with reliable Array-based selector
- **Fixed** Variable scope issues preventing Stop functionality

#### OpenRouter Provider Integration (commits 8a00921, afe895e, a95c2ec)
- **Added** Complete OpenRouter provider implementation with HTTP direct
- **Added** Usage metadata extraction from final SSE chunk
- **Added** `cost_usd` column to messages table
- **Added** Support for model variations (slash vs colon format)
- **Fixed** Token extraction from OpenRouter response format
- **Documented** Provider response format comparison guide

#### Token Breakdown & Real-time Metrics (commits c5fa989, 4b4d214, f547809)
- **Added** Persistent footer with token breakdown:
  - Prompt tokens (↑ sent)
  - Completion tokens (↓ received)
  - Total tokens
- **Added** Real-time updates during streaming:
  - Response time (live calculation)
  - TTFT (Time to First Token)
  - Cost in USD (calculated from usage)
- **Fixed** Number formatting in token display
- **Fixed** Duplicate footer update code causing JS errors

#### Session Management (commits 5f6fbd7, c08d78e)
- **Added** Access to specific quick-chat sessions by ID: `/admin/llm/quick-chat/{id}`
- **Added** Custom title modal for new conversations
- **Added** Settings persistence via localStorage:
  - Model selection
  - Temperature
  - Max tokens
  - Context limit
- **Fixed** Select2 visual refresh from localStorage (jQuery .on() compatibility)

#### UI Polish & Bug Fixes
- **Improved** Bubble title format (simplified provider/model display)
- **Fixed** $0.00 cost display (show zero instead of empty)
- **Fixed** Response time display in old messages with fallback
- **Fixed** Clear Chat button restoration (clearBtn error)
- **Fixed** Partial response visibility when stopping stream
- **Removed** Duplicate New Chat header toolbar
- **Removed** Colors from footer metrics in static bubbles (cleaner look)

#### Code Quality & Production Readiness (commit 907494c)
- **Removed** 25+ debugging console.log statements:
  - `settings-manager.blade.php` - 18 debug logs
  - `message-renderer.blade.php` - 1 debug log
  - `chat-workspace.blade.php` - 3 debug logs
  - `split-resizer.blade.php` - 2 debug logs
  - `event-handlers.blade.php` - 1 debug log
- **Kept** Essential error logging only:
  - Markdown parsing errors
  - Prism highlighting errors
  - EventSource connection errors
  - DELETE request errors
  - Initialization confirmation
- **Result** Clean console for production use

#### Documentation Updates (commit 523f663)
- **Updated** `plans/PLAN-v1.0.7.md` with actual progress:
  - Quick Chat Feature: 95% complete (FASE 1-4,6-7 done)
  - UI/UX Optimizations: 80% complete
  - 30+ commits documented
  - Metrics and timelines updated
  - Roadmap for remaining work defined

### Migration Notes

**Database Changes:**
```sql
-- New columns added to llm_messages table
ALTER TABLE llm_messages ADD COLUMN model VARCHAR(255) NULL;
ALTER TABLE llm_messages ADD COLUMN raw_response JSON NULL;
ALTER TABLE llm_messages ADD COLUMN cost_usd DECIMAL(10,6) NULL;
```

**⚠️ Reverted Changes (NOT in current codebase):**
- `message_id` column in `llm_manager_conversation_logs` - REVERTED (wrong table)
- Activity Logs DB persistence endpoints - REVERTED (incorrect implementation)
- **Correct approach:** Use `llm_manager_usage_logs` table (see /admin/llm/stream/test)

**Breaking Changes:** None - All changes are backward compatible

**Upgrade Path:** Run migrations to add new columns. Existing messages will have NULL values for new fields.

**Revert Details:** 7 commits (cc94a7d through f8fb81c) were removed via `git reset --hard f24d957` on 6 Dec 2025. System reverted to clean Activity Logs localStorage implementation.

---

## [1.0.6] - 2025-12-03

### Added - Multi-Instance Support for ChatWorkspace Component

#### Legacy Code Cleanup (commit 00349e9)

**Removed 17 unused legacy files** from `resources/views/admin/quick-chat/partials/`

**Deleted Files:**
- `partials/buttons/` (2 files: action-buttons, chat-settings)
- `partials/scripts/` (4 files: clipboard-utils, event-handlers, message-renderer, settings-manager)
- `partials/styles/` (4 files: buttons, dependencies, markdown, responsive)
- `partials/modals/` (1 file: modal-raw-message)
- `partials/drafts/` (1 file: chat-users)
- `partials/*.blade.php` (5 files: chat-messages, input-form, messages-container, scripts, styles)

**Reason for Removal:**
- System migrated to component architecture (`<x-llm-manager-chat-workspace>`)
- Quick Chat now uses `components/chat/` structure exclusively
- No external references found (grep search verified)
- `index.blade.php` uses component, NOT legacy partials
- `modal-raw-message` exists in new location: `components/chat/partials/modals/`

**Total cleanup:** 1,213 lines removed

**Verification:**
- ✅ Grep search: No external references to `admin.quick-chat.partials`
- ✅ index.blade.php: Uses `<x-llm-manager-chat-workspace>` component
- ✅ Controllers: Only render index.blade.php (no partials references)
- ✅ New system: All partials in `components/chat/partials/`

---

### Added - Multi-Instance Support for ChatWorkspace Component

**MAJOR FEATURE:** ChatWorkspace now supports múltiples instancias simultáneas en la misma página.

#### Multi-Instance Architecture

**Alpine.js Scopes Únicos:**
- `chatWorkspace_{{sessionId}}` - Scope único por sesión
- `splitResizer_{{sessionId}}` - Resizer independiente por sesión
- Factory pattern con auto-registro de componentes

**DOM IDs Dinámicos:**
- `messages-container-{{sessionId}}`
- `monitor-console-{{sessionId}}`
- `quick-chat-form-{{sessionId}}`
- `quick-chat-message-input-{{sessionId}}`
- `send-btn-{{sessionId}}`, `clear-btn-{{sessionId}}`
- `monitor-token-count-{{sessionId}}`, etc.

**Monitor Factory Pattern:**
- `window.LLMMonitorFactory.create(sessionId)` - Crear instancia
- `window.LLMMonitorFactory.get(sessionId)` - Obtener instancia
- `window.LLMMonitorFactory.getOrCreate(sessionId)` - Convenience method
- Cada monitor con su propio state, métricas e historial

**LocalStorage Isolation:**
- `llm_chat_monitor_open_{{sessionId}}`
- `llm_split_chat_flex_{{sessionId}}`
- `llm_split_monitor_flex_{{sessionId}}`
- `llm_chat_monitor_history_{{sessionId}}`

**Custom Events Enhanced:**
- Todos los eventos incluyen `sessionId` en `event.detail`
- Permite discriminar eventos de diferentes instancias
- Compatible con analytics y plugins multi-sesión

#### Files Modified (9)

**Components:**
- `resources/views/components/chat/chat-workspace.blade.php`
- `resources/views/components/chat/layouts/split-horizontal-layout.blade.php`

**Partials:**
- `resources/views/components/chat/partials/messages-container.blade.php`
- `resources/views/components/chat/partials/input-form.blade.php`

**Scripts:**
- `resources/views/components/chat/partials/scripts/chat-workspace.blade.php`
- `resources/views/components/chat/partials/scripts/split-resizer.blade.php`
- `resources/views/components/chat/partials/scripts/monitor-api.blade.php` (Factory pattern)

**Shared:**
- `resources/views/components/chat/shared/monitor.blade.php`
- `resources/views/components/chat/shared/monitor-console.blade.php`

#### Use Cases

**Dual-Chat Comparison:**
```blade
<div class="row">
    <div class="col-md-6">
        <x-llm-manager-chat-workspace :session="$session1" ... />
    </div>
    <div class="col-md-6">
        <x-llm-manager-chat-workspace :session="$session2" ... />
    </div>
</div>
```

**Model A/B Testing:**
- Comparar GPT-4 vs Claude 3 lado a lado
- Métricas independientes (tokens, cost, duration)
- Historial separado por sesión

**Multi-User Dashboard:**
- Monitoreo de múltiples usuarios simultáneos
- Dashboard administrativo
- Testing workflows en paralelo

#### Backward Compatibility

✅ **100% Compatible:**
- `window.LLMMonitor` apunta a instancia 'default'
- Código existente sin `sessionId` sigue funcionando
- Props del componente sin cambios breaking
- API methods mantienen misma signature

#### Documentation

- `docs/components/CHAT-WORKSPACE.md` - Updated to v1.0.6
- New section: "Multi-Instance Support" (500+ lines)
- Multi-instance API examples
- Use cases y best practices
- Testing examples

#### Migration Notes

**No database changes required.**

**Auto-migration:**
- Componentes existentes funcionan sin cambios
- SessionId se genera automáticamente (default si no hay sesión)
- localStorage antiguo se mantiene (instancia 'default')

**Recommended changes for new code:**
```javascript
// OLD (still works)
window.LLMMonitor.start();

// NEW (multi-instance aware)
const monitor = window.LLMMonitorFactory.get(sessionId);
monitor.start();
```

---

## [1.0.5] - 2025-12-03

### Changed - ChatWorkspace Component Optimizations (63% code reduction)

**REFACTOR:** Partitioned component code for 63% total reduction (740 → 270 lines)

#### Phase 1: Split-Horizontal Layout

**Optimized split-horizontal-layout.blade.php:**
- Reduced from 450 to 150 lines (66% reduction)
- Extracted 100 lines CSS to `partials/styles/split-horizontal.blade.php`
- Extracted 100 lines Alpine.js to `partials/scripts/split-resizer.blade.php`
- Extracted 50 lines Alpine.js to `partials/scripts/chat-workspace.blade.php`

**Benefits:**
- ✅ Separation of concerns (HTML/CSS/JS)
- ✅ Reusable Alpine components (chatWorkspace, splitResizer)
- ✅ Conditional loading (only when monitor-layout="split-horizontal")
- ✅ localStorage persistence for sizes
- ✅ Drag constraints (20%-80%)

#### Phase 2: Monitor Components

**Optimized monitor.blade.php:**
- Reduced from 230 to 100 lines (56% reduction)
- Extracted 230 lines JS to `partials/scripts/monitor-api.blade.php`
- Global `window.LLMMonitor` API now reusable

**Optimized monitor-console.blade.php:**
- Reduced from 60 to 20 lines (66% reduction)
- Extracted 50 lines CSS to `partials/styles/monitor-console.blade.php`
- Unified dark theme styling

**Benefits:**
- ✅ Single API for both monitor.blade.php and monitor-console.blade.php
- ✅ Null-safe DOM checks (no errors if elements missing)
- ✅ CSS reusable across full monitor and console-only views
- ✅ Maintainability - changes in one place

#### Files Created (Reusable Partials)

**Scripts (7 files):**
- `partials/scripts/chat-workspace.blade.php` (50 lines - Alpine chatWorkspace)
- `partials/scripts/split-resizer.blade.php` (100 lines - Alpine splitResizer)
- `partials/scripts/monitor-api.blade.php` (230 lines - window.LLMMonitor)
- `partials/scripts/clipboard-utils.blade.php` (utilities)
- `partials/scripts/event-handlers.blade.php` (event listeners)
- `partials/scripts/message-renderer.blade.php` (markdown rendering)
- `partials/scripts/settings-manager.blade.php` (config management)

**Styles (5 files):**
- `partials/styles/split-horizontal.blade.php` (100 lines - flexbox split)
- `partials/styles/monitor-console.blade.php` (50 lines - dark theme)
- `partials/styles/dependencies.blade.php` (external libs)
- `partials/styles/markdown.blade.php` (content styling)
- `partials/styles/buttons.blade.php` (action buttons)
- `partials/styles/responsive.blade.php` (media queries)

**Total:** 10 reusable partials + component refactors

#### Optimization Summary

| Component | Before | After | Reduction |
|-----------|--------|-------|-----------|
| split-horizontal-layout | 450 | 150 | **66%** ⬇️ |
| monitor.blade.php | 230 | 100 | **56%** ⬇️ |
| monitor-console.blade.php | 60 | 20 | **66%** ⬇️ |
| **TOTAL** | **740** | **270** | **63%** ⬇️ |

#### Additional Fixes

**Fixed sidebar monitor collapse (v2.0.1):**
- Changed from `x-show` to `:class` binding with `d-none`
- Monitor column now completely removed from grid when closed
- Chat expands to 100% width properly

**Consolidated monitor toggle button (v2.0.2):**
- Removed duplicates from chat-card.blade.php header
- Removed duplicates from split-horizontal-layout.blade.php header
- Single button in action-buttons.blade.php footer
- toggleMonitor() function works globally (Alpine component)

**Corrected include paths (v2.0.3):**
- Fixed 3 incorrect references to `admin.quick-chat.partials`
- Updated to `components.chat.partials` namespace
- Files: action-buttons, messages-container, input-form

#### Documentation

**Created comprehensive documentation:**
- `docs/components/CHAT-WORKSPACE.md` (1300+ lines)
  - Installation and props reference
  - Layout selection guide (sidebar vs split-horizontal)
  - Complete JavaScript API documentation
  - **Custom Events API section** (500+ lines)
    * Message events, Streaming events, Monitor events, Session events
    * 3 complete integration examples (Analytics, Auto-save, Dashboard)
    * Event structure, detail payload, and best practices
    * Enables external integrations, plugins, analytics without code coupling
  - Customization examples
  - Troubleshooting section
  - Performance benchmarks

- `docs/README.md` - Updated index
  - Component overview section
  - Architecture diagram
  - Optimization metrics
  - Quick start guide

- `resources/views/components/chat/README.md` - Technical docs
  - Updated to v2.1
  - Added Phase 2 metrics
  - Listed all partials
  - Documented all fixes

#### Performance Impact

**Bundle Size:**
- Before: ~750 lines mixed code (HTML + CSS + JS)
- After: ~400 lines partitioned + conditionally loaded
- Improvement: 46% smaller initial load

**Load Optimization:**
- Split CSS/JS only loads when `monitor-layout="split-horizontal"`
- Monitor API loads globally (needed by both layouts)
- Console CSS loads on-demand

**Maintainability:**
- Single source of truth for each concern
- Reusable across both layouts
- Testable components (isolated Alpine.js)
- Clean separation HTML/CSS/JS

#### Migration Notes

**No breaking changes** - Fully backward compatible:
- Existing `<x-llm-manager-chat-workspace>` calls work unchanged
- Props unchanged (session, configurations, show-monitor, etc.)
- API unchanged (window.LLMMonitor methods identical)
- Layouts unchanged (sidebar and split-horizontal)

**Cache clearing recommended after update:**
```bash
php artisan view:clear
php artisan optimize:clear
```

---

## [1.0.3] - 2025-11-27

### Removed - Code Sanitation

**REFACTOR:** Eliminated 190 lines of dead code from ServiceProvider

#### Background
- Code audit revealed ServiceProvider contained hook-based permission management that never executed
- ExtensionManager doesn't implement static hook registration (`registerInstallHook()`, `registerUninstallHook()` don't exist)
- LLMPermissionsSeeder was already the functional single source of truth (verified in production)
- Hooks were silently failing in try/catch blocks without visibility

#### Changes Made

**Removed from LLMServiceProvider.php (~190 lines):**
- ❌ `registerExtensionHooks()` method (110 lines)
- ❌ `installPermissions()` method (55 lines) - Never executed, incomplete (only assigned to super-admin vs seeder's 4 roles)
- ❌ `uninstallPermissions()` method (25 lines) - Never executed

**Created:**
- ✅ `LLMUninstallSeeder.php` - Proper permission cleanup during uninstallation
  - Deletes role_has_permissions entries
  - Deletes permissions matching `extensions:llm-manager:%`
  - Clears permission cache
  - Provides detailed console output

**Updated extension.json:**
```json
"seeders": {
  "core": [...],
  "demo": [...],
  "uninstall": ["LLMUninstallSeeder"]  // NEW
}
```

**Updated CPANEL Core (app/Services/Extensions/):**
- `ExtensionSeederManager`: Added `runUninstall()` method
- `ExtensionUninstaller`: Now executes uninstall seeders before migration rollback

#### Impact
- ✅ Cleaner codebase - removed confusing dead code
- ✅ Permissions now properly cleaned up on uninstall (was missing before)
- ✅ Consistent seeder-based approach for install/uninstall
- ✅ No breaking changes - installation flow unchanged
- ✅ LLMPermissionsSeeder remains the authoritative source for permission creation

#### Verification
```bash
# Before uninstall
SELECT COUNT(*) FROM permissions WHERE name LIKE 'extensions:llm-manager:%';
# Result: 12

# After uninstall (with remove_data=true)
SELECT COUNT(*) FROM permissions WHERE name LIKE 'extensions:llm-manager:%';
# Result: 0 ✅
```

#### Technical Details
- **Installation flow:** ExtensionSeederManager::runBase() → LLMPermissionsSeeder → 4 roles assigned (12, 12, 5, 8 perms)
- **Uninstallation flow:** ExtensionSeederManager::runUninstall() → LLMUninstallSeeder → Cleanup complete
- **No hooks involved:** System uses seeders exclusively, not ServiceProvider callbacks

---

## [1.0.1] - 2025-11-26

### Fixed - Seeder Architecture

**CRITICAL FIX:** Core data now populates correctly during installation

#### Problem
- Prompt Templates and Knowledge Base tables were empty after fresh installation
- Core data only appeared after manually clicking "Load Demo Data"
- Root cause: Essential application data was incorrectly placed in demo seeders

#### Solution
- **Created `LLMPromptTemplatesSeeder`** (core seeder)
  - 5 essential templates: Code Review, Text Summarization, Documentation Generator, Bug Analysis, Translation
  - All templates marked as global (`is_global=true`) for system-wide availability
  - Categories: analysis, summarization, generation, translation
- **Created `LLMKnowledgeBaseSeeder`** (core seeder)
  - 2 essential KB documents: Quick Start Guide, Provider Configuration Guide
  - Comprehensive markdown documentation for users
  - Ready for optional indexing via RAG system
- **Refactored `LLMDemoSeeder`** (demo seeder only)
  - Removed prompt templates (now in core)
  - Removed knowledge base documents (now in core)
  - Retained only true demo data: parameter overrides, workflows, conversations, usage stats
- **Updated `extension.json`**
  - Added `LLMPromptTemplatesSeeder` to core seeders array
  - Added `LLMKnowledgeBaseSeeder` to core seeders array
  - Total core seeders: 6 (was 4)

#### Impact
- ✅ Fresh installations now have 5 prompt templates immediately available
- ✅ Fresh installations now have 2 KB documents for user reference
- ✅ "Load Demo Data" correctly only adds conversations and usage statistics
- ✅ No breaking changes - purely additive

#### Technical Details
```json
"seeders": {
  "core": [
    "LLMPermissionsSeeder",
    "LLMConfigurationSeeder",
    "LLMPromptTemplatesSeeder",  // NEW
    "LLMKnowledgeBaseSeeder",    // NEW
    "LLMToolDefinitionsSeeder",
    "LLMMCPConnectorsSeeder"
  ],
  "demo": [
    "LLMDemoSeeder"  // Refactored
  ]
}
```

**Commit:** `9eb0d18` - feat: separate core data from demo seeders

---

## [1.0.4] - 2025-11-28

### Added - Real-Time Streaming & Permissions v2.0

**Release Status:** ✅ PRODUCTION READY

#### Streaming Infrastructure (100% Complete)

**SSE Controller** (`src/Http/Controllers/Admin/LLMStreamController.php`)
- `test()` - Interactive streaming test page with real-time UI
- `stream()` - Simple streaming endpoint with request validation
- `conversationStream()` - Streaming with full session history context
- Response headers optimized: `text/event-stream`, `no-cache`, `X-Accel-Buffering: no`
- Real-time token counting and cost tracking
- Event types: `chunk`, `done`, `error` for robust client handling

**Provider Streaming Implementations**

_OllamaProvider_ (Complete NDJSON Streaming)
- Real streaming with `fopen()` + `fgets()` (not buffered HTTP)
- Line-by-line JSON parsing for NDJSON format
- Context conversion to Ollama's formatted prompt style
- Chunk extraction from `response` field
- Support for models with `thinking` field (e.g., qwen3)
- Completion detection via `done` flag
- Parameters: `temperature`, `num_predict`, `top_p`

_OpenAIProvider_ (SDK Streaming)
- Message array construction from conversation context
- Uses OpenAI SDK `createStreamed()` method
- Delta content extraction from streamed chunks
- Multi-turn conversation support with full history
- Streaming with tool calls (function calling)

_Other Providers_ (Stubs Ready)
- AnthropicProvider: Stub implemented, ready for Anthropic streaming API
- OpenRouterProvider: Stub implemented, ready for OpenRouter streaming
- CustomProvider: Stub implemented, configurable for any SSE endpoint

**Frontend Streaming UI** (`resources/views/admin/stream/test.blade.php`)
- EventSource JavaScript client for SSE connections
- Real-time statistics panel: tokens, chunks, duration, cost
- Interactive controls:
  - Configuration selector (filters to streaming-capable providers only)
  - Temperature slider (0.0 - 2.0, step 0.1)
  - Max tokens input (1 - 4000)
  - Prompt textarea with placeholder
- Live response area with cursor animation
- Auto-scroll disabled (user can navigate page during streaming)
- SweetAlert2 notifications for connection status
- "Clear Response" and "Start Streaming" buttons

#### Usage Metrics Logging (Phase 1 Complete)

**Breaking Change:** `LLMProviderInterface::stream()` signature updated
```php
// OLD (v1.0.0)
public function stream(string $prompt, array $context, array $parameters, callable $callback): void

// NEW (v1.1.0)
public function stream(string $prompt, array $context, array $parameters, callable $callback): array

// Returns:
[
    'usage' => [
        'prompt_tokens' => int,
        'completion_tokens' => int,
        'total_tokens' => int,
    ],
    'model' => string,
    'finish_reason' => string|null, // 'stop', 'length', 'tool_calls', etc.
]
```

**LLMStreamLogger Service** (`src/Services/LLMStreamLogger.php`)
- `startSession(LLMConfiguration $config, string $prompt, array $params): array`
  - Creates session with UUID, start timestamp, configuration snapshot
- `endSession(array $session, array $metrics): void`
  - Calculates execution_time_ms, calls calculateCost(), saves to database
- `calculateCost(string $provider, string $model, array $usage): float`
  - Reads pricing from `config/llm-manager.php`, calculates per 1M tokens
- `logError(array $session, string $error): void`
  - Logs failed streaming attempts with status='error'

**Real Token Capture**
- OllamaProvider: Extracts `prompt_eval_count`, `eval_count` from NDJSON `done` chunk
- OpenAIProvider: Extracts `usage->promptTokens`, `completionTokens`, `totalTokens` from SDK response
- OpenRouterProvider: Same as OpenAI (SDK compatible)
- Database: 57+ real usage logs with accurate token counts and costs

**Pricing Configuration** (`config/llm-manager.php` lines 368-407)
```php
'pricing' => [
    'openai' => [
        'gpt-4o' => ['prompt' => 2.50, 'completion' => 10.00],
        'gpt-4o-mini' => ['prompt' => 0.15, 'completion' => 0.60],
    ],
    'anthropic' => [
        'claude-3-5-sonnet-20241022' => ['prompt' => 3.00, 'completion' => 15.00],
        'claude-3-opus-20240229' => ['prompt' => 15.00, 'completion' => 75.00],
    ],
    'openrouter' => [
        'gpt-5.1' => ['prompt' => 5.00, 'completion' => 15.00],
    ],
    'ollama' => [
        '*' => ['prompt' => 0.00, 'completion' => 0.00], // Local models are free
    ],
]
```

#### Permissions Protocol v2.0 Migration

**LLMPermissions Data Class** (`src/Data/Permissions/LLMPermissions.php`)
```php
class LLMPermissions {
    public static function getAll(): array {
        return [
            // Configurations (4)
            ['name' => 'view-llm-configs', 'alias' => 'Ver configuraciones LLM', ...],
            ['name' => 'create-llm-configs', 'alias' => 'Crear configuraciones LLM', ...],
            ['name' => 'edit-llm-configs', 'alias' => 'Editar configuraciones LLM', ...],
            ['name' => 'delete-llm-configs', 'alias' => 'Eliminar configuraciones LLM', ...],
            
            // Providers & Stats (2)
            ['name' => 'manage-llm-providers', 'alias' => 'Gestionar proveedores LLM', ...],
            ['name' => 'view-llm-stats', 'alias' => 'Ver estadísticas LLM', ...],
            
            // Testing (1)
            ['name' => 'test-llm-configs', 'alias' => 'Probar configuraciones LLM', ...],
            
            // Advanced (5)
            ['name' => 'manage-llm-encryption-keys', ...],
            ['name' => 'view-llm-conversations', ...],
            ['name' => 'manage-llm-knowledge-base', ...],
            ['name' => 'manage-llm-workflows', ...],
            ['name' => 'manage-llm-tools', ...],
        ];
    }
}
```

**Auto-Detection Integration**
- Extension Installer detects `LLMPermissions` class automatically
- Namespace convention: `Bithoven\{ExtensionName}\Data\Permissions`
- Backward compatibility: Falls back to `getPermissions()` method if class not found
- Composer PSR-4 autoload configured for `Data\Permissions` namespace

**ServiceProvider Cleanup**
- Removed `getPermissions()` method (now uses data class)
- Permissions registration via `registerPermissions()` method
- Cleaner separation of concerns

#### UI/UX Improvements

**Streaming Test Page Enhancements**
- Scroll fix (commit a775101):
  - Moved `max-height: 500px` from `card-body` to `card` (correct container)
  - Response area now scrolls correctly without growing indefinitely
- Auto-scroll removal (commit a775101):
  - Removed disruptive `responseDiv.scrollIntoView()` behavior
  - Users can navigate page freely during streaming
- Monitor improvements (commit 8f1debb):
  - Changed monitor colors: `bg-dark` → `bg-light-dark`, `text-light` → `text-gray-800`
  - Better readability and contrast
  - Monitor logs persistence (only clears initial "Monitor ready" message)

**Real-Time Activity Monitor** (commit 3403bdb)
- Refactored from static "Test Connection" to live "Streaming Activity" monitor
- Auto-activates during streaming
- Logs with timestamps:
  - Request details (configuration, prompt length, parameters)
  - SSE connection establishment
  - Chunk reception (with content preview)
  - Token counts and costs
  - Final metrics on completion
- Status badges: Inactive → Active → Completed/Stopped/Error
- Color-coded log levels
- Auto-scroll to bottom of console

**Stats Bar Expansion** (commit 054fb8c)
- Expanded from 3 to 6 columns:
  - Tokens (count)
  - Chunks (count)
  - Duration (seconds)
  - Cost (USD with $ symbol)
  - Log ID (database reference)
  - View Log (link to detailed log page)
- `stopStreaming(resetMetrics)` parameter added for controlled cleanup

**Activity History Table** (commit 054fb8c)
- Stores last 10 streaming sessions in localStorage
- Click to expand/collapse rows
- Columns: Date, Configuration, Prompt (truncated), Tokens, Cost, Duration, Status
- Functions: `addToActivityHistory()`, `renderActivityTable()`
- View Log button opens `/admin/llm/stats?log_id=X`

#### Routes & Configuration

**New Routes** (`routes/web.php`)
- `GET /admin/llm/stream/test` - Streaming test page (name: `admin.llm.stream.test`)
- `GET /admin/llm/stream/stream` - SSE simple endpoint (name: `admin.llm.stream.stream`)
- `GET /admin/llm/stream/conversation` - SSE with history (name: `admin.llm.stream.conversation`)
- `GET /admin/llm/activity` - Activity logs list (name: `admin.llm.activity.index`)
- `GET /admin/llm/activity/{id}` - Activity log details (name: `admin.llm.activity.show`)
- `GET /admin/llm/activity-export/csv` - CSV export (name: `admin.llm.activity.export.csv`)
- `GET /admin/llm/activity-export/json` - JSON export (name: `admin.llm.activity.export.json`)

**Breadcrumbs** (`routes/breadcrumbs.php`)
- `admin.llm.stream.test` - "Streaming Test" (parent: `admin.llm.dashboard`)
- `admin.llm.activity.index` - "Activity Logs" (parent: `admin.llm.dashboard`)
- `admin.llm.activity.show` - "Log Details" (parent: `admin.llm.activity.index`)

**CSRF Exceptions** (CPANEL `app/Http/Middleware/VerifyCsrfToken.php`)
- Added `admin/llm/stream/*` to `$except` array
- Allows EventSource connections without CSRF token

**Database Seeders**
- Updated configurations:
  - ID 1: Ollama Qwen 3 (qwen3:4b, endpoint: `http://localhost:11434`)
  - ID 2: Ollama DeepSeek Coder (deepseek-coder:6.7b, endpoint: `http://localhost:11434`)
- Fixed endpoint duplication (base URL only, provider appends path)

#### New Controllers & Services

**Created:**
- `src/Http/Controllers/Admin/LLMActivityController.php` - Activity logs management
- `src/Services/LLMStreamLogger.php` - Metrics logging service

**Modified:**
- `src/Http/Controllers/Admin/LLMStreamController.php` - Logger integration
- `src/Contracts/LLMProviderInterface.php` - BREAKING: stream() returns array
- All provider classes - Updated stream() implementations

### Changed

**Breaking Changes:**
1. `LLMProviderInterface::stream()` signature changed (returns array with metrics)
2. All providers must implement updated interface (stubs provided for unsupported streaming)
3. `ServiceProvider::getPermissions()` removed (use data class)

**Non-Breaking Changes:**
- Ollama endpoint configuration simplified (no duplicate `/api/generate`)
- Monitor UI color scheme updated
- Stats bar layout expanded

### Fixed

**Critical Fixes:**
- Permissions 403 error (migrated to Protocol v2.0)
- Response card scroll container (correct element targeted)
- Disruptive auto-scroll behavior (removed)
- Monitor color readability (improved contrast)

**Minor Fixes:**
- Validation table name corrected (`llm_manager_configurations`)
- Ollama endpoint duplication resolved
- CSRF verification excluded for streaming routes
- OpenRouter/OpenAI SDK cosmetic error for missing `predictedTokens` (commit 9d6da1a)

### Documentation

**Updated:**
- `CHANGELOG.md` - Complete v1.1.0 entry (this file)
- `PROJECT-STATUS.md` - Consolidated project state
- `ROADMAP.md` - Product roadmap with v1.2.0-v2.0.0 plan
- `LLM-MANAGER-PENDING-WORK.md` - Marked as obsolete, redirects to new docs

**Pending Updates:**
- `USAGE-GUIDE.md` - Add streaming section
- `API-REFERENCE.md` - Document streaming API
- `EXAMPLES.md` - Add streaming code examples

### Database

**No Migration Changes**
- Uses existing `llm_manager_usage_logs` table
- New columns will be added in v1.2.0 (`provider`, `model`)

### Testing

**Manual Testing:** 100%
- Ollama streaming tested with qwen3:4b and deepseek-coder:6.7b
- OpenAI streaming tested with gpt-4o-mini
- UI tested: scroll, auto-scroll, monitor, stats, activity table
- Permissions tested: all 12 LLM permissions working

**Automated Testing:** 0% (pending v1.2.0)
- PHPUnit test suite planned for v1.2.0
- Target: 80%+ code coverage

### Notes

**Requirements:**
- Active Ollama instance on `localhost:11434` (for Ollama provider)
- Browser with EventSource API support (all modern browsers)
- Permissions v2.0 protocol in CPANEL core

**Known Limitations:**
- Streaming disabled for Anthropic, OpenRouter, Custom providers (stubs ready)
- Browser cache issue with CSS changes (requires hard refresh)
- Activity table limited to 10 items in localStorage

**Migration from v1.0.0:**
- No database changes required
- Composer update recommended (`composer update bithoven/llm-manager`)
- Clear caches: `php artisan optimize:clear`
- Clear permissions cache: `php artisan permission:cache-reset`
- Custom providers using old `stream()` signature must update

**Next Steps (v1.2.0):**
- Statistics Dashboard with provider/model breakdown
- PHPUnit test suite with 80%+ coverage
- Streaming integration in Conversations UI
- Documentation updates for streaming API

---

## [Unreleased] - v1.1.0

### Added - Real-Time Streaming Support

#### Streaming Infrastructure
- **SSE (Server-Sent Events) Controller**
  - `LLMStreamController` with 3 endpoints:
    - `test()` - Interactive test page for streaming
    - `stream()` - Simple streaming endpoint with validation
    - `conversationStream()` - Streaming with session history context
  - Response headers: `text/event-stream`, `no-cache`, `X-Accel-Buffering: no`
  - Real-time token counting and statistics tracking
  - Event types: `chunk`, `done`, `error`

- **Provider Streaming Implementation**
  - `LLMProviderInterface::stream()` method (BREAKING CHANGE)
    - Signature: `stream(string $prompt, array $context, array $parameters, callable $callback): void`
    - Context format: `[{role: 'user|assistant', content: 'text'}]`
    - Feature detection: `supports(string $feature): bool`
  
  - `OllamaProvider` full NDJSON streaming
    - Line-by-line JSON parsing with `fgets()`
    - Context conversion to formatted prompt
    - Chunk extraction from `response` field
    - Completion detection via `done` flag
    - Parameters: `temperature`, `num_predict`, `top_p`
  
  - `OpenAIProvider` enhanced streaming
    - Message array construction from context
    - Uses SDK `createStreamed()` method
    - Delta content extraction
    - Multi-turn conversation support

#### Frontend Components
- **Interactive Test UI** (`resources/views/admin/llm/stream/test.blade.php`)
  - EventSource JavaScript client
  - Configuration selector (streaming-capable providers only)
  - Real-time statistics panel (tokens, chunks, duration)
  - Parameter controls (temperature: 0-2, max_tokens: 1-4000)
  - Auto-scroll and animated cursor
  - SweetAlert2 notifications for status
  - Clear Response and Start Streaming buttons

#### Routes & Configuration
- Routes registered in `routes/web.php`:
  - `GET /admin/llm/stream/test` - Streaming test page
  - `GET /admin/llm/stream/stream` - SSE endpoint
  - `GET /admin/llm/stream/conversation` - SSE with history
- Breadcrumbs configured for navigation
- CSRF exceptions added for SSE endpoints (`admin/llm/stream/*`)

#### Database Updates
- Updated seeders with streaming-ready configurations:
  - Ollama Qwen 3 (qwen3:4b) - ID 1
  - Ollama DeepSeek Coder (deepseek-coder:6.7b) - ID 2
  - Base endpoint: `http://localhost:11434` (provider appends `/api/generate`)

### Changed
- **BREAKING:** `LLMProviderInterface` now requires `stream()` method
- Provider implementations updated to support `$context` parameter
- Ollama endpoint configuration simplified (no duplicate `/api/generate`)

### Fixed
- Validation table name corrected (`llm_manager_configurations`)
- Ollama endpoint duplication issue resolved
- CSRF verification properly excluded for streaming routes

### In Progress
- Integration with Conversations UI (streaming toggle, stop button)
- Testing suite for streaming functionality
- Documentation for streaming API

### Notes
- Requires active Ollama instance on `localhost:11434`
- Browser must support EventSource API (all modern browsers)
- Streaming disabled for Anthropic, OpenRouter, Custom providers (stubs implemented)

---

## [1.0.0] - 2025-11-18

### Added - Initial Release v3.0

#### Core LLM Management
- Multi-provider support (Ollama, OpenAI, Anthropic, Custom)
- Per-extension LLM configurations
- Budget tracking and usage logs
- Provider cache for models auto-discovery
- Automatic fallback between configurations
- LLMManager service with provider abstraction
- Admin UI for configuration management

#### Advanced Features
- **Custom Metrics System**
  - Extensions can create custom metrics (numerical + JSON data)
  - API for recording, querying, and aggregating metrics
  - Dashboard visualization
  - Relationship with entities (bug, ticket, task)

- **Prompt Templates System**
  - Reusable templates with variable replacement (`{{variable}}`)
  - Database storage with versioning
  - Default parameters per template
  - Validation of required variables
  - CRUD API and admin interface

- **Parameter Override System**
  - Runtime override of model parameters (temperature, max_tokens, etc.)
  - Intelligent merge with configuration defaults
  - Per-provider parameter validation
  - Automatic fallback

#### Orchestration Platform
- **Conversation System**
  - Persistent sessions with context management
  - Complete message history
  - Audit logs for debugging
  - Session lifecycle management

- **RAG System (Retrieval-Augmented Generation)**
  - Document chunking with intelligent splitting
  - Vector embeddings (OpenAI API or local)
  - Semantic search over documentation
  - Automatic context injection
  - Artisan commands for indexing and embeddings

- **Multi-Agent Workflows**
  - Workflow definition with state machine
  - Multi-step orchestration
  - Conditional branching
  - Visual workflow builder
  - Agent coordination

#### Hybrid Tools System
- **Function Calling Support**
  - Native OpenAI tools API integration
  - Native Anthropic tools API integration
  - Gemini function calling support
  - Single API call execution (faster)

- **MCP Bundled Servers (4 servers)**
  - `filesystem` - File operations (create, read, list, delete)
  - `database` - Query execution, migrations, seeders
  - `laravel` - Artisan commands, routes, config access
  - `code-generation` - Generate controllers, models, migrations

- **MCP External Support**
  - GitHub API integration (community)
  - Context7 integration (community)
  - Custom user MCP servers
  - Visual management UI

- **Auto-Selection Intelligence**
  - Automatic provider capability detection
  - Function Calling prioritization (faster)
  - MCP fallback when native not available
  - Zero-config for end users

- **Security Layer**
  - Path whitelisting for file operations
  - Extension validation
  - File size limits
  - Command sanitization
  - Execution tracking

#### Database Schema
- 13 tables with `llm_*` prefix:
  - `llm_configurations` - Provider configurations
  - `llm_usage_logs` - Usage tracking
  - `llm_provider_cache` - Models cache
  - `llm_extension_metrics` - Custom metrics
  - `llm_prompt_templates` - Prompt templates
  - `llm_conversation_sessions` - Sessions
  - `llm_conversation_messages` - Messages
  - `llm_conversation_logs` - Audit logs
  - `llm_document_knowledge_base` - RAG documents
  - `llm_mcp_connectors` - MCP registry
  - `llm_agent_workflows` - Workflows
  - `llm_tool_definitions` - Tools registry
  - `llm_tool_executions` - Execution tracking

#### API & Integration
- Public API for extensions (Facade + REST)
- Blade components for UI integration
- Event system (RequestStarted, RequestCompleted, etc.)
- Middleware for validation
- Request validation classes

#### Admin UI
- Configuration management (CRUD)
- Conversations viewer with chat interface
- Knowledge Base management (RAG)
- Workflow builder (visual)
- MCP Servers management
- Statistics and cost reports
- Metrics dashboard

#### Artisan Commands
- `llm-manager:mcp:start` - Start MCP servers
- `llm-manager:mcp:list` - List servers
- `llm-manager:mcp:add` - Add external server
- `llm-manager:index-documents` - Index documents for RAG
- `llm-manager:generate-embeddings` - Generate embeddings
- `llm-manager:test-connection` - Test configuration

#### Documentation
- Complete installation guide
- Configuration reference
- API documentation
- Integration guide for developers
- Conversations guide
- RAG setup guide
- Workflows guide
- Tools development guide
- MCP servers guide

#### Testing
- Unit tests for core services
- Feature tests for API endpoints
- Integration tests with test extension
- Tests for all v3.0 features

### Requirements
- PHP ^8.2
- Laravel ^11.0
- Node.js ^18.0 (for MCP servers)
- Python ^3.9 (for database MCP)

### Migration Notes
- Creates 13 tables with `llm_*` prefix
- Requires permissions setup (13 permissions)
- MCP servers auto-install via post-install script
- Compatible with Fix Extension (IDs 1-N reserved)

### Breaking Changes
- None (initial release)

---

## Future Roadmap

### v3.1.0 (Planned)
- Real-time streaming responses
- WebSocket support for chat
- Advanced workflow templates
- More bundled MCP servers
- Plugin system for custom providers

### v3.2.0 (Planned)
- Multi-model ensemble support
- A/B testing for prompts
- Advanced cost optimization
- Extended analytics

### v4.0.0 (Future)
- Full agent autonomy
- Self-improving workflows
- Advanced RAG strategies
- Distributed agent network

---

## Support

- **Documentation:** `vendor/bithoven/llm-manager/docs/`
- **Issues:** https://github.com/bithoven/llm-manager/issues
- **Discord:** https://discord.gg/bithoven

---

[unreleased]: https://github.com/bithoven/llm-manager/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/bithoven/llm-manager/releases/tag/v1.0.0
