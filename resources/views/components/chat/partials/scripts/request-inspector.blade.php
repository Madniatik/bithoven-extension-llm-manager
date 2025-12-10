{{-- Request Inspector JavaScript Functions --}}
<script>
// Ensure functions are in global scope
window.populateRequestInspector = function(data, sessionId = null) {
    console.log('populateRequestInspector called', data);

    // Guardar en localStorage si se proporciona sessionId
    if (sessionId && window.RequestInspectorStorage) {
        RequestInspectorStorage.saveRequest(sessionId, data);
    }

    // Hide no-data message, show data display
    const noDataEl = document.getElementById('request-no-data');
    const dataDisplayEl = document.getElementById('request-data-display');
    
    if (!noDataEl || !dataDisplayEl) {
        console.error('[Request Inspector] Required DOM elements not found');
        return;
    }
    
    noDataEl.classList.add('d-none');
    dataDisplayEl.classList.remove('d-none');

    // 1. Populate Metadata
    document.getElementById('meta-provider').textContent = data.metadata.provider || 'N/A';
    document.getElementById('meta-model').textContent = data.metadata.model || 'N/A';
    document.getElementById('meta-endpoint').textContent = data.metadata.endpoint || 'N/A';
    document.getElementById('meta-timestamp').textContent = data.metadata.timestamp || 'N/A';
    document.getElementById('meta-session-id').textContent = data.metadata.session_id || data.metadata.conversation_id || 'N/A';
    document.getElementById('meta-request-message-id').textContent = data.metadata.request_message_id || 'N/A';
    // Response message ID starts as "Pending..." (updated by 'done' event)

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
};

/**
 * Copy current prompt to clipboard
 */
window.copyCurrentPrompt = function() {
    const promptText = document.getElementById('current-prompt-text').value;
    navigator.clipboard.writeText(promptText).then(() => {
        toastr.success('Prompt copied to clipboard');
    }).catch(err => {
        toastr.error('Failed to copy prompt');
        console.error('Clipboard copy failed', err);
    });
};

/**
 * Copy full JSON body to clipboard
 */
window.copyRequestJSON = function() {
    const jsonText = document.getElementById('full-json-code').textContent;
    navigator.clipboard.writeText(jsonText).then(() => {
        toastr.success('JSON copied to clipboard');
    }).catch(err => {
        toastr.error('Failed to copy JSON');
        console.error('Clipboard copy failed', err);
    });
};

/**
 * Download full JSON body as file
 */
window.downloadRequestJSON = function() {
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
};

/**
 * Escape HTML to prevent XSS
 */
window.escapeHtml = function(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

/**
 * Request Inspector Persistence (localStorage)
 * Guardar y recuperar datos del Request Inspector por sessionId
 */
window.RequestInspectorStorage = {
    /**
     * Guardar request data en localStorage
     * @param {string} sessionId - ID de la sesión del chat
     * @param {object} requestData - Datos del request a guardar
     */
    saveRequest(sessionId, requestData) {
        if (!sessionId) {
            console.warn('[Inspector Storage] No sessionId provided');
            return;
        }

        const storageKey = `llm_request_inspector_${sessionId}`;
        
        try {
            // Agregar timestamp si no existe
            if (!requestData.metadata) {
                requestData.metadata = {};
            }
            if (!requestData.metadata.timestamp) {
                requestData.metadata.timestamp = new Date().toLocaleString();
            }

            // Guardar en localStorage
            localStorage.setItem(storageKey, JSON.stringify(requestData));
            
            console.log(`[Inspector Storage] Request saved for session ${sessionId}`);
        } catch (error) {
            console.error('[Inspector Storage] Save failed:', error);
            if (error.name === 'QuotaExceededError') {
                toastr.warning('LocalStorage is full. Inspector data not saved.');
            }
        }
    },

    /**
     * Cargar request data desde localStorage
     * @param {string} sessionId - ID de la sesión del chat
     * @returns {object|null} - Request data o null si no existe
     */
    loadRequest(sessionId) {
        if (!sessionId) {
            console.warn('[Inspector Storage] No sessionId provided');
            return null;
        }

        const storageKey = `llm_request_inspector_${sessionId}`;
        
        try {
            const data = localStorage.getItem(storageKey);
            if (data) {
                const requestData = JSON.parse(data);
                console.log(`[Inspector Storage] Request loaded for session ${sessionId}`);
                return requestData;
            }
        } catch (error) {
            console.error('[Inspector Storage] Load failed:', error);
        }

        return null;
    },

    /**
     * Limpiar storage de una sesión específica
     * @param {string} sessionId - ID de la sesión del chat
     */
    clearSession(sessionId) {
        if (!sessionId) return;
        
        const storageKey = `llm_request_inspector_${sessionId}`;
        localStorage.removeItem(storageKey);
        console.log(`[Inspector Storage] Cleared session ${sessionId}`);
    },

    /**
     * Limpiar todas las sesiones antiguas (más de 7 días)
     */
    cleanupOldSessions() {
        const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 días en ms
        const now = Date.now();
        
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('llm_request_inspector_')) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    const timestamp = new Date(data?.metadata?.timestamp).getTime();
                    
                    if (now - timestamp > maxAge) {
                        localStorage.removeItem(key);
                        console.log(`[Inspector Storage] Removed old session: ${key}`);
                    }
                } catch (e) {
                    // Si falla parsing, eliminar entrada corrupta
                    localStorage.removeItem(key);
                }
            }
        }
    }
};

// Auto-cleanup al cargar la página (una vez al día)
const lastCleanup = localStorage.getItem('llm_inspector_last_cleanup');
const now = Date.now();
const oneDayMs = 24 * 60 * 60 * 1000;

if (!lastCleanup || (now - parseInt(lastCleanup)) > oneDayMs) {
    RequestInspectorStorage.cleanupOldSessions();
    localStorage.setItem('llm_inspector_last_cleanup', now.toString());
}

/**
 * Cargar datos del Request Inspector desde localStorage al cargar la página
 * Se debe llamar después de que el DOM esté listo
 */
window.loadRequestInspectorFromStorage = function(sessionId) {
    if (!sessionId) {
        console.warn('[Inspector Load] No sessionId provided');
        return;
    }

    const requestData = RequestInspectorStorage.loadRequest(sessionId);
    
    if (requestData) {
        // Restaurar datos en el inspector
        populateRequestInspector(requestData, sessionId);
        console.log('[Inspector Load] Data restored from localStorage for session:', sessionId);
        
        // Opcional: Mostrar toast de confirmación
        // toastr.info('Request Inspector data restored from previous session');
    } else {
        console.log('[Inspector Load] No stored data found for session:', sessionId);
    }
};

console.log('[Request Inspector] Functions loaded:', {
    populateRequestInspector: typeof window.populateRequestInspector,
    copyCurrentPrompt: typeof window.copyCurrentPrompt,
    copyRequestJSON: typeof window.copyRequestJSON,
    downloadRequestJSON: typeof window.downloadRequestJSON,
    escapeHtml: typeof window.escapeHtml,
    RequestInspectorStorage: typeof window.RequestInspectorStorage,
    loadRequestInspectorFromStorage: typeof window.loadRequestInspectorFromStorage
});
</script>
