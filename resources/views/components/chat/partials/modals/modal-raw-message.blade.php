{{-- Raw Message Modal --}}
<div class="modal fade" id="rawMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Raw Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Tabs Navigation --}}
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="metadata-tab" data-bs-toggle="tab" href="#metadata-content" role="tab" aria-controls="metadata-content" aria-selected="true">
                            <i class="ki-duotone ki-information-4 fs-4 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Metadata
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="raw-response-tab" data-bs-toggle="tab" href="#raw-response-content" role="tab" aria-controls="raw-response-content" aria-selected="false">
                            <i class="ki-duotone ki-code fs-4 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                            Raw Response
                        </a>
                    </li>
                </ul>
                
                {{-- Tabs Content --}}
                <div class="tab-content" id="rawDataTabs">
                    {{-- Metadata Tab --}}
                    <div class="tab-pane fade show active" id="metadata-content" role="tabpanel" aria-labelledby="metadata-tab">
                        <div class="mb-3">
                            <span class="badge badge-light-info">Processed metadata from LLM response</span>
                        </div>
                        <pre style="max-height: 500px; overflow-y: auto;"><code id="metadataContent" class="language-json"></code></pre>
                    </div>
                    
                    {{-- Raw Response Tab --}}
                    <div class="tab-pane fade" id="raw-response-content" role="tabpanel" aria-labelledby="raw-response-tab">
                        <div class="mb-3">
                            <span class="badge badge-light-primary">Complete raw response from provider</span>
                        </div>
                        <pre style="max-height: 500px; overflow-y: auto;"><code id="rawResponseContent" class="language-json"></code></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="copyActiveTabJSON()">
                    <i class="ki-duotone ki-copy fs-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Copy Active Tab
                </button>
            </div>
        </div>
    </div>
</div>
