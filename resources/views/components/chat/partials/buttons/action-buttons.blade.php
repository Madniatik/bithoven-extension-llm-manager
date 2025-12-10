{{-- Action Buttons --}}
<div class="d-flex align-items-center gap-1">
    @include('llm-manager::components.chat.partials.buttons.chat-settings')
    <button id="new-chat-btn-{{ $session?->id ?? 'default' }}" class="btn btn-sm btn-icon btn-active-light-primary"
        type="button" data-bs-toggle="tooltip" title="New Chat">
        {!! getIcon('ki-plus', 'fs-3', '', 'i') !!}
    </button>
    <button id="clear-btn-{{ $session?->id ?? 'default' }}" class="btn btn-icon btn-sm btn-active-light-danger"
        type="button" data-bs-toggle="tooltip" title="Delete Chat">
        {!! getIcon('ki-trash', 'fs-3', '', 'i') !!}
    </button>

    {{-- Solo mostrar separador si hay tabs de monitor --}}
    @if (
        $showMonitor &&
            ($isMonitorTabEnabled('console') ||
                $isMonitorTabEnabled('activity_log') ||
                $isMonitorTabEnabled('request_inspector')))
        <div class="separator mx-2"></div>
    @endif

    {{-- Monitor Console Tab --}}
    @if ($showMonitor && $isMonitorTabEnabled('console'))
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" @click="openMonitorTab('console')"
            :class="{ 'active': monitorOpen && activeTab === 'console' }" data-bs-toggle="tooltip"
            title="Console Monitor">
            {!! getIcon('ki-underlining', 'fs-3', '', 'i') !!}
        </button>
    @endif

    {{-- Request Tab --}}
    @if ($showMonitor && $isMonitorTabEnabled('request_inspector'))
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" @click="openMonitorTab('request')"
            :class="{ 'active': monitorOpen && activeTab === 'request' }" data-bs-toggle="tooltip"
            title="Request Inspector">
            {!! getIcon('ki-message-programming', 'fs-3', '', 'i') !!}
        </button>
    @endif

    {{-- Activity Logs Tab --}}
    @if ($showMonitor && $isMonitorTabEnabled('activity_log'))
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" @click="openMonitorTab('activity')"
            :class="{ 'active': monitorOpen && activeTab === 'activity' }" data-bs-toggle="tooltip" title="Activity Logs">
            {!! getIcon('ki-chart-pie-simple', 'fs-3', '', 'i') !!}
        </button>
    @endif

    {{-- Solo mostrar separador si hay tabs de monitor --}}
    @if (
        $showMonitor &&
            ($isMonitorTabEnabled('console') ||
                $isMonitorTabEnabled('activity_log') ||
                $isMonitorTabEnabled('request_inspector')))
        <div class="separator mx-2"></div>
    @endif

    {{-- Future implementations --}}
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" disabled data-bs-toggle="tooltip"
        title="Attach File">
        {!! getIcon('ki-paper-clip', 'fs-3', '', 'i') !!}
    </button>
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" disabled data-bs-toggle="tooltip"
        title="Export chat">
        {!! getIcon('ki-exit-up', 'fs-3', '', 'i') !!}
    </button>
</div>
<!--end::Actions-->
