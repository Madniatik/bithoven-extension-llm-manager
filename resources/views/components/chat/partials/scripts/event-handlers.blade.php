@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const workspace = document.querySelector('[data-session-id]');
    const sessionId = workspace?.dataset.sessionId || 'default';
    const monitorId = 'monitor-' + sessionId; // Match ChatWorkspace.php getMonitorId() format
    
    const sendBtn = document.getElementById(`send-btn-${sessionId}`);
    const stopBtn = document.getElementById(`stop-btn-${sessionId}`);
    const clearBtn = document.getElementById(`clear-btn-${sessionId}`);
    const newChatBtn = document.getElementById(`new-chat-btn-${sessionId}`);
    const messageInput = document.getElementById(`quick-chat-message-input-${sessionId}`);
    const modelSelector = document.getElementById(`quick-chat-model-selector-${sessionId}`);
    const messagesContainer = document.getElementById(`messages-container-${sessionId}`);
    const thinkingMessage = document.getElementById(`thinking-message-${sessionId}`);
    
    if (!sendBtn || !messageInput) return;
    
    let eventSource = null;
    let statsUpdateInterval = null;
    
    // Track user message for deletion if stopped before first chunk (GLOBAL SCOPE)
    let userMessageId = null;
    let savedUserPrompt = '';
    let chunkCount = 0;
    
    // Configure Marked.js for proper Markdown rendering (including code blocks)
    if (typeof marked !== 'undefined') {
        marked.setOptions({
            breaks: true,
            gfm: true,          // GitHub Flavored Markdown (supports ```)
            pedantic: false,    // Don't convert indented text to code blocks
            headerIds: false,
            mangle: false
        });
    }
    
    const showThinking = () => thinkingMessage?.classList.remove('d-none');
    const hideThinking = () => thinkingMessage?.classList.add('d-none');
    
    // Smart Auto-Scroll: detectar si usuario está en bottom
    let autoScrollEnabled = true; // Default: habilitado
    
    const isAtBottom = () => {
        if (!messagesContainer) return true;
        const threshold = 100; // 100px del bottom
        return messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < threshold;
    };
    
    // Scroll suave al bottom (solo si auto-scroll habilitado o forzado)
    const scrollToBottom = (force = false) => {
        if (!messagesContainer) return;
        
        if (autoScrollEnabled || force) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }
    };
    
    // Scroll para posicionar mensaje de usuario al top (con padding)
    const scrollToUserMessage = (messageBubble) => {
        if (!messagesContainer || !messageBubble) return;
        
        console.log('[Scroll] User message scroll triggered');
        const bubbleTop = messageBubble.offsetTop;
        const paddingTop = 20; // 20px de espacio arriba del bubble
        
        console.log('[Scroll] Bubble offsetTop:', bubbleTop, 'Target scroll:', bubbleTop - paddingTop);
        
        messagesContainer.scrollTo({
            top: bubbleTop - paddingTop,
            behavior: 'smooth'
        });
    };
    
    // Listener de scroll manual para detectar si usuario se aleja del bottom
    if (messagesContainer) {
        messagesContainer.addEventListener('scroll', () => {
            if (isAtBottom()) {
                autoScrollEnabled = true;
            } else {
                autoScrollEnabled = false;
            }
        });
    }
    
    const appendMessage = (role, content, tokens = 0, messageId = null, hidden = false) => {
        if (!messagesContainer) return;
        
        // Get provider/model from current configuration selector for assistant badges
        const configSelect = document.getElementById('quick-chat-model-selector-{{ $session?->id ?? "default" }}');
        const selectedOption = configSelect?.options[configSelect.selectedIndex];
        const provider = selectedOption?.dataset.provider || '';
        const model = selectedOption?.dataset.model || '';
        
        // Clone template
        const template = document.getElementById('message-bubble-template-{{ $session?->id ?? "default" }}');
        if (!template) {
            console.error('Message bubble template not found');
            return;
        }
        
        const bubble = template.content.cloneNode(true);
        const bubbleDiv = bubble.querySelector('.message-bubble');
        
        // Set role and message ID
        bubbleDiv.dataset.role = role;
        if (messageId) bubbleDiv.dataset.messageId = messageId;
        if (hidden) bubbleDiv.classList.add('d-none');
        
        // Justify content (alignment at bubble level)
        if (role === 'user') {
            bubbleDiv.classList.add('justify-content-end');
        } else {
            bubbleDiv.classList.add('justify-content-start');
        }
        
        // Alignment (inner wrapper)
        const alignment = role === 'user' ? 'align-items-end' : 'align-items-start';
        const innerWrapper = bubble.querySelector('[data-bubble-alignment]');
        innerWrapper.classList.add(alignment);
        
        // Avatar visibility
        if (role === 'assistant') {
            bubble.querySelector('.assistant-avatar')?.classList.remove('d-none');
        } else {
            bubble.querySelector('.user-avatar')?.classList.remove('d-none');
        }
        
        // Header text (name/model)
        const headerText = bubble.querySelector('[data-bubble-header-text]');
        if (role === 'assistant' && provider && model) {
            headerText.textContent = `${provider} / ${model}`;
        } else {
            headerText.textContent = role === 'user' ? '{{ auth()->user()->name ?? "User" }}' : 'Assistant';
        }
        
        // Timestamp
        const timestamp = new Date().toLocaleTimeString();
        bubble.querySelector('[data-bubble-timestamp]').textContent = timestamp;
        
        // Background color
        const bgClass = role === 'user' ? 'bg-light-success' : 'bg-light-primary';
        bubble.querySelector('.bubble-content-wrapper').classList.add(bgClass);
        
        // Content
        const contentDiv = bubble.querySelector('.message-content');
        if (role === 'assistant') {
            contentDiv.dataset.role = 'assistant';
        }
        
        // Renderizar Markdown
        let renderedContent = content.trim();
        if (typeof marked !== 'undefined' && renderedContent) {
            try {
                renderedContent = marked.parse(renderedContent);
            } catch (e) {
                console.warn('Markdown parsing failed:', e);
                renderedContent = content.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
            }
        } else {
            renderedContent = content.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }
        
        contentDiv.innerHTML = renderedContent;
        
        // Show footer for assistant
        if (role === 'assistant') {
            bubble.querySelector('.bubble-footer-container')?.classList.remove('d-none');
        }
        
        // Insert into DOM
        const insertedBubble = bubble.querySelector('.message-bubble');
        if (thinkingMessage) {
            messagesContainer.insertBefore(bubble, thinkingMessage);
        } else {
            messagesContainer.appendChild(bubble);
        }
        
        // Apply syntax highlighting AFTER DOM insertion
        if (contentDiv && typeof Prism !== 'undefined') {
            contentDiv.querySelectorAll('pre code').forEach(block => {
                try {
                    Prism.highlightElement(block);
                } catch (e) {
                    console.warn('Prism highlighting failed:', e);
                }
            });
        }
        
        // Add copy buttons to code blocks
        if (contentDiv) {
            contentDiv.querySelectorAll('pre').forEach(pre => {
                if (!pre.querySelector('.copy-code-btn')) {
                    const copyBtn = document.createElement('button');
                    copyBtn.className = 'btn btn-icon btn-sm btn-light-primary position-absolute top-0 end-0 m-1 copy-code-btn';
                    copyBtn.setAttribute('data-bs-toggle', 'tooltip');
                    copyBtn.setAttribute('title', 'Copy code');
                    copyBtn.onclick = function() { copyCodeBlock(this); };
                    copyBtn.innerHTML = '<i class="ki-duotone ki-copy fs-7"><span class="path1"></span><span class="path2"></span></i>';
                    pre.style.position = 'relative';
                    pre.appendChild(copyBtn);
                }
            });
        }
        
        // Add action buttons to bubble content wrapper (copy + raw)
        if (role === 'assistant') {
            const bubbleContent = messagesContainer.querySelector(`[data-message-id="${messageId}"] .bubble-content-wrapper`);
            if (bubbleContent && !bubbleContent.querySelector('.message-actions-container')) {
                const btnContainer = document.createElement('div');
                btnContainer.className = 'message-actions-container position-absolute top-0 end-0 m-2 d-flex gap-1';
                
                // Raw view button (only if message ID exists and is NOT temporary)
                if (messageId && !messageId.toString().startsWith('msg-')) {
                    const rawBtn = document.createElement('button');
                    rawBtn.className = 'btn btn-icon btn-sm btn-light-info raw-view-btn';
                    rawBtn.setAttribute('data-bs-toggle', 'tooltip');
                    rawBtn.setAttribute('title', 'View raw data');
                    rawBtn.onclick = function() { showRawMessage(messageId); };
                    rawBtn.innerHTML = '<i class="ki-duotone ki-code fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>';
                    btnContainer.appendChild(rawBtn);
                }
                
                // Copy button
                const copyBtn = document.createElement('button');
                copyBtn.className = 'btn btn-icon btn-sm btn-light copy-bubble-btn';
                copyBtn.setAttribute('data-bs-toggle', 'tooltip');
                copyBtn.setAttribute('title', 'Copy message');
                copyBtn.onclick = function() { copyBubbleContent(this); };
                copyBtn.innerHTML = '<i class="ki-duotone ki-copy fs-6"><span class="path1"></span><span class="path2"></span></i>';
                btnContainer.appendChild(copyBtn);
                
                bubbleContent.style.position = 'relative';
                bubbleContent.appendChild(btnContainer);
            }
        }
        
        scrollToBottom();
        
        // Retornar el bubble insertado (para poder hacer scroll a él)
        return insertedBubble;
    };
    
    /**
     * Sanitize incomplete Markdown to prevent broken HTML
     * Closes unclosed code blocks, lists, and inline code
     */
    const sanitizeIncompleteMarkdown = (content) => {
        let sanitized = content;
        
        // Count unclosed code blocks (```)
        const codeBlockMatches = sanitized.match(/```/g);
        if (codeBlockMatches && codeBlockMatches.length % 2 !== 0) {
            sanitized += '\n```'; // Close unclosed code block
        }
        
        // Count unclosed inline code (`)
        const inlineCodeMatches = sanitized.match(/`/g);
        if (inlineCodeMatches && inlineCodeMatches.length % 2 !== 0) {
            sanitized += '`'; // Close unclosed inline code
        }
        
        // Close unclosed HTML-like tags (basic protection)
        const openTags = sanitized.match(/<([a-z]+)(?![^>]*\/>)[^>]*>/gi) || [];
        const closeTags = sanitized.match(/<\/([a-z]+)>/gi) || [];
        
        if (openTags.length > closeTags.length) {
            // Simple approach: add closing tags for common markdown-generated HTML
            const commonTags = ['div', 'span', 'code', 'pre', 'ul', 'ol', 'li'];
            commonTags.forEach(tag => {
                const opens = (sanitized.match(new RegExp(`<${tag}[^>]*>`, 'gi')) || []).length;
                const closes = (sanitized.match(new RegExp(`</${tag}>`, 'gi')) || []).length;
                if (opens > closes) {
                    for (let i = 0; i < (opens - closes); i++) {
                        sanitized += `</${tag}>`;
                    }
                }
            });
        }
        
        return sanitized;
    };
    
    const updateMessage = (messageId, content, tokens = 0) => {
        const messageDiv = messagesContainer.querySelector(`[data-message-id="${messageId}"]`);
        if (!messageDiv) return;
        
        const contentDiv = messageDiv.querySelector('.message-content');
        const tokensDiv = messageDiv.querySelector('.message-tokens');
        
        // Ensure data-role attribute is set for assistant messages
        if (contentDiv && !contentDiv.hasAttribute('data-role')) {
            contentDiv.setAttribute('data-role', 'assistant');
        }
        
        // Sanitize incomplete markdown (for partial responses)
        const sanitizedContent = sanitizeIncompleteMarkdown(content.trim());
        
        // Render Markdown with marked.js
        let renderedContent = sanitizedContent;
        if (typeof marked !== 'undefined') {
            try {
                renderedContent = marked.parse(sanitizedContent);
            } catch (e) {
                console.warn('Markdown parsing failed:', e);
                renderedContent = sanitizedContent.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
        } else {
            renderedContent = sanitizedContent.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        
        if (contentDiv) {
            contentDiv.innerHTML = renderedContent;
            
            // Apply syntax highlighting to code blocks
            if (typeof Prism !== 'undefined') {
                contentDiv.querySelectorAll('pre code').forEach(block => {
                    try {
                        Prism.highlightElement(block);
                    } catch (e) {
                        console.warn('Prism highlighting failed:', e);
                    }
                });
            }
            
            // Add copy buttons to code blocks
            contentDiv.querySelectorAll('pre').forEach(pre => {
                if (!pre.querySelector('.copy-code-btn')) {
                    const copyBtn = document.createElement('button');
                    copyBtn.className = 'btn btn-icon btn-sm btn-light-primary position-absolute top-0 end-0 m-1 copy-code-btn';
                    copyBtn.setAttribute('data-bs-toggle', 'tooltip');
                    copyBtn.setAttribute('title', 'Copy code');
                    copyBtn.onclick = function() { copyCodeBlock(this); };
                    copyBtn.innerHTML = '<i class="ki-duotone ki-copy fs-7"><span class="path1"></span><span class="path2"></span></i>';
                    pre.style.position = 'relative';
                    pre.appendChild(copyBtn);
                }
            });
        }
        
        // Update footer tokens during streaming
        if (tokens > 0) {
            const footer = messageDiv.querySelector('.bubble-footer');
            const tokensSpan = footer?.querySelector('.footer-tokens');
            if (tokensSpan) {
                tokensSpan.innerHTML = `
                    <i class="ki-duotone ki-calculator fs-7 text-gray-400">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    ${tokens} tokens
                `;
            }
        }
        scrollToBottom();
    };
    
    const addMonitorLog = (message, type = 'info') => {
        const monitorLogs = document.getElementById(`monitor-logs-${sessionId}`);
        if (!monitorLogs) return;
        
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
        
        // Don't show timestamp for separator/empty/header lines
        if (message.startsWith('━') || message === '' || type === 'header') {
            logLine.textContent = message;
        } else {
            logLine.textContent = `[${timestamp}] ${message}`;
        }
        
        // Clear "ready" message on first real log
        if (monitorLogs.children.length === 1 && monitorLogs.textContent.includes('Monitor ready')) {
            monitorLogs.innerHTML = '';
        }
        
        monitorLogs.appendChild(logLine);
        
        // Auto-scroll to bottom
        const monitorContainer = monitorLogs.closest('.card-body');
        if (monitorContainer) {
            monitorContainer.scrollTop = monitorContainer.scrollHeight;
        }
    };
    
    const renderMessages = () => {
        if (!messagesContainer) return;
        
        // Clear existing messages (except thinking)
        const existingMessages = messagesContainer.querySelectorAll('.message-bubble');
        existingMessages.forEach(el => {
            if (el.id !== `thinking-message-${sessionId}`) {
                el.remove();
            }
        });
        
        // Render all messages from currentMessages array
        currentMessages.forEach((msg, index) => {
            appendMessage(msg.role, msg.content, msg.tokens, `msg-loaded-${index}`);
        });
    };
    
    const sendMessage = async () => {
        const prompt = messageInput.value.trim();
        if (!prompt || !modelSelector?.value) {
            toastr.warning('Please enter a message and select a model');
            return;
        }
        
        sendBtn.disabled = true;
        sendBtn.classList.add('d-none');
        stopBtn?.classList.remove('d-none');
        messageInput.disabled = true;
        const userPrompt = messageInput.value;
        messageInput.value = '';
        
        // Add user message to UI
        const userBubble = appendMessage('user', userPrompt);
        
        // Scroll inteligente: posicionar mensaje de usuario arriba (con padding)
        if (userBubble) {
            setTimeout(() => scrollToUserMessage(userBubble), 100);
            autoScrollEnabled = true; // Asegurar que auto-scroll está activo
        }
        
        addMonitorLog('🚀 Sending message to LLM...', 'info');
        addMonitorLog(`   Prompt: "${userPrompt.substring(0, 50)}${userPrompt.length > 50 ? '...' : ''}"`, 'debug');
        
        // Update thinking message with model info
        const configSelect = document.getElementById('quick-chat-model-selector-{{ $session?->id ?? "default" }}');
        const selectedOption = configSelect?.options[configSelect.selectedIndex];
        const thinkingProvider = selectedOption?.dataset.provider || '';
        const thinkingModel = selectedOption?.dataset.model || '';
        const thinkingModelInfo = document.getElementById('thinking-model-info-{{ $session?->id ?? "default" }}');
        if (thinkingModelInfo && thinkingProvider && thinkingModel) {
            thinkingModelInfo.textContent = `${thinkingProvider} / ${thinkingModel} thinking`;
        }
        
        showThinking();
        
        // Create empty assistant message (hidden until first chunk arrives)
        const assistantMessageId = `msg-${Date.now()}`;
        appendMessage('assistant', '', 0, assistantMessageId, true);
        
        // Get current settings from UI
        const temperature = parseFloat(document.getElementById('quick-chat-temperature')?.value || 0.7);
        const maxTokens = parseInt(document.getElementById('quick-chat-max-tokens')?.value || 2000, 10);
        const contextLimit = parseInt(document.getElementById('quick-chat-context-limit')?.value || 10, 10);
        
        // ========================================
        // 🔥 POPULATE REQUEST INSPECTOR (IMMEDIATE + SSE UPDATE)
        // ========================================
        // Phase 1: Build PARTIAL request data from form (immediate UI feedback)
        const partialRequestData = {
            metadata: {
                provider: thinkingProvider,
                model: thinkingModel,
                endpoint: selectedOption?.dataset.endpoint || 'N/A',
                timestamp: new Date().toISOString(),
                session_id: sessionId,
                message_id: null, // Will be set by backend
            },
            parameters: {
                temperature: temperature,
                max_tokens: maxTokens,
                context_limit: contextLimit,
            },
            system_instructions: selectedOption?.dataset.systemInstructions || null,
            context_messages: [], // Will be populated by SSE event
            current_prompt: userPrompt,
            full_request_body: {
                model: thinkingModel,
                prompt: userPrompt,
                temperature: temperature,
                max_tokens: maxTokens,
                stream: true,
            }
        };
        
        // Populate IMMEDIATELY with partial data (instant feedback)
        if (typeof window.populateRequestInspector === 'function') {
            window.populateRequestInspector(partialRequestData);
            addMonitorLog('📋 Request Inspector: partial data loaded', 'debug');
        } else {
            console.warn('[Event Handlers] populateRequestInspector function not found');
        }
        
        // EventSource with session_id, configuration_id, and custom parameters
        const params = new URLSearchParams({
            session_id: sessionId,
            configuration_id: modelSelector.value,
            prompt: userPrompt,
            temperature: temperature,
            max_tokens: maxTokens,
            context_limit: contextLimit
        });
        
        eventSource = new EventSource('{{ route("admin.llm.quick-chat.stream") }}?' + params);
        
        // Listen for request_data SSE event (Phase 2: Update with complete context_messages)
        eventSource.addEventListener('request_data', (event) => {
            const data = JSON.parse(event.data);
            console.log('[Event Handlers] SSE request_data received (COMPLETE with context)', data);
            
            // UPDATE Request Inspector with COMPLETE data (including context_messages from backend)
            if (typeof window.populateRequestInspector === 'function') {
                window.populateRequestInspector(data);
                addMonitorLog('📋 Request Inspector: updated with context_messages', 'success');
                console.log('[Event Handlers] Request Inspector updated with context_messages');
            } else {
                console.warn('[Event Handlers] populateRequestInspector function not found');
            }
        });
        
        // Start monitor tracking with provider/model
        if (window.LLMMonitor) {
            window.LLMMonitor.start(thinkingProvider, thinkingModel, monitorId);
        }
        
        let fullResponse = '';
        chunkCount = 0; // Reset global chunkCount
        let startTime = Date.now();
        let warningShown = false;
        let firstChunkTime = null;
        let streamCompleted = false; // Track if stream completed successfully
        const baseMaxTokens = {{ $configurations->first()->default_parameters['max_tokens'] ?? 8000 }};
        let currentMaxTokens = maxTokens; // Use UI value instead of base default
        
        // Initialize thinking tokens with input_tokens (prompt tokens)
        let inputTokens = 0;
        
        // Reset tracking variables for new message
        userMessageId = null;
        savedUserPrompt = '';
        
        // Update streaming stats in real-time (every 100ms)
        statsUpdateInterval = setInterval(() => {
            if (thinkingMessage && !thinkingMessage.classList.contains('d-none')) {
                const elapsed = ((Date.now() - startTime) / 1000).toFixed(2);
                const ttft = firstChunkTime ? ((firstChunkTime - startTime) / 1000).toFixed(2) : '...';
                
                const tokensSpan = thinkingMessage.querySelector('.thinking-tokens');
                const timeSpan = thinkingMessage.querySelector('.thinking-time');
                const ttftSpan = thinkingMessage.querySelector('.thinking-ttft');
                
                // Show only chunks received (not total tokens)
                if (tokensSpan) tokensSpan.textContent = chunkCount;
                if (timeSpan) timeSpan.textContent = elapsed;
                if (ttftSpan) ttftSpan.textContent = ttft;
            }
        }, 100);
        
        eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            
            if (data.type === 'metadata') {
                // Capture input_tokens from metadata event (sent before streaming starts)
                if (data.input_tokens) {
                    inputTokens = data.input_tokens;
                    addMonitorLog(`📥 Input tokens: ${inputTokens}`, 'debug');
                }
                // Capture user message ID and prompt for potential deletion/restoration
                if (data.user_message_id) {
                    userMessageId = data.user_message_id;
                    savedUserPrompt = data.user_prompt || '';
                    addMonitorLog(`💾 User message ID: ${userMessageId}`, 'debug');
                }
            } else if (data.type === 'chunk') {
                fullResponse += data.content;
                chunkCount++;
                const currentTokens = data.tokens || chunkCount;
                
                // Track chunk in monitor
                if (window.LLMMonitor) {
                    window.LLMMonitor.trackChunk(data.content, currentTokens, monitorId);
                }
                
                // Show assistant bubble on first chunk
                if (chunkCount === 1) {
                    const assistantBubble = messagesContainer.querySelector(`[data-message-id="${assistantMessageId}"]`);
                    if (assistantBubble) {
                        assistantBubble.classList.remove('d-none');
                    }
                    firstChunkTime = Date.now();
                    hideThinking();
                    addMonitorLog('✅ Streaming started', 'success');
                    addMonitorLog('⏳ Receiving chunks...', 'info');
                }
                
                // Update message DOM with Markdown rendering
                updateMessage(assistantMessageId, fullResponse, currentTokens);
                
                // Update footer with real-time metrics during streaming
                const assistantBubble = messagesContainer.querySelector(`[data-message-id="${assistantMessageId}"]`);
                if (assistantBubble) {
                    const footer = assistantBubble.querySelector('.bubble-footer');
                    
                    // Update response time in real-time
                    const currentResponseTime = ((Date.now() - startTime) / 1000).toFixed(2);
                    const responseTimeSpan = footer?.querySelector('.footer-response-time');
                    if (responseTimeSpan) {
                        responseTimeSpan.innerHTML = `
                            <i class="ki-duotone ki-timer fs-7 text-warning">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            ${currentResponseTime}s
                        `;
                        responseTimeSpan.classList.remove('text-gray-400');
                        responseTimeSpan.classList.add('text-warning');
                    }
                    
                    // Update TTFT once it's available
                    if (firstChunkTime) {
                        const ttft = ((firstChunkTime - startTime) / 1000).toFixed(2);
                        const ttftSpan = footer?.querySelector('.footer-ttft');
                        if (ttftSpan) {
                            ttftSpan.innerHTML = `
                                <i class="ki-duotone ki-flash-circle fs-7 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                TTFT: ${ttft}s
                            `;
                            ttftSpan.classList.remove('text-gray-400');
                            ttftSpan.classList.add('text-success');
                        }
                    }
                }
                
                if (chunkCount % 50 === 0) {
                    addMonitorLog(`📥 Received ${chunkCount} chunks (${currentTokens} tokens)`, 'info');
                }
                
                // Check if approaching max_tokens (90% threshold)
                const threshold = currentMaxTokens * 0.9;
                if (currentTokens > threshold && !warningShown) {
                    warningShown = true;
                    addMonitorLog('', 'info');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    addMonitorLog('⚠️  TOKEN LIMIT WARNING', 'header');
                    addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                    addMonitorLog(`   Current: ${currentTokens} tokens`, 'debug');
                    addMonitorLog(`   Limit: ${currentMaxTokens} tokens`, 'debug');
                    addMonitorLog(`   Usage: ${Math.round((currentTokens/currentMaxTokens)*100)}%`, 'debug');
                    addMonitorLog('', 'info');
                    
                    Swal.fire({
                        title: 'Token Limit Warning',
                        html: `<div class="text-start">
                            <p>Approaching max tokens:</p>
                            <ul>
                                <li><strong>Current:</strong> ${currentTokens} tokens</li>
                                <li><strong>Limit:</strong> ${currentMaxTokens} tokens</li>
                                <li><strong>Usage:</strong> ${Math.round((currentTokens/currentMaxTokens)*100)}%</li>
                            </ul>
                            <p class="text-muted">If you continue, ${baseMaxTokens} more tokens will be added (new limit: ${currentMaxTokens + baseMaxTokens}).</p>
                        </div>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: `Continue (+${baseMaxTokens} tokens)`,
                        cancelButtonText: 'Stop Stream',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-danger'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentMaxTokens += baseMaxTokens;
                            warningShown = false;
                            addMonitorLog(`✅ Token limit extended to ${currentMaxTokens}`, 'success');
                            addMonitorLog(`   Next warning at ${Math.round(currentMaxTokens * 0.9)} tokens (90%)`, 'debug');
                            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                            addMonitorLog('', 'info');
                        } else {
                            eventSource.close();
                            hideThinking();
                            addMonitorLog('⏸️  Stream stopped by user', 'info');
                            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                            toastr.info('Stream stopped');
                        }
                    });
                }
                
            } else if (data.type === 'done') {
                streamCompleted = true; // Mark stream as successfully completed
                hideThinking();
                clearInterval(statsUpdateInterval);
                const duration = Date.now() - startTime;
                
                // If no chunks received (empty response / error), create bubble now
                if (chunkCount === 0 && data.content) {
                    appendMessage('assistant', data.content, 0, assistantMessageId, false);
                }
                
                // Update message bubble with real DB message ID
                if (data.message_id) {
                    const assistantBubble = messagesContainer.querySelector(`[data-message-id="${assistantMessageId}"]`);
                    if (assistantBubble) {
                        assistantBubble.dataset.messageId = data.message_id;
                        
                        // Add error badge if this is an error message
                        if (data.metadata?.is_error) {
                            const headerDiv = assistantBubble.querySelector('.text-gray-600')?.parentElement;
                            if (headerDiv && !headerDiv.querySelector('.badge-warning')) {
                                const errorBadge = document.createElement('span');
                                errorBadge.className = 'badge badge-light-warning badge-sm ms-2';
                                errorBadge.title = 'This message contains an error explanation';
                                errorBadge.innerHTML = `
                                    <i class="ki-duotone ki-information-5 fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Error Message
                                `;
                                
                                // Insert after model badge, before timestamp
                                const timeSpan = headerDiv.querySelector('.text-gray-500.fs-8');
                                if (timeSpan) {
                                    headerDiv.insertBefore(errorBadge, timeSpan);
                                } else {
                                    headerDiv.appendChild(errorBadge);
                                }
                            }
                        }
                        
                        // Add raw data button now that we have real DB ID
                        const bubbleContent = assistantBubble.querySelector('.bubble-content-wrapper');
                        const btnContainer = bubbleContent?.querySelector('.message-actions-container');
                        
                        if (btnContainer && !btnContainer.querySelector('.raw-view-btn')) {
                            const rawBtn = document.createElement('button');
                            rawBtn.className = 'btn btn-icon btn-sm btn-light-info raw-view-btn';
                            rawBtn.setAttribute('data-bs-toggle', 'tooltip');
                            rawBtn.setAttribute('title', 'View raw data');
                            rawBtn.onclick = function() { showRawMessage(data.message_id); };
                            rawBtn.innerHTML = '<i class="ki-duotone ki-code fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>';
                            
                            // Insert before copy button
                            const copyBtn = btnContainer.querySelector('.copy-bubble-btn');
                            if (copyBtn) {
                                btnContainer.insertBefore(rawBtn, copyBtn);
                            } else {
                                btnContainer.appendChild(rawBtn);
                            }
                            
                            // Initialize tooltip
                            if (typeof bootstrap !== 'undefined') {
                                new bootstrap.Tooltip(rawBtn);
                            }
                        }
                        
                        // Update footer with final metrics
                        const footer = assistantBubble.querySelector('.bubble-footer');
                        
                        if (footer) {
                            // Tokens breakdown (prompt + completion)
                            const promptTokens = data.usage?.prompt_tokens || 0;
                            const completionTokens = data.usage?.completion_tokens || 0;
                            const totalTokens = data.usage?.total_tokens || 0;
                            
                            const tokensSpan = footer.querySelector('.footer-tokens');
                            if (tokensSpan) {
                                tokensSpan.innerHTML = `
                                    <i class="ki-duotone ki-calculator fs-7 text-gray-400">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    ${totalTokens} tokens <span class="text-gray-400" title="Sent / Received">(↑${promptTokens} / ↓${completionTokens})</span>
                                `;
                            }
                            
                            // Response Time
                            const responseTimeSpan = footer.querySelector('.footer-response-time');
                            if (responseTimeSpan && data.response_time) {
                                responseTimeSpan.innerHTML = `
                                    <i class="ki-duotone ki-timer fs-7 text-success">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    ${data.response_time.toFixed(2)}s
                                `;
                                responseTimeSpan.classList.remove('text-gray-400');
                                responseTimeSpan.classList.add('text-success');
                            }
                            
                            // TTFT (Time to First Chunk)
                            const ttftSpan = footer.querySelector('.footer-ttft');
                            if (ttftSpan && data.ttft) {
                                ttftSpan.innerHTML = `
                                    <i class="ki-duotone ki-flash-circle fs-7 text-warning">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    TTFT: ${data.ttft.toFixed(2)}s
                                `;
                                ttftSpan.classList.remove('text-gray-400');
                                ttftSpan.classList.add('text-warning');
                            }
                            
                            // Cost
                            const costSpan = footer.querySelector('.footer-cost');
                            if (costSpan && data.cost !== undefined) {
                                const costValue = parseFloat(data.cost).toFixed(6);
                                if (costValue > 0) {
                                    costSpan.innerHTML = `
                                        <i class="ki-duotone ki-dollar fs-7 text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        $${costValue}
                                    `;
                                    costSpan.classList.remove('text-gray-400', 'd-none');
                                    costSpan.classList.add('text-primary');
                                } else {
                                    costSpan.classList.add('d-none');
                                }
                            }
                        }
                    }
                }
                
                // Custom log for message ID (not in standard monitor output)
                if (data.message_id) {
                    addMonitorLog(`💾 Message ID: #${data.message_id}`, 'debug');
                }
                
                // Complete monitor tracking with full metrics
                const provider = selectedOption?.dataset.provider || 'unknown';
                const model = selectedOption?.dataset.model || 'unknown';
                if (window.LLMMonitor) {
                    window.LLMMonitor.complete(
                        provider, 
                        model, 
                        data.usage || null, 
                        data.cost || null, 
                        data.execution_time_ms || null, 
                        monitorId
                    );
                }
                
                eventSource?.close();
                eventSource = null;
                sendBtn.disabled = false;
                sendBtn.classList.remove('d-none');
                stopBtn?.classList.add('d-none');
                messageInput.disabled = false;
                messageInput.focus();
                
            } else if (data.type === 'error') {
                hideThinking();
                clearInterval(statsUpdateInterval);
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('❌ ERROR OCCURRED', 'header');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('', 'info');
                addMonitorLog('Error: ' + data.message, 'error');
                if (fullResponse) {
                    addMonitorLog('   Partial response received: ' + fullResponse.length + ' chars', 'debug');
                    addMonitorLog('   Chunks received: ' + chunkCount, 'debug');
                } else {
                    addMonitorLog('   No response received before error', 'debug');
                }
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                
                // Log error in monitor
                if (window.LLMMonitor) {
                    window.LLMMonitor.error(data.message || 'Unknown error', monitorId);
                }
                
                toastr.error(data.message);
                
                // Update bubble with error content (backend saves error message)
                if (data.message_id) {
                    const assistantBubble = messagesContainer.querySelector('[data-message-id="' + assistantMessageId + '"]');
                    if (assistantBubble) {
                        // Update message ID
                        assistantBubble.dataset.messageId = data.message_id;
                        
                        // Update content with error message
                        if (data.content) {
                            updateMessage(data.message_id, data.content, 0);
                        }
                        
                        // Add error badge
                        const headerDiv = assistantBubble.querySelector('.text-gray-600')?.parentElement;
                        if (headerDiv && !headerDiv.querySelector('.badge-warning')) {
                            const errorBadge = document.createElement('span');
                            errorBadge.className = 'badge badge-light-warning badge-sm ms-2';
                            errorBadge.title = 'This message contains an error explanation';
                            errorBadge.innerHTML = '<i class="ki-duotone ki-information-5 fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Error Message';
                            const timeSpan = headerDiv.querySelector('.text-gray-500.fs-8');
                            if (timeSpan) {
                                headerDiv.insertBefore(errorBadge, timeSpan);
                            } else {
                                headerDiv.appendChild(errorBadge);
                            }
                        }
                        
                        // Add retry button
                        const bubbleContent = assistantBubble.querySelector('.bubble-content-wrapper');
                        const contentDiv = bubbleContent?.querySelector('.message-content');
                        if (contentDiv && !contentDiv.parentElement.querySelector('.retry-error-btn')) {
                            const retryContainer = document.createElement('div');
                            retryContainer.className = 'mt-3 pt-3 border-top border-gray-300';
                            const retryBtn = document.createElement('button');
                            retryBtn.type = 'button';
                            retryBtn.className = 'btn btn-sm btn-light-warning retry-error-btn';
                            retryBtn.onclick = function() { retryErrorMessage(data.message_id); };
                            retryBtn.innerHTML = '<i class="ki-duotone ki-arrows-circle fs-6"><span class="path1"></span><span class="path2"></span></i> Retry with Higher Token Limit';
                            retryContainer.appendChild(retryBtn);
                            contentDiv.parentElement.appendChild(retryContainer);
                        }
                    }
                } else if (!fullResponse) {
                    // Only remove bubble if NO response AND no message_id (truly failed)
                    const assistantMessageDiv = messagesContainer.querySelector('[data-message-id="' + assistantMessageId + '"]');
                    assistantMessageDiv?.remove();
                }
                
                eventSource?.close();
                sendBtn.disabled = false;
                sendBtn.classList.remove('d-none');
                stopBtn?.classList.add('d-none');
                messageInput.disabled = false;
            }
        };
        
        eventSource.onerror = (error) => {
            // If stream completed successfully, this is just SSE connection closing (not a real error)
            if (streamCompleted) {
                eventSource?.close();
                sendBtn.disabled = false;
                sendBtn.classList.remove('d-none');
                stopBtn?.classList.add('d-none');
                messageInput.disabled = false;
                return; // Exit silently - stream finished OK
            }
            
            // Real error - stream didn't complete
            console.error('EventSource error:', error);
            hideThinking();
            clearInterval(statsUpdateInterval);
            addMonitorLog('', 'info');
            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
            addMonitorLog('❌ CONNECTION ERROR', 'header');
            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
            addMonitorLog('', 'info');
            addMonitorLog('Lost connection to server', 'error');
            addMonitorLog('', 'info');
            addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
            toastr.error('Streaming connection lost');
            
            eventSource?.close();
            sendBtn.disabled = false;
            sendBtn.classList.remove('d-none');
            stopBtn?.classList.add('d-none');
            messageInput.disabled = false;
        };
    };
    
    sendBtn.addEventListener('click', sendMessage);
    stopBtn?.addEventListener('click', async () => {
        if (eventSource) {
            clearInterval(statsUpdateInterval);
            eventSource.close();
            eventSource = null;
            hideThinking();
            sendBtn.disabled = false;
            sendBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            messageInput.disabled = false;
            
            const stoppedBeforeFirstChunk = chunkCount === 0;
            
            if (stoppedBeforeFirstChunk) {
                // Case 1: Stopped during "Thinking..." (before first chunk)
                addMonitorLog('⏸️  Streaming stopped BEFORE first chunk', 'info');
                addMonitorLog('   → Deleting user message from DB', 'debug');
                addMonitorLog('   → Removing user bubble from UI', 'debug');
                addMonitorLog('   → Restoring prompt to input', 'debug');
                
                // Delete user message from database
                if (userMessageId) {
                    try {
                        await fetch(`/admin/llm/messages/${userMessageId}`, {
                            method: 'DELETE',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                'Accept': 'application/json',
                            }
                        });
                    } catch (error) {
                        console.error('Failed to delete user message:', error);
                    }
                }
                
                // Remove user bubble from UI (find last visible user bubble before thinking message)
                const allUserBubbles = Array.from(messagesContainer.querySelectorAll('.message-bubble.justify-content-end'));
                const userBubble = allUserBubbles[allUserBubbles.length - 1]; // Get the last one
                if (userBubble) {
                    userBubble.remove();
                }
                
                // Restore prompt to input
                if (savedUserPrompt) {
                    messageInput.value = savedUserPrompt;
                }
                
                toastr.info('Stream stopped. Prompt restored to input.');
                
            } else {
                // Case 2: Stopped AFTER first chunk (streaming already started)
                addMonitorLog('⏸️  Streaming stopped AFTER first chunk', 'info');
                addMonitorLog('   → Deleting user message from DB', 'debug');
                addMonitorLog('   → Keeping user bubble visible', 'debug');
                addMonitorLog('   → Partial response kept visible (not saved to DB)', 'debug');
                
                // Delete user message from database (even though bubble stays visible)
                if (userMessageId) {
                    try {
                        await fetch(`/admin/llm/messages/${userMessageId}`, {
                            method: 'DELETE',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                'Accept': 'application/json',
                            }
                        });
                    } catch (error) {
                        console.error('Failed to delete user message:', error);
                    }
                }
                
                toastr.warning('Stream stopped. User message not saved. Partial response will disappear on refresh.');
            }
            
            // Keep partial assistant response visible (DON'T remove bubble)
            // It will disappear naturally on page refresh since it's not in DB
        }
    });
    
    /**
     * Clear/Delete current chat conversation
     */
    const clearConversation = async () => {
        const result = await Swal.fire({
            title: 'Delete This Chat?',
            html: '<div class="text-start"><p class="text-muted">This will permanently delete all messages in this conversation.</p><p class="fw-bold text-danger">This action cannot be undone.</p></div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="ki-duotone ki-trash"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Delete Chat',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-light'
            }
        });
        
        if (result.isConfirmed) {
            toastr.info('Deleting chat...');
            window.location.href = '{{ route("admin.llm.quick-chat.delete", ["sessionId" => $session?->id ?? 0]) }}';
        }
    };
    
    clearBtn?.addEventListener('click', clearConversation);
    
    // Shift+Enter to send (Enter for new line - comportamiento estándar)
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    

    
    // Renderizar Markdown en mensajes pre-existentes al cargar la página
    const renderExistingMessages = () => {
        if (!messagesContainer) return;
        
        const messageContents = messagesContainer.querySelectorAll('.message-content .markdown-content');
        
        messageContents.forEach(contentDiv => {
            const rawContent = contentDiv.textContent;
            
            if (typeof marked !== 'undefined' && rawContent) {
                try {
                    const renderedHTML = marked.parse(rawContent);
                    contentDiv.innerHTML = renderedHTML;
                    
                    // Apply syntax highlighting
                    if (typeof Prism !== 'undefined') {
                        contentDiv.querySelectorAll('pre code').forEach(block => {
                            try {
                                Prism.highlightElement(block);
                            } catch (e) {
                                console.warn('Prism highlighting failed:', e);
                            }
                        });
                    }
                } catch (e) {
                    console.warn('Markdown parsing failed for existing message:', e);
                    contentDiv.innerHTML = rawContent.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
                }
            }
        });
    };
    
    // Ejecutar después de que Marked y Prism estén disponibles
    if (typeof marked !== 'undefined') {
        renderExistingMessages();
    } else {
        // Esperar un poco si Marked aún no está cargado
        setTimeout(renderExistingMessages, 500);
    }
    
    /**
     * New Chat Button Handler
     * Shows modal with optional custom title
     */
    newChatBtn?.addEventListener('click', async () => {
        const now = new Date();
        const defaultTitle = 'Quick Chat - ' + now.getFullYear() + '-' + 
                           String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                           String(now.getDate()).padStart(2, '0') + ' ' + 
                           String(now.getHours()).padStart(2, '0') + ':' + 
                           String(now.getMinutes()).padStart(2, '0');
        
        const result = await Swal.fire({
            title: 'Start New Chat',
            html: `
                <div class="text-start">
                    <p class="mb-3 text-muted">Create a new conversation session.</p>
                    <label class="form-label fw-bold">Chat Title (optional):</label>
                    <input type="text" id="new-chat-title" class="form-control" 
                           placeholder="${defaultTitle}" 
                           maxlength="100">
                    <div class="form-text">Leave empty to use default title with timestamp</div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ki-duotone ki-plus"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Create Chat',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-light'
            },
            preConfirm: () => {
                const title = document.getElementById('new-chat-title').value.trim();
                return title || defaultTitle;
            }
        });
        
        if (result.isConfirmed) {
            const chatTitle = result.value;
            // Send title as query param to backend
            window.location.href = '{{ route("admin.llm.quick-chat.new") }}?title=' + encodeURIComponent(chatTitle);
        }
    });
    
    // Scroll inicial al último mensaje (al cargar página)
    setTimeout(() => {
        if (messagesContainer) {
            console.log('[Scroll] Initial scroll to bottom');
            console.log('[Scroll] Container scrollHeight:', messagesContainer.scrollHeight);
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'auto' // Instantáneo en carga inicial
            });
        } else {
            console.warn('[Scroll] Messages container not found for initial scroll');
        }
    }, 200);
    
    console.log('✅ Quick Chat ready - Press Enter or Send button');
});
</script>
@endpush