{{-- Action Buttons --}}
<div class="d-flex align-items-center gap-1">
    <button id="new-chat-btn-{{ $session?->id ?? 'default' }}" class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="New Chat" onclick="if(confirm('Start a new chat? This will reload the page.')) { window.location.href='{{ route('admin.llm.quick-chat.new') }}'; }">
        {!! getIcon('ki-plus', 'fs-3', '', 'i') !!}
    </button>
    @include('llm-manager::components.chat.partials.buttons.chat-settings')
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="Record Voice">
        <i class="bi bi-mic-fill fs-3"></i>
    </button>
    {{-- Monitor Toggle Button --}}
    @if ($showMonitor)
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" @click="toggleMonitor()"
            data-bs-toggle="tooltip" :title="monitorOpen ? 'Ocultar Monitor' : 'Mostrar Monitor'">
            {!! getIcon('ki-chart-line-down', 'fs-3', '', 'i') !!}
        </button>
    @endif
    <div class="separator mx-2"></div>
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="Attach File">
        {!! getIcon('ki-paper-clip', 'fs-3', '', 'i') !!}
    </button>
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="Export chat">
        {!! getIcon('ki-exit-up', 'fs-3', '', 'i') !!}
    </button>
</div>
<!--end::Actions-->
