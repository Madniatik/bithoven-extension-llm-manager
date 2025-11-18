<x-default-layout>
    @section('title', 'LLM Manager Dashboard')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.dashboard') }}
    @endsection

    <!-- Overview Cards -->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header flex-nowrap pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Configurations</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Active providers</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.llm.configurations.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column w-100">
                        <div class="d-flex justify-content-between fw-bold fs-6 text-gray-800 w-100 mt-auto mb-2">
                            <span>Active</span>
                            <span>{{ $stats['configurations']['active'] ?? 0 }}</span>
                        </div>
                        <div class="h-4px w-100 bg-light mb-2">
                            <div class="bg-success rounded h-4px" role="progressbar" style="width: {{ $stats['configurations']['total'] > 0 ? (($stats['configurations']['active'] / $stats['configurations']['total']) * 100) : 0 }}%"></div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-500 w-100">
                            {{ $stats['configurations']['total'] ?? 0 }} Total
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header flex-nowrap pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Requests Today</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">LLM API calls</span>
                    </h3>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column w-100">
                        <div class="fs-2hx fw-bold text-gray-800 mb-2">{{ number_format($stats['requests_today'] ?? 0) }}</div>
                        <div class="fw-semibold fs-6 text-gray-500">
                            <span class="badge badge-light-success">+{{ $stats['requests_growth'] ?? 0 }}%</span> vs yesterday
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header flex-nowrap pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Cost Today</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">API usage cost</span>
                    </h3>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column w-100">
                        <div class="fs-2hx fw-bold text-gray-800 mb-2">${{ number_format($stats['cost_today'] ?? 0, 4) }}</div>
                        <div class="fw-semibold fs-6 text-gray-500">
                            Budget: ${{ number_format($stats['daily_budget'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header flex-nowrap pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Active Conversations</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Open sessions</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.llm.conversations.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column w-100">
                        <div class="fs-2hx fw-bold text-gray-800 mb-2">{{ $stats['active_conversations'] ?? 0 }}</div>
                        <div class="fw-semibold fs-6 text-gray-500">
                            {{ $stats['total_conversations'] ?? 0 }} total sessions
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10">
        <!-- Recent Activity -->
        <div class="col-xl-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Recent Activity</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Latest LLM requests</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="timeline-label">
                        @foreach($recentActivity as $activity)
                        <div class="timeline-item">
                            <div class="timeline-label fw-bold text-gray-800 fs-7">{{ $activity->created_at->format('H:i') }}</div>
                            <div class="timeline-badge">
                                <i class="fa fa-genderless text-{{ $activity->status === 'success' ? 'success' : 'danger' }} fs-1"></i>
                            </div>
                            <div class="timeline-content d-flex">
                                <span class="fw-bold text-gray-800 ps-3">
                                    {{ $activity->extension_slug }}
                                    <span class="text-gray-500">- {{ $activity->configuration->name ?? 'N/A' }}</span>
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Extensions -->
        <div class="col-xl-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Top Extensions</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">By usage today</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    @foreach($topExtensions as $ext)
                    <div class="d-flex flex-stack mb-5">
                        <div class="d-flex align-items-center me-3">
                            <div class="symbol symbol-40px me-3">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold">{{ $ext->slug }}</span>
                                <span class="text-gray-500 fw-semibold fs-7">{{ number_format($ext->requests_count) }} requests</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-gray-800 fw-bold fs-6">${{ number_format($ext->total_cost, 4) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Alerts -->
    @if(isset($budgetAlerts) && $budgetAlerts->count() > 0)
    <div class="row g-5 g-xl-10 mt-5">
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Budget Alerts</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    @foreach($budgetAlerts as $alert)
                    <div class="alert alert-{{ $alert->level === 'critical' ? 'danger' : 'warning' }} d-flex align-items-center p-5 mb-3">
                        <i class="ki-duotone ki-shield-tick fs-2hx text-{{ $alert->level === 'critical' ? 'danger' : 'warning' }} me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">{{ $alert->extension_slug }}</h4>
                            <span>{{ $alert->message }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</x-default-layout>
