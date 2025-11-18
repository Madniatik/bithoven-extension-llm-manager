<x-default-layout>
    @section('title', $tool->name)
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.tools.show', $tool) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5">
        <!-- Tool Info -->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Tool Details</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.llm.tools.edit', $tool) }}" class="btn btn-sm btn-light">Edit</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-7">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Type:</span>
                            @if($tool->type === 'native')
                                <span class="badge badge-light-success">Native PHP</span>
                            @elseif($tool->type === 'mcp')
                                <span class="badge badge-light-primary">MCP Server</span>
                            @else
                                <span class="badge badge-light-info">Custom Script</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Extension:</span>
                            <span class="badge badge-light-secondary">{{ $tool->extension_slug }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Status:</span>
                            @if($tool->is_active)
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Implementation:</div>
                        <div class="text-gray-800 fs-6 font-monospace">{{ $tool->implementation }}</div>
                    </div>

                    @if($tool->description)
                    <div class="separator separator-dashed my-5"></div>
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Description:</div>
                        <div class="text-gray-800 fs-6">{{ $tool->description }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Parameters Schema -->
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Parameters Schema</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="bg-light-primary p-5 rounded">
                        <pre class="mb-0 font-monospace text-gray-800">{{ json_encode($tool->parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
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
                <h4 class="text-gray-800 mb-3">Execute Tool Directly</h4>
                <pre class="mb-0 font-monospace text-gray-700">use Bithoven\LLMManager\Services\Tools\LLMToolExecutor;

$executor = app(LLMToolExecutor::class);
$result = $executor->execute('{{ $tool->name }}', [
    // your parameters here
]);</pre>
            </div>

            <div class="bg-light p-5 rounded">
                <h4 class="text-gray-800 mb-3">Use with Function Calling</h4>
                <pre class="mb-0 font-monospace text-gray-700">use Bithoven\LLMManager\Facades\LLM;

$response = LLM::config('openai-gpt4o')
    ->tools(['{{ $tool->name }}'])
    ->generate('Use the {{ $tool->name }} tool to...');</pre>
            </div>
        </div>
    </div>
</x-default-layout>
