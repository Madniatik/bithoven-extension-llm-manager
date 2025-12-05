{{--
    Debug Console Registration - LLM Manager Extension
    
    Se inyecta automáticamente via View Composer en TODAS las páginas
    Registra la extensión en el sistema de Debug Console global
    
    @see CPANEL/resources/views/partials/debug-console-init.blade.php
    @see CPANEL/resources/views/layouts/master.blade.php (@stack('debug-console-extensions'))
--}}

@push('debug-console-extensions')
<script>
    // Auto-registro de LLM Manager en Debug Console
    @if(config('llm-manager.debug_console.level', 'none') !== 'none')
    window.DEBUG_CONSOLE_CONFIG = window.DEBUG_CONSOLE_CONFIG || {};
    window.DEBUG_CONSOLE_CONFIG['llm-manager'] = {
        enabled: true,
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
</script>
@endpush
