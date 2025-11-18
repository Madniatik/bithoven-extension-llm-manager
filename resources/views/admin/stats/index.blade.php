<x-default-layout>
    @section('title', 'LLM Usage Statistics')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.statistics.index') }}
    @endsection

    <!-- Period Filter -->
    <div class="card mb-5">
        <div class="card-body py-5">
            <div class="d-flex align-items-center">
                <div class="me-5">
                    <label class="fs-6 fw-semibold mb-2">Period:</label>
                    <select class="form-select form-select-solid" id="period-filter" onchange="location = '?period=' + this.value + '&extension={{ $extensionSlug }}'">
                        <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="me-5">
                    <label class="fs-6 fw-semibold mb-2">Extension:</label>
                    <select class="form-select form-select-solid" id="extension-filter" onchange="location = '?period={{ $period }}&extension=' + this.value">
                        <option value="">All Extensions</option>
                        @foreach($stats['by_extension'] as $slug => $data)
                            <option value="{{ $slug }}" {{ $extensionSlug === $slug ? 'selected' : '' }}>{{ $slug }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-auto">
                    <a href="{{ route('admin.llm.statistics.export', ['period' => $period, 'extension' => $extensionSlug]) }}" class="btn btn-light-primary">
                        <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($stats['total_requests']) }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Requests</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">${{ number_format($stats['total_cost'], 4) }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Cost</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #17C653">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($stats['total_tokens']) }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Tokens</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #FFA621">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($stats['avg_execution_time'], 0) }}ms</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Avg Response Time</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10">
        <!-- By Configuration -->
        <div class="col-xl-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">By Configuration</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <table class="table table-row-dashed fs-6 gy-3">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Configuration</th>
                                <th class="text-end">Requests</th>
                                <th class="text-end">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach($stats['by_configuration'] as $configId => $data)
                            <tr>
                                <td>Config #{{ $configId }}</td>
                                <td class="text-end">{{ number_format($data['count']) }}</td>
                                <td class="text-end">${{ number_format($data['cost'], 4) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- By Extension -->
        <div class="col-xl-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">By Extension</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <table class="table table-row-dashed fs-6 gy-3">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Extension</th>
                                <th class="text-end">Requests</th>
                                <th class="text-end">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach($stats['by_extension'] as $slug => $data)
                            <tr>
                                <td><span class="badge badge-light-info">{{ $slug }}</span></td>
                                <td class="text-end">{{ number_format($data['count']) }}</td>
                                <td class="text-end">${{ number_format($data['cost'], 4) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Budget Alerts -->
    @if($stats['recent_alerts']->count() > 0)
    <div class="card mt-5">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">Recent Budget Alerts</span>
            </h3>
        </div>
        <div class="card-body py-4">
            @foreach($stats['recent_alerts'] as $alert)
            <div class="alert alert-warning d-flex align-items-center p-5 mb-3">
                <i class="ki-duotone ki-shield-tick fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-dark">{{ $alert->extension_slug }}</h4>
                    <span class="text-muted">{{ $alert->message }} - {{ $alert->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</x-default-layout>
