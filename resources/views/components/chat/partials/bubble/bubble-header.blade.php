{{--
    BUBBLE HEADER COMPONENT
    
    Two-line header structure for message bubbles:
    Line 1: Avatar + Name/Model + Timestamp
    Line 2: Action buttons (Copy | View Raw | Delete)
    
    Variables:
    - $role: 'user' | 'assistant'
    - $userName: User name (for user messages)
    - $userAvatar: User avatar path (optional)
    - $userInitial: User initial letter
    - $provider: LLM provider (for assistant)
    - $model: LLM model name (for assistant)
    - $timestamp: Message timestamp (H:i:s)
    - $isError: Boolean - if message is error explanation
    - $messageId: Message ID (for data attributes)
--}}
@php
    // Defaults
    $role = $role ?? '';
    $messageId = $messageId ?? '';
    $userName = $userName ?? 'User';
    $userAvatar = $userAvatar ?? null;
    $userInitial = $userInitial ?? 'U';
    $provider = $provider ?? '';
    $model = $model ?? '';
    $timestamp = $timestamp ?? '';
    $isError = $isError ?? false;
    $bubbleNumber = $bubbleNumber ?? 0;
@endphp

<div class="bubble-header mb-2">
    {{-- Line 1: Avatar + Name/Model + Timestamp --}}
    <div class="d-flex align-items-center mb-1">
        {{-- Assistant avatar (left side) --}}
        <div class="symbol symbol-35px symbol-circle me-3 assistant-avatar {{ $role !== 'assistant' ? 'd-none' : '' }}">
            <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
        </div>

        <div class="d-flex flex-column {{ $role === 'user' ? 'align-items-end' : '' }}">
            {{-- Name/Model + Badges + Timestamp --}}
            <div class="flex-grow-1">
                <span class="text-gray-600 fw-semibold fs-8" data-bubble-header-text="">
                    @if ($role === 'user')
                        {{ $userName }}
                    @elseif ($role === 'assistant' && $provider && $model)
                        {{ ucfirst($provider) }} / {{ $model }}
                    @elseif ($role === 'assistant')
                        Assistant
                    @endif
                </span>

                @if ($isError)
                    <span class="badge badge-light-warning badge-sm ms-2"
                        title="This message contains an error explanation">
                        <i class="ki-duotone ki-information-5 fs-7">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Error Message
                    </span>
                @endif

                {{-- Bubble Number Badge (antes del timestamp) --}}
                @if($bubbleNumber > 0)
                <span class="badge badge-light-secondary badge-sm ms-2" data-bubble-number-badge style="font-size: 0.7rem;">({{ $bubbleNumber }})</span>
                @endif
                
                <span class="text-gray-500 fw-semibold fs-8 ms-2" data-bubble-timestamp="">
                    {{ $timestamp }}
                </span>
            </div>

            {{-- Line 2: Action Buttons --}}
            <div class="d-flex gap-2" style="margin-top: 0px;">
                {{-- Copy Button (both user & assistant) --}}
                <a href="#" class="text-muted text-hover-primary fs-8 text-decoration-none copy-message-btn"
                    data-message-id="{{ $messageId }}" title="Copy message content">
                    Copy
                </a>

                <span class="text-muted fs-8">|</span>

                {{-- View Raw Button (both user & assistant) --}}
                <a href="#" class="text-muted text-hover-primary fs-8 text-decoration-none view-raw-btn"
                    data-message-id="{{ $messageId }}" title="View raw response data">
                    View Raw
                </a>

                <span class="text-muted fs-8">|</span>

                {{-- Delete Button (both user & assistant) --}}
                <a href="#" class="text-muted text-hover-danger fs-8 text-decoration-none delete-message-btn"
                    data-message-id="{{ $messageId }}" title="Delete this message">
                    Delete
                </a>
                
                {{-- Resend Button (solo user bubbles, después de Delete) --}}
                @if($role === 'user')
                <span class="text-muted fs-8">|</span>
                
                <a href="#" class="text-muted text-hover-primary fs-8 text-decoration-none resend-message-btn"
                    data-message-id="{{ $messageId }}" title="Resend this message">
                    Resend
                </a>
                @endif
            </div>
        </div>

        {{-- User avatar (right side) --}}
        <div class="symbol symbol-35px symbol-circle ms-3 user-avatar {{ $role !== 'user' ? 'd-none' : '' }}">
            @if ($userAvatar)
                <img src="{{ asset('storage/' . $userAvatar) }}" alt="{{ $userName }}" />
            @else
                <span class="symbol-label bg-light-success text-success fw-bold">{{ $userInitial }}</span>
            @endif
        </div>
    </div>
</div>
