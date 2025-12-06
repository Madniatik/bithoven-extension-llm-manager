/**
 * Copy Logs Action
 * Copies monitor logs to clipboard
 */

/**
 * Show themed toast notification
 * @param {object} options - Swal options
 */
function showToast(options) {
    const theme = document.documentElement.getAttribute('data-bs-theme');
    const isDark = theme === 'dark';
        
    const defaultOptions = {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        background: isDark ? '#1e1e2d' : '#ffffff',
        color: isDark ? '#ffffff' : '#181c32',
        iconColor: isDark ? '#ffffff' : '#181c32'
    };
    
    if (window.Swal) {
        Swal.fire({ ...defaultOptions, ...options });
    }
}

/**
 * Copy logs to clipboard
 * @param {string} sessionId
 * @param {MonitorUI} ui
 * @returns {Promise<boolean>}
 */
export async function copyLogs(sessionId, ui) {
    // Check if logs exist
    if (!ui.hasLogs()) {
        showToast({
            icon: 'warning',
            title: 'No Logs',
            text: 'Console is empty. Send a message first.'
        });
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
        showToast({
            icon: 'success',
            title: 'Copied!',
            text: 'Monitor logs copied to clipboard',
            timer: 2000
        });
        
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
        
        showToast({
            icon: 'error',
            title: 'Copy Failed',
            text: 'Could not copy to clipboard. Please try again.',
            timer: 3000
        });
        
        return false;
    }
}
