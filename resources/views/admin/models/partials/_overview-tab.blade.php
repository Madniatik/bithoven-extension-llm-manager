<!--begin::Tab Overview-->
<div class="tab-pane fade show active" id="overview-tab" role="tabpanel">
    <!--begin::Card-->
    <div class="card card-flush mb-6 mb-xl-9">
        <!--begin::Card header-->
        <div class="card-header mt-6">
            <div class="card-title flex-column">
                <h3 class="fw-bold mb-1">Configuration Details</h3>
                <div class="fs-6 text-gray-500">Core settings for this LLM configuration</div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body p-9 pt-4">
            <!--begin::Dates-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">Name</label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-gray-800">{{ $model->name }}</span>
                </div>
            </div>
            <!--end::Dates-->

            <!--begin::Dates-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">Provider</label>
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-800 fs-6">
                        <span class="badge badge-light-primary">{{ ucfirst($model->provider->name) }}</span>
                    </span>
                </div>
            </div>
            <!--end::Dates-->

            <!--begin::Input group-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">Model</label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-gray-800">{{ $model->model }}</span>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">Status</label>
                <div class="col-lg-8 d-flex align-items-center">
                    @if($model->is_active)
                        <span class="badge badge-light-success fs-7 fw-bold">Active</span>
                    @else
                        <span class="badge badge-light-danger fs-7 fw-bold">Inactive</span>
                    @endif
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">
                    Max Tokens
                    <span class="ms-1" data-bs-toggle="tooltip" title="Maximum tokens per request">
                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-gray-800">{{ number_format($model->max_tokens ?? 0) }}</span>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">
                    Temperature
                    <span class="ms-1" data-bs-toggle="tooltip" title="Controls randomness: 0 = focused, 2 = creative">
                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-gray-800">{{ $model->temperature ?? 'Not set' }}</span>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">Created</label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-gray-800">{{ $model->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-7">
                <label class="col-lg-4 fw-semibold text-muted">Last Updated</label>
                <div class="col-lg-8">
                    <span class="fw-bold fs-6 text-gray-800">{{ $model->updated_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
            <!--end::Input group-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Recent Usage Logs-->
    @if($recentLogs->count() > 0)
    <div class="card card-flush mb-6 mb-xl-9">
        <!--begin::Card header-->
        <div class="card-header mt-6">
            <div class="card-title flex-column">
                <h3 class="fw-bold mb-1">Recent Usage</h3>
                <div class="fs-6 text-gray-500">Last 10 API calls</div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body p-9 pt-4">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                    <thead>
                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                            <th class="p-0 pb-3 min-w-150px text-start">DATE</th>
                            <th class="p-0 pb-3 min-w-100px text-end">TOKENS</th>
                            <th class="p-0 pb-3 min-w-100px text-end">COST</th>
                            <th class="p-0 pb-3 min-w-100px text-end">TIME</th>
                            <th class="p-0 pb-3 min-w-100px text-end">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                        <tr>
                            <td class="text-start">
                                <span class="text-gray-800 fw-bold">{{ $log->created_at->format('M d, Y H:i') }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-gray-800 fw-bold">{{ number_format($log->total_tokens) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-gray-800 fw-bold">${{ number_format($log->cost_usd, 4) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="badge badge-light-primary">{{ number_format($log->execution_time_ms) }}ms</span>
                            </td>
                            <td class="text-end">
                                @if($log->status === 'success')
                                    <span class="badge badge-light-success">Success</span>
                                @else
                                    <span class="badge badge-light-danger">{{ ucfirst($log->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!--end::Card body-->
    </div>
    @endif
    <!--end::Recent Usage Logs-->
</div>
<!--end::Tab Overview-->
