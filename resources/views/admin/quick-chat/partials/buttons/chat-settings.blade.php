<!--begin::Menu-->
<div class="">
    <button class="btn btn-sm btn-icon btn-active-light-primary" data-kt-menu-trigger="click"
        data-kt-menu-placement="top-start" aria-expanded="false" aria-haspopup="true" title="Settings">
        {!! getIcon('ki-setting-4','fs-2', '', 'i') !!}
    </button>
    <!--begin::Menu 3-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-300px py-3"
        data-kt-menu="true">
        <div class="row g-3 px-3 mb-3">
            {{-- Temperature --}}
            <div class="col-md-12">
                <label class="form-label">Temperature</label>
                <input type="range" id="quick-chat-temperature" name="temperature" 
                    class="form-range" min="0" max="2" step="0.1" 
                    value="{{ $session && $session->configuration ? $session->configuration->temperature : 0.7 }}">
                <small class="text-gray-600">Current: <span id="quick-chat-temp-display">{{ $session && $session->configuration ? $session->configuration->temperature : 0.7 }}</span></small>
            </div>
            
            <div class="separator my-2"></div>
            
            {{-- Max Tokens --}}
            <div class="col-md-12">
                <label class="form-label">Max Tokens</label>
                <input type="number" id="quick-chat-max-tokens" name="max_tokens" 
                    class="form-control form-control-solid form-control-sm" 
                    min="1" max="4000" 
                    value="{{ $session && $session->configuration ? $session->configuration->max_tokens : 2000 }}" 
                    placeholder="2000">
            </div>
            
            <div class="separator my-2"></div>
            
            {{-- Context Limit --}}
            <div class="col-md-12">
                <label class="form-label">Context Limit</label>
                <select id="quick-chat-context-limit" name="context_limit" 
                    class="form-select form-select-solid form-select-sm" 
                    data-control="select2" 
                    data-hide-search="true" 
                    data-placeholder="Select context limit">
                    <option value="5">Last 5 messages</option>
                    <option value="10" selected>Last 10 messages</option>
                    <option value="20">Last 20 messages</option>
                    <option value="50">Last 50 messages</option>
                    <option value="0">All messages</option>
                </select>
                <small class="text-gray-600">How much conversation history to send</small>
            </div>
        </div>
    </div>
    <!--end::Menu 3-->
</div>
<!--end::Menu-->