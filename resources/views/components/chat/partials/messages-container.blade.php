<div id="messages-container-{{ $session?->id ?? 'default' }}" 
     style="overflow-y: auto; scroll-behavior: smooth; height: 100%; max-height: calc(100vh - 450px);">
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
</div>
