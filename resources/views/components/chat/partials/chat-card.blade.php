{{--
    CHAT CARD
    Card principal del chat (reutilizable en ambos layouts)
--}}

<div class="card" id="kt_chat_messenger">
    {{-- Card Header --}}
    <div class="card-header" id="kt_chat_messenger_header">
        <div class="card-title">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">Quick Chat</span>
                <span class="text-gray-500 mt-1 fw-semibold fs-7">
                    @if ($session)
                        Conversación #{{ $session->id }} - {{ $messages->count() }} mensajes
                    @else
                        Conversación rápida con IA
                    @endif
                </span>
            </h3>
            @include('llm-manager::components.chat.partials.drafts.chat-users')
        </div>
        
        {{-- Toolbar --}}
        <div class="card-toolbar d-flex gap-2">
            @if($session)
                <span class="badge badge-light-info">Session ID: {{ $session->id }}</span>
                @if ($session->configuration)
                    <span class="badge badge-light-primary">{{ ucfirst($session->configuration->provider) }}</span>
                @endif
            @endif
        </div>
    </div>
    
    {{-- Card Body --}}
    <div class="card-body py-0 pt-4" id="kt_chat_messenger_body">
        @include('llm-manager::components.chat.partials.messages-container')
    </div>
    
    {{-- Card Footer --}}
    <div class="card-footer pt-4" id="kt_chat_messenger_footer">
        @include('llm-manager::components.chat.partials.input-form', ['configurations' => $configurations])
    </div>
</div>
