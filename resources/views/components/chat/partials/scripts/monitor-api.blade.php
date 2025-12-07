{{--
    Monitor API JavaScript Loader
    
    Loads the modular monitor system
    Multi-instance API for LLM streaming monitoring
    Each session gets its own monitor instance
--}}

<script>
// ============================================================================
// PLACEHOLDER: Define window.LLMMonitor immediately to avoid "undefined" errors
// This gets replaced with the full API once modules load
// ============================================================================
window.LLMMonitor = {
    _loading: true,
    start: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.start() called (placeholder, modules loading...)');
    },
    trackChunk: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.trackChunk() called (placeholder, modules loading...)');
    },
    complete: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.complete() called (placeholder, modules loading...)');
    },
    error: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.error() called (placeholder, modules loading...)');
    },
    clearLogs: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.clearLogs() called (placeholder, modules loading...)');
    },
    copyLogs: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.copyLogs() called (placeholder, modules loading...)');
    },
    downloadLogs: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.downloadLogs() called (placeholder, modules loading...)');
    },
    refresh: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.refresh() called (placeholder, modules loading...)');
    },
    clear: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.clear() called (placeholder, modules loading...)');
    },
    setSession: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.setSession() called (placeholder, modules loading...)');
    },
    getInstance: () => {
        if (window.MonitorLogger) MonitorLogger.debug('LLMMonitor.getInstance() called (placeholder, modules loading...)');
        return null;
    }
};

window.LLMMonitorFactory = null;
window.initLLMMonitor = () => {};

// ============================================================================
// ASYNC INITIALIZATION: Load modules and replace placeholder with real API
// ============================================================================
(async function() {
    // Base path for monitor modules (extension public folder)
    const basePath = '/vendor/bithoven/llm-manager/js/monitor';
    
    // Import monitor modules
    const { default: MonitorStorage } = await import(`${basePath}/core/MonitorStorage.js`);
    const { default: MonitorUI } = await import(`${basePath}/ui/render.js`);
    const { clearLogs, clearAll } = await import(`${basePath}/actions/clear.js`);
    const { copyLogs } = await import(`${basePath}/actions/copy.js`);
    const { downloadLogs } = await import(`${basePath}/actions/download.js`);
    
    // MonitorInstance class (inline because it needs all imports)
    class MonitorInstance {
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
        
        init() {
            this.history = this.storage.loadHistory();
            this.ui.renderActivityTable(this.history);
            this.ui.log('Monitor ready', 'info');
        }
        
        start(provider = null, model = null) {
            this.currentMetrics = {
                tokens: 0,
                chunks: 0,
                cost: 0,
                duration: 0,
                startTime: Date.now(),
                provider: provider,
                model: model
            };
            
            this.ui.updateStatus('Streaming...', 'primary');
            
            this.durationInterval = setInterval(() => {
                if (this.currentMetrics.startTime) {
                    this.currentMetrics.duration = Math.floor((Date.now() - this.currentMetrics.startTime) / 1000);
                    this.ui.updateDuration(this.currentMetrics.duration);
                }
            }, 1000);
            
            // Structured logging with REQUEST DETAILS section
            this.ui.log('━'.repeat(60), 'separator');
            this.ui.log('🚀 STREAM STARTED', 'header');
            this.ui.log('━'.repeat(60), 'separator');
            
            if (provider || model) {
                this.ui.log('', 'info'); // Empty line
                this.ui.log('📤 REQUEST DETAILS:', 'info');
                if (provider) {
                    this.ui.log(`🔌 Provider: ${provider}`, 'debug');
                }
                if (model) {
                    this.ui.log(`✅ Model: ${model}`, 'debug');
                }
                this.ui.log('⏳ Waiting for response...', 'info');
            } else {
                this.ui.log('⏳ Waiting for response...', 'info');
            }
            
            this.emitEvent('llm-streaming-started', { timestamp: Date.now(), provider, model });
        }
        
        trackChunk(chunk, tokens = 0) {
            this.currentMetrics.chunks++;
            this.currentMetrics.tokens += tokens;
            
            this.ui.updateMetrics({
                chunks: this.currentMetrics.chunks,
                tokens: this.currentMetrics.tokens
            });
            
            // Milestone logging (primeros 10 chunks, luego cada 10)
            if (this.currentMetrics.chunks <= 10 || this.currentMetrics.chunks % 10 === 0) {
                const preview = chunk.length > 30 
                    ? chunk.substring(0, 30) + '...' 
                    : chunk;
                this.ui.log(`📥 CHUNK #${this.currentMetrics.chunks}: "${preview}"`, 'chunk');
            }
            
            // Token milestones (cada 50 tokens)
            if (this.currentMetrics.tokens % 50 === 0 && this.currentMetrics.tokens > 0) {
                this.ui.log(`📊 Tokens received so far: ${this.currentMetrics.tokens}`, 'info');
            }
            
            this.emitEvent('llm-streaming-chunk', {
                chunk,
                tokens,
                totalTokens: this.currentMetrics.tokens,
                totalChunks: this.currentMetrics.chunks
            });
        }
        
        complete(provider = null, model = null, usage = null, cost = null, executionTimeMs = null) {
            clearInterval(this.durationInterval);
            
            // Calculate cost if not provided
            if (!cost && this.currentMetrics.tokens) {
                const costPerToken = 0.000002;
                this.currentMetrics.cost = this.currentMetrics.tokens * costPerToken;
            } else if (cost) {
                this.currentMetrics.cost = cost;
            }
            
            this.ui.updateCost(this.currentMetrics.cost);
            this.ui.updateStatus('Complete', 'success');
            
            // Use provider/model from metrics if not passed
            const finalProvider = provider || this.currentMetrics.provider || 'unknown';
            const finalModel = model || this.currentMetrics.model || 'unknown';
            
            const activity = {
                timestamp: new Date().toISOString(),
                provider: finalProvider,
                model: finalModel,
                tokens: this.currentMetrics.tokens,
                cost: this.currentMetrics.cost,
                duration: this.currentMetrics.duration
            };
            
            this.history = this.storage.addActivity(activity);
            this.ui.renderActivityTable(this.history);
            
            // Structured logging with FINAL METRICS section
            this.ui.log('', 'info'); // Empty line
            this.ui.log('━'.repeat(60), 'separator');
            this.ui.log('✅ STREAM COMPLETED', 'header');
            this.ui.log('━'.repeat(60), 'separator');
            this.ui.log('', 'info'); // Empty line
            
            this.ui.log('📊 FINAL METRICS:', 'info');
            
            if (usage) {
                this.ui.log(`📝 Prompt tokens: ${usage.prompt_tokens || 0}`, 'debug');
                this.ui.log(`✍️  Completion tokens: ${usage.completion_tokens || 0}`, 'debug');
                this.ui.log(`📦 Total tokens: ${usage.total_tokens || this.currentMetrics.tokens}`, 'debug');
            } else {
                this.ui.log(`📦 Total tokens: ${this.currentMetrics.tokens}`, 'debug');
            }
            
            const costValue = parseFloat(this.currentMetrics.cost) || 0;
            this.ui.log(`💰 Cost: $${costValue.toFixed(6)}`, 'debug');
            
            if (executionTimeMs) {
                this.ui.log(`⚡ Execution time: ${executionTimeMs}ms`, 'debug');
            }
            
            this.ui.log(`🔢 Chunks received: ${this.currentMetrics.chunks}`, 'debug');
            this.ui.log(`⏱️  Total duration: ${this.currentMetrics.duration}s`, 'debug');
            this.ui.log('', 'info'); // Empty line
            
            this.emitEvent('llm-streaming-completed', {
                provider: finalProvider,
                model: finalModel,
                totalTokens: this.currentMetrics.tokens,
                totalChunks: this.currentMetrics.chunks,
                duration: this.currentMetrics.duration,
                cost: this.currentMetrics.cost,
                usage,
                executionTimeMs
            });
        }
        
        error(message) {
            clearInterval(this.durationInterval);
            this.ui.updateStatus('Error', 'danger');
            
            // Structured error logging
            this.ui.log('', 'info'); // Empty line
            this.ui.log('━'.repeat(60), 'separator');
            this.ui.log('❌ ERROR OCCURRED', 'header');
            this.ui.log('━'.repeat(60), 'separator');
            this.ui.log('', 'info'); // Empty line
            this.ui.log(`⚠️  ${message}`, 'error');
            this.ui.log('', 'info'); // Empty line
            
            this.emitEvent('llm-streaming-error', {
                error: message,
                timestamp: Date.now()
            });
        }
        
        refresh() {
            this.ui.renderActivityTable(this.history);
            this.ui.log('Monitor refreshed', 'info');
        }
        
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
        
        emitEvent(eventName, detail) {
            window.dispatchEvent(new CustomEvent(eventName, {
                detail: {
                    sessionId: this.sessionId,
                    ...detail
                }
            }));
        }
        
        // Action Methods
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
    
    // MonitorFactory (Singleton)
    class MonitorFactory {
        constructor() {
            this.instances = {};
        }
        
        create(sessionId) {
            if (this.instances[sessionId]) {
                return this.instances[sessionId];
            }
            
            this.instances[sessionId] = new MonitorInstance(sessionId);
            return this.instances[sessionId];
        }
        
        get(sessionId) {
            return this.instances[sessionId];
        }
        
        getOrCreate(sessionId) {
            return this.get(sessionId) || this.create(sessionId);
        }
        
        destroy(sessionId) {
            const instance = this.instances[sessionId];
            if (instance && instance.durationInterval) {
                clearInterval(instance.durationInterval);
            }
            delete this.instances[sessionId];
        }
        
        getActiveInstances() {
            return Object.keys(this.instances);
        }
    }
    
    // Create singleton instance
    const factory = new MonitorFactory();
    
    // Make factory available globally
    window.LLMMonitorFactory = factory;
    
    // Auto-initialize monitors (immediate + delayed for Alpine.js)
    function initializeMonitors() {
        document.querySelectorAll('.llm-monitor').forEach(monitorEl => {
            const sessionId = monitorEl.dataset.monitorId || 'default';
            
            // Skip if already initialized
            if (factory.get(sessionId)) {
                return;
            }
            
            const monitor = factory.create(sessionId);
            monitor.init();
            
            // Log initialization
            console.log(`[LLMMonitor] Auto-initialized monitor: ${sessionId}`);
            if (window.MonitorLogger) {
                MonitorLogger.info(`Monitor auto-initialized: ${sessionId}`);
            }
        });
    }
    
    // Try immediate initialization
    initializeMonitors();
    
    // Retry on DOMContentLoaded (for late-loaded elements)
    document.addEventListener('DOMContentLoaded', initializeMonitors);
    
    // Retry after Alpine.js initialization (for x-show elements)
    document.addEventListener('alpine:init', () => {
        setTimeout(initializeMonitors, 100);
    });
    
    // Expose manual initialization helper
    window.initLLMMonitor = function(sessionId) {
        const monitorEl = document.querySelector(`.llm-monitor[data-monitor-id="${sessionId}"]`);
        if (!monitorEl) {
            console.warn(`[LLMMonitor] No monitor element found for session: ${sessionId}`);
            if (window.MonitorLogger) {
                MonitorLogger.warn(`No monitor element found for session: ${sessionId}`);
            }
            return null;
        }
        
        if (factory.get(sessionId)) {
            console.log(`[LLMMonitor] Monitor already initialized: ${sessionId}`);
            if (window.MonitorLogger) {
                MonitorLogger.debug(`Monitor already initialized: ${sessionId}`);
            }
            return factory.get(sessionId);
        }
        
        const monitor = factory.create(sessionId);
        monitor.init();
        console.log(`[LLMMonitor] Manually initialized monitor: ${sessionId}`);
        if (window.MonitorLogger) {
            MonitorLogger.info(`Monitor manually initialized: ${sessionId}`);
        }
        return monitor;
    };
    
    // Backward compatibility: window.LLMMonitor points to default instance
    // ====================================================================
    // HYBRID ADAPTER PATTERN (Opción 3)
    // Permite llamadas con/sin sessionId explícito
    // Soporta múltiples chats simultáneos en misma página
    // ====================================================================
    window.LLMMonitor = {
        _currentSessionId: null,
        
        /**
         * Set fallback session ID
         */
        setSession(sessionId) {
            this._currentSessionId = sessionId;
            if (window.MonitorLogger) {
                MonitorLogger.info(`Session set to: ${sessionId}`);
            }
        },
        
        /**
         * Get monitor instance (with auto-detection)
         */
        _getMonitor(sessionId) {
            // Priority order:
            // 1. Explicit sessionId parameter
            // 2. Fallback _currentSessionId
            // 3. Default 'default'
            const sid = sessionId || this._currentSessionId || 'default';
            
            if (!window.LLMMonitorFactory) {
                if (window.MonitorLogger) {
                    MonitorLogger.warn('LLMMonitorFactory not found');
                }
                return null;
            }
            
            const monitor = window.LLMMonitorFactory.get(sid);
            
            if (!monitor && window.MonitorLogger) {
                MonitorLogger.warn('No monitor instance found for session:', sid);
            }
            
            return monitor;
        },
        
        /**
         * Start monitoring (optional sessionId)
         * @param {string|null} provider - LLM provider name
         * @param {string|null} model - LLM model name
         * @param {string|null} sessionId - Optional session ID
         */
        start(provider = null, model = null, sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            const sid = sessionId || this._currentSessionId || 'default';
            if (monitor) {
                monitor.start(provider, model);
                if (window.MonitorLogger) {
                    MonitorLogger.info(`LLMMonitor started for session: ${sid} (${provider}/${model})`);
                }
            }
        },
        
        /**
         * Track chunk (optional sessionId)
         */
        trackChunk(chunk, tokens = 0, sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) {
                monitor.trackChunk(chunk, tokens);
            }
        },
        
        /**
         * Complete monitoring (optional sessionId)
         * @param {string|null} provider - LLM provider name
         * @param {string|null} model - LLM model name
         * @param {object|null} usage - Token usage object {prompt_tokens, completion_tokens, total_tokens}
         * @param {number|null} cost - Cost in USD
         * @param {number|null} executionTimeMs - Execution time in milliseconds
         * @param {string|null} sessionId - Optional session ID
         */
        complete(provider = null, model = null, usage = null, cost = null, executionTimeMs = null, sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            const sid = sessionId || this._currentSessionId || 'default';
            if (monitor) {
                monitor.complete(provider, model, usage, cost, executionTimeMs);
                if (window.MonitorLogger) {
                    MonitorLogger.info(`LLMMonitor completed for session: ${sid} (${provider}/${model})`);
                }
            }
        },
        
        /**
         * Log error (optional sessionId)
         */
        error(message, sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) {
                monitor.error(message);
                if (window.MonitorLogger) {
                    MonitorLogger.error(`LLMMonitor error: ${message}`);
                }
            }
        },
        
        /**
         * Clear logs (optional sessionId)
         */
        clearLogs(sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) monitor.clearLogs();
        },
        
        /**
         * Copy logs (optional sessionId)
         */
        copyLogs(sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) monitor.copyLogs();
        },
        
        /**
         * Download logs (optional sessionId)
         */
        downloadLogs(sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) monitor.downloadLogs();
        },
        
        /**
         * Refresh monitor UI (optional sessionId)
         */
        refresh(sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor && monitor.refresh) monitor.refresh();
        },
        
        /**
         * Clear all data (optional sessionId)
         */
        clear(sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor && monitor.clear) monitor.clear();
        },
        
        /**
         * Get monitor instance directly (for advanced usage)
         */
        getInstance(sessionId = null) {
            return this._getMonitor(sessionId);
        }
    };
})();
</script>
