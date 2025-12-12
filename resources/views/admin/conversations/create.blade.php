<x-default-layout>
    @section('title', 'New Conversation')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.conversations.create') }}
    @endsection

    <div class="row g-5 g-xl-10">
        <div class="col-xl-8 offset-xl-2">
            <div class="card card-flush">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Create New Conversation</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Choose your LLM configuration to start chatting</span>
                    </h3>
                </div>
                
                <form action="{{ route('admin.llm.conversations.store') }}" method="POST">
                    @csrf
                    
                    <div class="card-body pt-5">
                        <!-- Title (Optional) -->
                        <div class="mb-10">
                            <label class="form-label">Conversation Title (Optional)</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="e.g., Product Ideas Brainstorm" value="{{ old('title') }}">
                            <div class="form-text">Give your conversation a memorable name</div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- LLM Configuration Selection -->
                        <div class="mb-10">
                            <label class="form-label required">LLM Configuration</label>
                            <select name="configuration_id" class="form-select form-select-lg @error('configuration_id') is-invalid @enderror" required>
                                <option value="">Select a model...</option>
                                @foreach($configurations as $config)
                                    <option value="{{ $config->id }}" {{ old('configuration_id') == $config->id ? 'selected' : '' }}>
                                        {{ $config->name }} 
                                        <span class="text-muted">({{ ucfirst($config->provider->name) }} - {{ $config->model }})</span>
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose which AI model you want to chat with</div>
                            @error('configuration_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Configuration Preview -->
                        <div id="config-preview" style="display: none;" class="alert alert-primary d-flex align-items-center">
                            <i class="ki-duotone ki-information-5 fs-2hx text-primary me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-dark">Selected Configuration</h4>
                                <span id="config-name" class="text-gray-700"></span>
                                <div class="mt-2">
                                    <span class="badge badge-light-primary me-2" id="config-provider"></span>
                                    <span class="badge badge-light-info" id="config-model"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Information Box -->
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                            <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Quick Start Tips</h4>
                                    <div class="fs-6 text-gray-700">
                                        <p class="mb-2">• The conversation will be created with default settings (Temperature: 0.7, Max Tokens: 2000)</p>
                                        <p class="mb-2">• You can change these settings in the conversation page</p>
                                        <p class="mb-0">• Your settings will be saved per conversation using localStorage</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.llm.conversations.index') }}" class="btn btn-light">
                            <i class="ki-duotone ki-arrow-left fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Back to Conversations
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-message-add fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Create Conversation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const configSelect = document.querySelector('select[name="configuration_id"]');
            const configPreview = document.getElementById('config-preview');
            const configName = document.getElementById('config-name');
            const configProvider = document.getElementById('config-provider');
            const configModel = document.getElementById('config-model');

            configSelect.addEventListener('change', (e) => {
                if (e.target.value) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const optionText = selectedOption.text;
                    
                    // Parse the option text to extract provider and model
                    const match = optionText.match(/^(.+?)\s*\((.+?)\s*-\s*(.+?)\)$/);
                    
                    if (match) {
                        configName.textContent = match[1].trim();
                        configProvider.textContent = match[2].trim();
                        configModel.textContent = match[3].trim();
                    } else {
                        configName.textContent = optionText;
                        configProvider.textContent = '';
                        configModel.textContent = '';
                    }
                    
                    configPreview.style.display = 'flex';
                } else {
                    configPreview.style.display = 'none';
                }
            });

            // Trigger on page load if there's an old value
            if (configSelect.value) {
                configSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
    @endpush
</x-default-layout>
