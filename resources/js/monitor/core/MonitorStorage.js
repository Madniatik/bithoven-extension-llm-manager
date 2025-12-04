/**
 * Monitor Storage Manager
 * Handles localStorage operations for monitor history (multi-instance)
 */

export default class MonitorStorage {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.storageKey = `llm_chat_monitor_history_${sessionId}`;
    }

    /**
     * Load history from localStorage
     * @returns {Array}
     */
    loadHistory() {
        const saved = localStorage.getItem(this.storageKey);
        return saved ? JSON.parse(saved) : [];
    }

    /**
     * Save history to localStorage
     * @param {Array} history
     */
    saveHistory(history) {
        localStorage.setItem(this.storageKey, JSON.stringify(history));
    }

    /**
     * Clear history from localStorage
     */
    clearHistory() {
        localStorage.removeItem(this.storageKey);
    }

    /**
     * Add activity to history (maintains max 10 entries)
     * @param {Object} activity
     */
    addActivity(activity) {
        let history = this.loadHistory();
        history.unshift(activity);
        
        // Keep only last 10
        if (history.length > 10) {
            history = history.slice(0, 10);
        }
        
        this.saveHistory(history);
        return history;
    }
}
