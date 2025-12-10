{{--
    Monitor Header Action Buttons (Unified Component)
    
    Botones de acción del Monitor unificados para ambos layouts
    - Split Horizontal Layout
    - Sidebar Layout
    
    Props:
    - $monitorId: string (required) - Monitor instance ID
    - $showRefresh: bool (default: true) - Show refresh button
    - $showDownload: bool (default: true) - Show download button
    - $showCopy: bool (default: true) - Show copy button
    - $showClear: bool (default: true) - Show clear button
    - $showFullscreen: bool (default: false) - Show fullscreen toggle (only split layout)
    - $showClose: bool (default: false) - Show close button
    - $size: string (default: 'sm') - Button size ('sm' or 'md')
    
    Changelog v1.0:
    - Unificación de botones de split-horizontal-layout y sidebar-layout
    - Estandarización de íconos, tooltips y estilos
    - Separador visual entre grupos de botones
    - Alpine.js bindings para fullscreen toggle
--}}

@php
    $showRefresh = $showRefresh ?? true;
    $showDownload = $showDownload ?? true;
    $showCopy = $showCopy ?? true;
    $showClear = $showClear ?? true;
    $showFullscreen = $showFullscreen ?? false;
    $showClose = $showClose ?? false;
    $size = $size ?? 'sm';
    $iconSize = $size === 'sm' ? 'fs-1' : 'fs-2';
@endphp

<div class="d-flex gap-1 flex-shrink-0">
    {{-- GRUPO 1: Actions (Refresh, Download, Copy) --}}
    @if($showRefresh)
        {{-- Refresh --}}
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-primary"
                onclick="window.LLMMonitor.refresh('{{ $monitorId }}')"
                data-bs-toggle="tooltip" 
                title="Refresh monitor data">
            {!! getIcon('ki-arrows-circle', $iconSize, '', 'i') !!}
        </button>
    @endif

    @if($showDownload)
        {{-- Download Console Logs --}}
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-success"
                onclick="window.LLMMonitor.downloadConsole('{{ $monitorId }}')"
                data-bs-toggle="tooltip" 
                title="Download console logs">
            {!! getIcon('ki-cloud-download', $iconSize, '', 'i') !!}
        </button>
    @endif

    @if($showCopy)
        {{-- Copy Console Logs --}}
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-primary"
                onclick="window.LLMMonitor.copyConsole('{{ $monitorId }}')"
                data-bs-toggle="tooltip" 
                title="Copy console logs to clipboard">
            {!! getIcon('ki-copy', $iconSize, '', 'i') !!}
        </button>
    @endif

    {{-- Separator between groups --}}
    @if(($showRefresh || $showDownload || $showCopy) && ($showClear || $showFullscreen || $showClose))
        <div class="separator separator-vertical mx-1"></div>
    @endif

    {{-- GRUPO 2: Destructive Actions (Clear, Fullscreen, Close) --}}
    @if($showClear)
        {{-- Clear Console --}}
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-danger"
                onclick="window.LLMMonitor.clearConsole('{{ $monitorId }}')"
                data-bs-toggle="tooltip" 
                title="Clear console logs">
            {!! getIcon('ki-trash', $iconSize, '', 'i') !!}
        </button>
    @endif

    @if($showFullscreen)
        {{-- Fullscreen Toggle (Alpine.js binding) --}}
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-primary"
                @click="toggleMonitorFullscreen()"
                data-bs-toggle="tooltip" 
                :title="monitorFullscreen ? 'Exit fullscreen' : 'Fullscreen'">
            <span x-show="!monitorFullscreen">
                {!! getIcon('ki-maximize', $iconSize, '', 'i') !!}
            </span>
            <span x-show="monitorFullscreen" style="display: none;">
                {!! getIcon('ki-cross-square', $iconSize, '', 'i') !!}
            </span>
        </button>
    @endif

    @if($showClose)
        {{-- Close Monitor (Alpine.js binding) --}}
        <button type="button" 
                class="btn btn-icon btn-{{ $size }} btn-active-light-danger"
                @click="monitorOpen = false"
                data-bs-toggle="tooltip" 
                title="Close monitor">
            {!! getIcon('ki-cross', $iconSize, '', 'i') !!}
        </button>
    @endif
</div>
