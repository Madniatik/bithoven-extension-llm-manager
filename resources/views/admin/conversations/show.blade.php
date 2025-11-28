<x-default-layout>
    @section('title', 'Conversation Details')
    @section('breadcrumbs')
        {!! Breadcrumbs::render('admin.llm.conversations.show', $conversation) !!}
    @endsection

    <div class="row g-5 g-xl-10 mb-5">
        <!-- Conversation Info -->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Session Info</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-7">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Session ID:</span>
                            <span class="fw-bold text-gray-800 fs-7">{{ $conversation->session_id }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Configuration:</span>
                            <span class="badge badge-light-primary">{{ $conversation->configuration->name ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Status:</span>
                            @if($conversation->ended_at)
                                <span class="badge badge-light-secondary">Ended</span>
                            @elseif($conversation->expires_at && $conversation->expires_at->isPast())
                                <span class="badge badge-light-danger">Expired</span>
                            @else
                                <span class="badge badge-light-success">Active</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Messages:</span>
                            <span class="fw-bold text-gray-800">{{ $conversation->messages()->count() }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Total Tokens:</span>
                            <span class="fw-bold text-gray-800">{{ number_format($conversation->total_tokens) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Total Cost:</span>
                            <span class="fw-bold text-gray-800">${{ number_format($conversation->total_cost, 6) }}</span>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Created:</div>
                        <div class="text-gray-800 fs-7">{{ $conversation->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    @if($conversation->ended_at)
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Ended:</div>
                        <div class="text-gray-800 fs-7">{{ $conversation->ended_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @endif

                    @if($conversation->expires_at)
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Expires:</div>
                        <div class="text-gray-800 fs-7">{{ $conversation->expires_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @endif

                    <div class="separator separator-dashed my-5"></div>

                    <a href="{{ route('admin.llm.conversations.export', $conversation) }}" class="btn btn-light-primary w-100">
                        <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Export Conversation
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Messages</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">{{ $conversation->messages()->count() }} messages</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="scroll-y me-n5 pe-5 h-600px" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer" data-kt-scroll-wrappers="#kt_content" data-kt-scroll-offset="5px">
                        @foreach($conversation->messages as $message)
                        <div class="d-flex {{ $message->role === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-10">
                            <div class="d-flex flex-column align-items-{{ $message->role === 'user' ? 'end' : 'start' }}">
                                <div class="d-flex align-items-center mb-2">
                                    @if($message->role === 'assistant')
                                    <div class="symbol symbol-35px symbol-circle me-3">
                                        <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <span class="text-gray-600 fw-semibold fs-8">
                                            {{ ucfirst($message->role) }}
                                        </span>
                                        <span class="text-gray-500 fw-semibold fs-8 ms-2">
                                            {{ $message->created_at->format('H:i:s') }}
                                        </span>
                                    </div>

                                    @if($message->role === 'user')
                                    <div class="symbol symbol-35px symbol-circle ms-3">
                                        <span class="symbol-label bg-light-success text-success fw-bold">U</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="p-5 rounded {{ $message->role === 'user' ? 'bg-light-success' : 'bg-light-primary' }}" style="max-width: 70%">
                                    <div class="text-gray-800 fw-semibold fs-6">
                                        {{ $message->content }}
                                    </div>
                                </div>

                                @if($message->token_count)
                                <div class="text-gray-500 fw-semibold fs-8 mt-1">
                                    Tokens: {{ number_format($message->token_count) }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Message Input Area -->
                    <div class="separator separator-dashed my-5"></div>
                    
                    <form id="stream-message-form" class="d-flex flex-column gap-3">
                        @csrf
                        
                        <!-- Streaming Controls -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Temperature</label>
                                <input type="range" id="temperature" name="temperature" class="form-range" min="0" max="2" step="0.1" value="{{ $conversation->configuration->temperature ?? 0.7 }}">
                                <small class="text-gray-600">Current: <span id="temp-display">{{ $conversation->configuration->temperature ?? 0.7 }}</span></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Tokens</label>
                                <input type="number" id="max_tokens" name="max_tokens" class="form-control" min="1" max="4000" value="{{ $conversation->configuration->max_tokens ?? 2000 }}" placeholder="2000">
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div>
                            <label class="form-label">Your Message</label>
                            <textarea id="message-input" name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="button" id="send-stream-btn" class="btn btn-primary flex-shrink-0">
                                <i class="ki-duotone ki-send fs-2"><span class="path1"></span><span class="path2"></span></i>
                                Send with Streaming
                            </button>
                            <button type="button" id="stop-stream-btn" class="btn btn-danger flex-shrink-0" style="display: none;">
                                <i class="ki-duotone ki-stop fs-2"><span class="path1"></span><span class="path2"></span></i>
                                Stop Generating
                            </button>
                        </div>

                        <!-- Streaming Status -->
                        <div id="stream-status" style="display: none;" class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span id="stream-status-text">Streaming response...</span>
                            </div>
                        </div>

                        <!-- Real-time Response Display -->
                        <div id="stream-response-container" style="display: none;" class="p-5 rounded bg-light-primary">
                            <div class="mb-2">
                                <span class="text-gray-600 fw-semibold fs-8">Assistant Response</span>
                            </div>
                            <div id="stream-response" class="text-gray-800 fs-6" style="min-height: 100px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;"></div>
                            
                            <!-- Streaming Metrics -->
                            <div class="mt-3 pt-3 border-top">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-gray-600">Tokens</small>
                                        <div class="fw-bold"><span id="stream-tokens">0</span></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-gray-600">Time</small>
                                        <div class="fw-bold"><span id="stream-time">0</span>ms</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Conversation Streaming Handler
        class ConversationStreaming {
            constructor() {
                this.eventSource = null;
                this.startTime = null;
                this.tokenCount = 0;
            }

            init() {
                // Temperature slider listener
                document.getElementById('temperature').addEventListener('input', (e) => {
                    document.getElementById('temp-display').textContent = e.target.value;
                });

                // Send button listener
                document.getElementById('send-stream-btn').addEventListener('click', () => this.startStreaming());

                // Stop button listener
                document.getElementById('stop-stream-btn').addEventListener('click', () => this.stopStreaming());
            }

            startStreaming() {
                const messageInput = document.getElementById('message-input');
                const message = messageInput.value.trim();

                if (!message) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please enter a message',
                        icon: 'error',
                        timer: 3000
                    });
                    return;
                }

                // Get form data
                const temperature = document.getElementById('temperature').value;
                const maxTokens = document.getElementById('max_tokens').value;

                // Update UI
                document.getElementById('send-stream-btn').style.display = 'none';
                document.getElementById('stop-stream-btn').style.display = 'block';
                document.getElementById('stream-status').style.display = 'block';
                document.getElementById('stream-response-container').style.display = 'block';
                document.getElementById('stream-response').textContent = '';
                this.tokenCount = 0;
                this.startTime = Date.now();

                // Build URL with query params
                const url = new URL(
                    '{{ route("admin.llm.conversations.stream-reply", $conversation->id) }}',
                    window.location.origin
                );

                // Create EventSource with query parameters
                const eventSourceUrl = url.toString() + '?' + new URLSearchParams({
                    message: message,
                    temperature: temperature,
                    max_tokens: maxTokens
                });

                this.eventSource = new EventSource(eventSourceUrl);

                this.eventSource.onmessage = (event) => {
                    const data = JSON.parse(event.data);

                    if (data.type === 'chunk') {
                        // Append chunk to response
                        document.getElementById('stream-response').textContent += data.content;
                        this.tokenCount = data.tokens || this.tokenCount;
                        document.getElementById('stream-tokens').textContent = this.tokenCount;
                    } else if (data.type === 'done') {
                        // Streaming complete
                        this.tokenCount = data.usage?.completion_tokens || this.tokenCount;
                        const duration = Date.now() - this.startTime;
                        
                        document.getElementById('stream-tokens').textContent = this.tokenCount;
                        document.getElementById('stream-time').textContent = duration;
                        
                        this.eventSource.close();
                        this.onStreamComplete(message);
                    } else if (data.type === 'error') {
                        // Error occurred
                        Swal.fire({
                            title: 'Streaming Error',
                            text: data.message || 'An error occurred during streaming',
                            icon: 'error'
                        });
                        this.eventSource.close();
                        this.resetUI();
                    }
                };

                this.eventSource.onerror = (error) => {
                    console.error('EventSource error:', error);
                    Swal.fire({
                        title: 'Connection Error',
                        text: 'Lost connection to server',
                        icon: 'error'
                    });
                    this.eventSource.close();
                    this.resetUI();
                };
            }

            stopStreaming() {
                if (this.eventSource) {
                    this.eventSource.close();
                    document.getElementById('stream-status').style.display = 'none';
                    document.getElementById('stream-status-text').textContent = 'Streaming stopped';
                    this.resetUI();
                }
            }

            onStreamComplete(userMessage) {
                document.getElementById('stream-status').style.display = 'none';
                document.getElementById('message-input').value = '';
                
                // Show success notification
                Swal.fire({
                    title: 'Success',
                    text: 'Message streamed and saved to conversation',
                    icon: 'success',
                    timer: 2000
                });

                // Optionally reload the page to see new messages saved
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }

            resetUI() {
                document.getElementById('send-stream-btn').style.display = 'block';
                document.getElementById('stop-stream-btn').style.display = 'none';
                document.getElementById('stream-status').style.display = 'none';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            const streaming = new ConversationStreaming();
            streaming.init();
        });
    </script>
    @endpush
</x-default-layout>
