{{-- Model Selection --}}
<div class="flex-grow-1">
    <select id="quick-chat-model-selector-{{ $session?->id ?? 'default' }}" name="configuration_id"
        class="form-select form-select-sm form-select-solid w-auto w-lg-300px" data-control="select2" data-hide-search="false"
        data-placeholder="Select LLM Model" data-dropdown-css-class="w-lg-300px">
        @foreach ($configurations as $config)
            <option value="{{ $config->id }}" data-provider="{{ ucfirst($config->provider) }}"
                data-model="{{ $config->model }}"
                data-endpoint="{{ $config->api_endpoint ?? 'N/A' }}"
                {{ $session && $session->configuration_id == $config->id ? 'selected' : '' }}>
                {{ $config->name }} ({{ ucfirst($config->provider) }})
            </option>
        @endforeach
    </select>
</div>
