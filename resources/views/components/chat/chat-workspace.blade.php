{{--
    LLM Chat Workspace Component
    
    Componente maestro con layout configurable para monitor
    Sistema de configuración granular (v0.3.0+)
    
    Props (desde Workspace.php):
    - $session: LLMConversationSession|null
    - $configurations: Collection
    - $config: array (validated configuration)
    - $messages: Collection (generado por componente)
    - $monitorId: string (generado por componente)
    - Legacy props: $showMonitor, $monitorLayout, etc. (backward compatible)
--}}

@php
    // Extract config values for easier access
    $monitorEnabled = $config['features']['monitor']['enabled'] ?? false;
    $monitorOpenByDefault = $config['features']['monitor']['default_open'] ?? false;
    $monitorLayoutValue = $config['ui']['layout']['monitor'] ?? 'split-horizontal';
    $settingsPanelEnabled = $config['features']['settings_panel'] ?? true;
    
    // Performance optimizations
    $lazyLoadTabs = $config['performance']['lazy_load_tabs'] ?? true;
    $cachePreferences = $config['performance']['cache_preferences'] ?? true;
    
    // Custom CSS class
    $customCssClass = $config['advanced']['custom_css_class'] ?? '';
@endphp

{{-- Debug Console auto-loaded via View Composer globally --}}
{!! $__llmDebugConsoleRegistration ?? '' !!}

{{-- CRITICAL: Load Alpine.js components BEFORE using them in x-data attributes --}}
@if($settingsPanelEnabled)
    {!! '<script>' !!}
    @include('llm-manager::components.chat.partials.scripts.chat-settings')
    {!! '</script>' !!}
@endif

<div class="llm-chat-workspace {{ $customCssClass }}" 
     data-session-id="{{ $session?->id }}" 
     data-monitor-layout="{{ $monitorLayoutValue }}"
     x-data="chatWorkspace_{{ $session?->id ?? 'default' }}({{ $monitorEnabled ? 'true' : 'false' }}, {{ $monitorOpenByDefault ? 'true' : 'false' }}, '{{ $monitorLayoutValue }}', {{ $session?->id ?? 'null' }}, '{{ $monitorId }}')"
     id="chat-workspace-{{ $session?->id ?? 'default' }}">
    
    @if($monitorLayoutValue === 'split-horizontal')
        {{-- SPLIT HORIZONTAL LAYOUT: Chat + Monitor split --}}
        @include('llm-manager::components.chat.layouts.split-horizontal-layout')
    @else
        {{-- SIDEBAR LAYOUT: Monitor fijo derecha (60/40) --}}
        @include('llm-manager::components.chat.layouts.sidebar-layout')
    @endif
</div>

{{-- Raw Message Modal --}}
@include('llm-manager::components.chat.partials.modals.modal-raw-message')

{{-- 
    CONDITIONAL RESOURCE LOADING (FASE 3)
    
    Objetivo: Cargar solo los scripts/styles necesarios según config array
    Beneficio: 30-50% reducción en bundle size cuando features disabled
    
    Estrategia:
    - Core styles/scripts: SIEMPRE (dependencies, markdown, buttons)
    - Settings panel: Condicional ($settingsPanelEnabled)
    - Monitor layout: Condicional ($monitorLayoutValue)
    - Monitor tabs: Condicional por tab (console, request_inspector, activity_log)
--}}

{{-- Core Styles (always loaded) --}}
@include('llm-manager::components.chat.partials.styles.dependencies')
@include('llm-manager::components.chat.partials.styles.markdown')
@include('llm-manager::components.chat.partials.styles.buttons')
@include('llm-manager::components.chat.partials.styles.responsive')

{{-- Settings Panel styles (conditional) --}}
@if($settingsPanelEnabled)
    @include('llm-manager::components.chat.partials.styles.chat-settings')
@endif

{{-- Monitor Layout styles (conditional) --}}
@if($monitorLayoutValue === 'split-horizontal')
    @include('llm-manager::components.chat.partials.styles.split-horizontal')
@endif

{{-- Monitor Console styles (conditional on console tab enabled) --}}
@if($showMonitor && $isMonitorTabEnabled('console'))
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endif

{{-- Core Scripts (always loaded) --}}
@include('llm-manager::components.chat.partials.scripts.clipboard-utils')
@include('llm-manager::components.chat.partials.scripts.message-renderer')

@if($settingsPanelEnabled)
    @include('llm-manager::components.chat.partials.scripts.settings-manager')
@endif

{{-- Platform Detection & Cross-Platform Utilities (ALWAYS load first) --}}
@include('llm-manager::components.chat.partials.scripts.platform-utils')

{{-- Keyboard Shortcuts Module (depends on PlatformUtils) --}}
@include('llm-manager::components.chat.partials.scripts.keyboard-shortcuts')

@include('llm-manager::components.chat.partials.scripts.event-handlers')
@include('llm-manager::components.chat.partials.scripts.chat-workspace')

@if($showMonitor)
    {{-- Monitor API (core, always load if monitor enabled) --}}
    @include('llm-manager::components.chat.partials.scripts.monitor-api')
    
    {{-- Request Inspector (conditional on tab enabled) --}}
    @if($isMonitorTabEnabled('request_inspector'))
        @include('llm-manager::components.chat.partials.scripts.request-inspector')
    @endif
@endif

@if($monitorLayoutValue === 'split-horizontal')
    @include('llm-manager::components.chat.partials.scripts.split-resizer')
@endif

