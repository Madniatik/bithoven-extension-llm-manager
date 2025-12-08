{{-- Request Inspector Tab - Display request data sent to LLM models --}}
<div class="card card-flush shadow-sm h-100" id="request-inspector-container">
    <div class="card-body p-4">
        <div id="request-no-data" class="text-center text-muted py-10">
            <i class="ki-duotone ki-search-list fs-3x mb-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <p class="fw-semibold fs-5 mb-0">No Request Data</p>
            <p class="fs-7 text-muted">Send a message to see the request payload</p>
        </div>

        <div id="request-data-display" class="d-none">
            {{-- Section 1: Metadata --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#metadata-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-information fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Metadata
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="metadata-section" class="collapse show">
                    <div class="card-body p-4">
                        <table class="table table-row-bordered table-sm">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-gray-600 w-200px">Provider</td>
                                    <td id="meta-provider" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Model</td>
                                    <td id="meta-model" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Endpoint</td>
                                    <td id="meta-endpoint" class="text-gray-800 font-monospace fs-8">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Timestamp</td>
                                    <td id="meta-timestamp" class="text-gray-800 fs-8">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Session ID</td>
                                    <td id="meta-session-id" class="text-gray-800 font-monospace fs-8">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Message ID</td>
                                    <td id="meta-message-id" class="text-gray-800 font-monospace fs-8">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 2: Parameters --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#parameters-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-setting-2 fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Parameters
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="parameters-section" class="collapse show">
                    <div class="card-body p-4">
                        <table class="table table-row-bordered table-sm">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-gray-600 w-200px">Temperature</td>
                                    <td id="param-temperature" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Max Tokens</td>
                                    <td id="param-max-tokens" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Top P</td>
                                    <td id="param-top-p" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Context Limit</td>
                                    <td id="param-context-limit" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Actual Context Size</td>
                                    <td id="param-actual-context-size" class="text-gray-800 fw-bold">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 3: System Instructions --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#system-instructions-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-code fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        System Instructions
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="system-instructions-section" class="collapse">
                    <div class="card-body p-4">
                        <textarea id="system-instructions-text" class="form-control font-monospace fs-8" rows="6" readonly>No system instructions defined</textarea>
                    </div>
                </div>
            </div>

            {{-- Section 4: Context Messages --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#context-messages-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-message-text-2 fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Context Messages
                        <span id="context-count-badge" class="badge badge-light-primary ms-2">0</span>
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="context-messages-section" class="collapse">
                    <div class="card-body p-4">
                        <div id="context-messages-list" class="timeline">
                            <p class="text-muted">No context messages</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 5: Current Prompt --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#current-prompt-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-message-edit fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Current Prompt
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary me-2" onclick="copyCurrentPrompt()">
                            <i class="ki-duotone ki-copy fs-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Copy
                        </button>
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="current-prompt-section" class="collapse">
                    <div class="card-body p-4">
                        <textarea id="current-prompt-text" class="form-control font-monospace fs-8" rows="4" readonly>No prompt available</textarea>
                    </div>
                </div>
            </div>

            {{-- Section 6: Full JSON Body --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#full-json-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-abstract-26 fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Full JSON Body
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary me-2" onclick="copyRequestJSON()">
                            <i class="ki-duotone ki-copy fs-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Copy
                        </button>
                        <button type="button" class="btn btn-sm btn-light-success me-2" onclick="downloadRequestJSON()">
                            <i class="ki-duotone ki-cloud-download fs-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Download
                        </button>
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="full-json-section" class="collapse">
                    <div class="card-body p-4">
                        <pre><code id="full-json-code" class="language-json">No request data available</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
