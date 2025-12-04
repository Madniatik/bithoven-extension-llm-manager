/**
 * Download Logs Action
 * Downloads monitor logs as .txt file
 */

/**
 * Download logs as text file
 * @param {string} sessionId
 * @param {MonitorUI} ui
 * @returns {boolean}
 */
export function downloadLogs(sessionId, ui) {
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
        
        // Create blob
        const blob = new Blob([fullText], { type: 'text/plain;charset=utf-8' });
        
        // Create download link
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        
        // Filename: llm-monitor-session-{id}-{timestamp}.txt
        const fileTimestamp = new Date().toISOString().replace(/[:.]/g, '-').substring(0, 19);
        a.download = `llm-monitor-session-${sessionId}-${fileTimestamp}.txt`;
        
        // Trigger download
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        // Cleanup
        URL.revokeObjectURL(url);
        
        // Success notification
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Downloaded!',
                text: 'Monitor logs downloaded successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }
        
        // Emit event
        window.dispatchEvent(new CustomEvent('llm-monitor-logs-downloaded', {
            detail: {
                sessionId,
                timestamp: Date.now(),
                filename: a.download,
                linesCount: logsText.split('\n').length
            }
        }));
        
        return true;
        
    } catch (error) {
        console.error('Failed to download logs:', error);
        
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Download Failed',
                text: 'Could not download logs. Please try again.',
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
