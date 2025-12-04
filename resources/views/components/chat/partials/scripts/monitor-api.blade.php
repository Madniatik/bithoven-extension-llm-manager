{{--
    Monitor API JavaScript Loader
    
    Loads the modular monitor system
    Multi-instance API for LLM streaming monitoring
    Each session gets its own monitor instance
--}}

<script type="module">
    // Import monitor modules
    import MonitorStorage from '/vendor/llm-manager/js/monitor/core/MonitorStorage.js';
    import MonitorInstance from '/vendor/llm-manager/js/monitor/core/MonitorInstance.js';
    import MonitorFactory from '/vendor/llm-manager/js/monitor/core/MonitorFactory.js';
    
    // Make factory available globally
    window.LLMMonitorFactory = MonitorFactory;
    
    // Auto-initialize monitors on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Find all monitor elements and initialize them
        document.querySelectorAll('.llm-monitor').forEach(monitorEl => {
            const sessionId = monitorEl.dataset.monitorId || 'default';
            const monitor = MonitorFactory.create(sessionId);
            monitor.init();
        });
    });
    
    // Backward compatibility: window.LLMMonitor points to default instance
    if (!window.LLMMonitor) {
        Object.defineProperty(window, 'LLMMonitor', {
            get() {
                return MonitorFactory.getOrCreate('default');
            }
        });
    }
</script>
