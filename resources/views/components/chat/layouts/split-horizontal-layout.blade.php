{{--
    SPLIT HORIZONTAL LAYOUT
    El split afecta solo al BODY de la card (mensajes + consola)
    El header y footer (textarea) quedan fuera del split
--}}

<div class="card" id="kt_chat_messenger">
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

        {{-- Toolbar --}}
        <div class="card-toolbar d-flex gap-2">
            @if ($session)
                <span class="badge badge-light-info">Session ID: {{ $session->id }}</span>
                @if ($session->configuration)
                    <span class="badge badge-light-primary">{{ ucfirst($session->configuration->provider) }}</span>
                @endif
            @endif
        </div>
    </div>

    {{-- SPLIT CONTAINER (solo body: mensajes + monitor) --}}
    <div class="split-horizontal-container" id="llm-split-view-{{ $session?->id ?? 'default' }}" x-data="splitResizer_{{ $session?->id ?? 'default' }}({{ $session?->id ?? 'null' }})"
        x-init="init()">
        {{-- CHAT PANE (70% default) - Solo mensajes con scroll --}}
        <div class="split-pane split-chat" id="split-chat-pane-{{ $session?->id ?? 'default' }}">
            <div class="card-body py-0" id="kt_chat_messenger_body-{{ $session?->id ?? 'default' }}">
                @include('llm-manager::components.chat.partials.messages-container')
            </div>
        </div>

        @if ($showMonitor)
            {{-- RESIZER BAR (draggable) --}}
            <div x-show="monitorOpen" class="split-resizer" id="split-resizer-{{ $session?->id ?? 'default' }}"
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
                id="split-monitor-pane-{{ $session?->id ?? 'default' }}" data-monitor-id="{{ $monitorId }}"
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
                                @click="monitorOpen = false; localStorage.setItem('llm_chat_monitor_open_{{ $session?->id ?? 'default' }}', 'false')"
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
                    <div x-show="activeTab === 'activity'" x-cloak style="height: 100%;">
                        <div class="h-100 d-flex flex-column" style="background: #1e1e2d; color: #92929f; padding: 1rem;">
                            <h6 class="text-white mb-3">
                                <i class="ki-duotone ki-chart-pie-simple fs-3 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Activity History
                            </h6>
                            <div class="flex-grow-1" style="overflow-y: auto;">
                                <div class="table-responsive">
                                    <table class="table table-row-bordered align-middle gy-4 gs-9" id="monitor-activity-table-{{ $monitorId }}">
                                        <thead class="border-gray-200 fs-7 fw-bold" style="background: #15151e; color: #92929f;">
                                            <tr>
                                                <th class="ps-4">Time</th>
                                                <th>Provider</th>
                                                <th>Tokens</th>
                                                <th>Cost</th>
                                                <th>Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody id="monitor-activity-body-{{ $monitorId }}" class="fs-7" style="color: #92929f;">
                                            <tr>
                                                <td colspan="5" class="text-center py-4" style="color: #565674;">No activity yet</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Card Footer (fuera del split) - SIEMPRE VISIBLE --}}
    <div class="card-footer pt-4" id="kt_chat_messenger_footer">
        @include('llm-manager::components.chat.partials.form-elements.input-form', ['configurations' => $configurations])
    </div>
</div>

{{-- Message Bubble Template (hidden, for cloning via JS) --}}
<template id="message-bubble-template-{{ $session?->id ?? 'default' }}">
    @include('llm-manager::components.chat.partials.bubble.message-bubble-template')
</template>

{{-- Styles y scripts ahora en partials incluidos desde chat-workspace.blade.php --}}
