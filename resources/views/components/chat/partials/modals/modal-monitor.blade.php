{{-- Mobile: Modal --}}
<div class="modal fade" id="monitorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ki-duotone ki-chart-line-down fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Monitor de Streaming
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                @include('llm-manager::components.chat.shared.monitor-console', ['monitorId' => $monitorId])
            </div>
        </div>
    </div>
</div>
