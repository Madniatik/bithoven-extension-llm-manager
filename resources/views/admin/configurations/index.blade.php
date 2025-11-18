<x-default-layout>
    @section('title', 'LLM Configurations')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.configurations.index') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search configurations..."/>
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.llm.configurations.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    New Configuration
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="configurations-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-125px">Name</th>
                        <th class="min-w-100px">Provider</th>
                        <th class="min-w-100px">Model</th>
                        <th class="min-w-75px">Status</th>
                        <th class="min-w-100px">Usage</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($configurations as $config)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <span class="symbol-label bg-light-{{ $config->is_active ? 'success' : 'danger' }}">
                                        <i class="ki-duotone ki-{{ $config->provider === 'openai' ? 'abstract-26' : ($config->provider === 'ollama' ? 'cpu' : 'abstract-25') }} fs-2x text-{{ $config->is_active ? 'success' : 'danger' }}">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('admin.llm.configurations.show', $config) }}" class="text-gray-800 text-hover-primary mb-1">
                                        {{ $config->name }}
                                    </a>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $config->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light-primary">{{ strtoupper($config->provider) }}</span>
                        </td>
                        <td>{{ $config->model }}</td>
                        <td>
                            @if($config->is_active)
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold">{{ number_format($config->usage_logs_count ?? 0) }}</span>
                                <span class="text-muted fs-7">requests</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.configurations.show', $config) }}" class="menu-link px-3">View</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.configurations.edit', $config) }}" class="menu-link px-3">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('admin.llm.configurations.toggle', $config) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="menu-link px-3 w-100 text-start">
                                            {{ $config->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('admin.llm.configurations.destroy', $config) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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

    @push('scripts')
    <script>
        const filterSearch = document.querySelector('[data-kt-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            const table = document.getElementById('configurations-table');
            const rows = table.querySelectorAll('tbody tr');
            const searchText = e.target.value.toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    </script>
    @endpush
</x-default-layout>
