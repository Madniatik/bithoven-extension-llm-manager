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
        <div class="text-muted mb-2">[Monitor {{ $monitorId }} initialized]</div>
        
        {{-- Alpine.js reactive logs --}}
        <template x-for="log in logs" :key="log.id">
            <div class="mb-1" :class="{
                'text-success': log.type === 'success',
                'text-danger': log.type === 'error',
                'text-warning': log.type === 'warning',
                'text-info': log.type === 'info'
            }">
                <span class="text-gray-500" x-text="new Date(log.timestamp).toLocaleTimeString()"></span>
                <span x-text="` [${log.type.toUpperCase()}] ${log.message}`"></span>
                <template x-if="log.data">
                    <pre class="d-inline-block ms-2 text-gray-400" x-text="JSON.stringify(log.data, null, 2)" style="font-size: 11px;"></pre>
                </template>
            </div>
        </template>
    </div>
</div>

{{-- Monitor Console Styles --}}
@push('styles')
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endpush
