<x-default-layout>
    @section('title', 'Create Tool Definition')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.tools.create') }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Register New Tool</h3>
        </div>

        <form action="{{ route('admin.llm.tools.store') }}" method="POST">
            @csrf
            
            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tool Name</label>
                    <div class="col-lg-8">
                        <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., get_weather" required/>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Description</label>
                    <div class="col-lg-8">
                        <textarea name="description" class="form-control form-control-lg form-control-solid @error('description') is-invalid @enderror" rows="3" placeholder="What does this tool do?">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tool Type</label>
                    <div class="col-lg-8">
                        <select name="type" class="form-select form-select-solid @error('type') is-invalid @enderror" id="tool-type" required>
                            <option value="">Select Type</option>
                            <option value="function_calling" {{ old('type') == 'function_calling' ? 'selected' : '' }}>Native PHP Function/Class</option>
                            <option value="mcp" {{ old('type') == 'mcp' ? 'selected' : '' }}>MCP Server</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6" id="implementation-field">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Implementation</label>
                    <div class="col-lg-8">
                        <input type="text" name="implementation" class="form-control form-control-lg form-control-solid font-monospace @error('implementation') is-invalid @enderror" value="{{ old('implementation') }}" placeholder="function_name or ClassName@method or server:tool or /path/to/script.sh"/>
                        <div class="form-text" id="implementation-help">
                            <span class="native-help" style="display:none">Format: <code>function_name</code> or <code>ClassName@method</code></span>
                            <span class="mcp-help" style="display:none">Format: <code>server_name:tool_name</code></span>
                        </div>
                        @error('implementation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Extension Slug</label>
                    <div class="col-lg-8">
                        <input type="text" name="extension_slug" class="form-control form-control-lg form-control-solid @error('extension_slug') is-invalid @enderror" value="{{ old('extension_slug') }}" placeholder="my-extension"/>
                        @error('extension_slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Parameters Schema (JSON)</label>
                    <div class="col-lg-8">
                        <textarea name="parameters" class="form-control form-control-lg form-control-solid font-monospace @error('parameters') is-invalid @enderror" rows="8" placeholder='{"type": "object", "properties": {...}, "required": [...]}'>{!! old('parameters', '{"type": "object", "properties": {}, "required": []}') !!}</textarea>
                        <div class="form-text">JSON Schema format for tool parameters</div>
                        @error('parameters')
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
                <a href="{{ route('admin.llm.tools.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Register Tool</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.getElementById('tool-type').addEventListener('change', function() {
            const type = this.value;
            const helpers = document.querySelectorAll('#implementation-help span');
            helpers.forEach(h => h.style.display = 'none');
            
            if (type === 'function_calling') {
                document.querySelector('.native-help').style.display = 'inline';
            } else if (type === 'mcp') {
                document.querySelector('.mcp-help').style.display = 'inline';
            }
        });
    </script>
    @endpush
</x-default-layout>
