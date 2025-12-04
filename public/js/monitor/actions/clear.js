/**
 * Clear Actions
 * Handles clearing logs and complete monitor reset
 */

/**
 * Clear logs only (preserve metrics and history)
 * @param {string} sessionId
 * @param {MonitorUI} ui
 */
export function clearLogs(sessionId, ui) {
    ui.clearLogsDisplay();
    ui.log('Console cleared', 'info');
    
    // Emit event
    window.dispatchEvent(new CustomEvent('llm-monitor-logs-cleared', {
        detail: {
            sessionId,
            timestamp: Date.now()
        }
    }));
    
    // Success notification
    if (window.Swal) {
        Swal.fire({
            icon: 'success',
            title: 'Console Cleared',
            text: 'Monitor logs cleared successfully',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }
}

/**
 * Clear all monitor data (logs, history, metrics)
 * @param {string} sessionId
 * @param {MonitorStorage} storage
 * @param {MonitorUI} ui
 * @param {Function} resetCallback
 */
export function clearAll(sessionId, storage, ui, resetCallback) {
    if (!confirm(`Clear all monitoring data for session ${sessionId}?\n\nThis will remove:\n- Console logs\n- Activity history\n- Current metrics`)) {
        return;
    }
    
    // Clear storage
    storage.clearHistory();
    
    // Clear UI
    ui.clearLogsDisplay();
    ui.renderActivityTable([]);
    
    // Reset metrics
    if (resetCallback) {
        resetCallback();
    }
    
    ui.log(`Monitor ${sessionId} cleared`, 'warning');
    
    // Emit event
    window.dispatchEvent(new CustomEvent('llm-monitor-cleared', {
        detail: {
            sessionId,
            timestamp: Date.now()
        }
    }));
    
    // Success notification
    if (window.Swal) {
        Swal.fire({
            icon: 'success',
            title: 'Monitor Cleared',
            text: 'All monitoring data has been cleared',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }
}
