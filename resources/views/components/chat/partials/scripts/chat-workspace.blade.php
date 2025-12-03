{{--
    Chat Workspace Alpine Component
    Lógica principal para manejar el estado del workspace (monitor toggle, mobile, etc.)
--}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatWorkspace', (initialShowMonitor = false, initialMonitorOpen = false, layout = 'sidebar') => ({
        showMonitor: initialShowMonitor,
        monitorOpen: initialMonitorOpen,
        layout: layout,
        
        init() {
            // Load saved state from localStorage
            const saved = localStorage.getItem('llm_chat_monitor_open');
            if (saved !== null && this.showMonitor) {
                this.monitorOpen = saved === 'true';
            }
            
            console.log('[ChatWorkspace] Initialized', {
                showMonitor: this.showMonitor,
                monitorOpen: this.monitorOpen,
                layout: this.layout
            });
            
            // On mobile, bind toggle to modal
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
