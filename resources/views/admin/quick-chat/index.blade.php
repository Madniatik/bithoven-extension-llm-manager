<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    <div class="card" id="kt_chat_messenger">
        <div class="card-header" id="kt_chat_messenger_header">
            {{-- Card Title --}}
            <div class="card-title">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Quick Chat</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-7">
                        @if ($session)
                            Conversación #{{ $session->id }} - {{ $session->messages->count() }} mensajes
                        @else
                            Conversación rápida con IA
                        @endif
                    </span>
                </h3>
                @include('llm-manager::admin.quick-chat.partials.drafts.chat-users')
            </div>
            {{-- Card Toolbar --}}
            <div class="card-toolbar">
                <span class="badge badge-light-info">Session ID: {{ $session->id }}</span>
                @if ($session->configuration)
                    <span class="badge badge-light-primary ms-2">{{ ucfirst($session->configuration->provider) }}</span>
                @endif
            </div>
        </div>
        {{-- Card Body --}}
        <div class="card-body py-0" id="kt_chat_messenger_body">
            @include('llm-manager::admin.quick-chat.partials.messages-container')
        </div>
        {{-- Card Footer --}}
        <div class="card-footer pt-4" id="kt_chat_messenger_footer">
			{{-- Input Form & Buttons--}}
            @include('llm-manager::admin.quick-chat.partials.input-form', ['configurations' => $configurations])
        </div>
    </div>

    {{-- Raw Message Modal --}}
    @include('llm-manager::admin.quick-chat.partials.modals.modal-raw-message')

    {{-- Styles (partitioned) --}}
    @include('llm-manager::admin.quick-chat.partials.styles.dependencies')
    @include('llm-manager::admin.quick-chat.partials.styles.markdown')
    @include('llm-manager::admin.quick-chat.partials.styles.buttons')
    @include('llm-manager::admin.quick-chat.partials.styles.responsive')

    {{-- Scripts (partitioned) --}}
    @include('llm-manager::admin.quick-chat.partials.scripts.clipboard-utils')
    @include('llm-manager::admin.quick-chat.partials.scripts.message-renderer')
    @include('llm-manager::admin.quick-chat.partials.scripts.settings-manager')
    @include('llm-manager::admin.quick-chat.partials.scripts.event-handlers')
</x-default-layout>
