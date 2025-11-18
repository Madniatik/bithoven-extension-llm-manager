<x-default-layout>
    @section('title', 'Edit Configuration')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.configurations.edit', $configuration) }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Configuration: {{ $configuration->name }}</h3>
        </div>

        <form action="{{ route('admin.llm.configurations.update', $configuration) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Name</label>
                    <div class="col-lg-8">
                        <input type="text" name="name"
                            class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror"
                            value="{{ old('name', $configuration->name) }}" required />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Provider</label>
                    <div class="col-lg-8">
                        <select name="provider"
                            class="form-select form-select-solid @error('provider') is-invalid @enderror" required>
                            @foreach ($providers as $key => $provider)
                                <option value="{{ $key }}"
                                    {{ old('provider', $configuration->provider) == $key ? 'selected' : '' }}>
                                    {{ ucfirst($key) }}
                                </option>
                            @endforeach
                        </select>
                        @error('provider')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Model</label>
                    <div class="col-lg-8">
                        <select name="model" id="model-select"
                            class="form-select form-select-solid @error('model') is-invalid @enderror"
                            style="display: none;">
                            <option value="">Loading models...</option>
                        </select>
                        <input type="text" name="model_input" id="model-input"
                            class="form-control form-control-lg form-control-solid @error('model') is-invalid @enderror"
                            value="{{ old('model', $configuration->model) }}" required />
                        <div class="form-text text-muted" id="model-hint"></div>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">API Key</label>
                    <div class="col-lg-8">
                        <input type="password" name="api_key"
                            class="form-control form-control-lg form-control-solid @error('api_key') is-invalid @enderror"
                            value="{{ old('api_key') }}" placeholder="Leave empty to keep current" />
                        @error('api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">API Endpoint</label>
                    <div class="col-lg-8">
                        <input type="url" name="api_endpoint"
                            class="form-control form-control-lg form-control-solid @error('api_endpoint') is-invalid @enderror"
                            value="{{ old('api_endpoint', $configuration->api_endpoint) }}" />
                        @error('api_endpoint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6"></label>
                    <div class="col-lg-8">
                        <button type="button" class="btn btn-light-primary" id="test-connection">
                            <i class="ki-duotone ki-verify fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Test Connection
                        </button>
                        <span id="connection-status" class="ms-3"></span>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Max Tokens</label>
                    <div class="col-lg-8">
                        <input type="number" name="max_tokens"
                            class="form-control form-control-lg form-control-solid @error('max_tokens') is-invalid @enderror"
                            value="{{ old('max_tokens', $configuration->max_tokens) }}" min="1" />
                        @error('max_tokens')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Temperature</label>
                    <div class="col-lg-8">
                        <input type="number" name="temperature"
                            class="form-control form-control-lg form-control-solid @error('temperature') is-invalid @enderror"
                            value="{{ old('temperature', $configuration->temperature) }}" min="0" max="2"
                            step="0.1" />
                        @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $configuration->is_active) ? 'checked' : '' }} />
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.configurations.index') }}"
                    class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Configuration</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const providersConfig = @json($providers);
            const currentProvider = '{{ old('provider', $configuration->provider) }}';
            const currentModel = '{{ old('model', $configuration->model) }}';

            const modelSelect = document.getElementById('model-select');
            const modelInput = document.getElementById('model-input');
            const modelHint = document.getElementById('model-hint');
            const providerSelect = document.querySelector('select[name="provider"]');

            function updateModelField(provider) {
                const config = providersConfig[provider];

                if (!config) return;

                // Si soporta modelos dinámicos y es Ollama
                if (config.supports_dynamic_models && provider === 'ollama') {
                    // Mostrar select, ocultar input
                    modelSelect.style.display = 'block';
                    modelInput.style.display = 'none';
                    modelInput.removeAttribute('name');
                    modelSelect.setAttribute('name', 'model');

                    // Cargar modelos dinámicamente
                    loadOllamaModels(config.endpoint);
                    modelHint.textContent = 'Models loaded from Ollama server';
                }
                // Si soporta modelos dinámicos y es OpenRouter o OpenAI
                else if (config.supports_dynamic_models && (provider === 'openrouter' || provider === 'openai')) {
                    // Mostrar select, ocultar input
                    modelSelect.style.display = 'block';
                    modelInput.style.display = 'none';
                    modelInput.removeAttribute('name');
                    modelSelect.setAttribute('name', 'model');

                    // Cargar modelos dinámicamente via API
                    loadOpenAICompatibleModels(config.endpoint + config.endpoints.models, provider);
                    modelHint.textContent = 'Models loaded from ' + provider.charAt(0).toUpperCase() + provider.slice(1) +
                        ' API';
                }
                // Si tiene lista hardcodeada
                else if (config.available_models && config.available_models.length > 0) {
                    // Mostrar select, ocultar input
                    modelSelect.style.display = 'block';
                    modelInput.style.display = 'none';
                    modelInput.removeAttribute('name');
                    modelSelect.setAttribute('name', 'model');

                    // Poblar select con modelos hardcodeados
                    modelSelect.innerHTML = '<option value="">Select a model...</option>';
                    config.available_models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        option.selected = model === currentModel;
                        modelSelect.appendChild(option);
                    });
                    modelHint.textContent = 'Available models for ' + provider.charAt(0).toUpperCase() + provider.slice(1);
                }
                // Input manual
                else {
                    // Ocultar select, mostrar input
                    modelSelect.style.display = 'none';
                    modelInput.style.display = 'block';
                    modelSelect.removeAttribute('name');
                    modelInput.setAttribute('name', 'model');
                    modelInput.value = currentModel;

                    if (provider === 'openai') {
                        modelHint.textContent = 'Enter model name (e.g., gpt-4o, gpt-4o-mini, gpt-3.5-turbo)';
                    } else if (provider === 'custom') {
                        modelHint.textContent = 'Enter custom model name';
                    } else {
                        modelHint.textContent = 'Enter model name';
                    }
                }
            }

            function loadOllamaModels(endpoint) {
                modelSelect.innerHTML = '<option value="">Loading models...</option>';
                modelHint.textContent = 'Fetching models from Ollama...';

                const tagsEndpoint = endpoint + '/api/tags';

                fetch(tagsEndpoint)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch models');
                        return response.json();
                    })
                    .then(data => {
                        if (data.models && data.models.length > 0) {
                            modelSelect.innerHTML = '<option value="">Select a model...</option>';
                            data.models.forEach(model => {
                                const option = document.createElement('option');
                                option.value = model.name;
                                option.textContent = model.name + ' (' + Math.floor(model.size / 1024 / 1024 /
                                    1024) + 'GB)';
                                option.selected = model.name === currentModel;
                                modelSelect.appendChild(option);
                            });
                            modelHint.textContent = data.models.length + ' models available on Ollama server';
                        } else {
                            modelSelect.innerHTML = '<option value="">No models found</option>';
                            modelHint.textContent = 'No models found. Pull a model first: ollama pull llama3.2';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading Ollama models:', error);
                        modelSelect.innerHTML = '<option value="">Error loading models</option>';
                        modelHint.innerHTML =
                            '<span class="text-danger">Cannot connect to Ollama. Make sure it\'s running.</span>';
                    });
            }

            function loadOpenAICompatibleModels(modelsEndpoint, provider) {
                modelSelect.innerHTML = '<option value="">Loading models...</option>';
                modelHint.textContent = 'Fetching models from ' + provider + ' API...';

                const apiKey = document.querySelector('input[name="api_key"]').value || '{{ $configuration->api_key ?? '' }}';

                if (!apiKey || apiKey === '***') {
                    modelSelect.innerHTML = '<option value="">API key required</option>';
                    modelHint.innerHTML = '<span class="text-warning">⚠️ Enter API key first to load models</span>';
                    return;
                }

                fetch(modelsEndpoint, {
                        headers: {
                            'Authorization': 'Bearer ' + apiKey,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch models (HTTP ' + response.status + ')');
                        return response.json();
                    })
                    .then(data => {
                        if (data.data && data.data.length > 0) {
                            modelSelect.innerHTML = '<option value="">Select a model...</option>';
                            data.data.forEach(model => {
                                const option = document.createElement('option');
                                option.value = model.id;
                                option.textContent = model.id;
                                option.selected = model.id === currentModel;
                                modelSelect.appendChild(option);
                            });
                            modelHint.textContent = data.data.length + ' models available';
                        } else {
                            modelSelect.innerHTML = '<option value="">No models found</option>';
                            modelHint.textContent = 'No models found';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading models:', error);
                        modelSelect.innerHTML = '<option value="">Error loading models</option>';
                        modelHint.innerHTML = '<span class="text-danger">Cannot load models. Check API key.</span>';
                    });
            }

            // Inicializar en carga de página
            updateModelField(currentProvider);

            // Actualizar cuando cambie el provider
            providerSelect.addEventListener('change', function() {
                updateModelField(this.value);
            });

            document.getElementById('test-connection').addEventListener('click', function() {
                const btn = this;
                const status = document.getElementById('connection-status');
                const provider = document.querySelector('select[name="provider"]').value;
                const apiEndpoint = document.querySelector('input[name="api_endpoint"]').value;
                const apiKey = document.querySelector('input[name="api_key"]').value ||
                    '{{ $configuration->api_key ? '***' : '' }}';

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
                status.innerHTML = '';

                fetch('{{ route('admin.llm.configurations.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            provider: provider,
                            api_endpoint: apiEndpoint,
                            api_key: apiKey === '***' ? null : apiKey
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            status.innerHTML =
                                '<span class="badge badge-light-success">✓ Connection successful</span>';
                        } else {
                            status.innerHTML = '<span class="badge badge-light-danger">✗ ' + (data.message ||
                                'Connection failed') + '</span>';
                        }
                    })
                    .catch(error => {
                        status.innerHTML = '<span class="badge badge-light-danger">✗ Error: ' + error.message +
                            '</span>';
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML =
                            '<i class="ki-duotone ki-verify fs-2"><span class="path1"></span><span class="path2"></span></i> Test Connection';
                    });
            });
        </script>
    @endpush
</x-default-layout>
