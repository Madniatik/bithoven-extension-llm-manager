{{--
    PERFORMANCE SETTINGS SECTION
    
    Configuración de lazy loading, cache y optimizaciones
--}}

@php
    $sessionId = $sessionId ?? 'default';
@endphp

<div class="mb-8">
    <h3 class="mb-5">
        {!! getIcon('ki-rocket', 'fs-2 me-2', '', 'i') !!}
        Performance Settings
    </h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="lazy_load_tabs_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="lazy_load_tabs_{{ $sessionId }}">
                    Lazy Load Monitor Tabs
                </label>
                <div class="text-muted fs-7 mt-1">
                    Load tab content only when activated.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="minify_assets_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="minify_assets_{{ $sessionId }}">
                    Minify Assets
                </label>
                <div class="text-muted fs-7 mt-1">
                    Use minified CSS/JS for faster loading.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="cache_preferences_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="cache_preferences_{{ $sessionId }}">
                    Cache Preferences
                </label>
                <div class="text-muted fs-7 mt-1">
                    Cache settings in browser for faster access.
                </div>
            </div>
        </div>
    </div>

    {{-- Clear LocalStorage --}}
    <div class="separator my-6"></div>
    
    <div class="mb-5">
        <h5 class="mb-4">Storage Management</h5>
        <button type="button" class="btn btn-sm btn-light-warning" onclick="clearChatLocalStorage()">
            {!! getIcon('ki-trash', 'fs-4 me-2') !!}
            Clear Browser Cache
        </button>
        <div class="text-muted fs-7 mt-2">
            Clear all cached preferences and conversation history from browser.
        </div>
    </div>
</div>

@push('scripts')
<script>
function clearChatLocalStorage() {
    Swal.fire({
        title: 'Clear Browser Cache?',
        text: 'This will remove all cached preferences and conversation history. Server data will NOT be affected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, clear it!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Clear chat-related localStorage
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('chat_') || key.startsWith('llm_')) {
                    localStorage.removeItem(key);
                }
            });

            Swal.fire({
                title: 'Cleared!',
                text: 'Browser cache has been cleared. Please refresh the page.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        }
    });
}
</script>
@endpush
