@if ($session && $session->messages->count() > 0)
    @foreach ($session->messages as $message)
        {{-- {{ $message->role === 'user' ? 'Usuario' : 'Asistente' }} --}}
        <div class="d-flex {{ $message->role === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-10 message-bubble"
            data-message-id="{{ $message->id }}" data-message-role="{{ $message->role }}">
            <div class="d-flex flex-column align-items-{{ $message->role === 'user' ? 'end' : 'start' }}"
                style="width: 100%; max-width: 85%;">
                <div class="d-flex align-items-center mb-2">
                    @if ($message->role === 'assistant')
                        <div class="symbol symbol-35px symbol-circle me-3">
                            <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
                        </div>
                    @endif

                    <div>
                        <span class="text-gray-600 fw-semibold fs-8">
                            @if ($message->role === 'user')
                                {{ $session->user->name ?? 'User' }}
                            @else
                                Assistant
                            @endif
                        </span>
                        @if ($message->role === 'assistant' && $session->configuration)
                            <span
                                class="badge badge-light-primary badge-sm ms-2">{{ ucfirst($session->configuration->provider) }}</span>
                            <span class="badge badge-light-info badge-sm">{{ $session->configuration->model }}</span>
                        @endif
                        @if ($message->role === 'assistant' && isset($message->metadata['is_error']) && $message->metadata['is_error'])
                            <span class="badge badge-light-warning badge-sm ms-2" title="This message contains an error explanation">
                                <i class="ki-duotone ki-information-5 fs-7">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Error Message
                            </span>
                        @endif
                        <span class="text-gray-500 fw-semibold fs-8 ms-2">
                            {{ $message->created_at->format('H:i:s') }}
                        </span>
                    </div>

                    @if ($message->role === 'user')
                        <div class="symbol symbol-35px symbol-circle ms-3">
                            @if ($session->user && $session->user->avatar)
                                <img src="{{ asset('storage/' . $session->user->avatar) }}"
                                    alt="{{ $session->user->name }}" />
                            @elseif($session->user)
                                <span
                                    class="symbol-label bg-light-success text-success fw-bold">{{ strtoupper(substr($session->user->name, 0, 1)) }}</span>
                            @else
                                <span class="symbol-label bg-light-success text-success fw-bold">U</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="p-5 rounded {{ $message->role === 'user' ? 'bg-light-success' : 'bg-light-primary' }} bubble-content-wrapper position-relative"
                    style="max-width: 85%">
                    <div class="text-gray-800 fw-semibold fs-6 message-content"
                        @if ($message->role === 'assistant') data-role="assistant" @endif
                        data-raw-content="{{ base64_encode($message->content) }}">{{ $message->content }}</div>
                    
                    {{-- Retry button for error messages --}}
                    @if ($message->role === 'assistant' && isset($message->metadata['is_error']) && $message->metadata['is_error'])
                        <div class="mt-3 pt-3 border-top border-gray-300">
                            <button type="button" class="btn btn-sm btn-light-warning" onclick="retryErrorMessage({{ $message->id }})">
                                <i class="ki-duotone ki-arrows-circle fs-6">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Retry with Higher Token Limit
                            </button>
                        </div>
                    @endif
                </div>

                @if ($message->role === 'assistant')
                    <div class="text-gray-500 fw-semibold fs-8 mt-1 d-flex align-items-center gap-3 flex-wrap">
                        {{-- Tokens --}}
                        <span>
                            <i class="ki-duotone ki-calculator fs-7 text-gray-400">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            {{ number_format($message->tokens ?? 0) }} tokens
                        </span>

                        {{-- Response Time (column or metadata fallback) --}}
                        @php
                            $responseTime = $message->response_time ?? ($message->metadata['response_time'] ?? null);
                        @endphp
                        @if ($responseTime)
                            <span class="text-success">
                                <i class="ki-duotone ki-timer fs-7 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                {{ number_format($responseTime, 2) }}s
                            </span>
                        @endif

                        {{-- Time to First Chunk (TTFT) --}}
                        @if (isset($message->metadata['time_to_first_chunk']) && $message->metadata['time_to_first_chunk'])
                            <span class="text-warning" title="Time to first token">
                                <i class="ki-duotone ki-flash-circle fs-7 text-warning">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                TTFT: {{ number_format($message->metadata['time_to_first_chunk'], 2) }}s
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

{{-- Thinking indicator (solo spinner + stats, NO es un bubble completo) --}}
<div id="thinking-message-{{ $session?->id ?? 'default' }}" class="d-none mb-10" data-message-role="thinking">
    <div class="d-flex justify-content-start">
        <div class="d-flex flex-column align-items-start" style="width: 100%; max-width: 75%;">
            <div class="p-3 rounded bg-light-primary d-flex align-items-center">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="text-muted fw-semibold fs-7">Thinking<span class="streaming-cursor">|</span></span>
            </div>
            <div class="text-gray-500 fw-semibold fs-8 mt-2 d-flex align-items-center gap-3">
                <span><i class="ki-duotone ki-calculator fs-7 text-gray-400"><span class="path1"></span><span class="path2"></span></i> <span class="thinking-tokens">0</span> tokens</span>
                <span class="text-info"><i class="ki-duotone ki-timer fs-7 text-info"><span class="path1"></span><span class="path2"></span></i> <span class="thinking-time">0.00</span>s</span>
                <span class="text-warning"><i class="ki-duotone ki-flash-circle fs-7 text-warning"><span class="path1"></span><span class="path2"></span></i> TTFT: <span class="thinking-ttft">...</span>s</span>
            </div>
        </div>
    </div>
</div>
