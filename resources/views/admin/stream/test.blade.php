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
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="fs-2 fw-bold text-primary" id="tokenCount">0</div>
                        <div class="text-muted fs-7">Tokens Received</div>
                    </div>
                    <div class="col-md-4">
                        <div class="fs-2 fw-bold text-success" id="chunkCount">0</div>
                        <div class="text-muted fs-7">Chunks</div>
                    </div>
                    <div class="col-md-4">
                        <div class="fs-2 fw-bold text-info" id="duration">0s</div>
                        <div class="text-muted fs-7">Duration</div>
                    </div>
                </div>
            </div>
            <!--end::Stats-->

            <!--begin::Response-->
            <div class="mt-10">
                <label class="form-label">Response (Real-time)</label>
                <div class="card">
                    <div class="card-body bg-light-dark min-h-300px position-relative">
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

        let eventSource = null;
        let startTime = null;
        let durationInterval = null;
        let tokenCount = 0;
        let chunkCount = 0;

        // Start streaming
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Reset
            responseDiv.textContent = '';
            tokenCount = 0;
            chunkCount = 0;
            tokenCountEl.textContent = '0';
            chunkCountEl.textContent = '0';
            durationEl.textContent = '0s';
            stats.style.display = 'block';
            streamingIndicator.classList.remove('d-none');
            cursorDiv.classList.remove('d-none');

            // Get form data
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            // Start timer
            startTime = Date.now();
            durationInterval = setInterval(updateDuration, 100);

            // Create EventSource for SSE
            eventSource = new EventSource('{{ route('admin.llm.stream.stream') }}?' + params.toString());

            // Update UI
            startBtn.classList.add('d-none');
            stopBtn.classList.remove('d-none');
            form.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);

            // Handle messages
            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);

                if (data.type === 'chunk') {
                    // Append chunk to response
                    responseDiv.textContent += data.content;
                    chunkCount++;
                    chunkCountEl.textContent = chunkCount;
                    
                    if (data.tokens) {
                        tokenCount = data.tokens;
                        tokenCountEl.textContent = tokenCount;
                    }

                    // Auto-scroll to bottom
                    responseDiv.scrollIntoView({ behavior: 'smooth', block: 'end' });

                } else if (data.type === 'done') {
                    // Streaming complete
                    stopStreaming();
                    tokenCountEl.textContent = data.total_tokens;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Streaming Complete!',
                        text: `Received ${data.total_tokens} tokens in ${chunkCount} chunks`,
                        timer: 3000,
                        showConfirmButton: false
                    });

                } else if (data.type === 'error') {
                    // Handle error
                    stopStreaming();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Streaming Error',
                        text: data.message
                    });
                }
            };

            eventSource.onerror = function(error) {
                console.error('EventSource error:', error);
                stopStreaming();
                
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Lost connection to server'
                });
            };
        });

        // Stop streaming
        stopBtn.addEventListener('click', function() {
            stopStreaming();
        });

        // Clear response
        clearBtn.addEventListener('click', function() {
            responseDiv.textContent = '';
            stats.style.display = 'none';
            tokenCount = 0;
            chunkCount = 0;
        });

        function stopStreaming() {
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
        }

        function updateDuration() {
            if (startTime) {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                durationEl.textContent = elapsed + 's';
            }
        }
    });
    </script>
    @endpush
</x-default-layout>
