<x-default-layout>
    @section('title', 'Create LLM Configuration')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.configurations.create') }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New LLM Configuration</h3>
        </div>

        <form action="{{ route('admin.llm.configurations.store') }}" method="POST">
            @csrf
            
            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Name</label>
                    <div class="col-lg-8">
                        <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., OpenAI GPT-4" required/>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Provider</label>
                    <div class="col-lg-8">
                        <select name="provider" class="form-select form-select-solid @error('provider') is-invalid @enderror" required>
                            <option value="">Select Provider</option>
                            @foreach($providers as $key => $provider)
                                <option value="{{ $key }}" {{ old('provider') == $key ? 'selected' : '' }}>
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
                        <input type="text" name="model" class="form-control form-control-lg form-control-solid @error('model') is-invalid @enderror" value="{{ old('model') }}" placeholder="e.g., gpt-4o, llama2, claude-3-opus" required/>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">API Key</label>
                    <div class="col-lg-8">
                        <input type="password" name="api_key" class="form-control form-control-lg form-control-solid @error('api_key') is-invalid @enderror" value="{{ old('api_key') }}" placeholder="sk-..."/>
                        <div class="form-text">Leave empty if using environment variable</div>
                        @error('api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">API Endpoint</label>
                    <div class="col-lg-8">
                        <input type="url" name="api_endpoint" class="form-control form-control-lg form-control-solid @error('api_endpoint') is-invalid @enderror" value="{{ old('api_endpoint') }}" placeholder="https://api.openai.com/v1"/>
                        <div class="form-text">Optional custom endpoint</div>
                        @error('api_endpoint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Max Tokens</label>
                    <div class="col-lg-8">
                        <input type="number" name="max_tokens" class="form-control form-control-lg form-control-solid @error('max_tokens') is-invalid @enderror" value="{{ old('max_tokens', 2000) }}" min="1"/>
                        @error('max_tokens')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Temperature</label>
                    <div class="col-lg-8">
                        <input type="number" name="temperature" class="form-control form-control-lg form-control-solid @error('temperature') is-invalid @enderror" value="{{ old('temperature', 0.7) }}" min="0" max="2" step="0.1"/>
                        <div class="form-text">0 = deterministic, 2 = very creative</div>
                        @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}/>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.configurations.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Configuration</button>
            </div>
        </form>
    </div>
</x-default-layout>
