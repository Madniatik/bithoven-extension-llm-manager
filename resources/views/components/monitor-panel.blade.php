@props([
    'id' => 'monitor-' . uniqid(),
    'title' => '📡 Monitor',
    'height' => '300px',
    'maxHeight' => '600px',
    'class' => '',
])

<!--begin::Card-->
<div class="card card-flush {{ $class }}">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary" onclick="clearMonitor('{{ $id }}')">
                <i class="ki-duotone ki-trash fs-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                </i>
                Clear
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="{{ $id }}" 
             class="monitor-container bg-light-dark rounded p-4" 
             style="min-height: {{ $height }}; max-height: {{ $maxHeight }}; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.5;">
            <div class="text-muted text-center py-5">
                <i class="ki-duotone ki-information-2 fs-3x mb-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div>Waiting for events...</div>
            </div>
        </div>
    </div>
</div>
<!--end::Card-->
