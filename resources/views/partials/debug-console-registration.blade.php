{{--
    Debug Console Registration - LLM Manager Extension
    
    Auto-registro de la extensión en el sistema global de Debug Console.
    Se inyecta automáticamente vía View Composer en TODAS las páginas (ver LLMServiceProvider).
    
    ARQUITECTURA:
    - Global Control: setting('debug_console.enabled') - Master switch del sistema completo
    - Extension Control: config('llm-manager.debug_console.level') - Visibilidad de esta extensión
    
    LOGGER INSTANCE:
    - Crea window.MonitorLogger = instancia de DebugConsole para logs de LLM Manager
    - Uso en código JS: MonitorLogger.debug('msg'), MonitorLogger.info('msg'), etc.
    - Similar a AppLogger (core app) pero específico para esta extensión
    
    LEVELS:
    - 'none' = Extensión oculta del Debug Console (pero sistema puede estar activo)
    - 'error' = Solo errores críticos
    - 'warn' = Warnings y errores
    - 'info' = Info, warnings, errores
    - 'debug' = Todos los mensajes
    
    @see CPANEL/resources/views/partials/debug-console-init.blade.php - Core initialization
    @see CPANEL/resources/views/layouts/master.blade.php - @stack('debug-console-extensions')
    @see src/LLMServiceProvider.php - View Composer que inyecta este partial
    @see CPANEL/DOCS/CORE/Debug-Console/API-REFERENCE.md - Full documentation
--}}

@push('debug-console-extensions')
<script>
    @if(setting('debug_console.enabled', true))
    // Auto-registro de LLM Manager en Debug Console
    @if(config('llm-manager.debug_console.level', 'none') !== 'none')
    window.DEBUG_CONSOLE_CONFIG = window.DEBUG_CONSOLE_CONFIG || {};
    window.DEBUG_CONSOLE_CONFIG['llm-manager'] = {
        level: '{{ config('llm-manager.debug_console.level', 'debug') }}'
    };
    
    // Crear logger para la extensión (cuando DebugConsole esté disponible)
    document.addEventListener('DOMContentLoaded', function() {
        if (window.DebugConsole) {
            window.MonitorLogger = window.DebugConsole.create('llm-manager');
            MonitorLogger.info('LLM Manager Debug Console registered');
        }
    });
    @endif
    @endif
</script>
@endpush
