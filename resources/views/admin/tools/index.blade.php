<x-default-layout>
    @section('title', 'LLM Tools Registry')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.tools.index') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search tools..."/>
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.llm.tools.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Register Tool
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="tools-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">Tool Name</th>
                        <th class="min-w-100px">Type</th>
                        <th class="min-w-100px">Extension</th>
                        <th class="min-w-75px">Status</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($tools as $tool)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <span class="symbol-label bg-light-{{ $tool->type === 'native' ? 'success' : ($tool->type === 'mcp' ? 'primary' : 'info') }}">
                                        <i class="ki-duotone ki-{{ $tool->type === 'native' ? 'code' : ($tool->type === 'mcp' ? 'setting-2' : 'file') }} fs-2x text-{{ $tool->type === 'native' ? 'success' : ($tool->type === 'mcp' ? 'primary' : 'info') }}">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('admin.llm.tools.show', $tool) }}" class="text-gray-800 text-hover-primary mb-1">
                                        {{ $tool->name }}
                                    </a>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">
                                        {{ Str::limit($tool->description, 80) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($tool->type === 'native')
                                <span class="badge badge-light-success">Native PHP</span>
                            @elseif($tool->type === 'mcp')
                                <span class="badge badge-light-primary">MCP Server</span>
                            @else
                                <span class="badge badge-light-info">Custom Script</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light-secondary">{{ $tool->extension_slug }}</span>
                        </td>
                        <td>
                            @if($tool->is_active)
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.tools.show', $tool) }}" class="menu-link px-3">View</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.tools.edit', $tool) }}" class="menu-link px-3">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('admin.llm.tools.destroy', $tool) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
