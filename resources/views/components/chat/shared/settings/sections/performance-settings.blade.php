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
    
    {{-- System Information (for debugging and support) --}}
    <div class="separator my-6"></div>
    
    <div class="mb-5">
        <h5 class="mb-4">System Information</h5>
        <div class="alert alert-secondary d-flex align-items-center p-4">
            <div class="d-flex flex-column w-100" id="system_info_{{ $sessionId }}">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-gray-700 fw-semibold">Operating System:</span>
                    <span class="text-gray-800" id="sys_os_{{ $sessionId }}">Loading...</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-gray-700 fw-semibold">Browser:</span>
                    <span class="text-gray-800" id="sys_browser_{{ $sessionId }}">Loading...</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-gray-700 fw-semibold">Keyboard Modifier:</span>
                    <span class="text-gray-800" id="sys_modifier_{{ $sessionId }}">Loading...</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-gray-700 fw-semibold">Viewport:</span>
                    <span class="text-gray-800" id="sys_viewport_{{ $sessionId }}">Loading...</span>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-light" onclick="showFullSystemInfo()">
            {!! getIcon('ki-information', 'fs-4 me-2') !!}
            Show Full Details
        </button>
        <div class="text-muted fs-7 mt-2">
            System information for debugging and technical support.
        </div>
    </div>
</div>

<script>
// Populate system info when PlatformUtils is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof PlatformUtils !== 'undefined') {
        const sessionId = '{{ $sessionId }}';
        const info = PlatformUtils.getSystemInfo();
        
        // OS
        const osLabel = info.os === 'mac' ? 'macOS' : 
                        info.os === 'windows' ? 'Windows' : 
                        info.os === 'linux' ? 'Linux' :
                        info.os.charAt(0).toUpperCase() + info.os.slice(1);
        document.getElementById(`sys_os_${sessionId}`).textContent = osLabel;
        
        // Browser
        const browserLabel = info.browser.charAt(0).toUpperCase() + info.browser.slice(1);
        document.getElementById(`sys_browser_${sessionId}`).textContent = 
            `${browserLabel} ${info.browserVersion}`;
        
        // Modifier Key
        document.getElementById(`sys_modifier_${sessionId}`).textContent = 
            PlatformUtils.getModifierKey() + ' (' + PlatformUtils.getModifierSymbol() + ')';
        
        // Viewport
        document.getElementById(`sys_viewport_${sessionId}`).textContent = 
            `${info.viewportWidth} × ${info.viewportHeight} px`;
    }
});

function showFullSystemInfo() {
    if (typeof PlatformUtils === 'undefined') {
        toastr.error('PlatformUtils not loaded');
        return;
    }
    
    const info = PlatformUtils.getSystemInfo();
    
    const htmlContent = `
        <div class="text-start">
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr><th class="w-40">Operating System</th><td>${info.os}</td></tr>
                    <tr><th>Browser</th><td>${info.browser} ${info.browserVersion}</td></tr>
                    <tr><th>User Agent</th><td class="small">${info.userAgent}</td></tr>
                    <tr><th>Platform</th><td>${info.platform}</td></tr>
                    <tr><th>Language</th><td>${info.language}</td></tr>
                    <tr><th>Screen Resolution</th><td>${info.screenWidth} × ${info.screenHeight} px</td></tr>
                    <tr><th>Viewport Size</th><td>${info.viewportWidth} × ${info.viewportHeight} px</td></tr>
                    <tr><th>Color Depth</th><td>${info.colorDepth} bits</td></tr>
                    <tr><th>Pixel Ratio</th><td>${info.pixelRatio}</td></tr>
                    <tr><th>Touch Support</th><td>${info.touchSupport ? 'Yes' : 'No'}</td></tr>
                    <tr><th>Modifier Key</th><td>${PlatformUtils.getModifierKey()} (${PlatformUtils.getModifierSymbol()})</td></tr>
                </tbody>
            </table>
        </div>
    `;
    
    Swal.fire({
        title: 'System Information',
        html: htmlContent,
        width: '600px',
        confirmButtonText: 'Close',
        customClass: {
            confirmButton: 'btn btn-primary'
        }
    });
}
</script>

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
