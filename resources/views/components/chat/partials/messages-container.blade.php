<div id="messages-container-{{ $session?->id ?? 'default' }}">
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
</div>
