{{-- Action Buttons --}}
<div class="d-flex align-items-center gap-2">
    @include('llm-manager::admin.quick-chat.partials.buttons.chat-settings')
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="Record Voice">
        <i class="bi bi-mic-fill fs-3"></i>
    </button>
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="Attach File">
        {!! getIcon('ki-paper-clip', 'fs-3', '', 'i') !!}
    </button>
    <button class="btn btn-sm btn-icon btn-active-light-primary" type="button" data-bs-toggle="tooltip"
        title="Coming soon">
        {!! getIcon('ki-exit-up', 'fs-3', '', 'i') !!}
    </button>
</div>
<!--end::Actions-->
