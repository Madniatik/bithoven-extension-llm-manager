<div id="messages-container" class="scroll-y pe-5 h-600px" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer" data-kt-scroll-wrappers="#kt_content" data-kt-scroll-offset="5px">
    
    @if($session && $session->messages->count() > 0)
        @foreach($session->messages as $message)
        {{-- {{ $message->role === 'user' ? 'Usuario' : 'Asistente' }} --}}
        <div class="d-flex {{ $message->role === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-10 message-bubble" data-message-id="{{ $message->id }}">
            <div class="d-flex flex-column align-items-{{ $message->role === 'user' ? 'end' : 'start' }}" style="width: 100%; max-width: 75%;">
                <div class="d-flex align-items-center mb-2">
                    @if($message->role === 'assistant')
                    <div class="symbol symbol-35px symbol-circle me-3">
                        <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
                    </div>
                    @endif
                    
                    <div>
                        <span class="text-gray-600 fw-semibold fs-8">
                            @if($message->role === 'user')
                                {{ $session->user->name ?? 'User' }}
                            @else
                                Assistant
                            @endif
                        </span>
                        @if($message->role === 'assistant' && $session->configuration)
                            <span class="badge badge-light-primary badge-sm ms-2">{{ ucfirst($session->configuration->provider) }}</span>
                            <span class="badge badge-light-info badge-sm">{{ $session->configuration->model }}</span>
                        @endif
                        <span class="text-gray-500 fw-semibold fs-8 ms-2">
                            {{ $message->created_at->format('H:i:s') }}
                        </span>
                    </div>

                    @if($message->role === 'user')
                    <div class="symbol symbol-35px symbol-circle ms-3">
                        @if($session->user && $session->user->avatar)
                            <img src="{{ asset('storage/' . $session->user->avatar) }}" alt="{{ $session->user->name }}" />
                        @elseif($session->user)
                            <span class="symbol-label bg-light-success text-success fw-bold">{{ strtoupper(substr($session->user->name, 0, 1)) }}</span>
                        @else
                            <span class="symbol-label bg-light-success text-success fw-bold">U</span>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="p-5 rounded {{ $message->role === 'user' ? 'bg-light-success' : 'bg-light-primary' }}" style="max-width: 70%">
                    <div class="text-gray-800 fw-semibold fs-6 {{ $message->role === 'assistant' ? 'message-content' : '' }}" @if($message->role === 'assistant') data-role="assistant" @endif>@if($message->role === 'assistant'){{ $message->content }}@else{{ $message->content }}@endif</div>
                </div>

                @if($message->role === 'assistant')
                <div class="text-gray-500 fw-semibold fs-8 mt-1 d-flex align-items-center gap-3 flex-wrap">
                    {{-- Tokens --}}
                    <span>
                        <i class="ki-duotone ki-calculator fs-7 text-gray-400">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        {{ number_format($message->total_tokens) }} tokens
                    </span>

                    {{-- Response Time --}}
                    @if($message->response_time)
                    <span class="text-success">
                        <i class="ki-duotone ki-timer fs-7 text-success">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        {{ number_format($message->response_time, 2) }}s
                    </span>
                    @endif

                    {{-- Provider & Model --}}
                    @if(isset($message->metadata['provider']) && isset($message->metadata['model']))
                    <span class="text-primary">
                        <i class="ki-duotone ki-technology-2 fs-7 text-primary">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ ucfirst($message->metadata['provider']) }} / {{ $message->metadata['model'] }}
                    </span>
                    @endif

                    {{-- Streaming indicator --}}
                    @if(isset($message->metadata['is_streaming']) && $message->metadata['is_streaming'])
                    <span class="text-info" title="Streaming enabled - {{ $message->metadata['chunks_count'] ?? 0 }} chunks">
                        <i class="ki-duotone ki-cloud-download fs-7 text-info">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Streaming
                    </span>
                    @endif

                    {{-- Time to First Chunk (TTFT) --}}
                    @if(isset($message->metadata['time_to_first_chunk']))
                    <span class="text-warning" title="Time to first token">
                        <i class="ki-duotone ki-flash-circle fs-7 text-warning">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        TTFT: {{ $message->metadata['time_to_first_chunk'] }}s
                    </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @else
        {{-- Empty state --}}
        <div class="text-center py-10">
            <div class="text-gray-400 fs-3">
                <i class="ki-duotone ki-message-text-2 fs-3x mb-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="fw-semibold">No hay mensajes en esta conversación</div>
            </div>
        </div>
    @endif

    {{-- Thinking/Streaming message (ejemplo) --}}
    <div class="d-flex justify-content-start mb-10 message-bubble">
        <div class="d-flex flex-column align-items-start" style="width: 100%; max-width: 75%;">
            <div class="d-flex align-items-center mb-2">
                <div class="symbol symbol-35px symbol-circle me-3">
                    <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
                </div>
                <div>
                    <span class="text-gray-600 fw-semibold fs-8">Assistant</span>
                    @if($session && $session->configuration)
                        <span class="badge badge-light-primary badge-sm ms-2">{{ ucfirst($session->configuration->provider) }}</span>
                        <span class="badge badge-light-info badge-sm">{{ $session->configuration->model }}</span>
                    @endif
                    <span class="text-gray-500 fw-semibold fs-8 ms-2">{{ now()->format('H:i:s') }}</span>
                </div>
            </div>
            <div class="p-5 rounded bg-light-primary" style="max-width: 70%">
                <div class="text-gray-800 fw-semibold fs-6">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted">Thinking<span class="streaming-cursor">|</span></span>
                    </div>
                </div>
            </div>
            <div class="text-gray-500 fw-semibold fs-8 mt-1 d-flex align-items-center gap-3">
                <span><i class="ki-duotone ki-calculator fs-7 text-gray-400"><span class="path1"></span><span class="path2"></span></i> 0 tokens</span>
                <span class="text-info"><i class="ki-duotone ki-timer fs-7 text-info"><span class="path1"></span><span class="path2"></span></i> 0.00s</span>
                <span class="text-primary"><i class="ki-duotone ki-cloud-download fs-7 text-primary"><span class="path1"></span><span class="path2"></span></i> Streaming...</span>
            </div>
        </div>
    </div>

</div>
