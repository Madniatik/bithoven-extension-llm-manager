<x-default-layout>
    @section('title', 'Edit Knowledge Base Document')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.knowledge-base.edit', $document) }}
    @endsection

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Document: {{ $document->title }}</h3>
        </div>

        <form action="{{ route('admin.llm.knowledge-base.update', $document) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Title</label>
                    <div class="col-lg-8">
                        <input type="text" name="title" class="form-control form-control-lg form-control-solid @error('title') is-invalid @enderror" value="{{ old('title', $document->title) }}" required/>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Extension</label>
                    <div class="col-lg-8">
                        <input type="text" name="extension_slug" class="form-control form-control-lg form-control-solid @error('extension_slug') is-invalid @enderror" value="{{ old('extension_slug', $document->extension_slug) }}" required/>
                        @error('extension_slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Document Type</label>
                    <div class="col-lg-8">
                        <select name="document_type" class="form-select form-select-solid @error('document_type') is-invalid @enderror" required>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ old('document_type', $document->document_type) == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('document_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Content</label>
                    <div class="col-lg-8">
                        <textarea name="content" class="form-control form-control-lg form-control-solid @error('content') is-invalid @enderror" rows="15" required>{{ old('content', $document->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Metadata (JSON)</label>
                    <div class="col-lg-8">
                        <textarea name="metadata" class="form-control form-control-lg form-control-solid font-monospace @error('metadata') is-invalid @enderror" rows="5">{!! old('metadata', json_encode($document->metadata ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !!}</textarea>
                        @error('metadata')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Re-Index After Update</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="reindex" value="1" {{ old('reindex', true) ? 'checked' : '' }}/>
                            <label class="form-check-label">Re-index document after updating</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('admin.llm.knowledge-base.index') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Document</button>
            </div>
        </form>
    </div>
</x-default-layout>
