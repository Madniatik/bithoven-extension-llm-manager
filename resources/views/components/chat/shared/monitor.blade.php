{{--
    LLM Monitor Component (Shared)
    
    Real-time monitoring for LLM streaming sessions
    Features: metrics tracking, activity history, console logs
    
    Note: JavaScript API loaded globally via chat-workspace.blade.php
--}}

<div class="llm-monitor">
    {{-- Monitor Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Monitor</h5>
        <div>
            <button class="btn btn-sm btn-light-primary" onclick="window.LLMMonitor.refresh()">
                <i class="ki-duotone ki-arrows-circle fs-5"><span class="path1"></span><span class="path2"></span></i>
                Refresh
            </button>
            <button class="btn btn-sm btn-light-danger" onclick="window.LLMMonitor.clear()">
                <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                Clear
            </button>
        </div>
    </div>

    {{-- Real-time Metrics --}}
    <div class="card bg-light-info mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Tokens</div>
                    <div class="fs-3 fw-bold" id="monitor-token-count">0</div>
                </div>
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Duration</div>
                    <div class="fs-3 fw-bold" id="monitor-duration">0s</div>
                </div>
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Chunks</div>
                    <div class="fs-3 fw-bold" id="monitor-chunk-count">0</div>
                </div>
                <div class="col-6">
                    <div class="fs-7 text-gray-600">Cost</div>
                    <div class="fs-3 fw-bold" id="monitor-cost">$0.00</div>
                </div>
            </div>
            <div class="mt-3">
                <div class="fs-7 text-gray-600 mb-1">Status</div>
                <div id="monitor-status">
                    <span class="badge badge-light-secondary">Idle</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity History --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Activity History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-row-bordered align-middle gy-4 gs-9" id="monitor-activity-table">
                    <thead class="border-gray-200 fs-7 fw-bold bg-light">
                        <tr>
                            <th class="ps-4">Time</th>
                            <th>Provider</th>
                            <th>Tokens</th>
                            <th>Cost</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody id="monitor-activity-body" class="fs-7">
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">No activity yet</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Console Logs --}}
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Console</h6>
        </div>
        <div class="card-body p-0">
            <div id="monitor-console" class="monitor-console-dark" style="height: 200px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6;">
                <div id="monitor-logs">
                    <span class="text-muted">[Monitor initialized]</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monitor Console Styles --}}
@push('styles')
    @include('llm-manager::components.chat.partials.styles.monitor-console')
@endpush
