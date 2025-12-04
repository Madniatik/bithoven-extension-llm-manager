/**
 * Monitor Instance Class
 * Individual monitor for a specific chat session (multi-instance support)
 */

import MonitorStorage from './MonitorStorage.js';
import MonitorUI from '../ui/render.js';
import { clearLogs, clearAll } from '../actions/clear.js';
import { copyLogs } from '../actions/copy.js';
import { downloadLogs } from '../actions/download.js';

export default class MonitorInstance {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.storage = new MonitorStorage(sessionId);
        this.ui = new MonitorUI(sessionId);
        
        this.currentMetrics = {
            tokens: 0,
            chunks: 0,
            cost: 0,
            duration: 0,
            startTime: null
        };
        
        this.history = [];
        this.durationInterval = null;
    }

    /**
     * Initialize monitor (load history)
     */
    init() {
        this.history = this.storage.loadHistory();
        this.ui.renderActivityTable(this.history);
        this.ui.log('Monitor ready', 'info');
    }

    /**
     * Start streaming session
     */
    start() {
        this.currentMetrics = {
            tokens: 0,
            chunks: 0,
            cost: 0,
            duration: 0,
            startTime: Date.now()
        };
        
        this.ui.updateStatus('Streaming...', 'primary');
        
        // Start duration counter
        this.durationInterval = setInterval(() => {
            if (this.currentMetrics.startTime) {
                this.currentMetrics.duration = Math.floor((Date.now() - this.currentMetrics.startTime) / 1000);
                this.ui.updateDuration(this.currentMetrics.duration);
            }
        }, 1000);
        
        this.ui.log('Stream started', 'success');
        this.emitEvent('llm-streaming-started', { timestamp: Date.now() });
    }

    /**
     * Track received chunk
     * @param {string} chunk
     * @param {number} tokens
     */
    trackChunk(chunk, tokens = 0) {
        this.currentMetrics.chunks++;
        this.currentMetrics.tokens += tokens;
        
        this.ui.updateMetrics({
            chunks: this.currentMetrics.chunks,
            tokens: this.currentMetrics.tokens
        });
        
        this.ui.log(`Chunk received: ${tokens} tokens`, 'info');
        
        this.emitEvent('llm-streaming-chunk', {
            chunk,
            tokens,
            totalTokens: this.currentMetrics.tokens,
            totalChunks: this.currentMetrics.chunks
        });
    }

    /**
     * Complete streaming session
     * @param {string} provider
     * @param {string} model
     */
    complete(provider, model) {
        clearInterval(this.durationInterval);
        
        // Calculate final cost (example rate: $0.002 per 1K tokens)
        const costPerToken = 0.000002;
        this.currentMetrics.cost = this.currentMetrics.tokens * costPerToken;
        
        this.ui.updateCost(this.currentMetrics.cost);
        this.ui.updateStatus('Complete', 'success');
        
        // Add to history
        const activity = {
            timestamp: new Date().toISOString(),
            provider,
            model,
            tokens: this.currentMetrics.tokens,
            cost: this.currentMetrics.cost,
            duration: this.currentMetrics.duration
        };
        
        this.history = this.storage.addActivity(activity);
        this.ui.renderActivityTable(this.history);
        
        this.ui.log(`Stream complete: ${this.currentMetrics.tokens} tokens, $${this.currentMetrics.cost.toFixed(4)}`, 'success');
        
        this.emitEvent('llm-streaming-completed', {
            provider,
            model,
            totalTokens: this.currentMetrics.tokens,
            totalChunks: this.currentMetrics.chunks,
            duration: this.currentMetrics.duration,
            cost: this.currentMetrics.cost
        });
    }

    /**
     * Handle streaming error
     * @param {string} message
     */
    error(message) {
        clearInterval(this.durationInterval);
        this.ui.updateStatus('Error', 'danger');
        this.ui.log(message, 'error');
        
        this.emitEvent('llm-streaming-error', {
            error: message,
            timestamp: Date.now()
        });
    }

    /**
     * Refresh monitor UI
     */
    refresh() {
        this.ui.renderActivityTable(this.history);
        this.ui.log('Monitor refreshed', 'info');
    }

    /**
     * Reset metrics to initial state
     */
    reset() {
        clearInterval(this.durationInterval);
        this.currentMetrics = {
            tokens: 0,
            chunks: 0,
            cost: 0,
            duration: 0,
            startTime: null
        };
        
        this.ui.updateMetrics({ tokens: 0, chunks: 0 });
        this.ui.updateDuration(0);
        this.ui.updateCost(0);
        this.ui.updateStatus('Idle', 'secondary');
    }

    /**
     * Emit custom event
     * @param {string} eventName
     * @param {Object} detail
     */
    emitEvent(eventName, detail) {
        window.dispatchEvent(new CustomEvent(eventName, {
            detail: {
                sessionId: this.sessionId,
                ...detail
            }
        }));
    }

    // Action Methods (delegated to action modules)
    
    clearLogs() {
        return clearLogs(this.sessionId, this.ui);
    }

    clear() {
        return clearAll(this.sessionId, this.storage, this.ui, () => this.reset());
    }

    async copyLogs() {
        return copyLogs(this.sessionId, this.ui);
    }

    downloadLogs() {
        return downloadLogs(this.sessionId, this.ui);
    }
}
