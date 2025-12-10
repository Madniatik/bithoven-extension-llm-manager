/**
 * Chat Settings Component (Alpine.js)
 * 
 * Manages main tab switching and settings panel state.
 * 
 * @param {number|string} sessionId - Session ID (or 'default')
 * @returns {object} Alpine.js component data
 */
window.chatSettings = function(sessionId) {
    return {
        // Main tab state ('conversation' | 'settings')
        activeMainTab: 'conversation',
        
        /**
         * Initialize component
         */
        init() {
            console.log(`[Chat Settings ${sessionId}] Component initialized`);
            
            // NO persistir tab preference en localStorage (siempre empezar en 'conversation')
            this.activeMainTab = 'conversation';
            
            // Watch for tab changes (sin persistencia)
            this.$watch('activeMainTab', (value) => {
                console.log(`[Chat Settings ${sessionId}] Tab switched to: ${value}`);
                
                // Emit custom event for external integrations
                this.$dispatch('chat-tab-changed', {
                    sessionId: sessionId,
                    tab: value,
                    timestamp: Date.now()
                });
            });
        },
        
        /**
         * Switch to conversation tab
         */
        showConversation() {
            this.activeMainTab = 'conversation';
        },
        
        /**
         * Switch to settings tab
         */
        showSettings() {
            this.activeMainTab = 'settings';
        },
        
        /**
         * Check if conversation tab is active
         */
        isConversationActive() {
            return this.activeMainTab === 'conversation';
        },
        
        /**
         * Check if settings tab is active
         */
        isSettingsActive() {
            return this.activeMainTab === 'settings';
        }
    };
};

/**
 * Factory pattern for multi-instance support
 * Creates unique component instances per session
 */
window.chatSettingsFactory = {
    instances: {},
    
    /**
     * Create or get existing instance
     * 
     * @param {number|string} sessionId
     * @returns {object} Component instance
     */
    create(sessionId) {
        if (!this.instances[sessionId]) {
            this.instances[sessionId] = window.chatSettings(sessionId);
        }
        return this.instances[sessionId];
    },
    
    /**
     * Get existing instance
     * 
     * @param {number|string} sessionId
     * @returns {object|null} Component instance or null
     */
    get(sessionId) {
        return this.instances[sessionId] || null;
    },
    
    /**
     * Remove instance
     * 
     * @param {number|string} sessionId
     */
    destroy(sessionId) {
        delete this.instances[sessionId];
    }
};

/**
 * Register Alpine.js component globally
 * Usage in Blade: x-data="chatSettings(sessionId)"
 * where sessionId can be session ID number or 'default' for non-session chats
 */
document.addEventListener('alpine:init', () => {
    // Register component factory for each session
    window.Alpine.data('chatSettings', (sessionId) => window.chatSettings(sessionId));
});

/**
 * Initialize Bootstrap tooltips for chat settings
 */
document.addEventListener('DOMContentLoaded', () => {
    // Wait for menu to be rendered, then initialize tooltips
    setTimeout(() => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }, 500);
});

console.log('[Chat Settings] Alpine.js component registered');
