/**
 * Monitor System - Main Entry Point
 * Multi-instance monitor system for LLM Quick Chat
 * 
 * Usage:
 *   const monitor = LLMMonitorFactory.create('session-123');
 *   monitor.init();
 *   monitor.start();
 *   monitor.trackChunk(chunk, tokens);
 *   monitor.complete(provider, model);
 * 
 * Export Actions:
 *   monitor.clearLogs();      // Clear console only
 *   monitor.copyLogs();       // Copy to clipboard
 *   monitor.downloadLogs();   // Download as .txt
 *   monitor.clear();          // Clear all (confirm dialog)
 */

import MonitorFactory from './core/MonitorFactory.js';

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
Object.defineProperty(window, 'LLMMonitor', {
    get() {
        return MonitorFactory.getOrCreate('default');
    }
});

// Export factory for module usage
export default MonitorFactory;
