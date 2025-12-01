<x-default-layout>
    @section('title', 'Conversation Details')
    @section('breadcrumbs')
        {!! Breadcrumbs::render('admin.llm.conversations.show', $conversation->id) !!}
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

                    <a href="{{ route('admin.llm.conversations.export', $conversation->id) }}" class="btn btn-light-primary w-100">
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
                    <div id="messages-container" class="scroll-y me-n5 pe-5 h-600px" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer" data-kt-scroll-wrappers="#kt_content" data-kt-scroll-offset="5px">
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
                                            @if($message->role === 'user')
                                                {{ $conversation->user->name ?? 'User' }}
                                            @else
                                                Assistant
                                            @endif
                                        </span>
                                        @if($message->role === 'assistant')
                                            <span class="badge badge-light-primary badge-sm ms-2">{{ $conversation->configuration->provider }}</span>
                                            <span class="badge badge-light-info badge-sm">{{ $conversation->configuration->model }}</span>
                                        @endif
                                        <span class="text-gray-500 fw-semibold fs-8 ms-2">
                                            {{ $message->created_at->format('H:i:s') }}
                                        </span>
                                    </div>

                                    @if($message->role === 'user')
                                    <div class="symbol symbol-35px symbol-circle ms-3">
                                        @if($conversation->user && $conversation->user->avatar)
                                            <img src="{{ asset('storage/' . $conversation->user->avatar) }}" alt="{{ $conversation->user->name }}" />
                                        @elseif($conversation->user)
                                            <span class="symbol-label bg-light-success text-success fw-bold">{{ strtoupper(substr($conversation->user->name, 0, 1)) }}</span>
                                        @else
                                            <span class="symbol-label bg-light-success text-success fw-bold">U</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <div class="p-5 rounded {{ $message->role === 'user' ? 'bg-light-success' : 'bg-light-primary' }}" style="max-width: 70%">
                                    <div class="text-gray-800 fw-semibold fs-6 message-content" data-role="{{ $message->role }}" data-rendered="true">
                                        @if($message->role === 'assistant')
                                            {!! $message->content !!}
                                        @else
                                            {{ $message->content }}
                                        @endif
                                    </div>
                                </div>

                                @if($message->token_count || $message->tokens || $message->total_tokens)
                                <div class="text-gray-500 fw-semibold fs-8 mt-1 d-flex align-items-center gap-3 flex-wrap">
                                    {{-- Tokens --}}
                                    <span>
                                        <i class="ki-duotone ki-calculator fs-7 text-gray-400">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ number_format($message->total_tokens ?? $message->token_count ?? 0) }} tokens
                                    </span>

                                    {{-- Response Time --}}
                                    @if($message->response_time)
                                    <span class="text-success">
                                        <i class="ki-duotone ki-timer fs-7 text-success">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ number_format($message->response_time, 2) }}s
                                    </span>
                                    @endif

                                    {{-- Provider & Model --}}
                                    @if(isset($message->metadata['provider']) && isset($message->metadata['model']))
                                    <span class="text-primary">
                                        <i class="ki-duotone ki-technology-2 fs-7 text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ ucfirst($message->metadata['provider']) }} / {{ $message->metadata['model'] }}
                                    </span>
                                    @endif

                                    {{-- Streaming --}}
                                    @if(isset($message->metadata['is_streaming']) && $message->metadata['is_streaming'])
                                    <span class="text-info" title="{{ $message->metadata['chunks_count'] ?? 0 }} chunks">
                                        <i class="ki-duotone ki-cloud-download fs-7 text-info">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Stream
                                    </span>
                                    @endif

                                    {{-- TTFT --}}
                                    @if(isset($message->metadata['time_to_first_chunk']))
                                    <span class="text-warning" title="Time to first token">
                                        <i class="ki-duotone ki-flash-circle fs-7 text-warning">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ $message->metadata['time_to_first_chunk'] }}s
                                    </span>
                                    @endif
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
                        
                        <!-- Model Selection -->
                        <div>
                            <label class="form-label">LLM Model</label>
                            <select id="configuration_id" name="configuration_id" class="form-select">
                                @foreach($configurations as $config)
                                    <option value="{{ $config->id }}" 
                                        data-provider="{{ ucfirst($config->provider) }}"
                                        data-model="{{ $config->model }}"
                                        {{ ($conversation->configuration && $config->id == $conversation->configuration->id) ? 'selected' : '' }}>
                                        {{ $config->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-gray-600">Select which model to use for streaming</small>
                        </div>
                        
                        <!-- Streaming Controls -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Context Limit</label>
                                <select id="context_limit" name="context_limit" class="form-select">
                                    <option value="5">Last 5 messages</option>
                                    <option value="10" selected>Last 10 messages</option>
                                    <option value="20">Last 20 messages</option>
                                    <option value="50">Last 50 messages</option>
                                    <option value="0">All messages</option>
                                </select>
                                <small class="text-gray-600">How much conversation history to send</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Temperature</label>
                                <input type="range" id="temperature" name="temperature" class="form-range" min="0" max="2" step="0.1" value="{{ $conversation->configuration->temperature ?? 0.7 }}">
                                <small class="text-gray-600">Current: <span id="temp-display">{{ $conversation->configuration->temperature ?? 0.7 }}</span></small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Tokens</label>
                                <input type="number" id="max_tokens" name="max_tokens" class="form-control" min="1" max="4000" value="{{ $conversation->configuration->max_tokens ?? 2000 }}" placeholder="2000">
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div>
                            <label class="form-label">Your Message</label>
                            <textarea id="conversation-message-input" name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
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
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Marked.js for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js"></script>
    <!-- Prism.js for syntax highlighting -->
    <link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js"></script>
    <!-- markup-templating is required for PHP -->
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-css.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-sql.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-bash.min.js"></script>
    
    <style>
        /* Markdown content styling */
        .message-content[data-role="assistant"] h1,
        .message-content[data-role="assistant"] h2,
        .message-content[data-role="assistant"] h3,
        .message-content[data-role="assistant"] h4 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .message-content[data-role="assistant"] p {
            margin-bottom: 0.75rem;
        }
        
        .message-content[data-role="assistant"] ul,
        .message-content[data-role="assistant"] ol {
            margin-bottom: 0.75rem;
            padding-left: 1.5rem;
        }
        
        .message-content[data-role="assistant"] li {
            margin-bottom: 0.25rem;
        }
        
        .message-content[data-role="assistant"] code {
            background-color: rgba(0, 0, 0, 0.05);
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .message-content[data-role="assistant"] pre {
            background-color: #2d2d2d;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            margin-bottom: 1rem;
        }
        
        .message-content[data-role="assistant"] pre code {
            background-color: transparent;
            padding: 0;
            color: #f8f8f2;
        }
        
        .message-content[data-role="assistant"] blockquote {
            border-left: 4px solid #ddd;
            padding-left: 1rem;
            margin-left: 0;
            color: #666;
            font-style: italic;
        }
        
        .message-content[data-role="assistant"] hr {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid #ddd;
        }
        
        .message-content[data-role="assistant"] a {
            color: #007bff;
            text-decoration: underline;
        }
        
        .message-content[data-role="assistant"] strong {
            font-weight: 600;
        }
        
        .message-content[data-role="assistant"] em {
            font-style: italic;
        }
    </style>
    
    <script>
        // Conversation Streaming Handler
        class ConversationStreaming {
            constructor() {
                this.eventSource = null;
                this.startTime = null;
                this.tokenCount = 0;
                this.messagesContainer = document.getElementById('messages-container');
                this.currentAssistantMessage = null;
            }

            init() {
                // Load saved settings from localStorage
                this.loadSettings();
                
                // Debug: Monitor message input changes
                const messageInput = document.getElementById('conversation-message-input');
                if (messageInput) {
                    messageInput.addEventListener('input', (e) => {
                        console.log('Input changed:', e.target.value);
                    });
                    console.log('Message input listener attached to:', messageInput.id);
                } else {
                    console.error('conversation-message-input not found during init');
                }
                
                // Temperature slider listener
                document.getElementById('temperature').addEventListener('input', (e) => {
                    document.getElementById('temp-display').textContent = e.target.value;
                    this.saveSettings();
                });

                // Context limit listener
                document.getElementById('context_limit').addEventListener('change', () => this.saveSettings());

                // Max tokens listener
                document.getElementById('max_tokens').addEventListener('input', () => this.saveSettings());

                // Configuration selector listener
                document.getElementById('configuration_id').addEventListener('change', () => this.saveSettings());

                // Send button listener
                document.getElementById('send-stream-btn').addEventListener('click', () => this.startStreaming());

                // Stop button listener
                document.getElementById('stop-stream-btn').addEventListener('click', () => this.stopStreaming());
            }

            loadSettings() {
                const conversationId = {{ $conversation->id }};
                const savedSettings = localStorage.getItem(`llm_conversation_${conversationId}_settings`);
                
                if (savedSettings) {
                    const settings = JSON.parse(savedSettings);
                    
                    // Restore context limit
                    if (settings.context_limit !== undefined) {
                        const contextSelect = document.getElementById('context_limit');
                        contextSelect.value = settings.context_limit;
                    }
                    
                    // Restore temperature
                    if (settings.temperature !== undefined) {
                        const tempInput = document.getElementById('temperature');
                        tempInput.value = settings.temperature;
                        document.getElementById('temp-display').textContent = settings.temperature;
                    }
                    
                    // Restore max tokens
                    if (settings.max_tokens !== undefined) {
                        document.getElementById('max_tokens').value = settings.max_tokens;
                    }
                    
                    // Restore configuration
                    if (settings.configuration_id !== undefined) {
                        document.getElementById('configuration_id').value = settings.configuration_id;
                    }
                }
            }

            saveSettings() {
                const conversationId = {{ $conversation->id }};
                const settings = {
                    context_limit: document.getElementById('context_limit').value,
                    temperature: document.getElementById('temperature').value,
                    max_tokens: document.getElementById('max_tokens').value,
                    configuration_id: document.getElementById('configuration_id').value
                };
                
                localStorage.setItem(`llm_conversation_${conversationId}_settings`, JSON.stringify(settings));
            }

            createUserMessage(message) {
                const now = new Date();
                const timeStr = now.toTimeString().split(' ')[0];
                const userName = '{{ $conversation->user->name ?? "User" }}';
                const userInitial = '{{ $conversation->user ? strtoupper(substr($conversation->user->name, 0, 1)) : "U" }}';
                @if($conversation->user && $conversation->user->avatar)
                const userAvatar = `<img src="{{ asset('storage/' . $conversation->user->avatar) }}" alt="${userName}" />`;
                @else
                const userAvatar = `<span class="symbol-label bg-light-success text-success fw-bold">${userInitial}</span>`;
                @endif
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'd-flex justify-content-end mb-10';
                messageDiv.innerHTML = `
                    <div class="d-flex flex-column align-items-end">
                        <div class="d-flex align-items-center mb-2">
                            <div>
                                <span class="text-gray-600 fw-semibold fs-8">${userName}</span>
                                <span class="text-gray-500 fw-semibold fs-8 ms-2">${timeStr}</span>
                            </div>
                            <div class="symbol symbol-35px symbol-circle ms-3">
                                ${userAvatar}
                            </div>
                        </div>
                        <div class="p-5 rounded bg-light-success" style="max-width: 70%">
                            <div class="text-gray-800 fw-semibold fs-6">${this.escapeHtml(message)}</div>
                        </div>
                    </div>
                `;
                
                this.messagesContainer.appendChild(messageDiv);
                this.scrollToBottom();
            }

            createAssistantPlaceholder() {
                const now = new Date();
                const timeStr = now.toTimeString().split(' ')[0];
                const configSelect = document.getElementById('configuration_id');
                const selectedOption = configSelect.options[configSelect.selectedIndex];
                // Get provider and model from data attributes
                const provider = selectedOption.getAttribute('data-provider') || 'AI';
                const model = selectedOption.getAttribute('data-model') || '';
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'd-flex justify-content-start mb-10';
                messageDiv.id = 'streaming-assistant-message';
                messageDiv.innerHTML = `
                    <div class="d-flex flex-column align-items-start">
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-35px symbol-circle me-3">
                                <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
                            </div>
                            <div>
                                <span class="text-gray-600 fw-semibold fs-8">Assistant</span>
                                <span class="badge badge-light-primary badge-sm ms-2">${this.escapeHtml(provider)}</span>
                                ${model ? `<span class="badge badge-light-info badge-sm">${this.escapeHtml(model)}</span>` : ''}
                                <span class="text-gray-500 fw-semibold fs-8 ms-2">${timeStr}</span>
                            </div>
                        </div>
                        <div class="p-5 rounded bg-light-primary" style="max-width: 70%">
                            <div class="text-gray-800 fw-semibold fs-6" id="assistant-content">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="text-muted">Thinking...</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-gray-500 fw-semibold fs-8 mt-1" id="assistant-tokens" style="display: none;">
                            Tokens: <span id="token-count">0</span>
                        </div>
                    </div>
                `;
                
                this.messagesContainer.appendChild(messageDiv);
                this.currentAssistantMessage = messageDiv;
                this.scrollToBottom();
            }

            updateAssistantMessage(content, isComplete = false) {
                if (!this.currentAssistantMessage) return;
                
                const contentDiv = this.currentAssistantMessage.querySelector('#assistant-content');
                if (!contentDiv) return;

                if (isComplete) {
                    // Final content - render with Markdown
                    const markdownHtml = marked.parse(content);
                    contentDiv.innerHTML = `<div class="text-gray-800 fw-semibold fs-6 message-content" data-role="assistant">${markdownHtml}</div>`;
                    
                    // Apply syntax highlighting to code blocks (defensive)
                    contentDiv.querySelectorAll('pre code').forEach(block => {
                        if (typeof Prism !== 'undefined') {
                            try {
                                Prism.highlightElement(block);
                            } catch (error) {
                                console.warn('Prism highlighting failed:', error.message);
                                // Keep code block without highlighting
                            }
                        }
                    });
                } else {
                    // Streaming - show raw text (Markdown será visible)
                    contentDiv.innerHTML = `<div class="text-gray-800 fw-semibold fs-6" style="white-space: pre-wrap;">${this.escapeHtml(content)}</div>`;
                }
                
                this.scrollToBottom();
            }

            updateTokenCount(tokens) {
                if (!this.currentAssistantMessage) return;
                
                const tokenDiv = this.currentAssistantMessage.querySelector('#assistant-tokens');
                const tokenCount = this.currentAssistantMessage.querySelector('#token-count');
                
                if (tokenDiv && tokenCount) {
                    tokenDiv.style.display = 'block';
                    tokenCount.textContent = tokens;
                }
            }

            scrollToBottom() {
                this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            startStreaming() {
                const messageInput = document.getElementById('conversation-message-input');
                
                // Extended debug logging
                console.log('startStreaming called');
                console.log('messageInput element:', messageInput);
                console.log('messageInput placeholder:', messageInput ? messageInput.placeholder : 'N/A');
                console.log('messageInput.value:', messageInput ? messageInput.value : 'ELEMENT NOT FOUND');
                console.log('messageInput.textContent:', messageInput ? messageInput.textContent : 'N/A');
                console.log('messageInput.innerHTML:', messageInput ? messageInput.innerHTML : 'N/A');
                
                if (!messageInput) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Message input field not found. Please refresh the page.',
                        icon: 'error',
                        timer: 3000
                    });
                    return;
                }
                
                // Try multiple ways to get the value
                const value1 = messageInput.value;
                const value2 = messageInput.textContent;
                const value3 = messageInput.innerHTML;
                console.log('value (property):', value1);
                console.log('textContent:', value2);
                console.log('innerHTML:', value3);
                
                const message = (value1 || value2 || value3 || '').trim();
                console.log('message after trim:', message);

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
                const configurationId = document.getElementById('configuration_id').value;
                const contextLimit = document.getElementById('context_limit').value;

                // 1. Add user message to chat
                this.createUserMessage(message);

                // 2. Clear textarea
                messageInput.value = '';

                // 3. Create assistant placeholder with "Thinking..."
                this.createAssistantPlaceholder();

                // Update UI
                document.getElementById('send-stream-btn').style.display = 'none';
                document.getElementById('stop-stream-btn').style.display = 'block';
                messageInput.disabled = true;

                this.tokenCount = 0;
                this.startTime = Date.now();
                let fullResponse = '';

                // Build URL with query params
                const url = new URL(
                    '{{ route("admin.llm.conversations.stream-reply", $conversation->id) }}',
                    window.location.origin
                );

                // Create EventSource with query parameters
                const eventSourceUrl = url.toString() + '?' + new URLSearchParams({
                    message: message,
                    temperature: temperature,
                    max_tokens: maxTokens,
                    configuration_id: configurationId,
                    context_limit: contextLimit
                });

                this.eventSource = new EventSource(eventSourceUrl);

                this.eventSource.onmessage = (event) => {
                    const data = JSON.parse(event.data);

                    if (data.type === 'chunk') {
                        // Append chunk to full response
                        fullResponse += data.content;
                        
                        // Update assistant message with accumulated content
                        this.updateAssistantMessage(fullResponse, false);
                        
                        // Update token count
                        if (data.tokens) {
                            this.tokenCount = data.tokens;
                            this.updateTokenCount(this.tokenCount);
                        }
                    } else if (data.type === 'done') {
                        // Streaming complete - finalize message
                        this.updateAssistantMessage(fullResponse, true);
                        
                        // Update final token count
                        if (data.usage?.completion_tokens) {
                            this.tokenCount = data.usage.completion_tokens;
                            this.updateTokenCount(this.tokenCount);
                        }
                        
                        this.eventSource.close();
                        this.onStreamComplete();
                    } else if (data.type === 'error') {
                        // Error occurred
                        Swal.fire({
                            title: 'Streaming Error',
                            text: data.message || 'An error occurred during streaming',
                            icon: 'error'
                        });
                        
                        // Remove the placeholder message
                        if (this.currentAssistantMessage) {
                            this.currentAssistantMessage.remove();
                            this.currentAssistantMessage = null;
                        }
                        
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
                    
                    // Remove the placeholder message
                    if (this.currentAssistantMessage) {
                        this.currentAssistantMessage.remove();
                        this.currentAssistantMessage = null;
                    }
                    
                    this.eventSource.close();
                    this.resetUI();
                };
            }

            stopStreaming() {
                if (this.eventSource) {
                    this.eventSource.close();
                    this.resetUI();
                    
                    Swal.fire({
                        title: 'Stopped',
                        text: 'Streaming stopped by user',
                        icon: 'info',
                        timer: 2000
                    });
                }
            }

            onStreamComplete() {
                this.resetUI();
                
                // Remove streaming message ID
                if (this.currentAssistantMessage) {
                    this.currentAssistantMessage.removeAttribute('id');
                    this.currentAssistantMessage = null;
                }
            }

            resetUI() {
                document.getElementById('send-stream-btn').style.display = 'block';
                document.getElementById('stop-stream-btn').style.display = 'none';
                document.getElementById('message-input').disabled = false;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Check if marked.js is loaded
            if (typeof marked === 'undefined') {
                console.error('marked.js is not loaded');
                return;
            }

            // Configure marked.js
            marked.setOptions({
                breaks: true,
                gfm: true,
                headerIds: false,
                mangle: false
            });

            // Render existing assistant messages with Markdown
            document.querySelectorAll('.message-content[data-role="assistant"]:not([data-rendered="true"])').forEach(element => {
                try {
                    const content = element.textContent.trim();
                    
                    // Skip if empty
                    if (!content) return;
                    
                    element.innerHTML = marked.parse(content);
                    
                    // Apply syntax highlighting to code blocks (defensive)
                    element.querySelectorAll('pre code').forEach(block => {
                        if (typeof Prism !== 'undefined') {
                            try {
                                Prism.highlightElement(block);
                            } catch (error) {
                                console.warn('Prism highlighting failed:', error.message);
                                // Keep code block without highlighting
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error parsing markdown:', error);
                    // Keep original content if parsing fails
                }
            });

            const streaming = new ConversationStreaming();
            streaming.init();
        });
    </script>
    @endpush
</x-default-layout>
