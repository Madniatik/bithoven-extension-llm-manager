<x-default-layout>
    @section('title', 'LLM Activity Logs')

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.activity') }}
    @endsection

    <!--begin::Toolbar-->
    <div class="card mb-5">
        <div class="card-body">
            <!--begin::Filters-->
            <form method="GET" action="{{ route('admin.llm.activity.index') }}" class="row g-3 align-items-end">
                <!--begin::Provider Filter-->
                <div class="col-md-2">
                    <label class="form-label">Provider</label>
                    <select name="provider" class="form-select form-select-sm">
                        <option value="">All Providers</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider }}" {{ request('provider') === $provider ? 'selected' : '' }}>
                                {{ ucfirst($provider) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!--end::Provider Filter-->

                <!--begin::Status Filter-->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>
                <!--end::Status Filter-->

                <!--begin::Date From-->
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <!--end::Date From-->

                <!--begin::Date To-->
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <!--end::Date To-->

                <!--begin::Search-->
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search in prompt/response..." value="{{ request('search') }}">
                </div>
                <!--end::Search-->

                <!--begin::Actions-->
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="ki-outline ki-magnifier fs-4"></i>
                        Filter
                    </button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Filters-->

            <!--begin::Export Buttons-->
            <div class="mt-5 d-flex gap-2">
                <a href="{{ route('admin.llm.activity.export', request()->query()) }}" class="btn btn-sm btn-light-success">
                    <i class="ki-outline ki-file-down fs-4"></i>
                    Export CSV
                </a>
                <a href="{{ route('admin.llm.activity.export-json', request()->query()) }}" class="btn btn-sm btn-light-info">
                    <i class="ki-outline ki-file-down fs-4"></i>
                    Export JSON
                </a>
                <a href="{{ route('admin.llm.activity.export-sql', request()->query()) }}" class="btn btn-sm btn-light-primary">
                    <i class="ki-outline ki-file-down fs-4"></i>
                    Export SQL
                </a>
                @if(request()->hasAny(['provider', 'status', 'date_from', 'date_to', 'search']))
                    <a href="{{ route('admin.llm.activity.index') }}" class="btn btn-sm btn-light-danger">
                        <i class="ki-outline ki-cross-circle fs-4"></i>
                        Clear Filters
                    </a>
                @endif
            </div>
            <!--end::Export Buttons-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Activity Table-->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Logs ({{ $logs->total() }} total)</h3>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="w-25px">#</th>
                                <th class="min-w-150px">Date/Time</th>
                                <th class="min-w-120px">Provider</th>
                                <th class="min-w-100px">Model</th>
                                <th class="min-w-100px">User</th>
                                <th class="min-w-200px">Prompt</th>
                                <th class="min-w-80px text-end">Tokens</th>
                                <th class="min-w-80px text-end">Cost</th>
                                <th class="min-w-80px text-end">Duration</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-100px text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <span class="text-dark fw-bold d-block">{{ $log->executed_at->format('H:i:s') }}</span>
                                        <span class="text-muted fs-7">{{ $log->executed_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-primary">{{ $log->configuration->provider->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fs-7">{{ $log->configuration->model ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fs-7">{{ $log->user->name ?? 'System' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-gray-700 fs-7">{{ Str::limit($log->prompt, 50) }}</span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($log->total_tokens) }}</td>
                                    <td class="text-end fw-bold {{ $log->cost_usd > 0 ? 'text-warning' : 'text-success' }}">
                                        ${{ number_format($log->cost_usd, 6) }}
                                    </td>
                                    <td class="text-end">{{ number_format($log->execution_time_ms / 1000, 2) }}s</td>
                                    <td>
                                        @if($log->status === 'success')
                                            <span class="badge badge-light-success">Success</span>
                                        @else
                                            <span class="badge badge-light-danger">{{ ucfirst($log->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.llm.activity.show', $log->id) }}" class="btn btn-sm btn-light-primary btn-icon">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!--begin::Pagination-->
                <div class="mt-5">
                    {{ $logs->links() }}
                </div>
                <!--end::Pagination-->
            @else
                <div class="text-center py-10">
                    <i class="ki-outline ki-information-5 fs-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No activity logs found.</p>
                </div>
            @endif
        </div>
    </div>
    <!--end::Activity Table-->

</x-default-layout>
