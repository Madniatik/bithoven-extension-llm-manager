{{--
    MONITOR SETTINGS SECTION
    
    Configuración del panel Monitor (tabs, enable/disable)
--}}

@php
    $sessionId = $sessionId ?? 'default';
@endphp

<div class="mb-8">
    <h3 class="mb-5">
        {!! getIcon('ki-satellite', 'fs-2 me-2', '', 'i') !!}
        Monitor Settings
    </h3>
    
    {{-- Enable Monitor --}}
    <div class="form-check form-check-custom form-check-solid mb-4">
        <input class="form-check-input" type="checkbox" id="monitor_enabled_{{ $sessionId }}" x-model="monitorEnabled" checked>
        <label class="form-check-label fw-semibold text-gray-700" for="monitor_enabled_{{ $sessionId }}">
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
        <input class="form-check-input" type="checkbox" id="tab_console_{{ $sessionId }}" checked :disabled="!monitorEnabled">
        <label class="form-check-label fw-semibold text-gray-700" for="tab_console_{{ $sessionId }}">
            Console Tab
        </label>
        <div class="text-muted fs-7 mt-1">
            Real-time streaming logs and debug information.
        </div>
    </div>

    {{-- Request Inspector Tab --}}
    <div class="form-check form-check-custom form-check-solid mb-4">
        <input class="form-check-input" type="checkbox" id="tab_request_inspector_{{ $sessionId }}" checked :disabled="!monitorEnabled">
        <label class="form-check-label fw-semibold text-gray-700" for="tab_request_inspector_{{ $sessionId }}">
            Request Inspector Tab
        </label>
        <div class="text-muted fs-7 mt-1">
            Inspect LLM request payloads (metadata, context, parameters).
        </div>
    </div>

    {{-- Activity Log Tab --}}
    <div class="form-check form-check-custom form-check-solid mb-4">
        <input class="form-check-input" type="checkbox" id="tab_activity_log_{{ $sessionId }}" checked :disabled="!monitorEnabled">
        <label class="form-check-label fw-semibold text-gray-700" for="tab_activity_log_{{ $sessionId }}">
            Activity Log Tab
        </label>
        <div class="text-muted fs-7 mt-1">
            Historical activity logs with filtering options.
        </div>
    </div>
</div>
