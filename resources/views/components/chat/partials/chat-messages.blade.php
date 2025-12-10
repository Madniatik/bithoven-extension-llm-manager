@if ($session && $session->messages->count() > 0)
    @foreach ($session->messages as $message)
        @include('llm-manager::components.chat.partials.bubble.message-bubble-template', [
            'message' => $message,
            'session' => $session,
            'bubbleNumber' => $loop->iteration,
        ])
    @endforeach
@else
    {{-- Empty state --}}
    <div class="text-center py-10">
        <div class="text-gray-400 fs-3">
            {!! getIcon('ki-message-text-2', 'fs-5x mb-5', '', 'i') !!}
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
                <span class="text-muted fw-semibold fs-7">
                    <span id="thinking-model-info-{{ $session?->id ?? 'default' }}">Thinking</span>
                    <span class="streaming-cursor">|</span></span>
            </div>
            <div class="text-gray-500 fw-semibold fs-8 mt-2 d-flex align-items-center gap-3">
                <span>
                    <i class="bi bi-calculator fs-8"></i>
                    <span class="thinking-tokens">0</span>
                    tokens <span class="text-gray-400" title="Sent / Received">(↑0 / ↓0)</span>
                </span>
                <span class="text-gray-800">
                    <i class="bi bi-hourglass-split fs-7 text-gray-800"></i>
                    <span class="thinking-time">0.00</span>s
                </span>
                <span class="text-warning fs-8">
                    <i class="bi bi-stopwatch text-warning fs-9"></i> TTFT:
                    <span class="thinking-ttft fs-8">0.00</span>s
                </span>
                <span class="text-gray-400 fs-8">
                    <i class="bi bi-coin fs-8 text-gray-400"></i>
                    0.000000
                </span>
            </div>
        </div>
    </div>
</div>
