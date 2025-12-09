{{--
    PLATFORM UTILITIES
    
    Detección de sistema operativo y utilidades cross-platform.
    Este módulo detecta Mac/Windows/Linux y proporciona helpers
    para adaptar UX según la plataforma.
    
    Funcionalidades:
    - Detección de OS (macOS, Windows, Linux, iOS, Android)
    - Teclas modificadoras (Cmd vs Ctrl)
    - Tooltips adaptados por OS
    - Comportamientos específicos de plataforma
    
    @version 1.0.0
    @requires JavaScript ES6+
--}}

<script>
/**
 * Platform Detection & Cross-Platform Utilities
 * 
 * Proporciona información sobre el sistema operativo del usuario
 * y helpers para adaptar UX (keyboard shortcuts, tooltips, etc.)
 */
window.PlatformUtils = (function() {
    'use strict';
    
    // ===== PLATFORM DETECTION =====
    
    /**
     * Detecta el sistema operativo basado en userAgent
     * @returns {string} 'mac', 'windows', 'linux', 'ios', 'android', 'unknown'
     */
    const detectOS = () => {
        const userAgent = window.navigator.userAgent.toLowerCase();
        const platform = window.navigator.platform?.toLowerCase() || '';
        
        // Mac (macOS, Mac OS X)
        if (/mac|macintosh/.test(platform) || /mac os x/.test(userAgent)) {
            return 'mac';
        }
        
        // iOS (iPhone, iPad, iPod)
        if (/iphone|ipad|ipod/.test(userAgent)) {
            return 'ios';
        }
        
        // Windows
        if (/win/.test(platform) || /windows/.test(userAgent)) {
            return 'windows';
        }
        
        // Android
        if (/android/.test(userAgent)) {
            return 'android';
        }
        
        // Linux
        if (/linux/.test(platform) || /linux/.test(userAgent)) {
            return 'linux';
        }
        
        return 'unknown';
    };
    
    /**
     * Current OS detected
     * @type {string}
     */
    const currentOS = detectOS();
    
    // ===== MODIFIER KEYS =====
    
    /**
     * Obtiene la tecla modificadora principal según OS
     * @returns {string} 'Cmd' (Mac) o 'Ctrl' (Windows/Linux)
     */
    const getModifierKey = () => {
        return currentOS === 'mac' ? 'Cmd' : 'Ctrl';
    };
    
    /**
     * Obtiene el símbolo de la tecla modificadora
     * @returns {string} '⌘' (Mac) o 'Ctrl' (Windows/Linux)
     */
    const getModifierSymbol = () => {
        return currentOS === 'mac' ? '⌘' : 'Ctrl';
    };
    
    /**
     * Verifica si una tecla modificadora está presionada en un evento
     * @param {KeyboardEvent} event - Evento de teclado
     * @returns {boolean} true si Cmd (Mac) o Ctrl (Windows/Linux) está presionado
     */
    const isModifierPressed = (event) => {
        return currentOS === 'mac' ? event.metaKey : event.ctrlKey;
    };
    
    // ===== KEYBOARD SHORTCUTS HELPERS =====
    
    /**
     * Formatea un shortcut para mostrar según OS
     * @param {string} keys - Shortcut en formato genérico (ej: 'MOD+Enter', 'MOD+C')
     * @returns {string} Shortcut formateado (ej: 'Cmd+Enter' en Mac, 'Ctrl+Enter' en Windows)
     */
    const formatShortcut = (keys) => {
        const modifier = getModifierKey();
        return keys.replace(/MOD/g, modifier);
    };
    
    /**
     * Formatea un shortcut con símbolos para UI
     * @param {string} keys - Shortcut en formato genérico
     * @returns {string} Shortcut con símbolos (ej: '⌘+Enter' en Mac)
     */
    const formatShortcutSymbol = (keys) => {
        const symbol = getModifierSymbol();
        return keys.replace(/MOD/g, symbol);
    };
    
    /**
     * Genera un tooltip descriptivo para un shortcut
     * @param {string} action - Descripción de la acción (ej: 'Send message')
     * @param {string} keys - Shortcut en formato genérico
     * @returns {string} Tooltip completo (ej: 'Send message (Cmd+Enter)')
     */
    const getShortcutTooltip = (action, keys) => {
        const shortcut = formatShortcut(keys);
        return `${action} (${shortcut})`;
    };
    
    // ===== PLATFORM CHECKS =====
    
    /**
     * @returns {boolean} true si es macOS o iOS
     */
    const isMac = () => currentOS === 'mac' || currentOS === 'ios';
    
    /**
     * @returns {boolean} true si es Windows
     */
    const isWindows = () => currentOS === 'windows';
    
    /**
     * @returns {boolean} true si es Linux
     */
    const isLinux = () => currentOS === 'linux';
    
    /**
     * @returns {boolean} true si es móvil (iOS o Android)
     */
    const isMobile = () => currentOS === 'ios' || currentOS === 'android';
    
    /**
     * @returns {boolean} true si es desktop (Mac, Windows, Linux)
     */
    const isDesktop = () => ['mac', 'windows', 'linux'].includes(currentOS);
    
    // ===== PUBLIC API =====
    
    return {
        // OS Detection
        detectOS,
        currentOS,
        
        // Platform Checks
        isMac,
        isWindows,
        isLinux,
        isMobile,
        isDesktop,
        
        // Modifier Keys
        getModifierKey,
        getModifierSymbol,
        isModifierPressed,
        
        // Keyboard Shortcuts
        formatShortcut,
        formatShortcutSymbol,
        getShortcutTooltip,
    };
})();

/**
 * Log detected OS (útil para debugging)
 */
console.log('[PlatformUtils] Detected OS:', PlatformUtils.currentOS);
console.log('[PlatformUtils] Modifier Key:', PlatformUtils.getModifierKey());
</script>
