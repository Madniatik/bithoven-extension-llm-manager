<div id="messages-container-{{ $session?->id ?? 'default' }}" class="messages-container p-5"
    style="overflow-y: auto; scroll-behavior: smooth; height: 100%;">
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
</div>
