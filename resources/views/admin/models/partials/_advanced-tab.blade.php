<!--begin::Tab Advanced-->
<div class="tab-pane fade" id="advanced-tab" role="tabpanel">
    <!--begin::Card-->
    <div class="card card-flush mb-6 mb-xl-9">
        <!--begin::Card header-->
        <div class="card-header mt-6">
            <div class="card-title flex-column">
                <h3 class="fw-bold mb-1">
                    <i class="ki-duotone ki-lock fs-2 text-warning me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Advanced Settings
                </h3>
                <div class="fs-6 text-gray-500">Override provider defaults - Use with caution</div>
            </div>
            <div class="card-toolbar">
                <div class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" id="unlock-advanced" onchange="toggleAdvancedLock(this.checked)" />
                    <label class="form-check-label fw-bold text-gray-700" for="unlock-advanced">
                        <i class="ki-duotone ki-shield-cross fs-2 text-danger me-1" id="lock-icon-toggle">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Unlock Editing
                    </label>
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body p-9 pt-4">
            <form id="advanced-settings-form">
                @csrf
                
                <!--begin::Alert-->
                <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
                    <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4 mb-5 mb-sm-0">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h5 class="mb-1">Warning: Advanced Configuration</h5>
                        <span>These settings override provider defaults. Incorrect values may cause connection failures. Leave empty to use defaults.</span>
                    </div>
                </div>
                <!--end::Alert-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Custom API Endpoint
                        <span class="ms-1" data-bs-toggle="tooltip" title="Override the default provider endpoint">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="url" name="api_endpoint" id="api_endpoint" 
                               class="form-control form-control-lg form-control-solid advanced-field" 
                               placeholder="{{ $providerConfig['endpoint'] ?? 'https://api.example.com' }}" 
                               value="{{ $model->api_endpoint }}" 
                               disabled />
                        <div class="form-text">
                            Default: <code>{{ $providerConfig['endpoint'] ?? 'Not configured' }}</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Chat Endpoint Path
                        <span class="ms-1" data-bs-toggle="tooltip" title="Override the chat/completions endpoint path">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="endpoint_chat" id="endpoint_chat" 
                               class="form-control form-control-lg form-control-solid advanced-field" 
                               placeholder="{{ $providerConfig['endpoints']['chat'] ?? '/chat/completions' }}" 
                               value="{{ $model->endpoint_chat }}" 
                               disabled />
                        <div class="form-text">
                            Default: <code>{{ $providerConfig['endpoints']['chat'] ?? 'Not configured' }}</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Embeddings Endpoint Path
                        <span class="ms-1" data-bs-toggle="tooltip" title="Override the embeddings endpoint path">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="endpoint_embeddings" id="endpoint_embeddings" 
                               class="form-control form-control-lg form-control-solid advanced-field" 
                               placeholder="{{ $providerConfig['endpoints']['embeddings'] ?? '/embeddings' }}" 
                               value="{{ $model->endpoint_embeddings }}" 
                               disabled />
                        <div class="form-text">
                            Default: <code>{{ $providerConfig['endpoints']['embeddings'] ?? 'Not configured' }}</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Models List Endpoint Path
                        <span class="ms-1" data-bs-toggle="tooltip" title="Override the models list endpoint path">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="endpoint_models" id="endpoint_models" 
                               class="form-control form-control-lg form-control-solid advanced-field" 
                               placeholder="{{ $providerConfig['endpoints']['models'] ?? '/models' }}" 
                               value="{{ $model->endpoint_models }}" 
                               disabled />
                        <div class="form-text">
                            Default: <code>{{ $providerConfig['endpoints']['models'] ?? 'Not configured' }}</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Custom Headers (JSON)
                        <span class="ms-1" data-bs-toggle="tooltip" title="Additional HTTP headers in JSON format">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <textarea name="custom_headers" id="custom_headers" rows="4"
                                  class="form-control form-control-lg form-control-solid advanced-field" 
                                  placeholder='{"X-Custom-Header": "value"}'
                                  disabled>{{ $model->custom_headers ? json_encode(json_decode($model->custom_headers), JSON_PRETTY_PRINT) : '' }}</textarea>
                        <div class="form-text">
                            JSON format: <code>{"Header-Name": "value"}</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Request Timeout (seconds)
                        <span class="ms-1" data-bs-toggle="tooltip" title="Maximum time to wait for API response">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="number" name="timeout" id="timeout" 
                               class="form-control form-control-lg form-control-solid advanced-field" 
                               placeholder="30" 
                               value="{{ $model->timeout }}" 
                               min="5" max="300"
                               disabled />
                        <div class="form-text">
                            Default: <code>30 seconds</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Retry Attempts
                        <span class="ms-1" data-bs-toggle="tooltip" title="Number of retry attempts on failure">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="number" name="retry_attempts" id="retry_attempts" 
                               class="form-control form-control-lg form-control-solid advanced-field" 
                               placeholder="3" 
                               value="{{ $model->retry_attempts }}" 
                               min="0" max="10"
                               disabled />
                        <div class="form-text">
                            Default: <code>3 attempts</code>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Actions-->
                <div class="row">
                    <label class="col-lg-4 col-form-label"></label>
                    <div class="col-lg-8">
                        <button type="button" id="save-advanced-btn" class="btn btn-warning" onclick="saveAdvancedSettings()" disabled>
                            <i class="ki-duotone ki-shield-tick fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Save Advanced Settings
                        </button>
                        <button type="button" class="btn btn-light-danger ms-3" onclick="resetAdvancedSettings()" id="reset-advanced-btn" disabled>
                            <i class="ki-duotone ki-arrows-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Reset to Defaults
                        </button>
                    </div>
                </div>
                <!--end::Actions-->
            </form>
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>
<!--end::Tab Advanced-->

@push('scripts')
<script>
    // Toggle advanced settings lock
    function toggleAdvancedLock(unlocked) {
        const fields = document.querySelectorAll('.advanced-field');
        const saveBtn = document.getElementById('save-advanced-btn');
        const resetBtn = document.getElementById('reset-advanced-btn');
        const lockIcon = document.getElementById('lock-icon-toggle');
        
        fields.forEach(field => {
            field.disabled = !unlocked;
        });
        
        saveBtn.disabled = !unlocked;
        resetBtn.disabled = !unlocked;
        
        // Update icon
        if (unlocked) {
            lockIcon.classList.remove('ki-shield-cross', 'text-danger');
            lockIcon.classList.add('ki-shield-tick', 'text-success');
        } else {
            lockIcon.classList.remove('ki-shield-tick', 'text-success');
            lockIcon.classList.add('ki-shield-cross', 'text-danger');
        }
    }
    
    // Save advanced settings
    function saveAdvancedSettings() {
        Monitor.info('llm-monitor', 'Guardando configuración avanzada...');
        
        const form = document.getElementById('advanced-settings-form');
        const formData = new FormData(form);
        const data = {};
        
        // Build data object - send null for empty values
        const fields = ['api_endpoint', 'endpoint_chat', 'endpoint_embeddings', 'endpoint_models', 'custom_headers', 'timeout', 'retry_attempts'];
        
        fields.forEach(field => {
            const value = formData.get(field);
            
            if (field === 'custom_headers' && value && value.trim() !== '') {
                try {
                    data[field] = JSON.parse(value);
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid JSON',
                        text: 'Custom headers must be valid JSON format'
                    });
                    throw new Error('Invalid JSON');
                }
            } else if (value && value.trim() !== '') {
                data[field] = value;
            } else {
                // Send null to clear the field in database
                data[field] = null;
            }
        });
        
        const saveButton = document.getElementById('save-advanced-btn');
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        saveButton.disabled = true;
        
        fetch("{{ route('admin.llm.models.update-advanced', $model) }}", {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            if (data.success) {
                Monitor.success('llm-monitor', 'Configuración avanzada guardada');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message || 'Advanced settings updated successfully',
                    timer: 2000
                }).then(() => {
                    // Lock fields again after save
                    document.getElementById('unlock-advanced').checked = false;
                    toggleAdvancedLock(false);
                    location.reload();
                });
            } else {
                Monitor.error('llm-monitor', data.message || 'Error al guardar configuración');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed',
                    text: data.message || 'Failed to update advanced settings'
                });
            }
        })
        .catch(error => {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            Monitor.error('llm-monitor', 'Error de red: ' + error.message);
            
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving'
            });
        });
    }
    
    // Reset advanced settings to defaults
    function resetAdvancedSettings() {
        Swal.fire({
            title: 'Reset to Defaults?',
            text: "This will restore provider default values for all advanced settings",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f1416c',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset them!'
        }).then((result) => {
            if (result.isConfirmed) {
                Monitor.warning('llm-monitor', 'Restableciendo configuración a valores por defecto...');
                
                // Clear all advanced fields visually
                document.querySelectorAll('.advanced-field').forEach(field => {
                    field.value = '';
                });
                
                // Save with null values to use provider defaults
                const data = {
                    api_endpoint: null,
                    endpoint_chat: null,
                    endpoint_embeddings: null,
                    endpoint_models: null,
                    custom_headers: null,
                    timeout: null,
                    retry_attempts: null
                };
                
                const resetButton = document.getElementById('reset-advanced-btn');
                const originalText = resetButton.innerHTML;
                resetButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...';
                resetButton.disabled = true;
                
                fetch("{{ route('admin.llm.models.update-advanced', $model) }}", {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    resetButton.innerHTML = originalText;
                    resetButton.disabled = false;
                    
                    if (data.success) {
                        Monitor.success('llm-monitor', 'Configuración restablecida a valores por defecto');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Reset Complete!',
                            text: 'Advanced settings restored to provider defaults',
                            timer: 2000
                        }).then(() => {
                            // Lock fields and reload
                            document.getElementById('unlock-advanced').checked = false;
                            toggleAdvancedLock(false);
                            location.reload();
                        });
                    } else {
                        Monitor.error('llm-monitor', data.message || 'Error al restablecer configuración');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Reset Failed',
                            text: data.message || 'Failed to reset settings'
                        });
                    }
                })
                .catch(error => {
                    resetButton.innerHTML = originalText;
                    resetButton.disabled = false;
                    
                    Monitor.error('llm-monitor', 'Error de red: ' + error.message);
                    
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred during reset'
                    });
                });
            }
        });
    }
</script>
@endpush
