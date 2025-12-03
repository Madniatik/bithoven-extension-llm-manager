{{--
    Chat Workspace Alpine Component
    Lógica principal para manejar el estado del workspace (monitor toggle, mobile, etc.)
--}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Factory function: creates unique Alpine component per session
    const createChatWorkspace = (sessionId) => (initialShowMonitor = false, initialMonitorOpen = false, layout = 'sidebar', sid = null) => ({
        sessionId: sid || sessionId,
        showMonitor: initialShowMonitor,
        monitorOpen: initialMonitorOpen,
        layout: layout,
        
        init() {
            // Load saved state from localStorage (unique per session)
            const storageKey = `llm_chat_monitor_open_${this.sessionId}`;
            const saved = localStorage.getItem(storageKey);
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
            const storageKey = `llm_chat_monitor_open_${this.sessionId}`;
            localStorage.setItem(storageKey, this.monitorOpen);
            
            console.log('[ChatWorkspace] Monitor toggled:', {
                open: this.monitorOpen,
                layout: this.layout
            });
        },
        
        isMobile() {
            return window.innerWidth < 992; // Bootstrap lg breakpoint
        }
    });
    
    // ✅ FIX: Auto-register component for current session ID from DOM
    // Scan DOM BEFORE Alpine starts to find all session IDs
    document.querySelectorAll('[data-session-id]').forEach(el => {
        const sessionId = el.dataset.sessionId || 'default';
        const componentName = `chatWorkspace_${sessionId}`;
        Alpine.data(componentName, createChatWorkspace(sessionId));
        console.log(`[ChatWorkspace] Registered component: ${componentName}`);
    });
    
    // Fallback: Register default session
    if (!document.querySelector('[data-session-id]')) {
        Alpine.data('chatWorkspace_default', createChatWorkspace('default'));
        console.log('[ChatWorkspace] Registered component: chatWorkspace_default (fallback)');
    }
});
</script>
@endpush
