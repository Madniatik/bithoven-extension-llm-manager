{{--
    Monitor API JavaScript
    
    Global API for LLM streaming monitoring
    Tracks metrics, manages history, handles console logging
--}}

<script>
    window.LLMMonitor = {
        currentMetrics: {
            tokens: 0,
            chunks: 0,
            cost: 0,
            duration: 0,
            startTime: null
        },
        history: [],
        durationInterval: null,
        
        init() {
            // Load history from localStorage
            const saved = localStorage.getItem('llm_chat_monitor_history');
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
            const statusEl = document.getElementById('monitor-status');
            if (statusEl) {
                statusEl.innerHTML = '<span class="badge badge-light-primary">Streaming...</span>';
            }
            
            // Start duration counter
            this.durationInterval = setInterval(() => {
                if (this.currentMetrics.startTime) {
                    this.currentMetrics.duration = Math.floor((Date.now() - this.currentMetrics.startTime) / 1000);
                    const durationEl = document.getElementById('monitor-duration');
                    if (durationEl) {
                        durationEl.textContent = this.currentMetrics.duration + 's';
                    }
                }
            }, 1000);
            
            this.log('Stream started', 'success');
        },
        
        trackChunk(chunk, tokens = 0) {
            this.currentMetrics.chunks++;
            this.currentMetrics.tokens += tokens;
            
            const chunkEl = document.getElementById('monitor-chunk-count');
            const tokenEl = document.getElementById('monitor-token-count');
            
            if (chunkEl) chunkEl.textContent = this.currentMetrics.chunks;
            if (tokenEl) tokenEl.textContent = this.currentMetrics.tokens;
            
            this.log(`Chunk received: ${tokens} tokens`, 'info');
        },
        
        complete(provider, model) {
            clearInterval(this.durationInterval);
            
            // Calculate final cost (example rate: $0.002 per 1K tokens)
            const costPerToken = 0.000002;
            this.currentMetrics.cost = this.currentMetrics.tokens * costPerToken;
            
            const costEl = document.getElementById('monitor-cost');
            const statusEl = document.getElementById('monitor-status');
            
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
        },
        
        error(message) {
            clearInterval(this.durationInterval);
            const statusEl = document.getElementById('monitor-status');
            if (statusEl) {
                statusEl.innerHTML = '<span class="badge badge-light-danger">Error</span>';
            }
            this.log(message, 'error');
        },
        
        log(message, type = 'info') {
            const logsEl = document.getElementById('monitor-logs');
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
            const consoleEl = document.getElementById('monitor-console');
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
            
            localStorage.setItem('llm_chat_monitor_history', JSON.stringify(this.history));
            this.renderActivityTable();
        },
        
        renderActivityTable() {
            const tbody = document.getElementById('monitor-activity-body');
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
            if (confirm('Clear all monitoring data?')) {
                this.history = [];
                localStorage.removeItem('llm_chat_monitor_history');
                this.renderActivityTable();
                
                const logsEl = document.getElementById('monitor-logs');
                if (logsEl) {
                    logsEl.innerHTML = '<span class="text-gray-500">[Monitor cleared]</span>';
                }
                
                this.reset();
            }
        },
        
        reset() {
            clearInterval(this.durationInterval);
            this.currentMetrics = { tokens: 0, chunks: 0, cost: 0, duration: 0, startTime: null };
            
            const tokenEl = document.getElementById('monitor-token-count');
            const durationEl = document.getElementById('monitor-duration');
            const chunkEl = document.getElementById('monitor-chunk-count');
            const costEl = document.getElementById('monitor-cost');
            const statusEl = document.getElementById('monitor-status');
            
            if (tokenEl) tokenEl.textContent = '0';
            if (durationEl) durationEl.textContent = '0s';
            if (chunkEl) chunkEl.textContent = '0';
            if (costEl) costEl.textContent = '$0.00';
            if (statusEl) {
                statusEl.innerHTML = '<span class="badge badge-light-secondary">Idle</span>';
            }
        }
    };
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        window.LLMMonitor.init();
    });
</script>
