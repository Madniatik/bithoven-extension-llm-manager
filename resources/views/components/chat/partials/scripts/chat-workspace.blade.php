{{--
    Chat Workspace Alpine Component
    Lógica principal para manejar el estado del workspace (monitor toggle, tabs, etc.)
--}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Factory function: creates unique Alpine component per session
    const createChatWorkspace = (sessionId) => (initialShowMonitor = false, initialMonitorOpen = false, layout = 'sidebar', sid = null) => ({
        sessionId: sid || sessionId,
        showMonitor: initialShowMonitor,
        monitorOpen: initialMonitorOpen,
        activeTab: 'console', // Default tab: console
        layout: layout,
        logs: [], // Monitor logs array
        
        init() {
            // Load saved state from localStorage (unique per session)
            const storageKey = `llm_chat_monitor_open_${this.sessionId}`;
            const saved = localStorage.getItem(storageKey);
            if (saved !== null && this.showMonitor) {
                this.monitorOpen = saved === 'true';
            }
            
            // Load saved tab
            const tabKey = `llm_chat_monitor_tab_${this.sessionId}`;
            const savedTab = localStorage.getItem(tabKey);
            if (savedTab) {
                this.activeTab = savedTab;
            }
        },
        
        addLog(logEntry) {
            this.logs.push({
                ...logEntry,
                timestamp: logEntry.timestamp || new Date().toISOString(),
                id: Date.now() + Math.random()
            });
            
            // Keep only last 100 logs
            if (this.logs.length > 100) {
                this.logs.shift();
            }
            
            // Auto-scroll monitor console
            this.$nextTick(() => {
                const monitorConsole = document.getElementById(`monitor-console-${this.sessionId}`);
                if (monitorConsole) {
                    monitorConsole.scrollTop = monitorConsole.scrollHeight;
                }
            });
        },
        
        clearLogs() {
            this.logs = [];
        },
        
        toggleMonitor() {
            this.monitorOpen = !this.monitorOpen;
            const storageKey = `llm_chat_monitor_open_${this.sessionId}`;
            localStorage.setItem(storageKey, this.monitorOpen);
        },
        
        openMonitorTab(tab) {
            // Si ya está abierto con este tab, cerrar
            if (this.monitorOpen && this.activeTab === tab) {
                this.monitorOpen = false;
                localStorage.setItem(`llm_chat_monitor_open_${this.sessionId}`, 'false');
                return;
            }
            
            // Cambiar tab y abrir si estaba cerrado
            this.activeTab = tab;
            this.monitorOpen = true;
            localStorage.setItem(`llm_chat_monitor_tab_${this.sessionId}`, tab);
            localStorage.setItem(`llm_chat_monitor_open_${this.sessionId}`, 'true');
        }
    });
    
    // ✅ FIX: Auto-register component for current session ID from DOM
    // Scan DOM BEFORE Alpine starts to find all session IDs
    document.querySelectorAll('[data-session-id]').forEach(el => {
        const sessionId = el.dataset.sessionId || 'default';
        const componentName = `chatWorkspace_${sessionId}`;
        Alpine.data(componentName, createChatWorkspace(sessionId));
    });
    
    // Fallback: Register default session
    if (!document.querySelector('[data-session-id]')) {
        Alpine.data('chatWorkspace_default', createChatWorkspace('default'));
    }
});
</script>
@endpush
