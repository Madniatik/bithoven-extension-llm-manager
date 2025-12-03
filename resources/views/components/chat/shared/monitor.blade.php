{{--
    LLM Monitor Component (Shared)
    
    Real-time monitoring for LLM streaming sessions
    Features: metrics tracking, activity history, console logs
--}}

<div class="llm-monitor">
    {{-- Monitor Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Monitor</h5>
        <div>
            <button class="btn btn-sm btn-light-primary" onclick="window.LLMMonitor.refresh()">
                <i class="ki-duotone ki-arrows-circle fs-5"><span class="path1"></span><span class="path2"></span></i>
                Refresh
            </button>
            <button class="btn btn-sm btn-light-danger" onclick="window.LLMMonitor.clear()">
                <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                Clear
            </button>
        </div>
    </div>

    {{-- Real-time Metrics --}}
    <div class="card bg-light-info mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Tokens</div>
                    <div class="fs-3 fw-bold" id="monitor-token-count">0</div>
                </div>
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Duration</div>
                    <div class="fs-3 fw-bold" id="monitor-duration">0s</div>
                </div>
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Chunks</div>
                    <div class="fs-3 fw-bold" id="monitor-chunk-count">0</div>
                </div>
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Cost</div>
                    <div class="fs-3 fw-bold" id="monitor-cost">$0.00</div>
                </div>
            </div>
            <div class="mt-3">
                <div class="fs-7 text-gray-600 mb-1">Status</div>
                <div id="monitor-status">
                    <span class="badge badge-light-secondary">Idle</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity History --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Activity History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-row-bordered align-middle gy-4 gs-9" id="monitor-activity-table">
                    <thead class="border-gray-200 fs-7 fw-bold bg-light">
                        <tr>
                            <th class="ps-4">Time</th>
                            <th>Provider</th>
                            <th>Tokens</th>
                            <th>Cost</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody id="monitor-activity-body" class="fs-7">
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">No activity yet</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Console Logs --}}
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Console</h6>
        </div>
        <div class="card-body p-0">
            <div id="monitor-console" class="bg-dark text-white p-3 rounded" style="height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                <div id="monitor-logs">
                    <span class="text-gray-500">[Monitor initialized]</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monitor JavaScript API --}}
@push('scripts')
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
            document.getElementById('monitor-status').innerHTML = 
                '<span class="badge badge-light-primary">Streaming...</span>';
            
            // Start duration counter
            this.durationInterval = setInterval(() => {
                if (this.currentMetrics.startTime) {
                    this.currentMetrics.duration = Math.floor((Date.now() - this.currentMetrics.startTime) / 1000);
                    document.getElementById('monitor-duration').textContent = this.currentMetrics.duration + 's';
                }
            }, 1000);
            
            this.log('Stream started', 'success');
        },
        
        trackChunk(chunk, tokens = 0) {
            this.currentMetrics.chunks++;
            this.currentMetrics.tokens += tokens;
            
            document.getElementById('monitor-chunk-count').textContent = this.currentMetrics.chunks;
            document.getElementById('monitor-token-count').textContent = this.currentMetrics.tokens;
            
            this.log(`Chunk received: ${tokens} tokens`, 'info');
        },
        
        complete(provider, model) {
            clearInterval(this.durationInterval);
            
            // Calculate final cost (example rate: $0.002 per 1K tokens)
            const costPerToken = 0.000002;
            this.currentMetrics.cost = this.currentMetrics.tokens * costPerToken;
            
            document.getElementById('monitor-cost').textContent = '$' + this.currentMetrics.cost.toFixed(4);
            document.getElementById('monitor-status').innerHTML = 
                '<span class="badge badge-light-success">Complete</span>';
            
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
            document.getElementById('monitor-status').innerHTML = 
                '<span class="badge badge-light-danger">Error</span>';
            this.log(message, 'error');
        },
        
        log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const colors = {
                info: 'text-gray-400',
                success: 'text-success',
                error: 'text-danger',
                warning: 'text-warning'
            };
            
            const logEntry = `<div class="${colors[type]}">[${timestamp}] ${message}</div>`;
            document.getElementById('monitor-logs').innerHTML += logEntry;
            
            // Auto-scroll
            const console = document.getElementById('monitor-console');
            console.scrollTop = console.scrollHeight;
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
                document.getElementById('monitor-logs').innerHTML = '<span class="text-gray-500">[Monitor cleared]</span>';
                this.reset();
            }
        },
        
        reset() {
            clearInterval(this.durationInterval);
            this.currentMetrics = { tokens: 0, chunks: 0, cost: 0, duration: 0, startTime: null };
            document.getElementById('monitor-token-count').textContent = '0';
            document.getElementById('monitor-duration').textContent = '0s';
            document.getElementById('monitor-chunk-count').textContent = '0';
            document.getElementById('monitor-cost').textContent = '$0.00';
            document.getElementById('monitor-status').innerHTML = '<span class="badge badge-light-secondary">Idle</span>';
        }
    };
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        window.LLMMonitor.init();
    });
</script>
@endpush
