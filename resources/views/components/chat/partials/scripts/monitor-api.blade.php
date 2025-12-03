{{--
    Monitor API JavaScript - Factory Pattern
    
    Multi-instance API for LLM streaming monitoring
    Each session gets its own monitor instance
--}}

<script>
    window.LLMMonitorFactory = {
        instances: {},
        
        create(sessionId) {
            if (this.instances[sessionId]) {
                return this.instances[sessionId];
            }
            
            this.instances[sessionId] = {
                sessionId: sessionId,
                currentMetrics: {
                    tokens: 0,
                    chunks: 0,
                    cost: 0,
                    duration: 0,
                    startTime: null
                },
                history: [],
                durationInterval: null,
                
                // Helper to get element by dynamic ID
                getElement(baseId) {
                    return document.getElementById(`${baseId}-${this.sessionId}`);
                },
                
                init() {
                    // Load history from localStorage (unique per session)
                    const saved = localStorage.getItem(`llm_chat_monitor_history_${this.sessionId}`);
                    if (saved) {
                        this.history = JSON.parse(saved);
                        this.renderActivityTable();
                    }
                    this.log('Monitor ready', 'info');
                },
                
                start() {
                    this.currentMetrics = {
                        tokens: 0,
                        chunks: 0,
                        cost: 0,
                        duration: 0,
                        startTime: Date.now()
                    };
                    
                    // Update status
                    const statusEl = this.getElement('monitor-status');
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="badge badge-light-primary">Streaming...</span>';
                    }
                    
                    // Start duration counter
                    this.durationInterval = setInterval(() => {
                        if (this.currentMetrics.startTime) {
                            this.currentMetrics.duration = Math.floor((Date.now() - this.currentMetrics.startTime) / 1000);
                            const durationEl = this.getElement('monitor-duration');
                            if (durationEl) {
                                durationEl.textContent = this.currentMetrics.duration + 's';
                            }
                        }
                    }, 1000);
                    
                    this.log('Stream started', 'success');
                    
                    // Emit global event
                    window.dispatchEvent(new CustomEvent('llm-streaming-started', {
                        detail: {
                            sessionId: this.sessionId,
                            timestamp: Date.now()
                        }
                    }));
                },
                
                trackChunk(chunk, tokens = 0) {
                    this.currentMetrics.chunks++;
                    this.currentMetrics.tokens += tokens;
                    
                    const chunkEl = this.getElement('monitor-chunk-count');
                    const tokenEl = this.getElement('monitor-token-count');
                    
                    if (chunkEl) chunkEl.textContent = this.currentMetrics.chunks;
                    if (tokenEl) tokenEl.textContent = this.currentMetrics.tokens;
                    
                    this.log(`Chunk received: ${tokens} tokens`, 'info');
                    
                    // Emit global event
                    window.dispatchEvent(new CustomEvent('llm-streaming-chunk', {
                        detail: {
                            sessionId: this.sessionId,
                            chunk: chunk,
                            tokens: tokens,
                            totalTokens: this.currentMetrics.tokens,
                            totalChunks: this.currentMetrics.chunks
                        }
                    }));
                },
                
                complete(provider, model) {
                    clearInterval(this.durationInterval);
                    
                    // Calculate final cost (example rate: $0.002 per 1K tokens)
                    const costPerToken = 0.000002;
                    this.currentMetrics.cost = this.currentMetrics.tokens * costPerToken;
                    
                    const costEl = this.getElement('monitor-cost');
                    const statusEl = this.getElement('monitor-status');
                    
                    if (costEl) costEl.textContent = '$' + this.currentMetrics.cost.toFixed(4);
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="badge badge-light-success">Complete</span>';
                    }
                    
                    // Add to history
                    this.addToHistory({
                        timestamp: new Date().toISOString(),
                        provider: provider,
                        model: model,
                        tokens: this.currentMetrics.tokens,
                        cost: this.currentMetrics.cost,
                        duration: this.currentMetrics.duration
                    });
                    
                    this.log(`Stream complete: ${this.currentMetrics.tokens} tokens, $${this.currentMetrics.cost.toFixed(4)}`, 'success');
                    
                    // Emit global event
                    window.dispatchEvent(new CustomEvent('llm-streaming-completed', {
                        detail: {
                            sessionId: this.sessionId,
                            provider: provider,
                            model: model,
                            totalTokens: this.currentMetrics.tokens,
                            totalChunks: this.currentMetrics.chunks,
                            duration: this.currentMetrics.duration,
                            cost: this.currentMetrics.cost
                        }
                    }));
                },
                
                error(message) {
                    clearInterval(this.durationInterval);
                    const statusEl = this.getElement('monitor-status');
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="badge badge-light-danger">Error</span>';
                    }
                    this.log(message, 'error');
                    
                    // Emit global event
                    window.dispatchEvent(new CustomEvent('llm-streaming-error', {
                        detail: {
                            sessionId: this.sessionId,
                            error: message,
                            timestamp: Date.now()
                        }
                    }));
                },
                
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
                    
                    const logEntry = `<div class="${colors[type]}">[${timestamp}] ${message}</div>`;
                    logsEl.innerHTML += logEntry;
                    
                    // Auto-scroll
                    const consoleEl = this.getElement('monitor-console');
                    if (consoleEl) {
                        consoleEl.scrollTop = consoleEl.scrollHeight;
                    }
                },
                
                addToHistory(activity) {
                    this.history.unshift(activity);
                    
                    // Keep only last 10
                    if (this.history.length > 10) {
                        this.history = this.history.slice(0, 10);
                    }
                    
                    localStorage.setItem(`llm_chat_monitor_history_${this.sessionId}`, JSON.stringify(this.history));
                    this.renderActivityTable();
                },
                
                renderActivityTable() {
                    const tbody = this.getElement('monitor-activity-body');
                    if (!tbody) return;
                    
                    if (this.history.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500 py-4">No activity yet</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = this.history.map(activity => `
                        <tr>
                            <td class="ps-4">${new Date(activity.timestamp).toLocaleTimeString()}</td>
                            <td><span class="badge badge-light-primary">${activity.provider}</span></td>
                            <td>${activity.tokens.toLocaleString()}</td>
                            <td>$${activity.cost.toFixed(4)}</td>
                            <td>${activity.duration}s</td>
                        </tr>
                    `).join('');
                },
                
                refresh() {
                    this.renderActivityTable();
                    this.log('Monitor refreshed', 'info');
                },
                
                clear() {
                    if (confirm(`Clear monitoring data for session ${this.sessionId}?`)) {
                        this.history = [];
                        localStorage.removeItem(`llm_chat_monitor_history_${this.sessionId}`);
                        this.renderActivityTable();
                        
                        const logsEl = this.getElement('monitor-logs');
                        if (logsEl) {
                            logsEl.innerHTML = `<span class="text-gray-500">[Monitor ${this.sessionId} cleared]</span>`;
                        }
                        
                        this.reset();
                        
                        // Emit global event
                        window.dispatchEvent(new CustomEvent('llm-monitor-cleared', {
                            detail: {
                                sessionId: this.sessionId,
                                timestamp: Date.now()
                            }
                        }));
                    }
                },
                
                reset() {
                    clearInterval(this.durationInterval);
                    this.currentMetrics = { tokens: 0, chunks: 0, cost: 0, duration: 0, startTime: null };
                    
                    const tokenEl = this.getElement('monitor-token-count');
                    const durationEl = this.getElement('monitor-duration');
                    const chunkEl = this.getElement('monitor-chunk-count');
                    const costEl = this.getElement('monitor-cost');
                    const statusEl = this.getElement('monitor-status');
                    
                    if (tokenEl) tokenEl.textContent = '0';
                    if (durationEl) durationEl.textContent = '0s';
                    if (chunkEl) chunkEl.textContent = '0';
                    if (costEl) costEl.textContent = '$0.00';
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="badge badge-light-secondary">Idle</span>';
                    }
                }
            };
            
            return this.instances[sessionId];
        },
        
        get(sessionId) {
            return this.instances[sessionId];
        },
        
        // Get or create (convenience method)
        getOrCreate(sessionId) {
            return this.get(sessionId) || this.create(sessionId);
        }
    };
    
    // Initialize all monitors on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Find all monitor elements and initialize them
        document.querySelectorAll('.llm-monitor').forEach(monitorEl => {
            const sessionId = monitorEl.dataset.monitorId || 'default';
            const monitor = window.LLMMonitorFactory.create(sessionId);
            monitor.init();
        });
    });
    
    // Backward compatibility: window.LLMMonitor points to default instance
    Object.defineProperty(window, 'LLMMonitor', {
        get() {
            return window.LLMMonitorFactory.getOrCreate('default');
        }
    });
</script>
