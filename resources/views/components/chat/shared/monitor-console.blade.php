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
        <span class="text-muted">[Monitor {{ $monitorId }} initialized]</span>
    </div>
</div>

{{-- Monitor Console Styles --}}
@push('styles')
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endpush
