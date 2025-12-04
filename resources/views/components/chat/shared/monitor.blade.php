{{--
    LLM Monitor Component (Configurable)
    
    Real-time monitoring for LLM streaming sessions with configurable sections
    
    Props:
    - monitorId: Unique session identifier (default: from $session or 'default')
    - showCloseButton: Show/hide close button (default: false)
    - preset: Quick config - 'full'|'console-only'|'metrics-only'|'history-only'|'custom' (default: 'full')
    - showMetrics: Show real-time metrics section (override preset)
    - showHistory: Show activity history table (override preset)
    - showConsole: Show console logs (override preset)
    - showButtons: Show action buttons (override preset)
    
    Usage Examples:
    @include('llm-manager::components.chat.shared.monitor') // Full monitor
    @include('llm-manager::components.chat.shared.monitor', ['preset' => 'console-only']) // Only console
    @include('llm-manager::components.chat.shared.monitor', ['preset' => 'custom', 'showMetrics' => true, 'showConsole' => true]) // Custom config
--}}

@php
    // Presets configuration
    $presets = [
        'full' => [
            'showMetrics' => true,
            'showHistory' => true,
            'showConsole' => true,
            'showButtons' => true,
        ],
        'console-only' => [
            'showMetrics' => false,
            'showHistory' => false,
            'showConsole' => true,
            'showButtons' => true,
        ],
        'metrics-only' => [
            'showMetrics' => true,
            'showHistory' => false,
            'showConsole' => false,
            'showButtons' => false,
        ],
        'history-only' => [
            'showMetrics' => false,
            'showHistory' => true,
            'showConsole' => false,
            'showButtons' => true,
        ],
    ];
    
    // Get monitorId
    $monitorId = $monitorId ?? ($session?->id ?? 'default');
    
    // Debug: Log monitorId value
    if (config('app.debug')) {
        \Log::debug('[Monitor Component] monitorId resolved to: ' . $monitorId, [
            'passed_monitorId' => $monitorId ?? 'NULL',
            'session_id' => $session?->id ?? 'NULL',
            'final_monitorId' => $monitorId,
        ]);
    }
    
    // Get preset (default: 'full')
    $preset = $preset ?? 'full';
    
    // Apply preset configuration
    if ($preset !== 'custom' && isset($presets[$preset])) {
        $config = $presets[$preset];
        $showMetrics = $showMetrics ?? $config['showMetrics'];
        $showHistory = $showHistory ?? $config['showHistory'];
        $showConsole = $showConsole ?? $config['showConsole'];
        $showButtons = $showButtons ?? $config['showButtons'];
    } else {
        // Custom or fallback to individual props
        $showMetrics = $showMetrics ?? true;
        $showHistory = $showHistory ?? true;
        $showConsole = $showConsole ?? true;
        $showButtons = $showButtons ?? true;
    }
    
    // showCloseButton independent of preset
    $showCloseButton = $showCloseButton ?? false;
@endphp

<div class="llm-monitor" 
     data-monitor-id="{{ $monitorId }}"
     x-data="{ monitorId: '{{ $monitorId }}' }"
     x-init="
         $nextTick(() => {
             if (window.initLLMMonitor) {
                 window.initLLMMonitor('{{ $monitorId }}');
             }
         });
     ">
    {{-- Monitor Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="ki-duotone ki-code fs-3 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
            </i>
            Monitor
        </h5>
        
        @if($showButtons)
            <div class="d-flex gap-2">
                {{-- Download Logs (Green) --}}
                <button class="btn btn-sm btn-icon btn-light-success" 
                        onclick="window.LLMMonitor.downloadLogs('{{ $monitorId }}')"
                        data-bs-toggle="tooltip"
                        title="Download logs as .txt file">
                    <i class="ki-duotone ki-file-down fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>

                {{-- Copy Logs (Blue) --}}
                <button class="btn btn-sm btn-icon btn-light-primary" 
                        onclick="window.LLMMonitor.copyLogs('{{ $monitorId }}')"
                        data-bs-toggle="tooltip"
                        title="Copy logs to clipboard">
                    <i class="ki-duotone ki-copy fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>

                {{-- Clear Logs Only (Orange) --}}
                <button class="btn btn-sm btn-icon btn-light-warning" 
                        onclick="window.LLMMonitor.clearLogs('{{ $monitorId }}')"
                        data-bs-toggle="tooltip"
                        title="Clear console logs only">
                    <i class="ki-duotone ki-eraser fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                </button>

                {{-- Clear All (Red) --}}
                <button class="btn btn-sm btn-icon btn-light-danger" 
                        onclick="window.LLMMonitor.clear('{{ $monitorId }}')"
                        data-bs-toggle="tooltip"
                        title="Clear all monitoring data">
                    <i class="ki-duotone ki-trash fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                    </i>
                </button>

                {{-- Refresh (Gray) --}}
                <button class="btn btn-sm btn-icon btn-light" 
                        onclick="window.LLMMonitor.refresh('{{ $monitorId }}')"
                        data-bs-toggle="tooltip"
                        title="Refresh monitor display">
                    <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>

                @if($showCloseButton)
                    {{-- Close Monitor (Dark) - Solo en split-horizontal --}}
                    <button 
                        type="button" 
                        class="btn btn-sm btn-icon btn-light-dark" 
                        @click="monitorOpen = false; localStorage.setItem(`llm_chat_monitor_open_${sessionId}`, 'false')"
                        data-bs-toggle="tooltip"
                        title="Close monitor">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </button>
                @endif
            </div>
        @endif
    </div>

    @if($showMetrics)
        {{-- Real-time Metrics --}}
        <div class="card bg-light-info mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="fs-7 text-gray-600">Tokens</div>
                        <div class="fs-3 fw-bold" id="monitor-token-count-{{ $monitorId }}">0</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-7 text-gray-600">Duration</div>
                        <div class="fs-3 fw-bold" id="monitor-duration-{{ $monitorId }}">0s</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-7 text-gray-600">Chunks</div>
                        <div class="fs-3 fw-bold" id="monitor-chunk-count-{{ $monitorId }}">0</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-7 text-gray-600">Cost</div>
                        <div class="fs-3 fw-bold" id="monitor-cost-{{ $monitorId }}">$0.00</div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="fs-7 text-gray-600 mb-1">Status</div>
                    <div id="monitor-status-{{ $monitorId }}">
                        <span class="badge badge-light-secondary">Idle</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showHistory)
        {{-- Activity History --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Activity History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-row-bordered align-middle gy-4 gs-9" id="monitor-activity-table-{{ $monitorId }}">
                        <thead class="border-gray-200 fs-7 fw-bold bg-light">
                            <tr>
                                <th class="ps-4">Time</th>
                                <th>Provider</th>
                                <th>Tokens</th>
                                <th>Cost</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody id="monitor-activity-body-{{ $monitorId }}" class="fs-7">
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-4">No activity yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($showConsole)
        {{-- Console Logs --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Console</h6>
            </div>
            <div class="card-body p-0">
                <div id="monitor-console-{{ $monitorId }}" class="monitor-console-dark" style="height: 200px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6;">
                    <div id="monitor-logs-{{ $monitorId }}">
                        <span class="text-muted">[Monitor {{ $monitorId }} initialized]</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Monitor Console Styles --}}
@push('styles')
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endpush
