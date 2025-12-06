{{-- Mobile: Modal --}}
<div class="modal fade" id="monitorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ki-duotone ki-chart-line-down fs-2 me-2" x-show="activeTab === 'console'">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <i class="ki-duotone ki-chart-pie-simple fs-2 me-2" x-show="activeTab === 'activity'" x-cloak>
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <span x-text="activeTab === 'console' ? 'Console Monitor' : 'Activity Logs'"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 60vh; overflow-y: auto;">
                {{-- Console Tab --}}
                <div x-show="activeTab === 'console'">
                    @include('llm-manager::components.chat.shared.monitor-console', ['monitorId' => $monitorId])
                </div>

                {{-- Activity Logs Tab --}}
                <div x-show="activeTab === 'activity'" x-cloak>
                    <div class="h-100 d-flex flex-column" style="background: #1e1e2d; color: #92929f; padding: 1rem;">
                        <h6 class="text-white mb-3">
                            <i class="ki-duotone ki-chart-pie-simple fs-3 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Activity History
                        </h6>
                        <div class="flex-grow-1" style="overflow-y: auto;">
                            <div class="table-responsive">
                                <table class="table table-row-bordered align-middle gy-4 gs-9" id="monitor-activity-table-modal-{{ $monitorId }}">
                                    <thead class="border-gray-200 fs-7 fw-bold" style="background: #15151e; color: #92929f;">
                                        <tr>
                                            <th class="ps-4">Time</th>
                                            <th>Provider</th>
                                            <th>Tokens</th>
                                            <th>Cost</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monitor-activity-body-modal-{{ $monitorId }}" class="fs-7" style="color: #92929f;">
                                        <tr>
                                            <td colspan="5" class="text-center py-4" style="color: #565674;">No activity yet</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
