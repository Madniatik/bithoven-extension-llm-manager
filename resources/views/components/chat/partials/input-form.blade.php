<form id="quick-chat-form-{{ $session?->id ?? 'default' }}" class="d-flex flex-column gap-3">
    @csrf
    {{-- Message Input --}}
    <textarea id="quick-chat-message-input-{{ $session?->id ?? 'default' }}" class="form-control form-control-flush mb-3 bg-light" rows="1"
        data-kt-element="input" data-kt-autosize="true" placeholder="Type your message"></textarea>

    <!--begin:Toolbar-->
    <div class="d-flex flex-stack align-items-center">
        {{-- Action Buttons --}}
        @include('llm-manager::components.chat.partials.buttons.action-buttons')
        
        {{-- Model Selection --}}
        <div class="flex-grow-1 mx-3">
            <select id="quick-chat-model-selector-{{ $session?->id ?? 'default' }}" name="configuration_id" 
                class="form-select form-select-sm form-select-solid w-300px" 
                data-control="select2" 
                data-hide-search="false" 
                data-placeholder="Select LLM Model"
                data-dropdown-css-class="w-300px">
                @foreach ($configurations as $config)
                    <option value="{{ $config->id }}" 
                        data-provider="{{ ucfirst($config->provider) }}"
                        data-model="{{ $config->model }}"
                        {{ $session && $session->configuration_id == $config->id ? 'selected' : '' }}>
                        {{ $config->name }} ({{ ucfirst($config->provider) }})
                    </option>
                @endforeach
            </select>
        </div>
        
        {{-- Send/Stop/Clear Buttons --}}
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="send-btn-{{ $session?->id ?? 'default' }}" class="btn btn-icon btn-sm btn-primary" data-bs-toggle="tooltip"
                title="Send Message">
                <i class="ki-duotone ki-send fs-4"><span class="path1"></span><span class="path2"></span></i>
            </button>
            <button type="button" id="stop-btn-{{ $session?->id ?? 'default' }}" class="btn btn-icon btn-sm btn-danger d-none" data-bs-toggle="tooltip"
                title="Stop Streaming">
                <i class="ki-duotone ki-cross-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
            </button>
            <button type="button" id="clear-btn-{{ $session?->id ?? 'default' }}" class="btn btn-icon btn-sm btn-light-danger" data-bs-toggle="tooltip"
                title="Delete Chat">
                <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            </button>
        </div>
    </div>
    <!--end::Toolbar-->
</form>
