{{-- Request Inspector JavaScript Functions --}}
<script>
/**
 * Populate Request Inspector UI with data from SSE request_data event
 */
function populateRequestInspector(data) {
    console.log('populateRequestInspector called', data);

    // Hide no-data message, show data display
    document.getElementById('request-no-data').classList.add('d-none');
    document.getElementById('request-data-display').classList.remove('d-none');

    // 1. Populate Metadata
    document.getElementById('meta-provider').textContent = data.metadata.provider || 'N/A';
    document.getElementById('meta-model').textContent = data.metadata.model || 'N/A';
    document.getElementById('meta-endpoint').textContent = data.metadata.endpoint || 'N/A';
    document.getElementById('meta-timestamp').textContent = data.metadata.timestamp || 'N/A';
    document.getElementById('meta-session-id').textContent = data.metadata.session_id || data.metadata.conversation_id || 'N/A';
    document.getElementById('meta-message-id').textContent = data.metadata.message_id || 'N/A';

    // 2. Populate Parameters
    document.getElementById('param-temperature').textContent = data.parameters.temperature ?? 'N/A';
    document.getElementById('param-max-tokens').textContent = data.parameters.max_tokens ?? 'N/A';
    document.getElementById('param-top-p').textContent = data.parameters.top_p ?? 'N/A';
    document.getElementById('param-context-limit').textContent = data.parameters.context_limit ?? 'N/A';
    document.getElementById('param-actual-context-size').textContent = data.parameters.actual_context_size ?? 'N/A';

    // 3. Populate System Instructions
    const systemInstructions = data.system_instructions || 'No system instructions defined';
    document.getElementById('system-instructions-text').value = systemInstructions;

    // 4. Populate Context Messages
    const contextMessages = data.context_messages || [];
    document.getElementById('context-count-badge').textContent = contextMessages.length;

    if (contextMessages.length > 0) {
        const timelineHTML = contextMessages.map((msg, idx) => `
            <div class="timeline-item mb-3">
                <div class="timeline-line w-40px"></div>
                <div class="timeline-icon symbol symbol-circle symbol-40px">
                    <div class="symbol-label bg-light-${msg.role === 'user' ? 'primary' : 'success'}">
                        <i class="ki-duotone ki-${msg.role === 'user' ? 'user' : 'abstract-26'} fs-2 text-${msg.role === 'user' ? 'primary' : 'success'}">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="timeline-content mb-3">
                    <div class="fw-bold text-gray-800 mb-1">
                        ${msg.role.charAt(0).toUpperCase() + msg.role.slice(1)} 
                        <span class="badge badge-sm badge-light-info ms-2">${msg.tokens} tokens</span>
                    </div>
                    <div class="text-gray-600 fs-8 font-monospace">${escapeHtml(msg.content)}</div>
                    <div class="text-muted fs-9 mt-1">${msg.created_at}</div>
                </div>
            </div>
        `).join('');
        document.getElementById('context-messages-list').innerHTML = timelineHTML;
    } else {
        document.getElementById('context-messages-list').innerHTML = '<p class="text-muted">No context messages</p>';
    }

    // 5. Populate Current Prompt
    document.getElementById('current-prompt-text').value = data.current_prompt || 'No prompt available';

    // 6. Populate Full JSON Body (syntax highlighted with Prism.js)
    const fullJsonCode = document.getElementById('full-json-code');
    const jsonString = JSON.stringify(data.full_request_body, null, 2);
    fullJsonCode.textContent = jsonString;

    // Re-highlight with Prism.js if available
    if (typeof Prism !== 'undefined') {
        Prism.highlightElement(fullJsonCode);
    }
}

/**
 * Copy current prompt to clipboard
 */
function copyCurrentPrompt() {
    const promptText = document.getElementById('current-prompt-text').value;
    navigator.clipboard.writeText(promptText).then(() => {
        toastr.success('Prompt copied to clipboard');
    }).catch(err => {
        toastr.error('Failed to copy prompt');
        console.error('Clipboard copy failed', err);
    });
}

/**
 * Copy full JSON body to clipboard
 */
function copyRequestJSON() {
    const jsonText = document.getElementById('full-json-code').textContent;
    navigator.clipboard.writeText(jsonText).then(() => {
        toastr.success('JSON copied to clipboard');
    }).catch(err => {
        toastr.error('Failed to copy JSON');
        console.error('Clipboard copy failed', err);
    });
}

/**
 * Download full JSON body as file
 */
function downloadRequestJSON() {
    const jsonText = document.getElementById('full-json-code').textContent;
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const filename = `llm-request-${timestamp}.json`;

    const blob = new Blob([jsonText], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);

    toastr.success(`Downloaded ${filename}`);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
