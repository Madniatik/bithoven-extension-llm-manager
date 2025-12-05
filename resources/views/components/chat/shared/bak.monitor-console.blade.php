{{--
    Monitor Console Only (for split-horizontal layout)
    Solo la consola con fondo negro para el panel inferior
    
    Note: JavaScript API loaded globally via chat-workspace.blade.php
--}}

@php
    $monitorId = $session?->id ?? 'default';
@endphp

<div id="monitor-console-{{ $monitorId }}" class="monitor-console-dark" style="height: 100%; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6;">
    <div id="monitor-logs-{{ $monitorId }}">
        <div class="text-muted mb-2">Monitor ready. Send a message to see real-time activity...</div>
    </div>
</div>

{{-- Monitor Console Styles --}}
@push('styles')
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endpush
