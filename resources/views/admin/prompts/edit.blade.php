<x-default-layout>
    @section('title', 'Edit Prompt Template')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.prompts.edit', $template) }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Template: {{ $template->name }}</h3>
        </div>

        <form action="{{ route('admin.llm.prompts.update', $template) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Name</label>
                    <div class="col-lg-8">
                        <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required/>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Category</label>
                    <div class="col-lg-8">
                        <select name="category" class="form-select form-select-solid @error('category') is-invalid @enderror" required>
                            @foreach(['general', 'summarization', 'translation', 'analysis', 'generation', 'extraction', 'classification', 'custom'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $template->category) == $cat ? 'selected' : '' }}>
                                    {{ ucfirst($cat) }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Description</label>
                    <div class="col-lg-8">
                        <textarea name="description" class="form-control form-control-lg form-control-solid @error('description') is-invalid @enderror" rows="3">{{ old('description', $template->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Template</label>
                    <div class="col-lg-8">
                        <textarea name="template" class="form-control form-control-lg form-control-solid font-monospace @error('template') is-invalid @enderror" rows="10" required>{{ old('template', $template->template) }}</textarea>
                        @error('template')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Variables</label>
                    <div class="col-lg-8">
                        <div id="variables-container">
                            @if($template->variables && count($template->variables) > 0)
                                @foreach($template->variables as $variable)
                                <div class="input-group mb-3">
                                    <input type="text" name="variables[]" class="form-control form-control-solid" value="{{ $variable }}"/>
                                    <button class="btn btn-icon btn-light-danger" type="button" onclick="this.parentElement.remove()">
                                        <i class="ki-duotone ki-trash fs-2"></i>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                            <div class="input-group mb-3">
                                <input type="text" name="variables[]" class="form-control form-control-solid" placeholder="variable_name"/>
                                <button class="btn btn-icon btn-light-success" type="button" onclick="addVariable()">
                                    <i class="ki-duotone ki-plus fs-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Scope</label>
                    <div class="col-lg-8">
                        <div class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input" type="radio" name="is_global" value="1" id="global" {{ old('is_global', $template->is_global) == '1' ? 'checked' : '' }}/>
                            <label class="form-check-label" for="global">Global</label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" name="is_global" value="0" id="extension" {{ old('is_global', $template->is_global) == '0' ? 'checked' : '' }}/>
                            <label class="form-check-label" for="extension">Extension-specific</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-6" id="extension-select" style="display: {{ $template->is_global ? 'none' : 'flex' }};">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Extension</label>
                    <div class="col-lg-8">
                        <input type="text" name="extension_slug" class="form-control form-control-lg form-control-solid" value="{{ old('extension_slug', $template->extension_slug) }}"/>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}/>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.prompts.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Template</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function addVariable() {
            const container = document.getElementById('variables-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-3';
            div.innerHTML = `
                <input type="text" name="variables[]" class="form-control form-control-solid" placeholder="variable_name"/>
                <button class="btn btn-icon btn-light-danger" type="button" onclick="this.parentElement.remove()">
                    <i class="ki-duotone ki-trash fs-2"></i>
                </button>
            `;
            container.appendChild(div);
        }

        document.querySelectorAll('input[name="is_global"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('extension-select').style.display = 
                    this.value === '0' ? 'flex' : 'none';
            });
        });
    </script>
    @endpush
</x-default-layout>
