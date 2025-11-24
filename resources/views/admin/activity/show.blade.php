<x-default-layout>
    @section('title', 'Activity Log Details - #' . $log->id)

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.activity.show', $log) }}
    @endsection

    <!--begin::Summary Card-->
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">Log Summary</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.llm.activity.index') }}" class="btn btn-sm btn-light">
                    <i class="ki-outline ki-left fs-4"></i>
                    Back to Activity
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-5">
                <!--begin::ID-->
                <div class="col-md-2">
                    <div class="fw-bold text-muted mb-1">Log ID</div>
                    <div class="fs-3 fw-bold text-dark">#{{ $log->id }}</div>
                </div>
                <!--end::ID-->

                <!--begin::Status-->
                <div class="col-md-2">
                    <div class="fw-bold text-muted mb-1">Status</div>
                    @if($log->status === 'success')
                        <span class="badge badge-success fs-6">Success</span>
                    @else
                        <span class="badge badge-danger fs-6">{{ ucfirst($log->status) }}</span>
                    @endif
                </div>
                <!--end::Status-->

                <!--begin::Provider-->
                <div class="col-md-2">
                    <div class="fw-bold text-muted mb-1">Provider</div>
                    <span class="badge badge-light-primary fs-6">{{ $log->configuration->provider ?? 'N/A' }}</span>
                </div>
                <!--end::Provider-->

                <!--begin::Model-->
                <div class="col-md-3">
                    <div class="fw-bold text-muted mb-1">Model</div>
                    <div class="fs-6 text-dark">{{ $log->configuration->model ?? 'N/A' }}</div>
                </div>
                <!--end::Model-->

                <!--begin::User-->
                <div class="col-md-3">
                    <div class="fw-bold text-muted mb-1">User</div>
                    <div class="fs-6 text-dark">{{ $log->user->name ?? 'System' }}</div>
                </div>
                <!--end::User-->
            </div>
        </div>
    </div>
    <!--end::Summary Card-->

    <!--begin::Metrics Card-->
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">Metrics</h3>
        </div>
        <div class="card-body">
            <div class="row text-center g-5">
                <!--begin::Prompt Tokens-->
                <div class="col-md-3">
                    <div class="fs-2 fw-bold text-primary">{{ number_format($log->prompt_tokens) }}</div>
                    <div class="text-muted">Prompt Tokens</div>
                </div>
                <!--end::Prompt Tokens-->

                <!--begin::Completion Tokens-->
                <div class="col-md-3">
                    <div class="fs-2 fw-bold text-success">{{ number_format($log->completion_tokens) }}</div>
                    <div class="text-muted">Completion Tokens</div>
                </div>
                <!--end::Completion Tokens-->

                <!--begin::Total Tokens-->
                <div class="col-md-2">
                    <div class="fs-2 fw-bold text-info">{{ number_format($log->total_tokens) }}</div>
                    <div class="text-muted">Total Tokens</div>
                </div>
                <!--end::Total Tokens-->

                <!--begin::Cost-->
                <div class="col-md-2">
                    <div class="fs-2 fw-bold {{ $log->cost_usd > 0 ? 'text-warning' : 'text-success' }}">
                        ${{ number_format($log->cost_usd, 6) }}
                    </div>
                    <div class="text-muted">Cost USD</div>
                    @if($log->currency && $log->currency !== 'USD')
                        <div class="text-muted fs-7">{{ $log->currency }} {{ number_format($log->cost_original, 4) }}</div>
                    @endif
                </div>
                <!--end::Cost-->

                <!--begin::Duration-->
                <div class="col-md-2">
                    <div class="fs-2 fw-bold text-dark">{{ number_format($log->execution_time_ms / 1000, 2) }}s</div>
                    <div class="text-muted">Duration</div>
                    <div class="text-muted fs-7">{{ number_format($log->execution_time_ms) }}ms</div>
                </div>
                <!--end::Duration-->
            </div>
        </div>
    </div>
    <!--end::Metrics Card-->

    <!--begin::Prompt & Response-->
    <div class="row g-5">
        <!--begin::Prompt-->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Prompt</h3>
                </div>
                <div class="card-body">
                    <div class="bg-light-dark p-5 rounded" style="white-space: pre-wrap; word-wrap: break-word; max-height: 400px; overflow-y: auto;">{{ $log->prompt }}</div>
                </div>
            </div>
        </div>
        <!--end::Prompt-->

        <!--begin::Response-->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Response</h3>
                </div>
                <div class="card-body">
                    <div class="bg-light-dark p-5 rounded" style="white-space: pre-wrap; word-wrap: break-word; max-height: 400px; overflow-y: auto;">{{ $log->response }}</div>
                </div>
            </div>
        </div>
        <!--end::Response-->
    </div>
    <!--end::Prompt & Response-->

    @if($log->error_message)
        <!--begin::Error Card-->
        <div class="card mt-5">
            <div class="card-header bg-light-danger">
                <h3 class="card-title text-danger">Error Details</h3>
            </div>
            <div class="card-body">
                <div class="bg-light-danger p-5 rounded">
                    <pre class="mb-0">{{ $log->error_message }}</pre>
                </div>
            </div>
        </div>
        <!--end::Error Card-->
    @endif

    <!--begin::Timestamps-->
    <div class="card mt-5">
        <div class="card-header">
            <h3 class="card-title">Timestamps</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="fw-bold text-muted mb-1">Executed At</div>
                    <div class="fs-6 text-dark">{{ $log->executed_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="fw-bold text-muted mb-1">Created At</div>
                    <div class="fs-6 text-dark">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="fw-bold text-muted mb-1">Updated At</div>
                    <div class="fs-6 text-dark">{{ $log->updated_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Timestamps-->

</x-default-layout>
