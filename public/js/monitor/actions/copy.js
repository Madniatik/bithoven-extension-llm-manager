/**
 * Copy Logs Action
 * Copies monitor logs to clipboard
 */

/**
 * Copy logs to clipboard
 * @param {string} sessionId
 * @param {MonitorUI} ui
 * @returns {Promise<boolean>}
 */
export async function copyLogs(sessionId, ui) {
    // Check if logs exist
    if (!ui.hasLogs()) {
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'No Logs',
                text: 'Console is empty. Send a message first.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
        return false;
    }
    
    try {
        // Get logs as text
        const logsText = ui.getLogsAsText();
        
        // Add header
        const timestamp = new Date().toLocaleString();
        const fullText = `LLM Monitor Logs - Session ${sessionId}\nExported: ${timestamp}\n${'='.repeat(60)}\n\n${logsText}`;
        
        // Copy to clipboard
        await navigator.clipboard.writeText(fullText);
        
        // Success notification
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Monitor logs copied to clipboard',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }
        
        // Emit event
        window.dispatchEvent(new CustomEvent('llm-monitor-logs-copied', {
            detail: {
                sessionId,
                timestamp: Date.now(),
                linesCount: logsText.split('\n').length
            }
        }));
        
        return true;
        
    } catch (error) {
        console.error('Failed to copy logs:', error);
        
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Copy Failed',
                text: 'Could not copy to clipboard. Please try again.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
        
        return false;
    }
}
