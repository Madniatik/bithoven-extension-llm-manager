@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const workspace = document.querySelector('[data-session-id]');
    const sessionId = workspace?.dataset.sessionId || 'default';
    
    const sendBtn = document.getElementById(`send-btn-${sessionId}`);
    const clearBtn = document.getElementById(`clear-btn-${sessionId}`);
    const messageInput = document.getElementById(`quick-chat-message-input-${sessionId}`);
    const modelSelector = document.getElementById(`quick-chat-model-selector-${sessionId}`);
    const messagesContainer = document.getElementById(`messages-container-${sessionId}`);
    const thinkingMessage = document.getElementById(`thinking-message-${sessionId}`);
    
    if (!sendBtn || !messageInput) return;
    
    let eventSource = null;
    
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
                    <span class="text-gray-600 fw-semibold fs-8">${role === 'user' ? 'You' : 'Assistant'}</span>
                    <span class="text-gray-500 fw-semibold fs-8 ms-2">${timestamp}</span>
                    ${role === 'user' ? '<div class="symbol symbol-35px symbol-circle ms-3"><span class="symbol-label bg-light-success text-success fw-bold">U</span></div>' : ''}
                </div>
                <div class="position-relative">
                    <div class="p-5 rounded ${role === 'user' ? 'bg-light-success' : 'bg-light-primary'}" style="max-width: 85%">
                        <div class="message-content text-gray-800 fw-semibold fs-6">${renderedContent}</div>
                    </div>
                    ${role === 'assistant' ? `
                    <div class="message-actions position-absolute top-0 end-0 p-2" style="opacity: 0; transition: opacity 0.2s;">
                        <button type="button" class="btn btn-sm btn-icon btn-light-primary me-1" onclick="copyMessage(this)" title="Copy">
                            <i class="ki-duotone ki-copy fs-5"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-light-info" onclick="showRawMessage(this)" title="Raw">
                            <i class="ki-duotone ki-code fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                        </button>
                    </div>
                    ` : ''}
                </div>
                ${tokens > 0 && role === 'assistant' ? `<div class="message-tokens text-gray-500 fw-semibold fs-8 mt-1">${tokens} tokens</div>` : ''}
            </div>
        `;
        
        // Show actions on hover
        if (role === 'assistant') {
            const wrapper = div.querySelector('.position-relative');
            const actions = div.querySelector('.message-actions');
            wrapper.addEventListener('mouseenter', () => actions.style.opacity = '1');
            wrapper.addEventListener('mouseleave', () => actions.style.opacity = '0');
        }
        
        if (thinkingMessage) {
            messagesContainer.insertBefore(div, thinkingMessage);
        } else {
            messagesContainer.appendChild(div);
        }
        scrollToBottom();
        return div;
    };
    
    // Global functions for message actions
    window.copyMessage = function(btn) {
        const messageDiv = btn.closest('.message-bubble');
        const content = messageDiv.querySelector('.message-content').textContent;
        navigator.clipboard.writeText(content).then(() => {
            toastr.success('Message copied to clipboard');
        });
    };
    
    window.showRawMessage = function(btn) {
        const messageDiv = btn.closest('.message-bubble');
        const content = messageDiv.querySelector('.message-content').textContent;
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Raw Message</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <pre class="bg-dark text-light p-4 rounded" style="max-height: 500px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px;">${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-primary" onclick="navigator.clipboard.writeText(this.closest('.modal').querySelector('pre').textContent); toastr.success('Copied');">
                            <i class="ki-duotone ki-copy fs-5"><span class="path1"></span><span class="path2"></span></i>
                            Copy
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        modal.addEventListener('hidden.bs.modal', () => modal.remove());
    };
    
    const updateMessage = (messageId, content, tokens = 0) => {
        const messageDiv = messagesContainer.querySelector(`[data-message-id="${messageId}"]`);
        if (!messageDiv) return;
        
        const contentDiv = messageDiv.querySelector('.message-content');
        const tokensDiv = messageDiv.querySelector('.message-tokens');
        
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
    
    const addMonitorLog = (type, message, data = null) => {
        // Access Alpine component instance directly from workspace element
        const workspaceComponent = workspace?.__x?.$data;
        if (workspaceComponent && typeof workspaceComponent.addLog === 'function') {
            workspaceComponent.addLog({
                type,
                message,
                data,
                timestamp: new Date().toISOString()
            });
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
        messageInput.disabled = true;
        const userPrompt = messageInput.value;
        messageInput.value = '';
        
        // Add user message to UI
        appendMessage('user', userPrompt);
        
        addMonitorLog('info', 'Sending message to LLM...', { prompt: userPrompt.substring(0, 100) });
        
        showThinking();
        
        // Create empty assistant message
        const assistantMessageId = `msg-${Date.now()}`;
        const assistantDiv = appendMessage('assistant', '', 0, assistantMessageId);
        
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
        
        eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            
            if (data.type === 'chunk') {
                fullResponse += data.content;
                chunkCount++;
                
                // Update message DOM with Markdown rendering
                updateMessage(assistantMessageId, fullResponse, data.tokens || chunkCount);
                
                if (chunkCount === 1) {
                    hideThinking();
                    addMonitorLog('success', 'Streaming started', { chunks: 1 });
                }
                
                if (chunkCount % 50 === 0) {
                    addMonitorLog('info', `Received ${chunkCount} chunks`, { length: fullResponse.length });
                }
                
            } else if (data.type === 'done') {
                hideThinking();
                const duration = Date.now() - startTime;
                
                addMonitorLog('success', 'Message saved to DB', {
                    chunks: chunkCount,
                    tokens: data.usage?.total_tokens || chunkCount,
                    duration_ms: duration,
                    cost_usd: data.cost,
                    message_id: data.message_id
                });
                
                eventSource?.close();
                eventSource = null;
                sendBtn.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
                toastr.success(`Response complete! (${chunkCount} chunks, ${duration}ms)`);
                
            } else if (data.type === 'error') {
                hideThinking();
                addMonitorLog('error', 'Streaming error', { error: data.message });
                toastr.error(data.message);
                
                assistantDiv?.remove();
                
                eventSource?.close();
                sendBtn.disabled = false;
                messageInput.disabled = false;
            }
        };
        
        eventSource.onerror = (error) => {
            hideThinking();
            addMonitorLog('error', 'Connection lost', { error: error.toString() });
            toastr.error('Streaming connection lost');
            
            eventSource?.close();
            sendBtn.disabled = false;
            messageInput.disabled = false;
        };
    };
    
    const clearConversation = () => {
        if (confirm('Start a new chat? This will reload the page.')) {
            window.location.href = '{{ route("admin.llm.quick-chat.new") }}';
        }
    };
    
    sendBtn.addEventListener('click', sendMessage);
    clearBtn?.addEventListener('click', clearConversation);
    
    // Enter key to send (Ctrl+Enter for new line)
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.ctrlKey && !e.shiftKey) {
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
