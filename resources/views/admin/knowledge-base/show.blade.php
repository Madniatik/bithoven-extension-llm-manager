<x-default-layout>
    @section('title', $document->title)
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.knowledge-base.show', $document) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5">
        <!-- Document Info -->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Document Info</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.llm.knowledge-base.edit', $document) }}" class="btn btn-sm btn-light">Edit</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-7">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Extension:</span>
                            <span class="badge badge-light-info">{{ $document->extension_slug }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Status:</span>
                            @if($document->is_indexed)
                                <span class="badge badge-light-success">Indexed</span>
                            @else
                                <span class="badge badge-light-warning">Not Indexed</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Chunks:</span>
                            <span class="fw-bold text-gray-800">{{ $document->chunk_count }}</span>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    @if($document->indexed_at)
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Last Indexed:</div>
                        <div class="text-gray-800 fs-6">{{ $document->indexed_at->format('Y-m-d H:i:s') }}</div>
                        <div class="text-gray-500 fs-7">{{ $document->indexed_at->diffForHumans() }}</div>
                    </div>
                    @endif

                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Created:</div>
                        <div class="text-gray-800 fs-6">{{ $document->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    @if(!$document->is_indexed)
                    <div class="separator separator-dashed my-5"></div>
                    <form action="{{ route('admin.llm.knowledge-base.index-doc', $document) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-duotone ki-arrow-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                            Index Document
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Content -->
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Content</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="bg-light p-5 rounded" style="max-height: 600px; overflow-y: auto;">
                        <div class="text-gray-800 fs-6 white-space-pre-line">{{ $document->content }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chunks -->
    @if($document->chunk_count > 0)
    <div class="card">
        <div class="card-header pt-7">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">Indexed Chunks</span>
                <span class="text-gray-500 mt-1 fw-semibold fs-7">{{ $document->chunk_count }} chunks</span>
            </h3>
        </div>
        <div class="card-body pt-5">
            <div class="accordion" id="chunksAccordion">
                @foreach($document->content_chunks ?? [] as $index => $chunkText)
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading{{ $index }}">
                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                            <span class="fw-bold text-gray-800">Chunk #{{ $index + 1 }}</span>
                        </button>
                    </h2>
                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#chunksAccordion">
                        <div class="accordion-body">
                            <div class="text-gray-700">{{ $chunkText }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</x-default-layout>
