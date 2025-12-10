<div id="messages-container-{{ $session?->id ?? 'default' }}" class="messages-container pt-5 px-10"
    style="scroll-behavior: smooth; height: 100%;">
    
    {{-- Streaming Status Indicator (sticky top) --}}
    @include('llm-manager::components.chat.partials.streaming-status-indicator', ['sessionId' => $session?->id ?? 'default'])
    
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
</div>
