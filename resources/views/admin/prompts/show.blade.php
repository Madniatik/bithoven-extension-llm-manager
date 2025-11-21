<x-default-layout>
    @section('title', $template->name)
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.prompts.show', $template) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5">
        <!-- Template Info -->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Template Info</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.llm.prompts.edit', $template) }}" class="btn btn-sm btn-light me-2">Edit</a>
                        <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteTemplate()">Delete</button>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-7">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Category:</span>
                            <span class="badge badge-light-success">{{ $template->category }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Scope:</span>
                            @if($template->is_global)
                                <span class="badge badge-light-primary">Global</span>
                            @else
                                <span class="badge badge-light-info">Extension: {{ $template->extension_slug }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Status:</span>
                            @if($template->is_active)
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light-danger">Inactive</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Usage Count:</span>
                            <span class="fw-bold text-gray-800">{{ $template->usage_count ?? 0 }}</span>
                        </div>
                    </div>

                    @if($template->description)
                    <div class="separator separator-dashed my-5"></div>
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Description:</div>
                        <div class="text-gray-800 fs-6">{{ $template->description }}</div>
                    </div>
                    @endif

                    @if($template->variables && count($template->variables) > 0)
                    <div class="separator separator-dashed my-5"></div>
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Variables:</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($template->variables as $variable)
                                <span class="badge badge-light-warning">{{ $variable }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Template Content -->
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Template</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="bg-light-primary p-5 rounded">
                        <pre class="mb-0 font-monospace text-gray-800">{{ $template->template }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Examples -->
    <div class="card">
        <div class="card-header pt-7">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">Usage Examples</span>
            </h3>
        </div>
        <div class="card-body pt-5">
            <div class="bg-light p-5 rounded mb-5">
                <h4 class="text-gray-800 mb-3">PHP</h4>
                <pre class="mb-0 font-monospace text-gray-700">use Bithoven\LLMManager\Facades\LLM;

$response = LLM::template('{{ $template->slug }}', [
@foreach($template->variables ?? [] as $variable)
    '{{ $variable }}' => 'value',
@endforeach
]);</pre>
            </div>

            <div class="bg-light p-5 rounded">
                <h4 class="text-gray-800 mb-3">With Configuration</h4>
                <pre class="mb-0 font-monospace text-gray-700">$response = LLM::config('openai-gpt4o')
    ->template('{{ $template->slug }}', [
@foreach($template->variables ?? [] as $variable)
        '{{ $variable }}' => 'value',
@endforeach
    ]);</pre>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function deleteTemplate() {
            Swal.fire({
                title: 'Are you sure?',
                text: "Template \"{{ $template->name }}\" will be permanently deleted!",
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
                    form.action = "{{ route('admin.llm.prompts.destroy', $template) }}";
                    
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
