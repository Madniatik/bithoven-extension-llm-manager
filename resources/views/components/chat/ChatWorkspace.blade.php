{{--
    LLM Chat Workspace Component
    
    Componente maestro con layout drawer collapsible para monitor
    
    Props (desde ChatWorkspace.php):
    - $session: LLMConversationSession|null
    - $configurations: Collection
    - $showMonitor: bool
    - $monitorOpen: bool
    - $messages: Collection (generado por componente)
    - $monitorId: string (generado por componente)
--}}

<div class="llm-chat-workspace" data-session-id="{{ $session?->id }}" x-data="chatWorkspace({{ $showMonitor ? 'true' : 'false' }}, {{ $monitorOpen ? 'true' : 'false' }})">
    <div class="row g-5">
        {{-- CHAT PRINCIPAL (Desktop: 100% o 60% | Mobile: 100%) --}}
        <div :class="showMonitor && monitorOpen ? 'col-lg-8' : 'col-12'" class="chat-column">
            <div class="card" id="kt_chat_messenger">
                {{-- Card Header --}}
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
                        
                        {{-- Monitor Toggle Button --}}
                        @if($showMonitor)
                            <button 
                                type="button" 
                                class="btn btn-sm btn-icon btn-light-primary" 
                                @click="toggleMonitor()"
                                data-bs-toggle="tooltip"
                                :title="monitorOpen ? 'Ocultar Monitor' : 'Mostrar Monitor'">
                                <i class="ki-duotone ki-chart-line-down fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        @endif
                    </div>
                </div>
                
                {{-- Card Body --}}
                <div class="card-body py-0" id="kt_chat_messenger_body">
                    @include('llm-manager::components.chat.partials.messages-container')
                </div>
                
                {{-- Card Footer --}}
                <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                    @include('llm-manager::components.chat.partials.input-form', ['configurations' => $configurations])
                </div>
            </div>
        </div>

        {{-- MONITOR DRAWER (Desktop: 40% collapsible | Mobile: Modal) --}}
        @if($showMonitor)
            {{-- Desktop: Columna collapsible --}}
            <div 
                x-show="monitorOpen" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-4"
                class="col-lg-4 d-none d-lg-block monitor-column"
                style="display: none;">
                @include('llm-manager::components.chat.shared.monitor')
            </div>

            {{-- Mobile: Modal --}}
            <div class="modal fade" id="monitorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ki-duotone ki-chart-line-down fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Monitor de Streaming
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('llm-manager::components.chat.shared.monitor')
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Raw Message Modal --}}
@include('llm-manager::components.chat.partials.modals.modal-raw-message')

{{-- Shared JavaScript Utilities --}}
@push('scripts')
    <script src="{{ asset('vendor/llm-manager/js/streaming-handler.js') }}"></script>
    <script src="{{ asset('vendor/llm-manager/js/metrics-calculator.js') }}"></script>
@endpush

{{-- Styles (partitioned) --}}
@include('llm-manager::components.chat.partials.styles.dependencies')
@include('llm-manager::components.chat.partials.styles.markdown')
@include('llm-manager::components.chat.partials.styles.buttons')
@include('llm-manager::components.chat.partials.styles.responsive')

{{-- Scripts (partitioned) --}}
@include('llm-manager::components.chat.partials.scripts.clipboard-utils')
@include('llm-manager::components.chat.partials.scripts.message-renderer')
@include('llm-manager::components.chat.partials.scripts.settings-manager')
@include('llm-manager::components.chat.partials.scripts.event-handlers')

{{-- Alpine.js Component for Chat Workspace --}}
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatWorkspace', (initialShowMonitor = false, initialMonitorOpen = false) => ({
        showMonitor: initialShowMonitor,
        monitorOpen: initialMonitorOpen,
        
        init() {
            // Cargar estado desde localStorage
            const saved = localStorage.getItem('llm_chat_monitor_open');
            if (saved !== null && this.showMonitor) {
                this.monitorOpen = saved === 'true';
            }
            
            console.log('[ChatWorkspace] Initialized', {
                showMonitor: this.showMonitor,
                monitorOpen: this.monitorOpen
            });
            
            // En móvil, vincular toggle con modal
            if (this.isMobile()) {
                this.$watch('monitorOpen', (value) => {
                    if (value) {
                        const modal = new bootstrap.Modal(document.getElementById('monitorModal'));
                        modal.show();
                    }
                });
            }
        },
        
        toggleMonitor() {
            this.monitorOpen = !this.monitorOpen;
            localStorage.setItem('llm_chat_monitor_open', this.monitorOpen);
            
            console.log('[ChatWorkspace] Monitor toggled:', this.monitorOpen);
        },
        
        isMobile() {
            return window.innerWidth < 992; // Bootstrap lg breakpoint
        }
    }));
});
</script>
@endpush
