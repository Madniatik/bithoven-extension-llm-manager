{{--
    LLM Chat Workspace Component
    
    Componente maestro con layout configurable para monitor
    
    Props (desde ChatWorkspace.php):
    - $session: LLMConversationSession|null
    - $configurations: Collection
    - $showMonitor: bool
    - $monitorOpen: bool
    - $monitorLayout: string ('sidebar' | 'split-horizontal')
    - $messages: Collection (generado por componente)
    - $monitorId: string (generado por componente)
--}}

<div class="llm-chat-workspace" 
     data-session-id="{{ $session?->id }}" 
     data-monitor-layout="{{ $monitorLayout }}"
     x-data="chatWorkspace({{ $showMonitor ? 'true' : 'false' }}, {{ $monitorOpen ? 'true' : 'false' }}, '{{ $monitorLayout }}')">
    
    @if($monitorLayout === 'split-horizontal')
        {{-- SPLIT HORIZONTAL LAYOUT: Chat arriba (70%), Monitor abajo (30%) --}}
        @include('llm-manager::components.chat.layouts.split-horizontal-layout')
    @else
        {{-- SIDEBAR LAYOUT: Monitor fijo derecha (60/40) --}}
        @include('llm-manager::components.chat.layouts.sidebar-layout')
    @endif
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
    Alpine.data('chatWorkspace', (initialShowMonitor = false, initialMonitorOpen = false, layout = 'sidebar') => ({
        showMonitor: initialShowMonitor,
        monitorOpen: initialMonitorOpen,
        layout: layout,
        
        init() {
            // Cargar estado desde localStorage
            const saved = localStorage.getItem('llm_chat_monitor_open');
            if (saved !== null && this.showMonitor) {
                this.monitorOpen = saved === 'true';
            }
            
            console.log('[ChatWorkspace] Initialized', {
                showMonitor: this.showMonitor,
                monitorOpen: this.monitorOpen,
                layout: this.layout
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
            
            console.log('[ChatWorkspace] Monitor toggled:', {
                open: this.monitorOpen,
                layout: this.layout
            });
        },
        
        isMobile() {
            return window.innerWidth < 992; // Bootstrap lg breakpoint
        }
    }));
});
</script>
@endpush
