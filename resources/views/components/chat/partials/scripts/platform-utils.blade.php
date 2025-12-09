{{--
    PLATFORM UTILITIES
    
    Detección de sistema operativo, navegador y utilidades cross-platform.
    Este módulo detecta Mac/Windows/Linux, Chrome/Firefox/Safari y proporciona
    helpers para adaptar UX según la plataforma.
    
    Funcionalidades:
    - Detección de OS (macOS, Windows, Linux, iOS, Android)
    - Detección de Browser (Chrome, Firefox, Safari, Edge, Opera)
    - Versión del navegador
    - Información completa del sistema (viewport, touch support, etc.)
    - Teclas modificadoras (Cmd vs Ctrl)
    - Tooltips adaptados por OS
    - Comportamientos específicos de plataforma
    
    Uso:
    - PlatformUtils.currentOS → 'mac', 'windows', 'linux', etc.
    - PlatformUtils.currentBrowser → 'chrome', 'firefox', 'safari', etc.
    - PlatformUtils.getModifierKey() → 'Cmd' (Mac) o 'Ctrl' (Windows/Linux)
    - PlatformUtils.getSystemInfo() → Objeto completo con toda la info
    
    @version 1.1.0
    @requires JavaScript ES6+
--}}

<script>
/**
 * Platform Detection & Cross-Platform Utilities
 * 
 * Proporciona información sobre el sistema operativo, navegador y características
 * del dispositivo del usuario. Helpers para adaptar UX (keyboard shortcuts, tooltips, etc.)
 * 
 * API Pública:
 * - OS Detection: detectOS(), currentOS, isMac(), isWindows(), etc.
 * - Browser Detection: detectBrowser(), currentBrowser, currentBrowserVersion
 * - System Info: getSystemInfo() → viewport, touch support, screen resolution, etc.
 * - Modifier Keys: getModifierKey(), isModifierPressed(event)
 * - Shortcuts: formatShortcut('MOD+C') → 'Cmd+C' (Mac) o 'Ctrl+C' (Windows)
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
    
    // ===== BROWSER DETECTION =====
    
    /**
     * Detecta el navegador basado en userAgent
     * @returns {string} 'chrome', 'firefox', 'safari', 'edge', 'opera', 'unknown'
     */
    const detectBrowser = () => {
        const userAgent = window.navigator.userAgent.toLowerCase();
        
        // Edge (debe ir ANTES de Chrome porque usa Chromium)
        if (/edg\//.test(userAgent)) {
            return 'edge';
        }
        
        // Chrome
        if (/chrome/.test(userAgent) && !/edg\//.test(userAgent)) {
            return 'chrome';
        }
        
        // Firefox
        if (/firefox/.test(userAgent)) {
            return 'firefox';
        }
        
        // Safari (debe ir DESPUÉS de Chrome)
        if (/safari/.test(userAgent) && !/chrome/.test(userAgent)) {
            return 'safari';
        }
        
        // Opera
        if (/opr\/|opera/.test(userAgent)) {
            return 'opera';
        }
        
        return 'unknown';
    };
    
    /**
     * Obtiene la versión del navegador
     * @returns {string} Versión del navegador o 'unknown'
     */
    const getBrowserVersion = () => {
        const userAgent = window.navigator.userAgent;
        const browser = detectBrowser();
        
        let match;
        switch (browser) {
            case 'chrome':
                match = userAgent.match(/Chrome\/(\d+\.\d+)/);
                return match ? match[1] : 'unknown';
            case 'firefox':
                match = userAgent.match(/Firefox\/(\d+\.\d+)/);
                return match ? match[1] : 'unknown';
            case 'safari':
                match = userAgent.match(/Version\/(\d+\.\d+)/);
                return match ? match[1] : 'unknown';
            case 'edge':
                match = userAgent.match(/Edg\/(\d+\.\d+)/);
                return match ? match[1] : 'unknown';
            case 'opera':
                match = userAgent.match(/OPR\/(\d+\.\d+)/);
                return match ? match[1] : 'unknown';
            default:
                return 'unknown';
        }
    };
    
    /**
     * Current browser detected
     * @type {string}
     */
    const currentBrowser = detectBrowser();
    
    /**
     * Current browser version
     * @type {string}
     */
    const currentBrowserVersion = getBrowserVersion();
    
    /**
     * Obtiene información completa del navegador y plataforma
     * @returns {Object} Información completa del sistema
     */
    const getSystemInfo = () => {
        return {
            os: currentOS,
            browser: currentBrowser,
            browserVersion: currentBrowserVersion,
            userAgent: window.navigator.userAgent,
            platform: window.navigator.platform,
            language: window.navigator.language,
            screenWidth: window.screen.width,
            screenHeight: window.screen.height,
            viewportWidth: window.innerWidth,
            viewportHeight: window.innerHeight,
            colorDepth: window.screen.colorDepth,
            pixelRatio: window.devicePixelRatio,
            touchSupport: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
        };
    };
    
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
        
        // Browser Detection
        detectBrowser,
        getBrowserVersion,
        currentBrowser,
        currentBrowserVersion,
        getSystemInfo,
        
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
 * Log detected platform info (útil para debugging)
 */
if (PlatformUtils.isDesktop()) {
    console.log('[PlatformUtils] System:', PlatformUtils.currentOS, '/', PlatformUtils.currentBrowser, PlatformUtils.currentBrowserVersion);
    console.log('[PlatformUtils] Modifier Key:', PlatformUtils.getModifierKey());
} else {
    console.log('[PlatformUtils] Mobile:', PlatformUtils.currentOS, '/', PlatformUtils.currentBrowser);
}

// Log completo disponible con: console.table(PlatformUtils.getSystemInfo())
</script>
