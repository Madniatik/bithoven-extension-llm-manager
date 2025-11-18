<x-default-layout>
    @section('title', $configuration->name)
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.configurations.show', $configuration) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Configuration Details -->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Configuration Details</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.llm.configurations.edit', $configuration) }}" class="btn btn-sm btn-light">Edit</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-7">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Provider:</span>
                            <span class="badge badge-light-primary">{{ strtoupper($configuration->provider) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Model:</span>
                            <span class="fw-bold text-gray-800">{{ $configuration->model }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Status:</span>
                            @if($configuration->is_active)
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light-danger">Inactive</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Max Tokens:</span>
                            <span class="fw-bold text-gray-800">{{ number_format($configuration->max_tokens ?? 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Temperature:</span>
                            <span class="fw-bold text-gray-800">{{ $configuration->temperature ?? 'N/A' }}</span>
                        </div>
                    </div>

                    @if($configuration->api_endpoint)
                    <div class="separator separator-dashed my-5"></div>
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">API Endpoint:</div>
                        <div class="text-gray-800 fs-7 text-break">{{ $configuration->api_endpoint }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Usage Statistics</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Last 30 days</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="row g-5 g-xl-10">
                        <div class="col-md-6 col-xxl-3">
                            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ metronicAsset('media/patterns/vector-1.png') }}')">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($configuration->statistics->total_requests ?? 0) }}</span>
                                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Requests</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xxl-3">
                            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA;background-image:url('{{ metronicAsset('media/patterns/vector-1.png') }}')">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">${{ number_format($configuration->statistics->total_cost ?? 0, 4) }}</span>
                                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Cost</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xxl-3">
                            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #17C653;background-image:url('{{ metronicAsset('media/patterns/vector-1.png') }}')">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($configuration->statistics->total_tokens ?? 0) }}</span>
                                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Tokens</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xxl-3">
                            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #FFA621;background-image:url('{{ metronicAsset('media/patterns/vector-1.png') }}')">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($configuration->statistics->avg_execution_time ?? 0) }}ms</span>
                                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Avg Response Time</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Usage Logs -->
    <div class="card">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">Recent Usage</span>
                <span class="text-gray-500 mt-1 fw-semibold fs-7">Last 50 requests</span>
            </h3>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-150px">Date</th>
                        <th class="min-w-100px">Extension</th>
                        <th class="min-w-75px">Tokens</th>
                        <th class="min-w-75px">Cost</th>
                        <th class="min-w-75px">Time</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($configuration->usageLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td><span class="badge badge-light-info">{{ $log->extension_slug ?? 'N/A' }}</span></td>
                        <td>{{ number_format($log->total_tokens) }}</td>
                        <td>${{ number_format($log->cost_usd, 6) }}</td>
                        <td>{{ $log->execution_time_ms }}ms</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No usage logs yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-default-layout>
