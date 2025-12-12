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
                    <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-15"
                        placeholder="Search configurations..." />
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-config-modal">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    New Configuration
                </button>
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
                    @foreach ($configurations as $config)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-5">
                                        <span
                                            class="symbol-label bg-light-{{ $config->is_active ? 'success' : 'danger' }}">
                                            <i
                                                class="ki-duotone ki-{{ $config->provider->slug === 'openai' ? 'abstract-26' : ($config->provider->slug === 'ollama' ? 'cpu' : 'abstract-25') }} fs-2x text-{{ $config->is_active ? 'success' : 'danger' }}">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('admin.llm.models.show', $config) }}"
                                            class="text-gray-800 text-hover-primary mb-1">
                                            {{ $config->name }}
                                        </a>
                                        <span
                                            class="text-muted fw-semibold text-muted d-block fs-7">{{ $config->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-primary">{{ strtoupper($config->provider->slug) }}</span>
                            </td>
                            <td>{{ $config->model }}</td>
                            <td>
                                @if ($config->is_active)
                                    <span class="badge badge-light-success">Active</span>
                                @else
                                    <span class="badge badge-light-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span
                                        class="text-gray-800 fw-bold">{{ number_format($config->usage_logs_count ?? 0) }}</span>
                                    <span class="text-muted fs-7">requests</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-sm"
                                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                    data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="{{ route('admin.llm.models.show', $config) }}"
                                            class="menu-link px-3">View/Edit</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <form action="{{ route('admin.llm.configurations.toggle', $config) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit" class="menu-link px-3 w-100 text-start">
                                                {{ $config->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                    <div class="menu-item px-3">
                                        <form action="{{ route('admin.llm.configurations.destroy', $config) }}"
                                            method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="menu-link px-3 w-100 text-start text-danger">Delete</button>
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

    <!--begin::Create Configuration Modal-->
    <div class="modal fade" id="create-config-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form id="create-config-form" method="POST" action="{{ route('admin.llm.models.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="fw-bold">Create New LLM Configuration</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>

                    <div class="modal-body py-10 px-lg-17">
                        <div class="scroll-y me-n7 pe-7" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                            data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                            
                            <!--begin::Input group-->
                            <div class="mb-7">
                                <label class="required fw-semibold fs-6 mb-2">Name</label>
                                <input type="text" name="name" class="form-control form-control-solid" 
                                    placeholder="e.g., GPT-4 Production" required />
                                <div class="form-text">Must be unique</div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-7">
                                <label class="required fw-semibold fs-6 mb-2">Provider</label>
                                <select name="provider" class="form-select form-select-solid" required>
                                    <option value="">Select provider...</option>
                                    <option value="ollama">Ollama (Local)</option>
                                    <option value="openai">OpenAI</option>
                                    <option value="anthropic">Anthropic (Claude)</option>
                                    <option value="openrouter">OpenRouter</option>
                                    <option value="local">Local Model</option>
                                    <option value="custom">Custom Provider</option>
                                </select>
                                <div class="form-text">You can configure model and API key after creation</div>
                            </div>
                            <!--end::Input group-->

                        </div>
                    </div>

                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Create Configuration</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end::Create Configuration Modal-->

    @push('scripts')
        <script>
            const filterSearch = document.querySelector('[data-kt-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                const table = document.getElementById('configurations-table');
                const rows = table.querySelectorAll('tbody tr');
                const searchText = e.target.value.toLowerCase();

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchText) ? '' : 'none';
                });
            });

            // Handle modal form submission with validation
            document.getElementById('create-config-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.errors) {
                        // Show validation errors
                        const errorMessages = Object.values(data.errors).flat().join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMessages,
                            confirmButtonText: 'OK'
                        });
                    } else if (data.message) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while creating the configuration'
                    });
                });
            });
        </script>
    @endpush
</x-default-layout>
