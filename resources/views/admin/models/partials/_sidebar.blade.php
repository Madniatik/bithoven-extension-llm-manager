<!--begin::Sidebar-->
<div class="card mb-5 mb-xl-8">
    <!--begin::Card header-->
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold m-0">Actions</h3>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-2">
        <!--begin::Action-->
        <button type="button" class="btn btn-light-primary w-100 mb-3" onclick="toggleModelStatus()">
            <i class="ki-duotone ki-toggle-{{ $model->is_active ? 'on' : 'off' }} fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            {{ $model->is_active ? 'Disable' : 'Enable' }} Model
        </button>
        <!--end::Action-->

        <!--begin::Action-->
        <button type="button" id="test-connection-btn" class="btn btn-light-info w-100 mb-3"
            onclick="testModelConnection()">
            <i class="ki-duotone ki-verify fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            Test Connection
        </button>
        <!--end::Action-->

        <!--begin::Action-->
        <button type="button" class="btn btn-light-danger w-100" onclick="deleteModel()">
            <i class="ki-duotone ki-trash fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
            </i>
            Delete Provider
        </button>
        <!--end::Action-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Sidebar Actions-->

<!--begin::Activity Monitor-->
<x-monitor-panel 
    id="llm-monitor"
    title="📡 Activity Monitor"
    height="250px"
    maxHeight="400px"
    enableRecording="true"
    enableDownload="true"
    enableCopy="true"
    enableClear="true"
    class="mb-5 mb-xl-8"
/>
<!--end::Activity Monitor-->

<!--begin::Sidebar Info-->
<div class="card mb-5 mb-xl-8">
    <!--begin::Card header-->
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold m-0">Details</h3>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-2">
        <!--begin::Item-->
        <div class="d-flex align-items-center mb-7">
            <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Provider</span>
            <span class="badge badge-light-primary fs-8 fw-bold">{{ ucfirst($model->provider->name) }}</span>
        </div>
        <!--end::Item-->

        <!--begin::Item-->
        <div class="d-flex align-items-center mb-7">
            <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Model</span>
            <span class="fw-bold fs-7 text-gray-800">{{ $model->model }}</span>
        </div>
        <!--end::Item-->

        <!--begin::Item-->
        <div class="d-flex align-items-center mb-7">
            <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Status</span>
            @if ($model->is_active)
                <span class="badge badge-light-success fs-8 fw-bold">Active</span>
            @else
                <span class="badge badge-light-danger fs-8 fw-bold">Inactive</span>
            @endif
        </div>
        <!--end::Item-->

        <!--begin::Item-->
        <div class="d-flex align-items-center mb-7">
            <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Created</span>
            <span class="fw-bold fs-7 text-gray-800">{{ $model->created_at->format('M d, Y') }}</span>
        </div>
        <!--end::Item-->

        <!--begin::Item-->
        <div class="d-flex align-items-center mb-7">
            <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Updated</span>
            <span class="fw-bold fs-7 text-gray-800">{{ $model->updated_at->diffForHumans() }}</span>
        </div>
        <!--end::Item-->

        <!--begin::Item-->
        @if ($model->max_tokens)
            <div class="d-flex align-items-center mb-7">
                <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Max Tokens</span>
                <span class="fw-bold fs-7 text-gray-800">{{ number_format($model->max_tokens) }}</span>
            </div>
        @endif
        <!--end::Item-->

        <!--begin::Item-->
        @if ($model->temperature)
            <div class="d-flex align-items-center">
                <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Temperature</span>
                <span class="fw-bold fs-7 text-gray-800">{{ $model->temperature }}</span>
            </div>
        @endif
        <!--end::Item-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Sidebar Info-->

<!--begin::Capabilities-->
@if (!empty($providerConfig))
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold m-0">Capabilities</h3>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-2">
            <!--begin::Item-->
            <div class="d-flex align-items-center mb-5">
                <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">Dynamic Models</span>
                @if ($providerConfig['supports_dynamic_models'] ?? false)
                    <i class="ki-duotone ki-check-circle fs-1 text-success">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                @else
                    <i class="ki-duotone ki-cross-circle fs-1 text-danger">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                @endif
            </div>
            <!--end::Item-->

            <!--begin::Item-->
            <div class="d-flex align-items-center mb-5">
                <span class="fw-semibold fs-7 text-gray-600 flex-grow-1">API Key Required</span>
                @if ($providerConfig['requires_api_key'] ?? true)
                    <i class="ki-duotone ki-check-circle fs-1 text-warning">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                @else
                    <i class="ki-duotone ki-cross-circle fs-1 text-success">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                @endif
            </div>
            <!--end::Item-->

            <!--begin::Item-->
            <div class="d-flex flex-column">
                <span class="fw-semibold fs-7 text-gray-600 mb-2">Endpoints</span>
                <div class="ms-3">
                    @if (!empty($providerConfig['endpoints']))
                        @foreach ($providerConfig['endpoints'] as $type => $path)
                            @if ($path)
                                <div class="d-flex align-items-center mb-2">
                                    <span class="bullet bullet-dot bg-primary me-2"></span>
                                    <span class="fs-8 text-gray-700">{{ ucfirst($type) }}: <code
                                            class="fs-9">{{ $path }}</code></span>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            <!--end::Item-->
        </div>
        <!--end::Card body-->
    </div>
@endif
<!--end::Capabilities-->
