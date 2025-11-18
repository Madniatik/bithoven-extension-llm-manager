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
                        <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" value="{{ old('name', $configuration->name) }}" required/>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Provider</label>
                    <div class="col-lg-8">
                        <select name="provider" class="form-select form-select-solid @error('provider') is-invalid @enderror" required>
                            @foreach($providers as $key => $provider)
                                <option value="{{ $key }}" {{ old('provider', $configuration->provider) == $key ? 'selected' : '' }}>
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
                        <input type="text" name="model" class="form-control form-control-lg form-control-solid @error('model') is-invalid @enderror" value="{{ old('model', $configuration->model) }}" required/>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">API Key</label>
                    <div class="col-lg-8">
                        <input type="password" name="api_key" class="form-control form-control-lg form-control-solid @error('api_key') is-invalid @enderror" value="{{ old('api_key') }}" placeholder="Leave empty to keep current"/>
                        @error('api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">API Endpoint</label>
                    <div class="col-lg-8">
                        <input type="url" name="api_endpoint" class="form-control form-control-lg form-control-solid @error('api_endpoint') is-invalid @enderror" value="{{ old('api_endpoint', $configuration->api_endpoint) }}"/>
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
                        <input type="number" name="max_tokens" class="form-control form-control-lg form-control-solid @error('max_tokens') is-invalid @enderror" value="{{ old('max_tokens', $configuration->max_tokens) }}" min="1"/>
                        @error('max_tokens')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Temperature</label>
                    <div class="col-lg-8">
                        <input type="number" name="temperature" class="form-control form-control-lg form-control-solid @error('temperature') is-invalid @enderror" value="{{ old('temperature', $configuration->temperature) }}" min="0" max="2" step="0.1"/>
                        @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $configuration->is_active) ? 'checked' : '' }}/>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.configurations.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Configuration</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.getElementById('test-connection').addEventListener('click', function() {
            const btn = this;
            const status = document.getElementById('connection-status');
            const provider = document.querySelector('select[name="provider"]').value;
            const apiEndpoint = document.querySelector('input[name="api_endpoint"]').value;
            const apiKey = document.querySelector('input[name="api_key"]').value || '{{ $configuration->api_key ? "***" : "" }}';
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
            status.innerHTML = '';
            
            fetch('{{ route("admin.llm.configurations.test") }}', {
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
                    status.innerHTML = '<span class="badge badge-light-success">✓ Connection successful</span>';
                } else {
                    status.innerHTML = '<span class="badge badge-light-danger">✗ ' + (data.message || 'Connection failed') + '</span>';
                }
            })
            .catch(error => {
                status.innerHTML = '<span class="badge badge-light-danger">✗ Error: ' + error.message + '</span>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ki-duotone ki-verify fs-2"><span class="path1"></span><span class="path2"></span></i> Test Connection';
            });
        });
    </script>
    @endpush
</x-default-layout>
