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
    {{-- Monitor Console Tab --}}
    @if ($showMonitor)
        <button type="button" 
                class="btn btn-sm btn-icon btn-active-light-primary" 
                @click="openMonitorTab('console')"
                :class="{'active': monitorOpen && activeTab === 'console'}"
                data-bs-toggle="tooltip" 
                title="Console Monitor">
            {!! getIcon('ki-chart-line-down', 'fs-3', '', 'i') !!}
        </button>
    @endif

    {{-- Activity Logs Tab --}}
    @if ($showMonitor)
        <button type="button" 
                class="btn btn-sm btn-icon btn-active-light-warning" 
                @click="openMonitorTab('activity')"
                :class="{'active': monitorOpen && activeTab === 'activity'}"
                data-bs-toggle="tooltip" 
                title="Activity Logs">
            {!! getIcon('ki-chart-pie-simple', 'fs-3', '', 'i') !!}
        </button>
    @endif
    <div class="separator mx-2"></div>
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
