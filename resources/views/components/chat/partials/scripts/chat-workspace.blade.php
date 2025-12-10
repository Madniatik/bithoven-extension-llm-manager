{{--
    Chat Workspace Alpine Component
    Lógica principal para manejar el estado del workspace (monitor toggle, tabs, etc.)
--}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Factory function: creates unique Alpine component per session
    const createChatWorkspace = (sessionId) => (initialShowMonitor = false, initialMonitorOpen = false, layout = 'sidebar', sid = null, mid = null) => ({
        sessionId: sid || sessionId,
        monitorId: mid || `monitor-${sid || sessionId}`,
        showMonitor: initialShowMonitor,
        monitorOpen: initialMonitorOpen,
        activeTab: 'console', // Default tab: console
        layout: layout,
        logs: [], // Monitor logs array
        
        init() {
            // NO persistir estado del monitor - siempre inicia cerrado
            this.monitorOpen = false;
            
            // Tab por defecto: console (sin persistencia)
            this.activeTab = 'console';
            
            // Update monitor header on tab change
            this.$watch('activeTab', (newTab) => {
                this.updateMonitorHeader(newTab);
            });
            
            // Update monitor header when monitor opens
            this.$watch('monitorOpen', (isOpen) => {
                if (isOpen) {
                    this.$nextTick(() => {
                        this.updateMonitorHeader(this.activeTab);
                    });
                }
            });
        },
        
        updateMonitorHeader(tab) {
            const monitorId = this.monitorId; // Use correct monitorId (e.g., 'monitor-39')
            const iconEl = document.getElementById(`monitor-header-icon-${monitorId}`);
            const textEl = document.getElementById(`monitor-header-text-${monitorId}`);
            
            console.log('[Monitor Header] Update attempt:', {
                tab,
                monitorId,
                iconElFound: !!iconEl,
                textElFound: !!textEl,
                sessionId: this.sessionId
            });
            
            if (!iconEl || !textEl) {
                console.warn('[Monitor Header] Elements not found:', {
                    iconId: `monitor-header-icon-${monitorId}`,
                    textId: `monitor-header-text-${monitorId}`
                });
                return;
            }
            
            const configs = {
                console: {
                    icon: 'ki-satellite',
                    title: 'Monitor'
                },
                activity: {
                    icon: 'ki-chart-simple',
                    title: 'Activity Logs'
                },
                request: {
                    icon: 'ki-data',
                    title: 'Request Inspector'
                }
            };
            
            const config = configs[tab] || configs.console;
            
            console.log('[Monitor Header] Applying config:', config);
            
            // Update icon (use getIcon helper format)
            iconEl.innerHTML = `<i class="ki-duotone ${config.icon} fs-2x me-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>`;
            
            // Update title
            textEl.textContent = config.title;
            
            console.log('[Monitor Header] Updated successfully');
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
            // NO persistir en localStorage
        },
        
        openMonitorTab(tab) {
            // Si ya está abierto con este tab, cerrar
            if (this.monitorOpen && this.activeTab === tab) {
                this.monitorOpen = false;
                return;
            }
            
            // Cambiar tab y abrir si estaba cerrado
            this.activeTab = tab;
            this.monitorOpen = true;
            // NO persistir en localStorage
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
