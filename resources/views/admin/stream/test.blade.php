<x-default-layout>
    @section('title', 'LLM Streaming Test')

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.stream.test') }}
    @endsection

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header">
            <h3 class="card-title">Real-Time Streaming Test</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-primary">SSE (Server-Sent Events)</span>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body">
            <!--begin::Form-->
            <form id="streamForm">
                <!--begin::Configuration-->
                <div class="mb-10">
                    <label class="form-label required">LLM Configuration</label>
                    <select class="form-select" name="configuration_id" id="configuration_id" required>
                        <option value="">Select configuration...</option>
                        @foreach($configurations as $config)
                            <option value="{{ $config->id }}">
                                {{ $config->name }} ({{ $config->provider }} - {{ $config->model }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Select an active LLM configuration for streaming</div>
                </div>
                <!--end::Configuration-->

                <!--begin::Prompt-->
                <div class="mb-10">
                    <label class="form-label required">Prompt</label>
                    <textarea 
                        class="form-control" 
                        name="prompt" 
                        id="prompt" 
                        rows="4" 
                        placeholder="Enter your prompt here..."
                        required
                    >Write a short story about a robot learning to feel emotions.</textarea>
                </div>
                <!--end::Prompt-->

                <!--begin::Parameters-->
                <div class="row mb-10">
                    <div class="col-md-6">
                        <label class="form-label">Temperature</label>
                        <input 
                            type="number" 
                            class="form-control" 
                            name="temperature" 
                            id="temperature" 
                            min="0" 
                            max="2" 
                            step="0.1" 
                            value="0.7"
                        >
                        <div class="form-text">Controls randomness (0 = focused, 2 = creative)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Tokens</label>
                        <input 
                            type="number" 
                            class="form-control" 
                            name="max_tokens" 
                            id="max_tokens" 
                            min="1" 
                            max="4000" 
                            value="1000"
                        >
                        <div class="form-text">Maximum response length</div>
                    </div>
                </div>
                <!--end::Parameters-->

                <!--begin::Actions-->
                <div class="d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-light" id="clearBtn">
                        <i class="ki-outline ki-trash fs-2"></i>
                        Clear Response
                    </button>
                    <button type="submit" class="btn btn-primary" id="startBtn">
                        <i class="ki-outline ki-rocket fs-2"></i>
                        Start Streaming
                    </button>
                    <button type="button" class="btn btn-danger d-none" id="stopBtn">
                        <i class="ki-outline ki-cross-circle fs-2"></i>
                        Stop
                    </button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->

            <!--begin::Stats-->
            <div class="mt-10 p-5 bg-light rounded" id="stats" style="display: none;">
                <div class="row text-center g-5">
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold text-primary" id="tokenCount">0</div>
                        <div class="text-muted fs-7">Tokens</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold text-success" id="chunkCount">0</div>
                        <div class="text-muted fs-7">Chunks</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold text-info" id="duration">0s</div>
                        <div class="text-muted fs-7">Duration</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold text-warning" id="cost">$0.00</div>
                        <div class="text-muted fs-7">Cost</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-6 fw-bold text-dark" id="logId">-</div>
                        <div class="text-muted fs-7">Log ID</div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-light-primary d-none" id="viewLogBtn">
                            <i class="ki-outline ki-eye fs-4"></i>
                            View Log
                        </button>
                    </div>
                </div>
            </div>
            <!--end::Stats-->

            <!--begin::Response-->
            <div class="mt-10">
                <label class="form-label">Response (Real-time)</label>
                <div class="card">
                    <div class="card-body bg-light-dark min-h-300px max-h-500px position-relative" style="overflow-y: auto;">
                        <div id="streamingIndicator" class="d-none">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="text-muted">Streaming...</span>
                        </div>
                        <div id="response" class="fs-5 text-gray-800" style="white-space: pre-wrap; word-wrap: break-word;"></div>
                        <div id="cursor" class="d-none" style="display: inline-block; width: 10px; height: 20px; background: #3E97FF; animation: blink 1s infinite;"></div>
                    </div>
                </div>
            </div>
            <!--end::Response-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Connection Monitor Card-->
    <div class="card mt-10">
        <!--begin::Card header-->
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-monitor fs-2 me-2"></i>
                Streaming Monitor
                <span class="badge badge-light-primary ms-2" id="monitorStatus">Inactive</span>
            </h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-danger" id="clearMonitorBtn">
                    <i class="ki-outline ki-trash fs-4"></i>
                    Clear Monitor
                </button>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body">
            <div class="alert alert-info d-flex align-items-center mb-5">
                <i class="ki-outline ki-information-5 fs-2x me-3"></i>
                <div class="d-flex flex-column">
                    <h5 class="mb-1">Real-Time Streaming Monitor</h5>
                    <span>This monitor shows all backend activity during streaming: requests sent, chunks received, events, tokens, and final metrics. Automatically activates when you start streaming.</span>
                </div>
            </div>

            <!--begin::Monitor Console-->
            <div class="card" style="min-height: 300px; max-height: 500px; overflow-y: auto;" id="monitorConsole">
                <div class="card-body p-5 bg-light-dark">
                    <div id="monitorLogs" class="text-gray-800 font-monospace fs-7" style="white-space: pre-wrap; word-wrap: break-word;">
                        <span class="text-muted">Monitor ready. Start a streaming request to see real-time activity...</span>
                    </div>
                </div>
            </div>
            <!--end::Monitor Console-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Connection Monitor Card-->

    <!--begin::Activity Card-->
    <div class="card mt-10">
        <!--begin::Card header-->
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary me-2" id="refreshActivityBtn">
                    <i class="ki-outline ki-arrows-circle fs-4"></i>
                    Refresh
                </button>
                <button type="button" class="btn btn-sm btn-light-danger" id="clearActivityBtn">
                    <i class="ki-outline ki-trash fs-4"></i>
                    Clear History
                </button>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="activityTable">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="w-25px">#</th>
                            <th class="min-w-150px">Time</th>
                            <th class="min-w-120px">Provider</th>
                            <th class="min-w-100px">Model</th>
                            <th class="min-w-80px text-end">Tokens</th>
                            <th class="min-w-80px text-end">Cost</th>
                            <th class="min-w-80px text-end">Duration</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-100px text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activityTableBody">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-10">
                                <i class="ki-outline ki-information-5 fs-3x mb-3"></i>
                                <p class="mb-0">No activity yet. Start a streaming test above.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Activity Card-->

    @push('scripts')
    <style>
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('streamForm');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const clearBtn = document.getElementById('clearBtn');
        const responseDiv = document.getElementById('response');
        const cursorDiv = document.getElementById('cursor');
        const streamingIndicator = document.getElementById('streamingIndicator');
        const stats = document.getElementById('stats');
        const tokenCountEl = document.getElementById('tokenCount');
        const chunkCountEl = document.getElementById('chunkCount');
        const durationEl = document.getElementById('duration');
        const costEl = document.getElementById('cost');
        const logIdEl = document.getElementById('logId');
        const viewLogBtn = document.getElementById('viewLogBtn');
        const activityTableBody = document.getElementById('activityTableBody');
        const refreshActivityBtn = document.getElementById('refreshActivityBtn');
        const clearActivityBtn = document.getElementById('clearActivityBtn');
        const clearMonitorBtn = document.getElementById('clearMonitorBtn');
        const monitorLogs = document.getElementById('monitorLogs');
        const monitorConsole = document.getElementById('monitorConsole');
        const monitorStatus = document.getElementById('monitorStatus');

        let eventSource = null;
        let startTime = null;
        let durationInterval = null;
        let tokenCount = 0;
        let chunkCount = 0;
        let currentLogId = null;
        let finalMetrics = null;
        let activityHistory = JSON.parse(localStorage.getItem('llm_activity_history') || '[]');

        // Load activity history on page load
        renderActivityTable();

        // Start streaming
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Reset
            responseDiv.textContent = '';
            tokenCount = 0;
            chunkCount = 0;
            currentLogId = null;
            finalMetrics = null;
            tokenCountEl.textContent = '0';
            chunkCountEl.textContent = '0';
            durationEl.textContent = '0s';
            costEl.textContent = '$0.00';
            logIdEl.textContent = '-';
            viewLogBtn.classList.add('d-none');
            stats.style.display = 'block';
            streamingIndicator.classList.remove('d-none');
            cursorDiv.classList.remove('d-none');

            // Get form data
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            // Get configuration details for monitor
            const configSelect = document.getElementById('configuration_id');
            const selectedOption = configSelect.selectedOptions[0];
            const configText = selectedOption.text;
            const matches = configText.match(/\(([^)]+)\)/);
            const [provider, model] = matches ? matches[1].split(' - ') : ['Unknown', 'Unknown'];
            
            const prompt = document.getElementById('prompt').value;
            const temperature = document.getElementById('temperature').value;
            const maxTokens = document.getElementById('max_tokens').value;

            // Activate monitor
            monitorStatus.textContent = 'Active';
            monitorStatus.className = 'badge badge-success ms-2';
            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
            addMonitorLog('🚀 STARTING STREAMING REQUEST', 'header');
            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
            addMonitorLog('', 'info');
            addMonitorLog('📤 REQUEST DETAILS:', 'info');
            addMonitorLog(`   Provider: ${provider}`, 'debug');
            addMonitorLog(`   Model: ${model}`, 'debug');
            addMonitorLog(`   Temperature: ${temperature}`, 'debug');
            addMonitorLog(`   Max Tokens: ${maxTokens}`, 'debug');
            addMonitorLog(`   Prompt: "${prompt.substring(0, 50)}${prompt.length > 50 ? '...' : ''}"`, 'debug');
            addMonitorLog('', 'info');
            addMonitorLog('🔌 Opening SSE connection...', 'info');
            addMonitorLog(`   URL: ${window.location.origin}/admin/llm/stream/stream`, 'debug');
            addMonitorLog(`   Method: GET with query params`, 'debug');

            // Start timer
            startTime = Date.now();
            durationInterval = setInterval(updateDuration, 100);

            // Create EventSource for SSE
            eventSource = new EventSource('{{ route('admin.llm.stream.stream') }}?' + params.toString());

            // Update UI
            startBtn.classList.add('d-none');
            stopBtn.classList.remove('d-none');
            form.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
            
            addMonitorLog('', 'info');
            addMonitorLog('✅ SSE connection established', 'success');
            addMonitorLog('⏳ Waiting for response chunks...', 'info');
            addMonitorLog('', 'info');

            // Handle messages
            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);

                if (data.type === 'chunk') {
                    // Append chunk to response
                    responseDiv.textContent += data.content;
                    chunkCount++;
                    chunkCountEl.textContent = chunkCount;
                    
                    // Monitor log (show first 10 chunks, then only milestones)
                    if (chunkCount <= 10 || chunkCount % 10 === 0) {
                        const preview = data.content.length > 30 
                            ? data.content.substring(0, 30) + '...' 
                            : data.content;
                        addMonitorLog(`📥 CHUNK #${chunkCount}: "${preview}"`, 'chunk');
                    }
                    
                    if (data.tokens) {
                        tokenCount = data.tokens;
                        tokenCountEl.textContent = tokenCount;
                        
                        // Log token updates every 50 tokens
                        if (tokenCount % 50 === 0) {
                            addMonitorLog(`📊 Tokens received so far: ${tokenCount}`, 'info');
                        }
                    }

                    // Auto-scroll to bottom
                    responseDiv.scrollIntoView({ behavior: 'smooth', block: 'end' });

                } else if (data.type === 'done') {
                    addMonitorLog('', 'info');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    addMonitorLog('✅ STREAMING COMPLETED', 'header');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    addMonitorLog('', 'info');
                    
                    // Store final metrics
                    finalMetrics = {
                        usage: data.usage || {},
                        cost: data.cost || 0,
                        execution_time_ms: data.execution_time_ms || 0,
                        log_id: data.log_id || null
                    };
                    
                    // Log final metrics
                    addMonitorLog('📊 FINAL METRICS:', 'info');
                    if (data.usage) {
                        addMonitorLog(`   Prompt Tokens: ${data.usage.prompt_tokens || 0}`, 'debug');
                        addMonitorLog(`   Completion Tokens: ${data.usage.completion_tokens || 0}`, 'debug');
                        addMonitorLog(`   Total Tokens: ${data.usage.total_tokens || 0}`, 'debug');
                        tokenCountEl.textContent = data.usage.total_tokens || tokenCount;
                        tokenCount = data.usage.total_tokens || tokenCount;
                    }
                    
                    if (data.cost !== undefined) {
                        addMonitorLog(`   Cost USD: $${parseFloat(data.cost).toFixed(6)}`, 'debug');
                        costEl.textContent = '$' + parseFloat(data.cost).toFixed(6);
                    }
                    
                    if (data.execution_time_ms) {
                        addMonitorLog(`   Execution Time: ${data.execution_time_ms}ms (${(data.execution_time_ms / 1000).toFixed(2)}s)`, 'debug');
                    }
                    
                    addMonitorLog(`   Total Chunks: ${chunkCount}`, 'debug');
                    
                    if (data.log_id) {
                        addMonitorLog(`   Log ID: #${data.log_id}`, 'debug');
                        currentLogId = data.log_id;
                        logIdEl.textContent = '#' + data.log_id;
                        viewLogBtn.classList.remove('d-none');
                    }
                    
                    addMonitorLog('', 'info');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    
                    // Deactivate monitor
                    monitorStatus.textContent = 'Completed';
                    monitorStatus.className = 'badge badge-light-success ms-2';
                    
                    // Streaming complete
                    stopStreaming(false); // false = don't reset metrics
                    
                    // Add to activity history
                    addToActivityHistory({
                        timestamp: new Date().toISOString(),
                        provider: provider,
                        model: model,
                        tokens: tokenCount,
                        cost: parseFloat(data.cost || 0),
                        duration: (data.execution_time_ms / 1000).toFixed(2),
                        status: 'completed',
                        log_id: data.log_id,
                        prompt: prompt.substring(0, 100),
                        response: responseDiv.textContent.substring(0, 100)
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Streaming Complete!',
                        html: `
                            <div class="text-start">
                                <p><strong>Tokens:</strong> ${tokenCount}</p>
                                <p><strong>Chunks:</strong> ${chunkCount}</p>
                                <p><strong>Cost:</strong> $${parseFloat(data.cost || 0).toFixed(6)}</p>
                                <p><strong>Duration:</strong> ${(data.execution_time_ms / 1000).toFixed(2)}s</p>
                                ${data.log_id ? `<p><strong>Log ID:</strong> #${data.log_id}</p>` : ''}
                            </div>
                        `,
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });

                } else if (data.type === 'error') {
                    addMonitorLog('', 'info');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    addMonitorLog('❌ ERROR OCCURRED', 'header');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    addMonitorLog('', 'info');
                    addMonitorLog(`Error: ${data.message}`, 'error');
                    addMonitorLog('', 'info');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    
                    // Deactivate monitor
                    monitorStatus.textContent = 'Error';
                    monitorStatus.className = 'badge badge-danger ms-2';
                    
                    // Handle error
                    stopStreaming(true);
                    
                    // Add error to activity history
                    addToActivityHistory({
                        timestamp: new Date().toISOString(),
                        provider: provider,
                        model: model,
                        tokens: 0,
                        cost: 0,
                        duration: 0,
                        status: 'error',
                        log_id: null,
                        prompt: prompt.substring(0, 100),
                        response: data.message
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Streaming Error',
                        text: data.message
                    });
                }
            };

            eventSource.onerror = function(error) {
                console.error('EventSource error:', error);
                
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('❌ CONNECTION ERROR', 'header');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('', 'info');
                addMonitorLog('Lost connection to server', 'error');
                addMonitorLog('EventSource readyState: ' + eventSource.readyState, 'debug');
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                
                // Deactivate monitor
                monitorStatus.textContent = 'Error';
                monitorStatus.className = 'badge badge-danger ms-2';
                
                stopStreaming(true); // true = reset on error
                
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Lost connection to server'
                });
            };
        });

        // Stop streaming
        stopBtn.addEventListener('click', function() {
            addMonitorLog('', 'info');
            addMonitorLog('⏸️  User stopped streaming manually', 'info');
            addMonitorLog(`   Chunks received: ${chunkCount}`, 'debug');
            addMonitorLog(`   Tokens received: ${tokenCount}`, 'debug');
            addMonitorLog('', 'info');
            
            // User manually stopped - keep current metrics
            stopStreaming(false);
            
            // Deactivate monitor
            monitorStatus.textContent = 'Stopped';
            monitorStatus.className = 'badge badge-warning ms-2';
            
            // Show what we got so far
            if (chunkCount > 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Streaming Stopped',
                    html: `
                        <div class="text-start">
                            <p><strong>Tokens received:</strong> ${tokenCount}</p>
                            <p><strong>Chunks received:</strong> ${chunkCount}</p>
                            <p class="text-muted"><small>Note: Partial streaming data (stopped manually)</small></p>
                        </div>
                    `,
                    confirmButtonText: 'OK'
                });
            }
        });

        // Clear response
        clearBtn.addEventListener('click', function() {
            responseDiv.textContent = '';
            stats.style.display = 'none';
            tokenCount = 0;
            chunkCount = 0;
            currentLogId = null;
            finalMetrics = null;
            tokenCountEl.textContent = '0';
            chunkCountEl.textContent = '0';
            costEl.textContent = '$0.00';
            logIdEl.textContent = '-';
            viewLogBtn.classList.add('d-none');
        });

        // View log button
        viewLogBtn.addEventListener('click', function() {
            if (currentLogId) {
                // TODO: Open log details in modal or new tab
                // For now, open stats page (will be implemented in Point 3)
                window.open('/admin/llm/stats?log_id=' + currentLogId, '_blank');
            }
        });

        // Refresh activity table
        refreshActivityBtn.addEventListener('click', function() {
            renderActivityTable();
            toastr.success('Activity table refreshed');
        });

        // Clear activity history
        clearActivityBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Clear Activity History?',
                text: "This will delete all local activity records. This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    activityHistory = [];
                    localStorage.setItem('llm_activity_history', JSON.stringify(activityHistory));
                    renderActivityTable();
                    toastr.success('Activity history cleared');
                }
            });
        });

        // Clear Monitor
        clearMonitorBtn.addEventListener('click', function() {
            monitorLogs.innerHTML = '<span class="text-muted">Monitor cleared. Start a streaming request to see real-time activity...</span>';
            monitorStatus.textContent = 'Inactive';
            monitorStatus.className = 'badge badge-light-primary ms-2';
        });

        // Add log to monitor
        function addMonitorLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString('es-ES');
            let colorClass = 'text-gray-800';
            
            switch(type) {
                case 'success':
                    colorClass = 'text-success fw-bold';
                    break;
                case 'error':
                    colorClass = 'text-danger fw-bold';
                    break;
                case 'debug':
                    colorClass = 'text-muted';
                    break;
                case 'info':
                    colorClass = 'text-primary';
                    break;
                case 'chunk':
                    colorClass = 'text-gray-700';
                    break;
                case 'header':
                    colorClass = 'text-dark fw-bold fs-6';
                    break;
                case 'separator':
                    colorClass = 'text-gray-400';
                    break;
            }
            
            const logLine = document.createElement('div');
            logLine.className = colorClass;
            
            // Don't show timestamp for separator lines, empty lines, or headers
            if (message.startsWith('━') || message === '' || type === 'header') {
                logLine.textContent = message;
            } else {
                logLine.textContent = `[${timestamp}] ${message}`;
            }
            
            // Only clear "ready" message on first real log (check if only has one child with "Monitor ready")
            if (monitorLogs.children.length === 1) {
                const firstChild = monitorLogs.children[0];
                if (firstChild.querySelector && firstChild.querySelector('.text-muted')) {
                    monitorLogs.innerHTML = '';
                } else if (firstChild.textContent && firstChild.textContent.includes('Monitor ready')) {
                    monitorLogs.innerHTML = '';
                }
            }
            
            monitorLogs.appendChild(logLine);
            
            // Auto-scroll to bottom
            monitorConsole.scrollTop = monitorConsole.scrollHeight;
        }

        function stopStreaming(resetMetrics = false) {
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }

            if (durationInterval) {
                clearInterval(durationInterval);
                durationInterval = null;
            }

            streamingIndicator.classList.add('d-none');
            cursorDiv.classList.add('d-none');
            startBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            form.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
            
            // Only reset metrics if explicitly requested (e.g., on error)
            if (resetMetrics) {
                tokenCount = 0;
                chunkCount = 0;
                currentLogId = null;
                finalMetrics = null;
                tokenCountEl.textContent = '0';
                chunkCountEl.textContent = '0';
                costEl.textContent = '$0.00';
                logIdEl.textContent = '-';
                viewLogBtn.classList.add('d-none');
            }
        }

        function updateDuration() {
            if (startTime) {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                durationEl.textContent = elapsed + 's';
            }
        }

        // Add to activity history
        function addToActivityHistory(activity) {
            // Add to beginning of array (most recent first)
            activityHistory.unshift(activity);
            
            // Keep only last 10 items
            if (activityHistory.length > 10) {
                activityHistory = activityHistory.slice(0, 10);
            }
            
            // Save to localStorage
            localStorage.setItem('llm_activity_history', JSON.stringify(activityHistory));
            
            // Update table
            renderActivityTable();
        }

        // Render activity table
        function renderActivityTable() {
            if (activityHistory.length === 0) {
                activityTableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center text-muted py-10">
                            <i class="ki-outline ki-information-5 fs-3x mb-3"></i>
                            <p class="mb-0">No activity yet. Start a streaming test above.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            activityHistory.forEach((activity, index) => {
                const date = new Date(activity.timestamp);
                const timeStr = date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                
                const statusBadge = activity.status === 'completed' 
                    ? '<span class="badge badge-light-success">Completed</span>'
                    : '<span class="badge badge-light-danger">Error</span>';

                const rowId = `activity-row-${index}`;
                const detailsId = `activity-details-${index}`;

                html += `
                    <tr id="${rowId}" class="cursor-pointer" data-index="${index}">
                        <td>${activityHistory.length - index}</td>
                        <td>
                            <span class="text-dark fw-bold">${timeStr}</span>
                            <span class="text-muted d-block fs-7">${date.toLocaleDateString('es-ES')}</span>
                        </td>
                        <td><span class="badge badge-light-primary">${activity.provider}</span></td>
                        <td><span class="text-gray-800 fs-7">${activity.model}</span></td>
                        <td class="text-end fw-bold">${activity.tokens.toLocaleString()}</td>
                        <td class="text-end fw-bold ${activity.cost > 0 ? 'text-warning' : 'text-success'}">$${activity.cost.toFixed(6)}</td>
                        <td class="text-end">${activity.duration}s</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-light-primary btn-icon toggle-details-btn" data-index="${index}">
                                <i class="ki-outline ki-down fs-3"></i>
                            </button>
                            ${activity.log_id ? `
                                <button type="button" class="btn btn-sm btn-light-info btn-icon ms-2" onclick="window.open('/admin/llm/stats?log_id=${activity.log_id}', '_blank')">
                                    <i class="ki-outline ki-eye fs-3"></i>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                    <tr id="${detailsId}" class="d-none bg-light-primary">
                        <td colspan="9" class="p-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-dark fw-bold mb-3">Prompt:</h6>
                                    <p class="text-gray-700 fs-7 mb-0">${activity.prompt}${activity.prompt.length >= 100 ? '...' : ''}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-dark fw-bold mb-3">Response:</h6>
                                    <p class="text-gray-700 fs-7 mb-0">${activity.response}${activity.response.length >= 100 ? '...' : ''}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            });

            activityTableBody.innerHTML = html;

            // Add click handlers for toggle buttons
            document.querySelectorAll('.toggle-details-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const index = this.getAttribute('data-index');
                    const detailsRow = document.getElementById(`activity-details-${index}`);
                    const icon = this.querySelector('i');
                    
                    if (detailsRow.classList.contains('d-none')) {
                        detailsRow.classList.remove('d-none');
                        icon.classList.remove('ki-down');
                        icon.classList.add('ki-up');
                    } else {
                        detailsRow.classList.add('d-none');
                        icon.classList.remove('ki-up');
                        icon.classList.add('ki-down');
                    }
                });
            });
        }
    });
    </script>
    @endpush
</x-default-layout>
