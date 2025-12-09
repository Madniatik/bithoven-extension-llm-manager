{{--
    SPLIT HORIZONTAL LAYOUT
    El split afecta solo al BODY de la card (mensajes + consola)
    El header y footer (textarea) quedan fuera del split
--}}

@php
    $sessionId = $session?->id ?? 'default';
    $showSettings = $showSettings ?? true; // Por defecto mostrar settings tab
@endphp

<div class="card" id="kt_chat_messenger" x-data="chatSettings({{ is_numeric($sessionId) ? $sessionId : '\'' . $sessionId . '\'' }})">
    {{-- Card Header (fuera del split) --}}
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
    </div>

    {{-- Tab Navigation --}}
    @include('llm-manager::components.chat.partials.tab-navigation', [
        'sessionId' => $sessionId,
        'showSettings' => $showSettings,
    ])

    {{-- TAB CONTENT WRAPPER --}}
    <div class="tab-content">
        {{-- TAB: Conversación | Monitor --}}
        <div x-show="activeMainTab === 'conversation'" style="display: block;">
            {{-- SPLIT CONTAINER (solo body: mensajes + monitor) --}}
            <div class="split-horizontal-container" id="llm-split-view-{{ $sessionId }}" 
                 x-data="splitResizer_{{ $sessionId }}({{ $session?->id ?? 'null' }})"
                 x-init="init()">
                {{-- CHAT PANE (70% default) - Solo mensajes con scroll --}}
                <div class="split-pane split-chat" id="split-chat-pane-{{ $sessionId }}">
                    <div class="card-body py-0" id="kt_chat_messenger_body-{{ $sessionId }}">
                        @include('llm-manager::components.chat.partials.messages-container')
                    </div>
                </div>

        @if ($showMonitor)
            {{-- RESIZER BAR (draggable) --}}
            <div x-show="monitorOpen" class="split-resizer" id="split-resizer-{{ $sessionId }}"
                @mousedown="startResize($event)" style="display: none;">
                <div class="split-resizer-handle">
                    {!! getIcon('ki-row-vertical', 'fs-3', '', 'i') !!}
                </div>
            </div>

            {{-- MONITOR PANE (30% default) - SOLO CONSOLA --}}
            <div x-show="monitorOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-4" class="split-pane split-monitor llm-monitor"
                id="split-monitor-pane-{{ $sessionId }}" data-monitor-id="{{ $monitorId }}"
                x-data="{ monitorId: '{{ $monitorId }}' }" x-init="$nextTick(() => {
                    if (window.initLLMMonitor) {
                        window.initLLMMonitor('{{ $monitorId }}');
                    }
                });" style="display: none;">

                {{-- Console Header (sticky) - NO SCROLL --}}
                <div class="monitor-header-sticky border-bottom border-gray-300">
                    <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 gap-2">
                        {{-- Left: Title + Inline Metrics --}}
                        <div class="d-flex align-items-center gap-10 flex-grow-1">
                            <h6 class="mb-0 text-gray-800 fw-bold d-flex align-items-center">
                                {!! getIcon('ki-satellite', 'fs-2x me-2', '', 'i') !!}
                                Monitor
                            </h6>

                            {{-- Inline Metrics (desktop only) --}}
                            <div class="text-muted fs-7 fw-normal d-none d-md-flex align-items-center gap-2">
                                <span>Status: <span id="monitor-status-{{ $monitorId }}"
                                        class="text-gray-800 fw-semibold">Idle</span></span>
                                <span>•</span>
                                <span>Tokens: <span id="monitor-token-count-{{ $monitorId }}"
                                        class="text-gray-800 fw-semibold">0</span></span>
                                <span>•</span>
                                <span>Chunks: <span id="monitor-chunk-count-{{ $monitorId }}"
                                        class="text-gray-800 fw-semibold">0</span></span>
                                <span>•</span>
                                <span>Time: <span id="monitor-duration-{{ $monitorId }}"
                                        class="text-gray-800 fw-semibold">0s</span></span>
                                <span>•</span>
                                <span>Costs: <span id="monitor-cost-{{ $monitorId }}"
                                        class="text-gray-800 fw-semibold">$0.00</span></span>
                            </div>
                        </div>

                        {{-- Right: Action Buttons --}}
                        <div class="d-flex gap-1 flex-shrink-0">
                            {{-- Refresh --}}
                            <button type="button" class="btn btn-icon btn-sm btn-active-secondary"
                                onclick="window.LLMMonitor.refresh('{{ $monitorId }}')" data-bs-toggle="tooltip"
                                title="Refresh">
                                {!! getIcon('ki-arrows-circle', 'fs-1', '', 'i') !!}
                            </button>

                            {{-- Download Logs --}}
                            <button type="button" class="btn btn-icon btn-sm btn-active-light-primary"
                                onclick="window.LLMMonitor.downloadLogs('{{ $monitorId }}')"
                                data-bs-toggle="tooltip" title="Download logs">
                                {!! getIcon('ki-cloud-download', 'fs-1', '', 'i') !!}
                            </button>

                            {{-- Copy Logs --}}
                            <button type="button" class="btn btn-icon btn-sm btn-active-light-primary"
                                onclick="window.LLMMonitor.copyLogs('{{ $monitorId }}')" data-bs-toggle="tooltip"
                                title="Copy logs">
                                {!! getIcon('ki-copy', 'fs-1', '', 'i') !!}
                            </button>

                            {{-- Clear All --}}
                            <button type="button" class="btn btn-icon btn-sm btn-active-light-danger"
                                onclick="window.LLMMonitor.clear('{{ $monitorId }}')" data-bs-toggle="tooltip"
                                title="Clear all">
                                {!! getIcon('ki-trash', 'fs-1', '', 'i') !!}
                            </button>

                            {{-- Close --}}
                            <button type="button" class="btn btn-icon btn-sm btn-active-light-dark"
                                @click="monitorOpen = false; localStorage.setItem('llm_chat_monitor_open_{{ $sessionId }}', 'false')"
                                data-bs-toggle="tooltip" title="Close">
                                {!! getIcon('ki-cross', 'fs-1', '', 'i') !!}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabs Body (scrollable) --}}
                <div class="monitor-console-body p-0">
                    {{-- Console Tab --}}
                    <div x-show="activeTab === 'console'" style="height: 100%;">
                        @include('llm-manager::components.chat.shared.monitor-console', [
                            'monitorId' => $monitorId,
                        ])
                    </div>

                    {{-- Activity Logs Tab --}}
                    <div x-show="activeTab === 'activity'" style="height: 100%;">
                        @include('llm-manager::admin.stream.partials.activity-table', [
                            'sessionId' => $session?->id ?? null,
                        ])
                    </div>

                    {{-- Request Inspector Tab (NO x-cloak = DOM always exists) --}}
                    <div x-show="activeTab === 'request'" style="height: 100%; overflow-y: auto;">
                        @include('llm-manager::components.chat.shared.monitor-request-inspector')
                    </div>
                </div>
            </div>
        @endif
            </div>
            {{-- END SPLIT CONTAINER --}}
        </div>
        {{-- END TAB: Conversación | Monitor --}}

    @if($showSettings)
        {{-- TAB: Chat Settings --}}
        <div x-show="activeMainTab === 'settings'" style="display: none;">
            <div class="p-5">
                @include('llm-manager::components.chat.partials.settings-form', [
                    'sessionId' => $sessionId,
                ])
            </div>
        </div>
        {{-- END TAB: Chat Settings --}}
    @endif
</div>
{{-- END TAB CONTENT WRAPPER --}}

    {{-- Card Footer (fuera del split) - SIEMPRE VISIBLE excepto en Settings tab --}}
    <div class="card-footer pt-4" id="kt_chat_messenger_footer" x-show="activeMainTab === 'conversation'">
        @include('llm-manager::components.chat.partials.form-elements.input-form', [
            'configurations' => $configurations,
        ])
    </div>
</div>

{{-- Message Bubble Template (hidden, for cloning via JS) --}}
<template id="message-bubble-template-{{ $sessionId }}">
    @include('llm-manager::components.chat.partials.bubble.message-bubble-template')
</template>

{{-- Styles y scripts ahora en partials incluidos desde chat-workspace.blade.php --}}
