/**
 * Monitor UI Renderer
 * Handles all DOM updates for a monitor instance
 */

export default class MonitorUI {
    constructor(sessionId) {
        this.sessionId = sessionId;
    }

    /**
     * Get element by dynamic ID
     * @param {string} baseId
     * @returns {HTMLElement|null}
     */
    getElement(baseId) {
        return document.getElementById(`${baseId}-${this.sessionId}`);
    }

    /**
     * Log message to console
     * @param {string} message
     * @param {string} type - info|success|error|warning
     */
    log(message, type = 'info') {
        const logsEl = this.getElement('monitor-logs');
        if (!logsEl) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const colors = {
            info: 'text-gray-400',
            success: 'text-success',
            error: 'text-danger',
            warning: 'text-warning'
        };
        
        const logEntry = document.createElement('div');
        logEntry.className = colors[type];
        logEntry.setAttribute('data-timestamp', Date.now());
        logEntry.textContent = `[${timestamp}] ${message}`;
        
        logsEl.appendChild(logEntry);
        
        // Auto-scroll
        const consoleEl = this.getElement('monitor-console');
        if (consoleEl) {
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }
    }

    /**
     * Update metrics display
     * @param {Object} metrics - {tokens?, chunks?}
     */
    updateMetrics(metrics) {
        if (metrics.tokens !== undefined) {
            // Desktop/sidebar view
            const tokenEl = this.getElement('monitor-token-count');
            if (tokenEl) tokenEl.textContent = metrics.tokens;
            
            // Split-horizontal header view
            const tokensHeaderEl = this.getElement('monitor-tokens');
            if (tokensHeaderEl) tokensHeaderEl.textContent = metrics.tokens;
        }
        
        if (metrics.chunks !== undefined) {
            // Desktop/sidebar view
            const chunkEl = this.getElement('monitor-chunk-count');
            if (chunkEl) chunkEl.textContent = metrics.chunks;
            
            // Split-horizontal header view
            const chunksHeaderEl = this.getElement('monitor-chunks');
            if (chunksHeaderEl) chunksHeaderEl.textContent = metrics.chunks;
        }
    }

    /**
     * Update duration display
     * @param {number} duration - in seconds
     */
    updateDuration(duration) {
        // Desktop/sidebar view
        const durationEl = this.getElement('monitor-duration');
        if (durationEl) {
            durationEl.textContent = duration + 's';
        }
    }

    /**
     * Update cost display
     * @param {number} cost
     */
    updateCost(cost) {
        // Desktop/sidebar view
        const costEl = this.getElement('monitor-cost');
        if (costEl) {
            const costValue = parseFloat(cost) || 0;
            costEl.textContent = '$' + costValue.toFixed(4);
        }
    }

    /**
     * Update status badge
     * @param {string} text
     * @param {string} type - primary|success|danger|secondary
     */
    updateStatus(text, type) {
        // Desktop/sidebar view (with badge)
        const statusEl = this.getElement('monitor-status');
        if (statusEl) {
            statusEl.innerHTML = `<span class="badge badge-light-${type}">${text}</span>`;
        }
        
        // Split-horizontal header view (text only)
        const statusHeaderEl = this.getElement('monitor-status');
        if (statusHeaderEl && !statusHeaderEl.querySelector('.badge')) {
            statusHeaderEl.textContent = text;
        }
    }

    /**
     * Render activity history table
     * @param {Array} history
     */
    renderActivityTable(history) {
        const tbody = this.getElement('monitor-activity-body');
        if (!tbody) return;
        
        if (history.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500 py-4">No activity yet</td></tr>';
            return;
        }
        
        tbody.innerHTML = history.map(activity => {
            const costValue = parseFloat(activity.cost) || 0;
            return `
            <tr>
                <td class="ps-4">${new Date(activity.timestamp).toLocaleTimeString()}</td>
                <td><span class="badge badge-light-primary">${activity.provider}</span></td>
                <td>${activity.tokens.toLocaleString()}</td>
                <td>$${costValue.toFixed(4)}</td>
                <td>${activity.duration}s</td>
            </tr>
        `}).join('');
    }

    /**
     * Clear logs display (console only)
     */
    clearLogsDisplay() {
        const logsEl = this.getElement('monitor-logs');
        if (logsEl) {
            logsEl.innerHTML = '<span class="text-gray-500">Monitor ready. Send a message to see real-time activity...</span>';
        }
    }

    /**
     * Get all log entries as text
     * @returns {string}
     */
    getLogsAsText() {
        const logsEl = this.getElement('monitor-logs');
        if (!logsEl) return '';
        
        const logDivs = logsEl.querySelectorAll('div[data-timestamp]');
        return Array.from(logDivs).map(div => div.textContent).join('\n');
    }

    /**
     * Check if logs are empty
     * @returns {boolean}
     */
    hasLogs() {
        const logsEl = this.getElement('monitor-logs');
        if (!logsEl) return false;
        
        const logDivs = logsEl.querySelectorAll('div[data-timestamp]');
        return logDivs.length > 0;
    }
}
