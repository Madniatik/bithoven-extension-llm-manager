{{-- 
    Bubble Footer Component
    Unified footer for assistant message bubbles
    
    Variables (optional, defaults to placeholders for JS population):
    - $totalTokens: Total tokens count
    - $promptTokens: Input tokens
    - $completionTokens: Output tokens
    - $responseTime: Response time in seconds
    - $ttft: Time to first token in seconds
    - $cost: Cost in USD
    - $isNewBubble: Boolean, true for new bubbles (colored), false for old (gray)
--}}
@php
    $totalTokens = $totalTokens ?? 0;
    $promptTokens = $promptTokens ?? 0;
    $completionTokens = $completionTokens ?? 0;
    $responseTime = $responseTime ?? null;
    $ttft = $ttft ?? null;
    $cost = $cost ?? null;
    $isNewBubble = $isNewBubble ?? false; // Default: old bubble (no color)
@endphp

<div class="bubble-footer text-gray-500 fw-semibold fs-8 mt-1 d-flex align-items-center gap-3 flex-wrap">
    {{-- Tokens --}}
    <span class="footer-tokens {{ !$totalTokens ? 'text-gray-400' : '' }}">
        <i class="bi bi-calculator fs-8 {{ !$totalTokens ? 'text-gray-400' : '' }}"></i>
        @if ($totalTokens > 0)
            {{ number_format($totalTokens) }} tokens
            @if ($promptTokens > 0 && $completionTokens > 0)
                <span class="text-gray-400" title="Sent / Received">(↑{{ number_format($promptTokens) }} /
                    ↓{{ number_format($completionTokens) }})</span>
            @endif
        @else
            0 tokens <span class="text-gray-400" title="Sent / Received">(↑0 / ↓0)</span>
        @endif
    </span>

    {{-- Response Time --}}
    <span
        class="footer-response-time {{ !$responseTime ? 'text-gray-400' : ($isNewBubble ? 'text-success' : 'text-gray-400') }}">
        <i
            class="bi bi-hourglass-bottom fs-7 {{ !$responseTime ? 'text-gray-400' : ($isNewBubble ? 'text-success' : 'text-gray-400') }}"></i>
        {{ $responseTime ? number_format($responseTime, 2) . 's' : '...' }}
    </span>

    {{-- Time to First Token (solo en bubbles nuevos) --}}
    @if ($isNewBubble)
        <span class="footer-ttft fs-8 {{ !$ttft ? 'text-gray-400' : 'text-info' }}">
            <i class="bi bi-stopwatch fs-8 {{ !$ttft ? 'text-gray-400' : 'text-info' }}"></i>
            TTFT: {{ $ttft ? number_format($ttft, 2) . 's' : '...' }}
        </span>
    @endif

    {{-- Cost (solo si > 0) --}}
    @if ($cost && $cost > 0)
        <span class="footer-cost {{ $isNewBubble ? 'text-primary' : 'text-primary' }}">
            <i class="bi bi-coin fs-8 {{ $isNewBubble ? 'text-primary' : 'text-primary' }}"></i>
            {{ number_format($cost, 6) }}
        </span>
    @elseif($cost === null)
        <span class="footer-cost text-gray-400">
            <i class="bi bi-coin fs-8 text-gray-400"></i>
            $...
        </span>
    @endif
</div>
