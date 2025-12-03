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
    <div class="split-horizontal-container" id="llm-split-view" x-data="splitResizer()" x-init="init()">
        {{-- CHAT PANE (70% default) - Solo mensajes con scroll --}}
        <div class="split-pane split-chat" id="split-chat-pane">
            <div class="card-body py-0" id="kt_chat_messenger_body">
                @include('llm-manager::components.chat.partials.messages-container')
            </div>
        </div>
        
        @if($showMonitor)
            {{-- RESIZER BAR (draggable) --}}
            <div 
                x-show="monitorOpen"
                class="split-resizer" 
                id="split-resizer"
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
                id="split-monitor-pane"
                style="display: none;">
                
                {{-- Console Header con botón cerrar --}}
                <div class="d-flex justify-content-between align-items-center mb-2 px-3 pt-3">
                    <h6 class="mb-0 text-gray-700">
                        <i class="ki-duotone ki-code fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        Monitor Console
                    </h6>
                    <button 
                        type="button" 
                        class="btn btn-sm btn-icon btn-light-dark" 
                        @click="monitorOpen = false; localStorage.setItem('llm_chat_monitor_open', 'false')"
                        data-bs-toggle="tooltip"
                        title="Cerrar Monitor">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </button>
                </div>
                
                {{-- Solo la consola (fondo negro) --}}
                <div class="px-3 pb-3" style="height: calc(100% - 60px);">
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
