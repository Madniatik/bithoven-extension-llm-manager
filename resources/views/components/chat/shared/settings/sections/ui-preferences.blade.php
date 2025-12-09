{{--
    UI PREFERENCES SECTION
    
    Configuración de layout, toolbar buttons y estilos visuales
--}}

@php
    $sessionId = $sessionId ?? 'default';
@endphp

<div class="mb-8">
    <h3 class="mb-5">
        {!! getIcon('ki-element-11', 'fs-2 me-2', '', 'i') !!}
        UI Preferences
    </h3>
    
    {{-- Chat Layout --}}
    <div class="mb-5">
        <label class="form-label fw-semibold text-gray-700">Chat Layout</label>
        <select class="form-select form-select-solid" id="chat_layout_{{ $sessionId }}">
            <option value="bubble" selected>Bubble Style (WhatsApp-like)</option>
            <option value="drawer">Drawer Style</option>
            <option value="compact">Compact Style</option>
        </select>
        <div class="text-muted fs-7 mt-1">
            Message display style.
        </div>
    </div>

    {{-- Custom CSS Class --}}
    <div class="mb-5">
        <label class="form-label fw-semibold text-gray-700">Custom CSS Class</label>
        <input type="text" class="form-control form-control-solid" id="custom_css_class_{{ $sessionId }}" placeholder="e.g., custom-chat-theme">
        <div class="text-muted fs-7 mt-1">
            Add custom CSS class to the chat container.
        </div>
    </div>

    {{-- Toolbar Buttons --}}
    <h5 class="mt-6 mb-4">Toolbar Buttons</h5>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="btn_new_chat_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="btn_new_chat_{{ $sessionId }}">
                    New Chat Button
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="btn_clear_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="btn_clear_{{ $sessionId }}">
                    Clear Chat Button
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="btn_download_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="btn_download_{{ $sessionId }}">
                    Download History Button
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="btn_monitor_toggle_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="btn_monitor_toggle_{{ $sessionId }}">
                    Monitor Toggle Button
                </label>
            </div>
        </div>
    </div>
</div>
