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
            @if($session)
                <span class="badge badge-light-info">Session ID: {{ $session->id }}</span>
                @if ($session->configuration)
                    <span class="badge badge-light-primary">{{ ucfirst($session->configuration->provider) }}</span>
                @endif
            @endif
        </div>
    </div>
    
    {{-- SPLIT CONTAINER (solo body: mensajes + monitor) --}}
    <div class="split-horizontal-container" 
         id="llm-split-view-{{ $session?->id ?? 'default' }}" 
         x-data="splitResizer_{{ $session?->id ?? 'default' }}({{ $session?->id ?? 'null' }})" 
         x-init="init()">
        {{-- CHAT PANE (70% default) - Solo mensajes con scroll --}}
        <div class="split-pane split-chat" id="split-chat-pane-{{ $session?->id ?? 'default' }}">
            <div class="card-body py-0" id="kt_chat_messenger_body-{{ $session?->id ?? 'default' }}">
                @include('llm-manager::components.chat.partials.messages-container')
            </div>
        </div>
        
        @if($showMonitor)
            {{-- RESIZER BAR (draggable) --}}
            <div 
                x-show="monitorOpen"
                class="split-resizer" 
                id="split-resizer-{{ $session?->id ?? 'default' }}"
                @mousedown="startResize($event)"
                style="display: none;">
                <div class="split-resizer-handle">
                    <i class="ki-duotone ki-row-vertical fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            
            {{-- MONITOR PANE (30% default) - SOLO CONSOLA --}}
            <div 
                x-show="monitorOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-4"
                class="split-pane split-monitor" 
                id="split-monitor-pane-{{ $session?->id ?? 'default' }}"
                style="display: none;">
                
                {{-- Console Header (sticky) - NO SCROLL --}}
                <div class="monitor-header-sticky bg-white border-bottom border-gray-300">
                    <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 gap-2">
                        {{-- Left: Title + Inline Metrics --}}
                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                            <h6 class="mb-0 text-gray-800 fw-bold d-flex align-items-center">
                                <i class="ki-duotone ki-code fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                Monitor
                            </h6>
                            
                            {{-- Inline Metrics (desktop only) --}}
                            <div class="text-muted fs-7 fw-normal d-none d-md-flex align-items-center gap-2">
                                <span>Status: <span id="monitor-status-{{ $monitorId }}" class="text-gray-800 fw-semibold">Idle</span></span>
                                <span>•</span>
                                <span>Tokens: <span id="monitor-tokens-{{ $monitorId }}" class="text-gray-800 fw-semibold">0</span></span>
                                <span>•</span>
                                <span>Chunks: <span id="monitor-chunks-{{ $monitorId }}" class="text-gray-800 fw-semibold">0</span></span>
                                <span>•</span>
                                <span id="monitor-duration-{{ $monitorId }}" class="text-gray-800 fw-semibold">0s</span>
                                <span>•</span>
                                <span id="monitor-cost-{{ $monitorId }}" class="text-gray-800 fw-semibold">$0.00</span>
                            </div>
                        </div>
                        
                        {{-- Right: Action Buttons --}}
                        <div class="d-flex gap-1 flex-shrink-0">
                            {{-- Download Logs --}}
                            <button type="button" 
                                    class="btn btn-icon btn-sm btn-light-success" 
                                    onclick="window.LLMMonitor.downloadLogs('{{ $monitorId }}')"
                                    data-bs-toggle="tooltip"
                                    title="Download logs">
                                <i class="ki-duotone ki-file-down fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>

                            {{-- Copy Logs --}}
                            <button type="button" 
                                    class="btn btn-icon btn-sm btn-light-primary" 
                                    onclick="window.LLMMonitor.copyLogs('{{ $monitorId }}')"
                                    data-bs-toggle="tooltip"
                                    title="Copy logs">
                                <i class="ki-duotone ki-copy fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>

                            {{-- Clear All --}}
                            <button type="button" 
                                    class="btn btn-icon btn-sm btn-light-warning" 
                                    onclick="window.LLMMonitor.clear('{{ $monitorId }}')"
                                    data-bs-toggle="tooltip"
                                    title="Clear all">
                                <i class="ki-duotone ki-trash fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </button>

                            {{-- Refresh --}}
                            <button type="button" 
                                    class="btn btn-icon btn-sm btn-light-info" 
                                    onclick="window.LLMMonitor.refresh('{{ $monitorId }}')"
                                    data-bs-toggle="tooltip"
                                    title="Refresh">
                                <i class="ki-duotone ki-arrows-circle fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>

                            {{-- Close --}}
                            <button type="button" 
                                    class="btn btn-icon btn-sm btn-light-dark" 
                                    @click="monitorOpen = false; localStorage.setItem('llm_chat_monitor_open_{{ $session?->id ?? 'default' }}', 'false')"
                                    data-bs-toggle="tooltip"
                                    title="Close">
                                <i class="ki-duotone ki-cross fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Mobile-only: Metrics in 2nd row --}}
                    <div class="d-md-none px-3 pb-2">
                        <div class="text-muted fs-7 fw-normal">
                            <div class="mb-1">
                                <span>Status: <span id="monitor-status-mobile-{{ $monitorId }}" class="text-gray-800 fw-semibold">Idle</span></span>
                                <span class="mx-2">•</span>
                                <span>Tokens: <span id="monitor-tokens-mobile-{{ $monitorId }}" class="text-gray-800 fw-semibold">0</span></span>
                                <span class="mx-2">•</span>
                                <span>Chunks: <span id="monitor-chunks-mobile-{{ $monitorId }}" class="text-gray-800 fw-semibold">0</span></span>
                            </div>
                            <div>
                                <span>Duration: <span id="monitor-duration-mobile-{{ $monitorId }}" class="text-gray-800 fw-semibold">0s</span></span>
                                <span class="mx-2">•</span>
                                <span>Cost: <span id="monitor-cost-mobile-{{ $monitorId }}" class="text-gray-800 fw-semibold">$0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Console Body (scrollable) - Black Background --}}
                <div class="monitor-console-body">
                    @include('llm-manager::components.chat.shared.monitor-console')
                </div>
            </div>
        @endif
    </div>
    
    {{-- Card Footer (fuera del split) - SIEMPRE VISIBLE --}}
    <div class="card-footer pt-4" id="kt_chat_messenger_footer">
        @include('llm-manager::components.chat.partials.input-form', ['configurations' => $configurations])
    </div>
</div>

{{-- Styles y scripts ahora en partials incluidos desde chat-workspace.blade.php --}}
