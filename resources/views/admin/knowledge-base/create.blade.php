<x-default-layout>
    @section('title', 'Add Knowledge Base Document')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.knowledge-base.create') }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Document</h3>
        </div>

        <form action="{{ route('admin.llm.knowledge-base.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Title</label>
                    <div class="col-lg-8">
                        <input type="text" name="title" class="form-control form-control-lg form-control-solid @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Document title" required/>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Extension</label>
                    <div class="col-lg-8">
                        <input type="text" name="extension_slug" class="form-control form-control-lg form-control-solid @error('extension_slug') is-invalid @enderror" value="{{ old('extension_slug') }}" placeholder="my-extension" required/>
                        @error('extension_slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Content</label>
                    <div class="col-lg-8">
                        <textarea name="content" class="form-control form-control-lg form-control-solid @error('content') is-invalid @enderror" rows="15" placeholder="Document content..." required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Metadata (JSON)</label>
                    <div class="col-lg-8">
                        <textarea name="metadata" class="form-control form-control-lg form-control-solid font-monospace @error('metadata') is-invalid @enderror" rows="5" placeholder='{"author": "...", "tags": [...]}'>{!! old('metadata', '{}') !!}</textarea>
                        <div class="form-text">Optional metadata in JSON format</div>
                        @error('metadata')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Auto-Index</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="auto_index" value="1" {{ old('auto_index', true) ? 'checked' : '' }}/>
                            <label class="form-check-label">Automatically index after creation</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.knowledge-base.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Document</button>
            </div>
        </form>
    </div>
</x-default-layout>
