@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const workspace = document.querySelector('[data-session-id]');
    const sessionId = workspace?.dataset.sessionId || 'default';
    
    const sendBtn = document.getElementById(`send-btn-${sessionId}`);
    const stopBtn = document.getElementById(`stop-btn-${sessionId}`);
    const clearBtn = document.getElementById(`clear-btn-${sessionId}`);
    const messageInput = document.getElementById(`quick-chat-message-input-${sessionId}`);
    const modelSelector = document.getElementById(`quick-chat-model-selector-${sessionId}`);
    const messagesContainer = document.getElementById(`messages-container-${sessionId}`);
    const thinkingMessage = document.getElementById(`thinking-message-${sessionId}`);
    
    if (!sendBtn || !messageInput) return;
    
    let eventSource = null;
    let statsUpdateInterval = null;
    
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
    
    const scrollToBottom = () => {
        if (messagesContainer) {
            setTimeout(() => messagesContainer.scrollTop = messagesContainer.scrollHeight, 100);
        }
    };
    
    const appendMessage = (role, content, tokens = 0, messageId = null) => {
        if (!messagesContainer) return;
        
        const div = document.createElement('div');
        div.className = `d-flex mb-10 message-bubble ${role === 'user' ? 'justify-content-end' : 'justify-content-start'}`;
        if (messageId) div.dataset.messageId = messageId;
        
        const timestamp = new Date().toLocaleTimeString();
        
        // Renderizar Markdown para ambos roles
        let renderedContent = content;
        if (typeof marked !== 'undefined') {
            try {
                renderedContent = marked.parse(content);
            } catch (e) {
                console.warn('Markdown parsing failed:', e);
                renderedContent = content.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
            }
        } else {
            renderedContent = content.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }
        
        div.innerHTML = `
            <div class="d-flex flex-column align-items-${role === 'user' ? 'end' : 'start'}" style="width: 100%; max-width: 85%;">
                <div class="d-flex align-items-center mb-2">
                    ${role === 'assistant' ? '<div class="symbol symbol-35px symbol-circle me-3"><span class="symbol-label bg-light-primary text-primary fw-bold">AI</span></div>' : ''}
                    <div>
                        <span class="text-gray-600 fw-semibold fs-8">${role === 'user' ? '{{ auth()->user()->name ?? "User" }}' : 'Assistant'}</span>
                        <span class="text-gray-500 fw-semibold fs-8 ms-2">${timestamp}</span>
                    </div>
                    ${role === 'user' ? '<div class="symbol symbol-35px symbol-circle ms-3"><span class="symbol-label bg-light-success text-success fw-bold">U</span></div>' : ''}
                </div>
                <div class="p-5 rounded ${role === 'user' ? 'bg-light-success' : 'bg-light-primary'} bubble-content-wrapper" style="max-width: 85%">
                    <div class="message-content text-gray-800 fw-semibold fs-6"${role === 'assistant' ? ' data-role="assistant"' : ''}>${renderedContent}</div>
                </div>
                ${tokens > 0 && role === 'assistant' ? `<div class="message-tokens text-gray-500 fw-semibold fs-8 mt-1">${tokens} tokens</div>` : ''}
            </div>
        `;
        
        // Apply syntax highlighting + add copy buttons to code blocks
        const contentDiv = div.querySelector('.message-content');
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
            const bubbleContent = div.querySelector('.bubble-content-wrapper');
            if (bubbleContent && !bubbleContent.querySelector('.message-actions-container')) {
                const btnContainer = document.createElement('div');
                btnContainer.className = 'message-actions-container position-absolute top-0 end-0 m-2 d-flex gap-1';
                
                // Raw view button (only if message ID exists)
                if (messageId) {
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
        
        if (thinkingMessage) {
            messagesContainer.insertBefore(div, thinkingMessage);
        } else {
            messagesContainer.appendChild(div);
        }
        scrollToBottom();
        return div;
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
        
        // Render Markdown with marked.js
        let renderedContent = content;
        if (typeof marked !== 'undefined') {
            try {
                renderedContent = marked.parse(content);
            } catch (e) {
                console.warn('Markdown parsing failed:', e);
                renderedContent = content.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
        } else {
            renderedContent = content.replace(/</g, '&lt;').replace(/>/g, '&gt;');
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
        
        if (tokensDiv) {
            tokensDiv.textContent = `${tokens} tokens`;
        } else if (tokens > 0) {
            const wrapper = messageDiv.querySelector('.d-flex.flex-column');
            const newTokensDiv = document.createElement('div');
            newTokensDiv.className = 'message-tokens text-gray-500 fw-semibold fs-8 mt-1';
            newTokensDiv.textContent = `${tokens} tokens`;
            wrapper.appendChild(newTokensDiv);
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
        appendMessage('user', userPrompt);
        
        addMonitorLog('🚀 Sending message to LLM...', 'info');
        addMonitorLog(`   Prompt: "${userPrompt.substring(0, 50)}${userPrompt.length > 50 ? '...' : ''}"`, 'debug');
        
        showThinking();
        
        // Create empty assistant message (will be filled with streaming chunks)
        const assistantMessageId = `msg-${Date.now()}`;
        appendMessage('assistant', '', 0, assistantMessageId);
        
        // EventSource with session_id and configuration_id
        const params = new URLSearchParams({
            session_id: sessionId,
            configuration_id: modelSelector.value,
            prompt: userPrompt
        });
        
        eventSource = new EventSource('{{ route("admin.llm.quick-chat.stream") }}?' + params);
        
        let fullResponse = '';
        let chunkCount = 0;
        let startTime = Date.now();
        let warningShown = false;
        let firstChunkTime = null;
        const baseMaxTokens = {{ $configurations->first()->default_parameters['max_tokens'] ?? 8000 }};
        let currentMaxTokens = baseMaxTokens;
        
        // Update streaming stats in real-time (every 100ms)
        statsUpdateInterval = setInterval(() => {
            const thinkingStats = thinkingMessage?.querySelector('.text-gray-500.fw-semibold.fs-8');
            if (thinkingStats && !thinkingMessage.classList.contains('d-none')) {
                const elapsed = ((Date.now() - startTime) / 1000).toFixed(2);
                const ttft = firstChunkTime ? ((firstChunkTime - startTime) / 1000).toFixed(2) : '...';
                thinkingStats.innerHTML = `
                    <span><i class="ki-duotone ki-calculator fs-7 text-gray-400"><span class="path1"></span><span class="path2"></span></i> ${chunkCount} tokens</span>
                    <span class="text-info"><i class="ki-duotone ki-timer fs-7 text-info"><span class="path1"></span><span class="path2"></span></i> ${elapsed}s</span>
                    <span class="text-warning"><i class="ki-duotone ki-flash-circle fs-7 text-warning"><span class="path1"></span><span class="path2"></span></i> TTFT: ${ttft}s</span>
                    <span class="text-primary"><i class="ki-duotone ki-cloud-download fs-7 text-primary"><span class="path1"></span><span class="path2"></span></i> Streaming...</span>
                `;
            }
        }, 100);
        
        eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            
            if (data.type === 'chunk') {
                fullResponse += data.content;
                chunkCount++;
                const currentTokens = data.tokens || chunkCount;
                
                // Update message DOM with Markdown rendering
                updateMessage(assistantMessageId, fullResponse, currentTokens);
                
                if (chunkCount === 1) {
                    firstChunkTime = Date.now();
                    hideThinking();
                    addMonitorLog('✅ Streaming started', 'success');
                    addMonitorLog('⏳ Receiving chunks...', 'info');
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
                hideThinking();
                clearInterval(statsUpdateInterval);
                const duration = Date.now() - startTime;
                
                // Update message bubble with real DB message ID
                if (data.message_id) {
                    const assistantBubble = messagesContainer.querySelector(`[data-message-id="${assistantMessageId}"]`);
                    if (assistantBubble) {
                        assistantBubble.dataset.messageId = data.message_id;
                    }
                }
                
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('✅ STREAMING COMPLETED', 'header');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('', 'info');
                addMonitorLog('📊 FINAL METRICS:', 'info');
                if (data.usage) {
                    addMonitorLog(`   Prompt Tokens: ${data.usage.prompt_tokens || 0}`, 'debug');
                    addMonitorLog(`   Completion Tokens: ${data.usage.completion_tokens || 0}`, 'debug');
                    addMonitorLog(`   Total Tokens: ${data.usage.total_tokens || 0}`, 'debug');
                }
                if (data.cost !== undefined) {
                    addMonitorLog(`   Cost USD: $${parseFloat(data.cost).toFixed(6)}`, 'debug');
                }
                addMonitorLog(`   Total Chunks: ${chunkCount}`, 'debug');
                addMonitorLog(`   Duration: ${(duration / 1000).toFixed(2)}s`, 'debug');
                if (data.message_id) {
                    addMonitorLog(`   Message ID: #${data.message_id}`, 'debug');
                }
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                
                eventSource?.close();
                eventSource = null;
                sendBtn.disabled = false;
                sendBtn.classList.remove('d-none');
                stopBtn?.classList.add('d-none');
                messageInput.disabled = false;
                messageInput.focus();
                toastr.success(`Response complete! (${chunkCount} chunks, ${(duration/1000).toFixed(2)}s)`);
                
            } else if (data.type === 'error') {
                hideThinking();
                clearInterval(statsUpdateInterval);
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('❌ ERROR OCCURRED', 'header');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                addMonitorLog('', 'info');
                addMonitorLog(`Error: ${data.message}`, 'error');
                if (fullResponse) {
                    addMonitorLog(`   Partial response received: ${fullResponse.length} chars`, 'debug');
                    addMonitorLog(`   Chunks received: ${chunkCount}`, 'debug');
                    // Keep partial response visible (don't remove bubble)
                } else {
                    addMonitorLog(`   No response received before error`, 'debug');
                }
                addMonitorLog('', 'info');
                addMonitorLog('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'separator');
                toastr.error(data.message);
                
                // Only remove bubble if NO response was received
                if (!fullResponse) {
                    const assistantMessageDiv = messagesContainer.querySelector(`[data-message-id="${assistantMessageId}"]`);
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
    
    const clearConversation = () => {
        if (confirm('Start a new chat? This will reload the page.')) {
            window.location.href = '{{ route("admin.llm.quick-chat.new") }}';
        }
    };
    
    sendBtn.addEventListener('click', sendMessage);
    stopBtn?.addEventListener('click', () => {
        if (eventSource) {
            clearInterval(statsUpdateInterval);
            eventSource.close();
            eventSource = null;
            hideThinking();
            sendBtn.disabled = false;
            sendBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            messageInput.disabled = false;
            addMonitorLog('⏸️  Streaming stopped by user', 'info');
            toastr.info('Streaming stopped');
        }
    });
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
        
        console.log(`✅ Rendered ${messageContents.length} existing messages with Markdown`);
    };
    
    // Ejecutar después de que Marked y Prism estén disponibles
    if (typeof marked !== 'undefined') {
        renderExistingMessages();
    } else {
        // Esperar un poco si Marked aún no está cargado
        setTimeout(renderExistingMessages, 500);
    }
    
    console.log('✅ Quick Chat ready - Press Enter or Send button');
});
</script>
@endpush
