<x-default-layout>
    @section('title', 'Conversations')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.conversations.index') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search conversations..."/>
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.llm.conversations.create') }}" class="btn btn-primary me-3">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    New Conversation
                </a>
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Filter
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Status:</label>
                            <div>
                                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Select option">
                                    <option value="">All</option>
                                    <option value="active">Active</option>
                                    <option value="ended">Ended</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">Reset</button>
                            <button type="submit" class="btn btn-primary fw-semibold px-6">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="conversations-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-150px">Session ID</th>
                        <th class="min-w-100px">Configuration</th>
                        <th class="min-w-75px">Messages</th>
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-75px">Tokens</th>
                        <th class="min-w-125px">Created</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($sessions as $conversation)
                    <tr>
                        <td>
                            <a href="{{ route('admin.llm.conversations.show', $conversation) }}" class="text-gray-800 text-hover-primary">
                                {{ Str::limit($conversation->session_id, 20) }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-light-primary">{{ $conversation->configuration->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-light-info">{{ $conversation->messages()->count() }}</span>
                        </td>
                        <td>
                            @if($conversation->ended_at)
                                <span class="badge badge-light-secondary">Ended</span>
                            @elseif($conversation->expires_at && $conversation->expires_at->isPast())
                                <span class="badge badge-light-danger">Expired</span>
                            @else
                                <span class="badge badge-light-success">Active</span>
                            @endif
                        </td>
                        <td>{{ number_format($conversation->total_tokens) }}</td>
                        <td>
                            <span data-bs-toggle="tooltip" title="{{ $conversation->created_at->format('Y-m-d H:i:s') }}">
                                {{ $conversation->created_at->diffForHumans() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.conversations.show', $conversation) }}" class="menu-link px-3">View</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.llm.conversations.export', $conversation) }}" class="menu-link px-3">Export</a>
                                </div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('admin.llm.conversations.destroy', $conversation) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="menu-link px-3 w-100 text-start text-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10">
                            <div class="text-gray-600">
                                <i class="ki-duotone ki-message-text-2 fs-3x mb-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <p class="fs-5 fw-bold">No conversations found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-default-layout>
