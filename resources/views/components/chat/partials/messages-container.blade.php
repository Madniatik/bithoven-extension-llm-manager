<div id="messages-container-{{ $session?->id ?? 'default' }}" class="messages-container pt-5 px-10"
    style="scroll-behavior: smooth; height: 100%;">
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
</div>
