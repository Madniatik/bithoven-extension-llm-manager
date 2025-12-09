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
    // Manejar sessionId de forma segura (puede venir de variable, sesión, o ser default)
    $sessionId = $sessionId ?? ($session->id ?? 'default');
@endphp

<div x-data="{ monitorEnabled: true }">
    {{-- SECTION: Monitor Settings --}}
    <div class="mb-8">
        <h3 class="mb-5">
            {!! getIcon('ki-satellite', 'fs-2 me-2', '', 'i') !!}
            Monitor Settings
        </h3>
        
        {{-- Enable Monitor --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="monitor_enabled" x-model="monitorEnabled" checked disabled>
            <label class="form-check-label fw-semibold text-gray-700" for="monitor_enabled">
                Enable Monitor Panel
            </label>
            <div class="text-muted fs-7 mt-1">
                Show/hide the monitor panel with real-time metrics and logs.
            </div>
        </div>

        {{-- Monitor Tabs --}}
        <h5 class="mt-6 mb-4">Monitor Tabs</h5>
        
        {{-- Console Tab --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="tab_console" checked disabled :disabled="!monitorEnabled || $el.disabled">
            <label class="form-check-label fw-semibold text-gray-700" for="tab_console">
                Console Tab
            </label>
            <div class="text-muted fs-7 mt-1">
                Real-time streaming logs and debug information.
            </div>
        </div>

        {{-- Request Inspector Tab --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="tab_request_inspector" checked disabled :disabled="!monitorEnabled || $el.disabled">
            <label class="form-check-label fw-semibold text-gray-700" for="tab_request_inspector">
                Request Inspector Tab
            </label>
            <div class="text-muted fs-7 mt-1">
                Inspect LLM request payloads (metadata, context, parameters).
            </div>
        </div>

        {{-- Activity Log Tab --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="tab_activity_log" checked disabled :disabled="!monitorEnabled || $el.disabled">
            <label class="form-check-label fw-semibold text-gray-700" for="tab_activity_log">
                Activity Log Tab
            </label>
            <div class="text-muted fs-7 mt-1">
                Historical activity logs with filtering options.
            </div>
        </div>
    </div>

    <div class="separator separator-dashed my-6"></div>

    {{-- SECTION: UI Preferences --}}
    <div class="mb-8">
        <h3 class="mb-5">
            {!! getIcon('ki-element-11', 'fs-2 me-2', '', 'i') !!}
            UI Preferences
        </h3>
        
        {{-- Chat Layout --}}
        <div class="mb-5">
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

        {{-- Custom CSS Class --}}
        <div class="mb-5">
            <label class="form-label fw-semibold text-gray-700">Custom CSS Class</label>
            <input type="text" class="form-control form-control-solid" placeholder="e.g., custom-chat-theme" disabled>
            <div class="text-muted fs-7 mt-1">
                Add custom CSS class to the chat container.
            </div>
        </div>

        {{-- Toolbar Buttons --}}
        <h5 class="mt-6 mb-4">Toolbar Buttons</h5>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_new_chat" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_new_chat">
                        New Chat Button
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_clear" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_clear">
                        Clear Chat Button
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_download" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_download">
                        Download History Button
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_monitor_toggle" checked disabled>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_monitor_toggle">
                        Monitor Toggle Button
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="separator separator-dashed my-6"></div>

    {{-- SECTION: Performance --}}
    <div class="mb-8">
        <h3 class="mb-5">
            {!! getIcon('ki-rocket', 'fs-2 me-2', '', 'i') !!}
            Performance
        </h3>
        
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="lazy_load_tabs" checked disabled>
            <label class="form-check-label fw-semibold text-gray-700" for="lazy_load_tabs">
                Lazy Load Tabs
            </label>
            <div class="text-muted fs-7 mt-1">
                Load tab content only when activated (reduces initial load time).
            </div>
        </div>

        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="cache_preferences" checked disabled>
            <label class="form-check-label fw-semibold text-gray-700" for="cache_preferences">
                Cache Preferences
            </label>
            <div class="text-muted fs-7 mt-1">
                Save settings to localStorage for faster loading.
            </div>
        </div>
    </div>

    <div class="separator separator-dashed my-6"></div>

    {{-- Form Actions --}}
    <div class="d-flex justify-content-end gap-2 mt-8">
        <button type="button" class="btn btn-light" disabled>
            Reset to Defaults
        </button>
        <button type="button" class="btn btn-primary" disabled>
            {!! getIcon('ki-check', 'fs-2 me-1', '', 'i') !!}
            Save Settings
        </button>
    </div>
</div>
