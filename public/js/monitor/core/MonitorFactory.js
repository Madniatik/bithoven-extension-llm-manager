/**
 * Monitor Factory
 * Creates and manages multiple monitor instances (multi-window support)
 */

import MonitorInstance from './MonitorInstance.js';

class MonitorFactory {
    constructor() {
        this.instances = {};
    }

    /**
     * Create new monitor instance
     * @param {string} sessionId
     * @returns {MonitorInstance}
     */
    create(sessionId) {
        if (this.instances[sessionId]) {
            return this.instances[sessionId];
        }
        
        this.instances[sessionId] = new MonitorInstance(sessionId);
        return this.instances[sessionId];
    }

    /**
     * Get existing monitor instance
     * @param {string} sessionId
     * @returns {MonitorInstance|undefined}
     */
    get(sessionId) {
        return this.instances[sessionId];
    }

    /**
     * Get or create monitor instance (convenience method)
     * @param {string} sessionId
     * @returns {MonitorInstance}
     */
    getOrCreate(sessionId) {
        return this.get(sessionId) || this.create(sessionId);
    }

    /**
     * Destroy monitor instance
     * @param {string} sessionId
     */
    destroy(sessionId) {
        const instance = this.instances[sessionId];
        if (instance && instance.durationInterval) {
            clearInterval(instance.durationInterval);
        }
        delete this.instances[sessionId];
    }

    /**
     * Get all active instances
     * @returns {Array<string>}
     */
    getActiveInstances() {
        return Object.keys(this.instances);
    }
}

// Singleton instance
const factory = new MonitorFactory();

export default factory;
