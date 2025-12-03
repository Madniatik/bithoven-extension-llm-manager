{{--
    Monitor Console Only (for split-horizontal layout)
    Solo la consola con fondo negro para el panel inferior
    
    Note: JavaScript API loaded globally via chat-workspace.blade.php
--}}

<div id="monitor-console" class="monitor-console-dark" style="height: 100%; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6;">
    <div id="monitor-logs">
        <span class="text-muted">[Monitor initialized]</span>
    </div>
</div>

{{-- Monitor Console Styles --}}
@push('styles')
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endpush
