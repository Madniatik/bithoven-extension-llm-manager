{{--
    LLM Chat Workspace Component
    
    Componente maestro con layout configurable para monitor
    
    Props (desde ChatWorkspace.php):
    - $session: LLMConversationSession|null
    - $configurations: Collection
    - $showMonitor: bool
    - $monitorOpen: bool
    - $monitorLayout: string ('sidebar' | 'split-horizontal')
    - $messages: Collection (generado por componente)
    - $monitorId: string (generado por componente)
--}}

{{-- Debug Console auto-loaded via View Composer globally --}}
{!! $__llmDebugConsoleRegistration ?? '' !!}

<div class="llm-chat-workspace" 
     data-session-id="{{ $session?->id }}" 
     data-monitor-layout="{{ $monitorLayout }}"
     x-data="chatWorkspace_{{ $session?->id ?? 'default' }}({{ $showMonitor ? 'true' : 'false' }}, {{ $monitorOpen ? 'true' : 'false' }}, '{{ $monitorLayout }}', {{ $session?->id ?? 'null' }})"
     id="chat-workspace-{{ $session?->id ?? 'default' }}">
    
    @if($monitorLayout === 'split-horizontal')
        {{-- SPLIT HORIZONTAL LAYOUT: Chat arriba (70%), Monitor abajo (30%) --}}
        @include('llm-manager::components.chat.layouts.split-horizontal-layout')
    @else
        {{-- SIDEBAR LAYOUT: Monitor fijo derecha (60/40) --}}
        @include('llm-manager::components.chat.layouts.sidebar-layout')
    @endif
</div>

{{-- Raw Message Modal --}}
@include('llm-manager::components.chat.partials.modals.modal-raw-message')

{{-- Shared JavaScript Utilities --}}
{{-- REMOVED: External JS files not needed, logic inline in Blade --}}
{{-- @push('scripts')
    <script src="{{ asset('vendor/llm-manager/js/streaming-handler.js') }}"></script>
    <script src="{{ asset('vendor/llm-manager/js/metrics-calculator.js') }}"></script>
@endpush --}}

{{-- Styles (partitioned) --}}
@include('llm-manager::components.chat.partials.styles.dependencies')
@include('llm-manager::components.chat.partials.styles.markdown')
@include('llm-manager::components.chat.partials.styles.buttons')
@include('llm-manager::components.chat.partials.styles.responsive')
@include('llm-manager::components.chat.partials.styles.chat-settings')
@if($monitorLayout === 'split-horizontal')
    @include('llm-manager::components.chat.partials.styles.split-horizontal')
@endif

{{-- Scripts (partitioned) --}}
@include('llm-manager::components.chat.partials.scripts.clipboard-utils')
@include('llm-manager::components.chat.partials.scripts.message-renderer')
@include('llm-manager::components.chat.partials.scripts.settings-manager')
@include('llm-manager::components.chat.partials.scripts.chat-settings')
@include('llm-manager::components.chat.partials.scripts.event-handlers')
@include('llm-manager::components.chat.partials.scripts.chat-workspace')
@include('llm-manager::components.chat.partials.scripts.monitor-api')
@include('llm-manager::components.chat.partials.scripts.request-inspector')
@if($monitorLayout === 'split-horizontal')
    @include('llm-manager::components.chat.partials.scripts.split-resizer')
@endif
