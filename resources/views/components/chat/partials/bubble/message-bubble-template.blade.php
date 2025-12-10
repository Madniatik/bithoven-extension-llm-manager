{{-- 
    MESSAGE BUBBLE TEMPLATE (Hybrid: Backend + JavaScript)
    
    Usage 1 (Backend): @include with $message and $session variables
    Usage 2 (JavaScript): <template> cloning with empty data attributes
    
    Variables (optional):
    - $message: Message model instance
    - $session: Chat session model instance
--}}
@php
    // Defaults for JavaScript cloning (when used as <template>)
    $message = $message ?? null;
    $session = $session ?? null;
    $bubbleNumber = $bubbleNumber ?? 0; // Bubble numbering

    // Dynamic values or empty for JS population
    $role = $message?->role ?? '';
    $messageId = $message?->id ?? '';
    $content = $message?->content ?? '';
    $timestamp = $message?->created_at?->format('H:i:s') ?? '';

    // User info
    $userName = $message?->role === 'user' ? $session?->user?->name ?? (auth()->user()?->name ?? 'User') : '';
    $userAvatar = $session?->user?->avatar ?? (auth()->user()?->avatar ?? null);
    $userInitial = $userName ? strtoupper(substr($userName, 0, 1)) : 'U';

    // Assistant info
    $provider = $message?->llmConfiguration?->provider ?? '';
    $model = $message?->llmConfiguration?->model ?? '';

    // Metadata
    $totalTokens = $message?->tokens ?? 0;
    $promptTokens = $message?->metadata['input_tokens'] ?? 0;
    $completionTokens = $message?->metadata['output_tokens'] ?? 0;
    $responseTime = $message?->response_time ?? ($message?->metadata['response_time'] ?? null);
    $ttft = $message?->metadata['time_to_first_chunk'] ?? null;
    $cost = $message?->cost_usd ?? ($message?->metadata['cost_usd'] ?? null);
    $isError = $message?->metadata['is_error'] ?? false;

    // CSS classes
    $alignmentClass = $role === 'user' ? 'justify-content-end' : ($role === 'assistant' ? 'justify-content-start' : '');
    $innerAlignmentClass = $role === 'user' ? 'align-items-end' : ($role === 'assistant' ? 'align-items-start' : '');
    $bgColorClass = $role === 'user' ? 'bg-light-success' : ($role === 'assistant' ? 'bg-light-primary' : '');
@endphp

<div class="d-flex {{ $alignmentClass }} mb-10 message-bubble" data-role="{{ $role }}"
    data-message-id="{{ $messageId }}" data-bubble-number="{{ $bubbleNumber }}">

    {{-- Inner wrapper with alignment --}}
    <div class="d-flex flex-column {{ $innerAlignmentClass }}" data-bubble-alignment=""
        style="width: 100%; max-width: 85%;">

        {{-- Header: Two-line structure with actions --}}
        @include('llm-manager::components.chat.partials.bubble.bubble-header', [
            'role' => $role,
            'messageId' => $messageId,
            'userName' => $userName,
            'userAvatar' => $userAvatar,
            'userInitial' => $userInitial,
            'provider' => $provider,
            'model' => $model,
            'timestamp' => $timestamp,
            'isError' => $isError,
            'bubbleNumber' => $bubbleNumber,
        ])

        {{-- Content wrapper --}}
        <div class="p-5 {{ $role === 'assistant' ? 'pb-2' : '' }} rounded bubble-content-wrapper {{ $bgColorClass }}" data-bubble-bg-class=""
            style="max-width: 85%; overflow-x: hidden; word-wrap: break-word; overflow-wrap: break-word;">
            <div class="message-content text-gray-800 fw-semibold fs-6"
                @if ($role === 'assistant') data-role="assistant" @endif data-bubble-content=""
                style="overflow-x: auto; max-width: 100%;">
                @if ($content)
                    {{ $content }}
                @endif
            </div>
        </div>

        {{-- Footer (solo assistant) --}}
        <div class="bubble-footer-container {{ $role !== 'assistant' ? 'd-none' : '' }}">
            @include('llm-manager::components.chat.partials.bubble.bubble-footer', [
                'totalTokens' => $totalTokens,
                'promptTokens' => $promptTokens,
                'completionTokens' => $completionTokens,
                'responseTime' => $responseTime,
                'ttft' => $ttft,
                'cost' => $cost,
                'isNewBubble' => !$message, // true when cloning (no $message), false for backend rendering
            ])
        </div>
    </div>
</div>
