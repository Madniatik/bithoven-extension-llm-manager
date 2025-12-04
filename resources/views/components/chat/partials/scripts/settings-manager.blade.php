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
            
            console.log('🔍 Loading settings for session:', sessionId);
            console.log('📦 Raw localStorage data:', savedSettings);
            
            if (savedSettings) {
                const settings = JSON.parse(savedSettings);
                console.log('✅ Parsed settings:', settings);
                
                // Restore context limit (with Select2 refresh)
                if (settings.context_limit !== undefined) {
                    const contextSelect = document.getElementById('quick-chat-context-limit');
                    console.log('🔧 Context select element:', contextSelect);
                    console.log('🔧 Setting context_limit to:', settings.context_limit);
                    if (contextSelect) {
                        contextSelect.value = settings.context_limit;
                        console.log('🔧 Context value after set:', contextSelect.value);
                        // Trigger Select2 to update visually
                        $(contextSelect).trigger('change');
                        console.log('✅ Select2 triggered for context_limit');
                    }
                }
                
                // Restore temperature
                if (settings.temperature !== undefined) {
                    const tempInput = document.getElementById('quick-chat-temperature');
                    const tempDisplay = document.getElementById('quick-chat-temp-display');
                    console.log('🔧 Setting temperature to:', settings.temperature);
                    if (tempInput) tempInput.value = settings.temperature;
                    if (tempDisplay) tempDisplay.textContent = settings.temperature;
                }
                
                // Restore max tokens
                if (settings.max_tokens !== undefined) {
                    const maxTokensInput = document.getElementById('quick-chat-max-tokens');
                    console.log('🔧 Setting max_tokens to:', settings.max_tokens);
                    if (maxTokensInput) maxTokensInput.value = settings.max_tokens;
                }
                
                // Restore configuration (footer selector with Select2 refresh)
                if (settings.configuration_id !== undefined) {
                    const configSelect = document.getElementById('quick-chat-model-selector-' + sessionId);
                    console.log('🔧 Model select element:', configSelect);
                    console.log('🔧 Setting configuration_id to:', settings.configuration_id);
                    if (configSelect) {
                        configSelect.value = settings.configuration_id;
                        console.log('🔧 Model value after set:', configSelect.value);
                        // Trigger Select2 to update visually
                        $(configSelect).trigger('change');
                        console.log('✅ Select2 triggered for model selector');
                    } else {
                        console.warn('⚠️ Model selector not found with ID: quick-chat-model-selector-' + sessionId);
                    }
                }
            } else {
                console.log('ℹ️ No saved settings found in localStorage');
            }
        }
        
        /**
         * Save settings to localStorage
         */
        function saveQuickChatSettings() {
            const modelSelectorId = 'quick-chat-model-selector-' + sessionId;
            const settings = {
                configuration_id: document.getElementById(modelSelectorId)?.value,
                context_limit: document.getElementById('quick-chat-context-limit')?.value,
                temperature: document.getElementById('quick-chat-temperature')?.value,
                max_tokens: document.getElementById('quick-chat-max-tokens')?.value
            };
            
            localStorage.setItem(`quick_chat_session_${sessionId}_settings`, JSON.stringify(settings));
            console.log('💾 Quick Chat settings saved:', settings);
            console.log('📍 Saved to key:', `quick_chat_session_${sessionId}_settings`);
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
        const configSelect = document.getElementById('quick-chat-model-selector-' + sessionId);
        if (configSelect) {
            configSelect.addEventListener('change', saveQuickChatSettings);
            console.log('✅ Model selector listener attached to:', 'quick-chat-model-selector-' + sessionId);
        } else {
            console.warn('⚠️ Could not attach listener - model selector not found:', 'quick-chat-model-selector-' + sessionId);
        }
        
        // Load settings on init
        loadQuickChatSettings();
        
        console.log('✅ Settings manager initialized for session:', sessionId);
    });
</script>
@endpush
