<!--begin::Tab Edit-->
<div class="tab-pane fade" id="edit-tab" role="tabpanel">
    <!--begin::Card-->
    <div class="card card-flush mb-6 mb-xl-9">
        <!--begin::Card header-->
        <div class="card-header mt-6">
            <div class="card-title flex-column">
                <h3 class="fw-bold mb-1">Edit Configuration</h3>
                <div class="fs-6 text-gray-500">Update LLM configuration settings</div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body p-9 pt-4">
            <form id="edit-model-form">
                @csrf
                
                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Name</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="name" class="form-control form-control-lg form-control-solid" 
                               placeholder="Configuration name" value="{{ $model->name }}" required />
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Provider</label>
                    <div class="col-lg-8 fv-row">
                        <select name="provider" id="provider-select" class="form-select form-select-solid form-select-lg" 
                                onchange="updateModelField(this.value)" disabled>
                            @foreach($providers as $key => $config)
                                <option value="{{ $key }}" {{ $model->provider === $key ? 'selected' : '' }}>
                                    {{ ucfirst($key) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Provider cannot be changed after creation</div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Model</label>
                    <div class="col-lg-8 fv-row">
                        <!-- Select (for providers with available_models) -->
                        <select name="model" id="model-select" class="form-select form-select-solid form-select-lg" 
                                style="{{ (!empty($providerConfig['available_models']) && !$providerConfig['supports_dynamic_models']) ? '' : 'display: none;' }}">
                            <option value="">Select a model...</option>
                            @if(!empty($providerConfig['available_models']))
                                @foreach($providerConfig['available_models'] as $modelOption)
                                    <option value="{{ $modelOption }}" {{ $model->model === $modelOption ? 'selected' : '' }}>
                                        {{ $modelOption }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        
                        <!-- Input (for custom or dynamic models) -->
                        <input type="text" name="model_input" id="model-input" 
                               class="form-control form-control-lg form-control-solid" 
                               placeholder="Model name" 
                               value="{{ $model->model }}"
                               style="{{ (!empty($providerConfig['available_models']) && !$providerConfig['supports_dynamic_models']) ? 'display: none;' : '' }}" />
                        
                        <div class="form-text" id="model-hint">
                            @if($providerConfig['supports_dynamic_models'] ?? false)
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="loadDynamicModels()">
                                    <i class="ki-duotone ki-arrow-down fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Load Models
                                </button>
                                <span class="text-muted ms-2">Click to load available models from provider</span>
                            @else
                                Enter the model identifier
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        API Key
                        @if($providerConfig['requires_api_key'] ?? true)
                            <span class="required"></span>
                        @endif
                    </label>
                    <div class="col-lg-8 fv-row">
                        <div class="input-group input-group-lg input-group-solid">
                            <input type="password" id="api-key-input" name="api_key" 
                                   class="form-control form-control-lg form-control-solid" 
                                   placeholder="Your API key" value="{{ $model->api_key }}" 
                                   {{ ($providerConfig['requires_api_key'] ?? true) ? 'required' : '' }} />
                            <button type="button" class="btn btn-icon btn-light-primary" 
                                    onclick="toggleApiKeyVisibility()" 
                                    title="Show/Hide API Key">
                                <i id="eye-icon" class="ki-duotone ki-eye fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </button>
                            <button type="button" class="btn btn-icon btn-light-info" 
                                    onclick="copyApiKey()" 
                                    title="Copy to clipboard">
                                <i class="ki-duotone ki-copy fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>
                        <div class="form-text">
                            @if($providerConfig['requires_api_key'] ?? true)
                                Required for authentication
                            @else
                                Optional - leave empty if not needed
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Max Tokens
                        <span class="ms-1" data-bs-toggle="tooltip" title="Maximum tokens per request">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="number" name="max_tokens" class="form-control form-control-lg form-control-solid" 
                               placeholder="e.g., 4096" value="{{ $model->max_tokens }}" min="1" />
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        Temperature
                        <span class="ms-1" data-bs-toggle="tooltip" title="Controls randomness: 0 = focused, 2 = creative">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <div class="d-flex align-items-center">
                            <input type="range" name="temperature" id="temperature-slider" 
                                   class="form-range me-4" 
                                   value="{{ $model->temperature ?? 0.7 }}" 
                                   step="0.1" min="0" max="2" 
                                   oninput="updateTemperatureValue(this.value)" />
                            <div class="d-flex flex-column align-items-center" style="min-width: 60px;">
                                <span id="temperature-value" class="badge badge-light-primary fs-5">{{ $model->temperature ?? 0.7 }}</span>
                            </div>
                        </div>
                        <div class="form-text mt-2">
                            <span class="text-muted">0 = More focused and deterministic</span>
                            <span class="text-muted mx-3">|</span>
                            <span class="text-muted">2 = More creative and random</span>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-7">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8 fv-row">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                   value="1" {{ $model->is_active ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-500" for="is_active">
                                Enable this configuration
                            </label>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Actions-->
                <div class="row">
                    <label class="col-lg-4 col-form-label"></label>
                    <div class="col-lg-8">
                        <button type="button" id="save-model-btn" class="btn btn-primary" onclick="saveModel()">
                            <i class="ki-duotone ki-check fs-2"></i>
                            Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary ms-3" onclick="location.reload()">
                            Cancel
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
<!--end::Tab Edit-->

@push('scripts')
<script>
    // Update temperature value display
    function updateTemperatureValue(value) {
        document.getElementById('temperature-value').textContent = parseFloat(value).toFixed(1);
    }
    
    // Toggle API Key visibility
    function toggleApiKeyVisibility() {
        const input = document.getElementById('api-key-input');
        const icon = document.getElementById('eye-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ki-eye');
            icon.classList.add('ki-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('ki-eye-slash');
            icon.classList.add('ki-eye');
        }
    }
    
    // Copy API Key to clipboard
    function copyApiKey() {
        const input = document.getElementById('api-key-input');
        const value = input.value;
        
        if (!value) {
            Swal.fire({
                icon: 'warning',
                title: 'No API Key',
                text: 'There is no API key to copy',
                timer: 2000
            });
            return;
        }
        
        navigator.clipboard.writeText(value).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'API key copied to clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(err => {
            console.error('Failed to copy:', err);
            Swal.fire({
                icon: 'error',
                title: 'Copy Failed',
                text: 'Failed to copy to clipboard',
                timer: 2000
            });
        });
    }

    
    // Update model field based on provider
    function updateModelField(provider) {
        const providers = @json($providers);
        const providerConfig = providers[provider] || {};
        
        const selectField = document.getElementById('model-select');
        const inputField = document.getElementById('model-input');
        const hintDiv = document.getElementById('model-hint');
        
        // Determine which field to show
        const hasAvailableModels = providerConfig.available_models && providerConfig.available_models.length > 0;
        const supportsDynamic = providerConfig.supports_dynamic_models || false;
        
        if (hasAvailableModels && !supportsDynamic) {
            // Show select with hardcoded models
            selectField.style.display = '';
            inputField.style.display = 'none';
            selectField.required = true;
            inputField.required = false;
            
            // Populate select
            selectField.innerHTML = '<option value="">Select a model...</option>';
            providerConfig.available_models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                selectField.appendChild(option);
            });
            
            hintDiv.textContent = 'Select from available models';
        } else if (supportsDynamic) {
            // Show input with load button
            selectField.style.display = 'none';
            inputField.style.display = '';
            selectField.required = false;
            inputField.required = true;
            
            hintDiv.innerHTML = 'Click to load available models <button type="button" class="btn btn-sm btn-light-primary ms-2" onclick="loadDynamicModels()">Load Models</button>';
        } else {
            // Show plain input
            selectField.style.display = 'none';
            inputField.style.display = '';
            selectField.required = false;
            inputField.required = true;
            
            hintDiv.textContent = 'Enter the model identifier';
        }
    }
    
    // Load dynamic models from provider via backend proxy
    function loadDynamicModels() {
        const provider = document.getElementById('provider-select').value;
        const providers = @json($providers);
        const providerConfig = providers[provider] || {};
        
        if (!providerConfig.supports_dynamic_models) {
            Swal.fire({
                icon: 'info',
                title: 'Not Supported',
                text: 'This provider does not support dynamic model loading',
                timer: 2000
            });
            return;
        }
        
        const inputField = document.getElementById('model-input');
        const selectField = document.getElementById('model-select');
        const hintDiv = document.getElementById('model-hint');
        const loadBtn = hintDiv.querySelector('button');
        
        // Save current model to pre-select after loading
        const currentModel = inputField.value || selectField.value || '{{ $model->model }}';
        
        // Show loading state
        if (loadBtn) {
            loadBtn.disabled = true;
            loadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        }
        
        // Get endpoint and API key
        const endpoint = document.querySelector('input[name="api_endpoint"]')?.value || providerConfig.endpoint || '';
        const apiKey = document.querySelector('input[name="api_key"]')?.value || '';
        
        // Call backend proxy
        fetch('{{ route("admin.llm.configurations.load-models") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                provider: provider,
                api_endpoint: endpoint,
                api_key: apiKey,
                use_cache: true
            })
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Failed to load models');
            }
            
            const models = result.models || [];
            
            if (models.length === 0) {
                throw new Error('No models returned from provider');
            }
            
            // Convert to select
            selectField.innerHTML = '<option value="">Select a model...</option>';
            
            let modelFound = false;
            models.forEach(model => {
                const option = document.createElement('option');
                const modelId = model.id || model.name || model;
                option.value = modelId;
                option.textContent = modelId;
                
                // Pre-select current model if exists
                if (modelId === currentModel) {
                    option.selected = true;
                    modelFound = true;
                }
                
                selectField.appendChild(option);
            });
            
            // Switch to select
            selectField.style.display = '';
            inputField.style.display = 'none';
            selectField.required = true;
            inputField.required = false;
            
            // Update hint with success message
            let successMsg = `${models.length} models loaded`;
            if (result.cached) {
                successMsg += ' <span class="badge badge-light-info ms-2">Cached</span>';
            }
            if (modelFound) {
                successMsg += ' <span class="badge badge-success ms-2">Current model found</span>';
            } else if (currentModel) {
                successMsg += ` <span class="badge badge-warning ms-2">Current model "${currentModel}" not in list</span>`;
            }
            
            hintDiv.innerHTML = successMsg + ' <button type="button" class="btn btn-sm btn-light-primary ms-3" onclick="loadDynamicModels()"><i class="ki-duotone ki-arrows-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Reload</button>';
            
            // Show success toast
            Swal.fire({
                icon: 'success',
                title: 'Models Loaded!',
                text: `${models.length} models available`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        })
        .catch(error => {
            console.error('Error loading models:', error);
            
            // Reset button
            if (loadBtn) {
                loadBtn.disabled = false;
                loadBtn.innerHTML = '<i class="ki-duotone ki-arrow-down fs-2"><span class="path1"></span><span class="path2"></span></i> Load Models';
            }
            
            // Show error
            Swal.fire({
                icon: 'error',
                title: 'Failed to Load Models',
                text: error.message || 'Check API key and endpoint configuration',
                timer: 3000
            });
            
            hintDiv.innerHTML = '<span class="text-danger">' + (error.message || 'Failed to load models') + '</span> <button type="button" class="btn btn-sm btn-light-primary ms-3" onclick="loadDynamicModels()"><i class="ki-duotone ki-arrows-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Retry</button>';
        });
    }
</script>
@endpush
