{{--
    KEYBOARD SHORTCUTS MODULE
    
    Gestiona atajos de teclado adaptados por sistema operativo.
    Soporta 2 modos configurables:
    - Mode A (Default): Enter sends, Shift+Enter new line
    - Mode B: Enter new line, Cmd/Ctrl+Enter sends
    
    Dependencias:
    - PlatformUtils (platform-utils.blade.php)
    - ChatWorkspaceConfigValidator (config.ux.keyboard.shortcuts_mode)
    
    @version 1.0.0
    @requires PlatformUtils
--}}

<script>
/**
 * Keyboard Shortcuts Manager
 * 
 * Gestiona comportamiento de teclado según configuración de usuario
 * y plataforma (Mac/Windows/Linux).
 */
window.KeyboardShortcuts = (function() {
    'use strict';
    
    // ===== CONFIGURATION =====
    
    /**
     * Current shortcuts mode
     * @type {string} 'A' or 'B'
     */
    let currentMode = 'A'; // Default: Enter sends
    
    /**
     * Load mode from config or localStorage
     * @param {string} sessionId - Session ID para localStorage key
     * @returns {string} 'A' or 'B'
     */
    const loadMode = (sessionId) => {
        // Intentar cargar desde config pasado por backend (futuro)
        const configMode = window.chatWorkspaceConfig?.ux?.keyboard?.shortcuts_mode;
        if (configMode === 'A' || configMode === 'B') {
            return configMode;
        }
        
        // Fallback: localStorage
        const storageKey = `llm_chat_keyboard_mode_${sessionId}`;
        const storedMode = localStorage.getItem(storageKey);
        if (storedMode === 'A' || storedMode === 'B') {
            return storedMode;
        }
        
        // Default: Mode A
        return 'A';
    };
    
    /**
     * Save mode to localStorage
     * @param {string} mode - 'A' or 'B'
     * @param {string} sessionId - Session ID
     */
    const saveMode = (mode, sessionId) => {
        if (mode !== 'A' && mode !== 'B') {
            console.warn('[KeyboardShortcuts] Invalid mode:', mode);
            return;
        }
        
        currentMode = mode;
        const storageKey = `llm_chat_keyboard_mode_${sessionId}`;
        localStorage.setItem(storageKey, mode);
        
        console.log('[KeyboardShortcuts] Mode saved:', mode);
    };
    
    // ===== KEYBOARD LOGIC =====
    
    /**
     * Determina si un evento de teclado debe enviar el mensaje
     * @param {KeyboardEvent} event - Evento keydown
     * @returns {boolean} true si debe enviar mensaje
     */
    const shouldSendMessage = (event) => {
        if (event.key !== 'Enter') {
            return false;
        }
        
        const isShiftPressed = event.shiftKey;
        const isModifierPressed = PlatformUtils.isModifierPressed(event);
        
        if (currentMode === 'A') {
            // Mode A: Enter sends, Shift+Enter new line
            return !isShiftPressed && !isModifierPressed;
        } else {
            // Mode B: Enter new line, Cmd/Ctrl+Enter sends
            return isModifierPressed && !isShiftPressed;
        }
    };
    
    /**
     * Handler para eventos keydown en textarea
     * @param {KeyboardEvent} event - Evento keydown
     * @param {Function} sendCallback - Callback para enviar mensaje
     */
    const handleKeydown = (event, sendCallback) => {
        if (shouldSendMessage(event)) {
            event.preventDefault();
            sendCallback();
        }
        // Si no debe enviar, dejar comportamiento default (nueva línea)
    };
    
    // ===== TOOLTIPS & UI =====
    
    /**
     * Obtiene descripción del modo actual
     * @returns {string} Descripción legible del modo
     */
    const getModeDescription = () => {
        const modifier = PlatformUtils.getModifierKey();
        
        if (currentMode === 'A') {
            return `Enter sends message, Shift+Enter for new line`;
        } else {
            return `${modifier}+Enter sends message, Enter for new line`;
        }
    };
    
    /**
     * Obtiene shortcut formateado para enviar mensaje
     * @returns {string} Ej: 'Enter' (Mode A) o 'Cmd+Enter' (Mode B en Mac)
     */
    const getSendShortcut = () => {
        if (currentMode === 'A') {
            return 'Enter';
        } else {
            return PlatformUtils.formatShortcut('MOD+Enter');
        }
    };
    
    /**
     * Obtiene shortcut formateado para nueva línea
     * @returns {string} Ej: 'Shift+Enter' (Mode A) o 'Enter' (Mode B)
     */
    const getNewLineShortcut = () => {
        if (currentMode === 'A') {
            return 'Shift+Enter';
        } else {
            return 'Enter';
        }
    };
    
    /**
     * Obtiene tooltip completo para el input
     * @returns {string} Tooltip con shortcuts actuales
     */
    const getInputTooltip = () => {
        const send = getSendShortcut();
        const newLine = getNewLineShortcut();
        return `${send} to send, ${newLine} for new line`;
    };
    
    // ===== INITIALIZATION =====
    
    /**
     * Inicializa el módulo con configuración
     * @param {string} sessionId - Session ID
     * @param {HTMLTextAreaElement} textarea - Textarea element
     * @param {Function} sendCallback - Callback para enviar mensaje
     */
    const init = (sessionId, textarea, sendCallback) => {
        // Cargar modo guardado
        currentMode = loadMode(sessionId);
        
        console.log('[KeyboardShortcuts] Initialized with mode:', currentMode);
        console.log('[KeyboardShortcuts] Description:', getModeDescription());
        
        // Attach keydown listener
        textarea.addEventListener('keydown', (event) => {
            handleKeydown(event, sendCallback);
        });
        
        // Update tooltip (si existe)
        const tooltip = getInputTooltip();
        if (textarea.hasAttribute('title')) {
            textarea.setAttribute('title', tooltip);
        }
    };
    
    // ===== PUBLIC API =====
    
    return {
        // Initialization
        init,
        
        // Configuration
        loadMode,
        saveMode,
        getCurrentMode: () => currentMode,
        
        // Keyboard Logic
        shouldSendMessage,
        handleKeydown,
        
        // UI Helpers
        getModeDescription,
        getSendShortcut,
        getNewLineShortcut,
        getInputTooltip,
    };
})();
</script>
