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

    {{-- Styles --}}
    @include('llm-manager::admin.quick-chat.partials.styles')
    
    {{-- Scripts --}}
    @include('llm-manager::admin.quick-chat.partials.scripts')
    
    {{-- Raw Message Modal --}}
    <div class="modal fade" id="rawMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Raw Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre><code id="rawMessageContent" class="language-json"></code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="copyRawMessage()">Copy JSON</button>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
