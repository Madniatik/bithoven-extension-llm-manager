{{--
    CHAT SETTINGS FORM - Reutilizable
    
    Formulario de configuración del chat workspace.
    Este componente es reutilizable en:
    - Tab "Chat Settings" (split-horizontal-layout)
    - Sidebar layout (futuro)
    - Modal settings (futuro)
    
    Props:
    - $sessionId (int|null) - Session ID
    - $config (array) - Current configuration (futuro, por ahora null)
--}}

@php
    $sessionId = $sessionId ?? 'default';
@endphp

<div class="card h-100" x-data="{ 
    expandedSections: {
        monitor: true,
        ui: false,
        performance: false,
        advanced: false
    }
}">
    <div class="card-header">
        <h3 class="card-title">
            {!! getIcon('ki-setting-2', 'fs-2x me-2', '', 'i') !!}
            Chat Configuration
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary" disabled>
                {!! getIcon('ki-check', 'fs-2 me-1', '', 'i') !!}
                Save Settings
            </button>
            <button type="button" class="btn btn-sm btn-light ms-2" disabled>
                Reset to Defaults
            </button>
        </div>
    </div>

    <div class="card-body overflow-auto" style="max-height: calc(100vh - 350px);">
        {{-- Nota: Formulario no funcional aún --}}
        <div class="alert alert-info d-flex align-items-center mb-5">
            {!! getIcon('ki-information-5', 'fs-2tx me-3', '', 'i') !!}
            <div class="d-flex flex-column">
                <h5 class="mb-1">UI Preview Mode</h5>
                <span>This settings panel is currently in preview mode. Functionality will be implemented in Phase 2.</span>
            </div>
        </div>

        {{-- SECTION: Monitor Settings --}}
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between cursor-pointer py-2"
                 @click="expandedSections.monitor = !expandedSections.monitor">
                <h4 class="mb-0">
                    {!! getIcon('ki-satellite', 'fs-2 me-2', '', 'i') !!}
                    Monitor Settings
                </h4>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="!expandedSections.monitor">
                    {!! getIcon('ki-down', 'fs-2', '', 'i') !!}
                </span>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="expandedSections.monitor">
                    {!! getIcon('ki-up', 'fs-2', '', 'i') !!}
                </span>
            </div>
            <div class="separator separator-dashed my-3"></div>

            <div x-show="expandedSections.monitor" class="ps-5">
                {{-- Enable Monitor --}}
                <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="monitor_enabled" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="monitor_enabled">
                        Enable Monitor Panel
                    </label>
                    <div class="text-muted fs-7 mt-1">
                        Show/hide the monitor panel with real-time metrics and logs.
                    </div>
                </div>

                {{-- Default Monitor State --}}
                <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="monitor_default_open" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="monitor_default_open">
                        Open by Default
                    </label>
                    <div class="text-muted fs-7 mt-1">
                        Monitor panel will be open when chat loads.
                    </div>
                </div>

                {{-- Monitor Tabs --}}
                <div class="mt-5">
                    <h5 class="mb-3">Monitor Tabs</h5>
                    
                    {{-- Console Tab --}}
                    <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                        <input class="form-check-input" type="checkbox" id="tab_console" checked disabled>
                        <label class="form-check-label fw-semibold text-gray-700" for="tab_console">
                            Console Tab
                        </label>
                        <div class="text-muted fs-7 mt-1">
                            Real-time streaming logs and debug information.
                        </div>
                    </div>

                    {{-- Request Inspector Tab --}}
                    <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                        <input class="form-check-input" type="checkbox" id="tab_request_inspector" checked disabled>
                        <label class="form-check-label fw-semibold text-gray-700" for="tab_request_inspector">
                            Request Inspector Tab
                        </label>
                        <div class="text-muted fs-7 mt-1">
                            Inspect LLM request payloads (metadata, context, parameters).
                        </div>
                    </div>

                    {{-- Activity Log Tab --}}
                    <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                        <input class="form-check-input" type="checkbox" id="tab_activity_log" checked disabled>
                        <label class="form-check-label fw-semibold text-gray-700" for="tab_activity_log">
                            Activity Log Tab
                        </label>
                        <div class="text-muted fs-7 mt-1">
                            Historical activity logs with filtering options.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: UI Preferences --}}
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between cursor-pointer py-2"
                 @click="expandedSections.ui = !expandedSections.ui">
                <h4 class="mb-0">
                    {!! getIcon('ki-element-11', 'fs-2 me-2', '', 'i') !!}
                    UI Preferences
                </h4>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="!expandedSections.ui">
                    {!! getIcon('ki-down', 'fs-2', '', 'i') !!}
                </span>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="expandedSections.ui">
                    {!! getIcon('ki-up', 'fs-2', '', 'i') !!}
                </span>
            </div>
            <div class="separator separator-dashed my-3"></div>

            <div x-show="expandedSections.ui" class="ps-5">
                {{-- Monitor Layout --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-gray-700">Monitor Layout</label>
                    <select class="form-select form-select-solid" disabled>
                        <option value="split-horizontal" selected>Split Horizontal (Chat 70% + Monitor 30%)</option>
                        <option value="sidebar">Sidebar (Chat 60% + Monitor 40%)</option>
                        <option value="drawer">Drawer (Overlay)</option>
                    </select>
                    <div class="text-muted fs-7 mt-1">
                        Choose how the monitor panel is displayed.
                    </div>
                </div>

                {{-- Chat Layout --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-gray-700">Chat Layout</label>
                    <select class="form-select form-select-solid" disabled>
                        <option value="bubble" selected>Bubble Style (WhatsApp-like)</option>
                        <option value="drawer">Drawer Style</option>
                        <option value="compact">Compact Style</option>
                    </select>
                    <div class="text-muted fs-7 mt-1">
                        Message display style.
                    </div>
                </div>

                {{-- Toolbar Buttons --}}
                <div class="mt-5">
                    <h5 class="mb-3">Toolbar Buttons</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                                <input class="form-check-input" type="checkbox" id="btn_new_chat" checked disabled>
                                <label class="form-check-label fw-semibold text-gray-700" for="btn_new_chat">
                                    New Chat Button
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                                <input class="form-check-input" type="checkbox" id="btn_clear" checked disabled>
                                <label class="form-check-label fw-semibold text-gray-700" for="btn_clear">
                                    Clear Chat Button
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                                <input class="form-check-input" type="checkbox" id="btn_download" checked disabled>
                                <label class="form-check-label fw-semibold text-gray-700" for="btn_download">
                                    Download History Button
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                                <input class="form-check-input" type="checkbox" id="btn_monitor_toggle" checked disabled>
                                <label class="form-check-label fw-semibold text-gray-700" for="btn_monitor_toggle">
                                    Monitor Toggle Button
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: Performance --}}
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between cursor-pointer py-2"
                 @click="expandedSections.performance = !expandedSections.performance">
                <h4 class="mb-0">
                    {!! getIcon('ki-rocket', 'fs-2 me-2', '', 'i') !!}
                    Performance
                </h4>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="!expandedSections.performance">
                    {!! getIcon('ki-down', 'fs-2', '', 'i') !!}
                </span>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="expandedSections.performance">
                    {!! getIcon('ki-up', 'fs-2', '', 'i') !!}
                </span>
            </div>
            <div class="separator separator-dashed my-3"></div>

            <div x-show="expandedSections.performance" class="ps-5">
                <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="lazy_load_tabs" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="lazy_load_tabs">
                        Lazy Load Tabs
                    </label>
                    <div class="text-muted fs-7 mt-1">
                        Load tab content only when activated (reduces initial load time).
                    </div>
                </div>

                <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="cache_preferences" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="cache_preferences">
                        Cache Preferences
                    </label>
                    <div class="text-muted fs-7 mt-1">
                        Save settings to localStorage for faster loading.
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: Advanced --}}
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between cursor-pointer py-2"
                 @click="expandedSections.advanced = !expandedSections.advanced">
                <h4 class="mb-0">
                    {!! getIcon('ki-code', 'fs-2 me-2', '', 'i') !!}
                    Advanced
                </h4>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="!expandedSections.advanced">
                    {!! getIcon('ki-down', 'fs-2', '', 'i') !!}
                </span>
                <span class="svg-icon svg-icon-muted svg-icon-2hx" 
                      x-show="expandedSections.advanced">
                    {!! getIcon('ki-up', 'fs-2', '', 'i') !!}
                </span>
            </div>
            <div class="separator separator-dashed my-3"></div>

            <div x-show="expandedSections.advanced" class="ps-5">
                <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="debug_mode" disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="debug_mode">
                        Debug Mode
                    </label>
                    <div class="text-muted fs-7 mt-1">
                        Show detailed console logs for troubleshooting.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-gray-700">Custom CSS Class</label>
                    <input type="text" class="form-control form-control-solid" placeholder="e.g., custom-chat-theme" disabled>
                    <div class="text-muted fs-7 mt-1">
                        Add custom CSS class to the chat container.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted fs-7">
                {!! getIcon('ki-information-5', 'fs-2 me-1', '', 'i') !!}
                Settings will persist to localStorage when functionality is enabled.
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" disabled>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" disabled>
                    {!! getIcon('ki-check', 'fs-2 me-1', '', 'i') !!}
                    Save & Apply
                </button>
            </div>
        </div>
    </div>
</div>
