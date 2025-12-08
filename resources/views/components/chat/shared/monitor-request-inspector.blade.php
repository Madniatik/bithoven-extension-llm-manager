{{--
    Request Inspector Tab
    Display request data sent to LLM models
    
    Note: JavaScript functions loaded via partials/scripts/request-inspector.blade.php
    Note: Called from streaming-handler.js via populateRequestInspector(data)
--}}

<div class="card card-flush shadow-sm h-100" id="request-inspector-container">
    <div class="card-body p-4">
        <div id="request-no-data" class="text-center text-muted py-10">
            <i class="ki-duotone ki-search-list fs-3x mb-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <p class="fw-semibold fs-5 mb-0">No Request Data</p>
            <p class="fs-7 text-muted">Send a message to see the request payload</p>
        </div>

        <div id="request-data-display" class="d-none">
            {{-- Section 1: Metadata --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#metadata-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-information fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Metadata
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="metadata-section" class="collapse show">
                    <div class="card-body p-4">
                        <table class="table table-row-bordered table-sm">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-gray-600 w-200px">Provider</td>
                                    <td id="meta-provider" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Model</td>
                                    <td id="meta-model" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Endpoint</td>
                                    <td id="meta-endpoint" class="text-gray-800 font-monospace fs-8">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Timestamp</td>
                                    <td id="meta-timestamp" class="text-gray-800 fs-8">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Session ID</td>
                                    <td id="meta-session-id" class="text-gray-800 font-monospace fs-8">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Message ID</td>
                                    <td id="meta-message-id" class="text-gray-800 font-monospace fs-8">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 2: Parameters --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#parameters-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-setting-2 fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Parameters
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="parameters-section" class="collapse show">
                    <div class="card-body p-4">
                        <table class="table table-row-bordered table-sm">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-gray-600 w-200px">Temperature</td>
                                    <td id="param-temperature" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Max Tokens</td>
                                    <td id="param-max-tokens" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Top P</td>
                                    <td id="param-top-p" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Context Limit</td>
                                    <td id="param-context-limit" class="text-gray-800">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-gray-600">Actual Context Size</td>
                                    <td id="param-actual-context-size" class="text-gray-800 fw-bold">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 3: System Instructions --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#system-instructions-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-code fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        System Instructions
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="system-instructions-section" class="collapse">
                    <div class="card-body p-4">
                        <textarea id="system-instructions-text" class="form-control font-monospace fs-8" rows="6" readonly>No system instructions defined</textarea>
                    </div>
                </div>
            </div>

            {{-- Section 4: Context Messages --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#context-messages-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-message-text-2 fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Context Messages
                        <span id="context-count-badge" class="badge badge-light-primary ms-2">0</span>
                    </h3>
                    <div class="card-toolbar">
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="context-messages-section" class="collapse">
                    <div class="card-body p-4">
                        <div id="context-messages-list" class="timeline">
                            <p class="text-muted">No context messages</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 5: Current Prompt --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#current-prompt-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-message-edit fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Current Prompt
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary me-2" onclick="copyCurrentPrompt()">
                            <i class="ki-duotone ki-copy fs-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Copy
                        </button>
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="current-prompt-section" class="collapse">
                    <div class="card-body p-4">
                        <textarea id="current-prompt-text" class="form-control font-monospace fs-8" rows="4" readonly>No prompt available</textarea>
                    </div>
                </div>
            </div>

            {{-- Section 6: Full JSON Body --}}
            <div class="card mb-4 border border-gray-300">
                <div class="card-header collapsible cursor-pointer" data-bs-toggle="collapse" data-bs-target="#full-json-section">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-abstract-26 fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Full JSON Body
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary me-2" onclick="copyRequestJSON()">
                            <i class="ki-duotone ki-copy fs-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Copy
                        </button>
                        <button type="button" class="btn btn-sm btn-light-success me-2" onclick="downloadRequestJSON()">
                            <i class="ki-duotone ki-cloud-download fs-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Download
                        </button>
                        <span class="btn btn-sm btn-icon btn-active-light-primary">
                            <i class="ki-duotone ki-down fs-2"></i>
                        </span>
                    </div>
                </div>
                <div id="full-json-section" class="collapse">
                    <div class="card-body p-4">
                        <pre><code id="full-json-code" class="language-json">No request data available</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
