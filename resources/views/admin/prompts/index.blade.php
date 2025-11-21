<x-default-layout>
    @section('title', 'Prompt Templates')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.prompts.index') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search templates..."/>
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.llm.prompts.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    New Template
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="row g-6 g-xl-9">
                @foreach($templates as $template)
                <div class="col-md-6 col-xl-4">
                    <div class="card border border-2 border-gray-300 border-hover">
                        <div class="card-header border-0 pt-9">
                            <div class="card-title m-0">
                                <div class="symbol symbol-50px w-50px bg-light">
                                    <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <span class="badge badge-light-success fw-bold me-auto px-4 py-3">{{ $template->category }}</span>
                            </div>
                        </div>

                        <div class="card-body p-9">
                            <div class="fs-3 fw-bold text-gray-900">{{ $template->name }}</div>
                            <p class="text-gray-500 fw-semibold fs-5 mt-1 mb-3">{{ Str::limit($template->description, 100) }}</p>
                            
                            <div class="text-gray-400 fw-semibold fs-7 mb-5">
                                <i class="ki-duotone ki-calendar fs-6 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Created {{ $template->created_at->diffForHumans() }}
                            </div>

                            <div class="d-flex flex-wrap mb-5">
                                @if($template->is_active)
                                    <span class="badge badge-light-success me-2">Active</span>
                                @else
                                    <span class="badge badge-light-danger me-2">Inactive</span>
                                @endif
                                
                                @if($template->is_global)
                                    <span class="badge badge-light-primary">Global</span>
                                @else
                                    <span class="badge badge-light-info">Extension</span>
                                @endif
                            </div>

                            <div class="h-4px w-100 bg-light mb-5">
                                <div class="bg-success rounded h-4px" role="progressbar" style="width: {{ $template->usage_count > 0 ? min(($template->usage_count / 100) * 100, 100) : 0 }}%"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-gray-500 fw-semibold fs-7">
                                    Used {{ $template->usage_count ?? 0 }} times
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.llm.prompts.show', $template) }}" class="btn btn-sm btn-light-primary">View</a>
                                    <a href="#" class="btn btn-sm btn-icon btn-light-danger" onclick="deleteTemplate({{ $template->id }}, '{{ $template->name }}'); return false;">
                                        <i class="ki-duotone ki-trash fs-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function deleteTemplate(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Template "${name}" will be permanently deleted!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/llm/prompts/${id}`;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
    @endpush
</x-default-layout>
