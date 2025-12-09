<div id="messages-container-{{ $session?->id ?? 'default' }}" 
     style="overflow-y: auto; scroll-behavior: smooth; height: 100%; max-height: calc(100vh - 450px); position: relative;">
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
    
    <!--begin::Scroll to bottom button-->
    <button id="scroll-to-bottom-btn-{{ $session?->id ?? 'default' }}" 
            class="scroll-to-bottom-btn d-none"
            title="Scroll to bottom">
        <i class="ki-duotone ki-arrow-down fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        <span class="unread-badge badge badge-circle badge-danger d-none" id="unread-badge-{{ $session?->id ?? 'default' }}">0</span>
    </button>
    <!--end::Scroll to bottom button-->
</div>
