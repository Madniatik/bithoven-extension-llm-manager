<x-default-layout>
    @section('title', 'Knowledge Base')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.knowledge-base.index') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search documents..."/>
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.llm.knowledge-base.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Add Document
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kb-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">Title</th>
                        <th class="min-w-100px">Extension</th>
                        <th class="min-w-75px">Status</th>
                        <th class="min-w-100px">Chunks</th>
                        <th class="min-w-125px">Last Indexed</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($documents as $document)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-document fs-2x text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('admin.llm.knowledge-base.show', $document) }}" class="text-gray-800 text-hover-primary mb-1">
                                        {{ $document->title }}
                                    </a>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">
                                        {{ Str::limit($document->content, 100) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light-info">{{ $document->extension_slug }}</span>
                        </td>
                        <td>
                            @if($document->is_indexed)
                                <span class="badge badge-light-success">Indexed</span>
                            @else
                                <span class="badge badge-light-warning">Not Indexed</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light-primary">{{ $document->chunks()->count() }}</span>
                        </td>
                        <td>
                            @if($document->indexed_at)
                                <span data-bs-toggle="tooltip" title="{{ $document->indexed_at->format('Y-m-d H:i:s') }}">
                                    {{ $document->indexed_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.knowledge-base.show', $document) }}" class="menu-link px-3">View</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.knowledge-base.edit', $document) }}" class="menu-link px-3">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('admin.llm.knowledge-base.index-document', $document) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="menu-link px-3 w-100 text-start">
                                            {{ $document->is_indexed ? 'Re-index' : 'Index Now' }}
                                        </button>
                                    </form>
                                </div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('admin.llm.knowledge-base.destroy', $document) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="menu-link px-3 w-100 text-start text-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-default-layout>
