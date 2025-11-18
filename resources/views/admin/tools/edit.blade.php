<x-default-layout>
    @section('title', 'Edit Tool Definition')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.tools.edit', $tool) }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Tool: {{ $tool->name }}</h3>
        </div>

        <form action="{{ route('admin.llm.tools.update', $tool) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tool Name</label>
                    <div class="col-lg-8">
                        <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" value="{{ old('name', $tool->name) }}" required/>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Description</label>
                    <div class="col-lg-8">
                        <textarea name="description" class="form-control form-control-lg form-control-solid @error('description') is-invalid @enderror" rows="3">{{ old('description', $tool->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tool Type</label>
                    <div class="col-lg-8">
                        <select name="type" class="form-select form-select-solid @error('type') is-invalid @enderror" id="tool-type" required>
                            <option value="native" {{ old('type', $tool->type) == 'native' ? 'selected' : '' }}>Native PHP Function/Class</option>
                            <option value="mcp" {{ old('type', $tool->type) == 'mcp' ? 'selected' : '' }}>MCP Server</option>
                            <option value="custom" {{ old('type', $tool->type) == 'custom' ? 'selected' : '' }}>Custom Script</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Implementation</label>
                    <div class="col-lg-8">
                        <input type="text" name="implementation" class="form-control form-control-lg form-control-solid font-monospace @error('implementation') is-invalid @enderror" value="{{ old('implementation', $tool->implementation) }}"/>
                        @error('implementation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Extension Slug</label>
                    <div class="col-lg-8">
                        <input type="text" name="extension_slug" class="form-control form-control-lg form-control-solid @error('extension_slug') is-invalid @enderror" value="{{ old('extension_slug', $tool->extension_slug) }}"/>
                        @error('extension_slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Parameters Schema (JSON)</label>
                    <div class="col-lg-8">
                        <textarea name="parameters" class="form-control form-control-lg form-control-solid font-monospace @error('parameters') is-invalid @enderror" rows="8">{!! old('parameters', json_encode($tool->parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !!}</textarea>
                        @error('parameters')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $tool->is_active) ? 'checked' : '' }}/>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.tools.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Tool</button>
            </div>
        </form>
    </div>
</x-default-layout>
