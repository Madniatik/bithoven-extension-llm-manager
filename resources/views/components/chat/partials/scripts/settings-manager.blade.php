@push('scripts')
<script>
    /**
     * Settings Manager for Quick Chat
     * Handles localStorage persistence for settings and model selection
     */
    
    document.addEventListener('DOMContentLoaded', () => {
        const sessionId = {{ $session ? $session->id : 'null' }};
        
        if (!sessionId) {
            console.warn('Quick Chat: No session ID available for settings');
            return;
        }
        
        /**
         * Load saved settings from localStorage
         */
        function loadQuickChatSettings() {
            const savedSettings = localStorage.getItem(`quick_chat_session_${sessionId}_settings`);
            
            if (savedSettings) {
                const settings = JSON.parse(savedSettings);
                
                // Restore context limit (with Select2 refresh)
                if (settings.context_limit !== undefined) {
                    const contextSelect = document.getElementById('quick-chat-context-limit');
                    if (contextSelect) {
                        contextSelect.value = settings.context_limit;
                        // Trigger Select2 to update visually
                        $(contextSelect).trigger('change');
                    }
                }
                
                // Restore temperature
                if (settings.temperature !== undefined) {
                    const tempInput = document.getElementById('quick-chat-temperature');
                    const tempDisplay = document.getElementById('quick-chat-temp-display');
                    if (tempInput) tempInput.value = settings.temperature;
                    if (tempDisplay) tempDisplay.textContent = settings.temperature;
                }
                
                // Restore max tokens
                if (settings.max_tokens !== undefined) {
                    const maxTokensInput = document.getElementById('quick-chat-max-tokens');
                    if (maxTokensInput) maxTokensInput.value = settings.max_tokens;
                }
                
                // Restore configuration (footer selector with Select2 refresh)
                if (settings.configuration_id !== undefined) {
                    const configSelect = document.getElementById('quick-chat-model-selector');
                    if (configSelect) {
                        configSelect.value = settings.configuration_id;
                        // Trigger Select2 to update visually
                        $(configSelect).trigger('change');
                    }
                }
            }
        }
        
        /**
         * Save settings to localStorage
         */
        function saveQuickChatSettings() {
            const settings = {
                configuration_id: document.getElementById('quick-chat-model-selector')?.value,
                context_limit: document.getElementById('quick-chat-context-limit')?.value,
                temperature: document.getElementById('quick-chat-temperature')?.value,
                max_tokens: document.getElementById('quick-chat-max-tokens')?.value
            };
            
            localStorage.setItem(`quick_chat_session_${sessionId}_settings`, JSON.stringify(settings));
            console.log('Quick Chat settings saved:', settings);
        }
        
        // Temperature slider listener
        const tempInput = document.getElementById('quick-chat-temperature');
        if (tempInput) {
            tempInput.addEventListener('input', (e) => {
                const tempDisplay = document.getElementById('quick-chat-temp-display');
                if (tempDisplay) tempDisplay.textContent = e.target.value;
                saveQuickChatSettings();
            });
        }
        
        // Context limit listener
        const contextSelect = document.getElementById('quick-chat-context-limit');
        if (contextSelect) {
            contextSelect.addEventListener('change', saveQuickChatSettings);
        }
        
        // Max tokens listener
        const maxTokensInput = document.getElementById('quick-chat-max-tokens');
        if (maxTokensInput) {
            maxTokensInput.addEventListener('input', saveQuickChatSettings);
        }
        
        // Configuration selector listener (footer selector)
        const configSelect = document.getElementById('quick-chat-model-selector');
        if (configSelect) {
            configSelect.addEventListener('change', saveQuickChatSettings);
        }
        
        // Load settings on init
        loadQuickChatSettings();
        
        console.log('✅ Settings manager initialized');
    });
</script>
@endpush
