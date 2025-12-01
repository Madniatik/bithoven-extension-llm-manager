<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    <div class="card card-flush h-xl-100">
        <div class="card-header pt-7">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">Quick Chat</span>
                <span class="text-gray-500 mt-1 fw-semibold fs-7">
                    @if($session)
                        Conversación #{{ $session->id }} - {{ $session->messages->count() }} mensajes
                    @else
                        Conversación rápida con IA
                    @endif
                </span>
            </h3>
            @if($session)
            <div class="card-toolbar">
                <span class="badge badge-light-info">Session ID: {{ $session->id }}</span>
                @if($session->configuration)
                <span class="badge badge-light-primary ms-2">{{ ucfirst($session->configuration->provider) }}</span>
                @endif
            </div>
            @endif
        </div>
        
        <div class="card-body pt-5">
            {{-- Messages Container --}}
            @include('llm-manager::admin.quick-chat.partials.messages-container')

            {{-- Separator --}}
            <div class="separator separator-dashed my-5"></div>
            
            {{-- Input Form --}}
            @include('llm-manager::admin.quick-chat.partials.input-form', ['configurations' => $configurations])
        </div>
    </div>

    {{-- Raw Message Modal --}}
    @include('llm-manager::admin.quick-chat.partials.modal-raw-message')

    {{-- Styles --}}
    @include('llm-manager::admin.quick-chat.partials.styles')
    
    {{-- Scripts --}}
    @include('llm-manager::admin.quick-chat.partials.scripts')
</x-default-layout>
