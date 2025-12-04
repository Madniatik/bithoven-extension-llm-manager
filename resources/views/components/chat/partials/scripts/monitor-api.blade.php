{{--
    Monitor API JavaScript Loader
    
    Loads the modular monitor system
    Multi-instance API for LLM streaming monitoring
    Each session gets its own monitor instance
--}}

<script type="module">
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
        
        start() {
            this.currentMetrics = {
                tokens: 0,
                chunks: 0,
                cost: 0,
                duration: 0,
                startTime: Date.now()
            };
            
            this.ui.updateStatus('Streaming...', 'primary');
            
            this.durationInterval = setInterval(() => {
                if (this.currentMetrics.startTime) {
                    this.currentMetrics.duration = Math.floor((Date.now() - this.currentMetrics.startTime) / 1000);
                    this.ui.updateDuration(this.currentMetrics.duration);
                }
            }, 1000);
            
            this.ui.log('Stream started', 'success');
            this.emitEvent('llm-streaming-started', { timestamp: Date.now() });
        }
        
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
        
        complete(provider, model) {
            clearInterval(this.durationInterval);
            
            const costPerToken = 0.000002;
            this.currentMetrics.cost = this.currentMetrics.tokens * costPerToken;
            
            this.ui.updateCost(this.currentMetrics.cost);
            this.ui.updateStatus('Complete', 'success');
            
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
        
        error(message) {
            clearInterval(this.durationInterval);
            this.ui.updateStatus('Error', 'danger');
            this.ui.log(message, 'error');
            
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
            
            if (window.LLMMonitor._debugMode) {
                console.log(`[LLMMonitor] Auto-initialized monitor: ${sessionId}`);
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
            return null;
        }
        
        if (factory.get(sessionId)) {
            console.log(`[LLMMonitor] Monitor already initialized: ${sessionId}`);
            return factory.get(sessionId);
        }
        
        const monitor = factory.create(sessionId);
        monitor.init();
        console.log(`[LLMMonitor] Manually initialized monitor: ${sessionId}`);
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
        _debugMode: true, // Cambiar a false en producción
        
        /**
         * Set fallback session ID
         */
        setSession(sessionId) {
            this._currentSessionId = sessionId;
            if (this._debugMode) {
                console.log(`[LLMMonitor] Session set to: ${sessionId}`);
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
                if (this._debugMode) {
                    console.warn('[LLMMonitor] LLMMonitorFactory not found');
                }
                return null;
            }
            
            const monitor = window.LLMMonitorFactory.get(sid);
            
            if (!monitor && this._debugMode) {
                console.warn(`[LLMMonitor] No monitor instance found for session: ${sid}`);
            }
            
            return monitor;
        },
        
        /**
         * Start monitoring (optional sessionId)
         */
        start(sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) {
                monitor.start();
                if (this._debugMode) {
                    console.log(`[LLMMonitor] Started: ${sessionId || this._currentSessionId || 'default'}`);
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
         */
        complete(provider, model, sessionId = null) {
            const monitor = this._getMonitor(sessionId);
            if (monitor) {
                monitor.complete(provider, model);
                if (this._debugMode) {
                    console.log(`[LLMMonitor] Completed: ${sessionId || this._currentSessionId || 'default'}`);
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
                if (this._debugMode) {
                    console.error(`[LLMMonitor] Error: ${message}`);
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
</script>
