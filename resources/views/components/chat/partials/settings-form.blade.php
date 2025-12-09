{{--
    CHAT SETTINGS FORM - Reutilizable
    
    Formulario de configuración del chat workspace.
    Este componente es reutilizable en:
    - Tab "Chat Settings" (split-horizontal-layout)
    - Sidebar layout (futuro)
    - Modal settings (futuro)
    
    Props:
    - $sessionId (int|null) - Session ID
    - $config (array) - Current configuration (futuro, por ahora null)
--}}

@php
    // Manejar sessionId de forma segura (puede venir de variable, sesión, o ser default)
    $sessionId = $sessionId ?? ($session->id ?? 'default');
@endphp

<div x-data="{
    monitorEnabled: true,
    
    init() {
        // Cargar configuración guardada del usuario
        this.loadUserPreferences();
        
        // Escuchar evento de guardado desde footer
        window.addEventListener('workspace-save-settings', () => {
            this.saveSettings();
        });
        
        // Escuchar evento de reset desde footer
        window.addEventListener('workspace-reset-settings', () => {
            this.resetToDefaults();
        });
    },
    
    // Cargar preferencias guardadas del usuario
    async loadUserPreferences() {
        try {
            const response = await fetch('{{ route('admin.llm.workspace.preferences.get') }}');
            const data = await response.json();
            
            if (data.success && data.config) {
                const config = data.config;
                
                // Monitor settings
                this.monitorEnabled = config.features?.monitor?.enabled ?? true;
                document.getElementById('monitor_enabled').checked = this.monitorEnabled;
                document.getElementById('tab_console').checked = config.features?.monitor?.tabs?.console ?? true;
                document.getElementById('tab_request_inspector').checked = config.features?.monitor?.tabs?.request_inspector ?? true;
                document.getElementById('tab_activity_log').checked = config.features?.monitor?.tabs?.activity_log ?? true;
                
                // UI settings
                document.getElementById('chat_layout').value = config.ui?.layout?.chat ?? 'bubble';
                document.getElementById('custom_css_class').value = config.advanced?.custom_css_class ?? '';
                
                // Toolbar buttons
                document.getElementById('btn_new_chat').checked = config.ui?.buttons?.new_chat ?? true;
                document.getElementById('btn_clear').checked = config.ui?.buttons?.clear ?? true;
                document.getElementById('btn_download').checked = config.ui?.buttons?.download ?? true;
                document.getElementById('btn_monitor_toggle').checked = config.ui?.buttons?.monitor_toggle ?? true;
                
                // Performance settings
                document.getElementById('lazy_load_tabs').checked = config.performance?.lazy_load_tabs ?? true;
                document.getElementById('cache_preferences').checked = config.performance?.cache_preferences ?? true;
            }
        } catch (error) {
            console.error('Error loading user preferences:', error);
        }
    },
    
    // Función para capturar todos los valores del formulario
    collectFormValues() {
        return {
            features: {
                monitor: {
                    enabled: document.getElementById('monitor_enabled').checked,
                    tabs: {
                        console: document.getElementById('tab_console').checked,
                        request_inspector: document.getElementById('tab_request_inspector').checked,
                        activity_log: document.getElementById('tab_activity_log').checked,
                    },
                },
                settings_panel: true,
                persistence: true,
                toolbar: true,
            },
            ui: {
                layout: {
                    chat: document.getElementById('chat_layout').value,
                    monitor: 'split-horizontal',
                },
                buttons: {
                    new_chat: document.getElementById('btn_new_chat').checked,
                    clear: document.getElementById('btn_clear').checked,
                    settings: true,
                    download: document.getElementById('btn_download').checked,
                    monitor_toggle: document.getElementById('btn_monitor_toggle').checked,
                },
                mode: 'full',
            },
            performance: {
                lazy_load_tabs: document.getElementById('lazy_load_tabs').checked,
                minify_assets: false,
                cache_preferences: document.getElementById('cache_preferences').checked,
            },
            advanced: {
                multi_instance: false,
                custom_css_class: document.getElementById('custom_css_class').value,
                debug_mode: false,
            },
        };
    },
    
    // Guardar settings
    async saveSettings() {
        const config = this.collectFormValues();
        
        try {
            const response = await fetch('{{ route('admin.llm.workspace.preferences.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                },
                body: JSON.stringify({ config }),
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Saved!',
                    text: data.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Si se necesita reload, preguntar al usuario
                if (data.needs_reload) {
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Reload Required',
                            text: 'Some settings require a page reload to take effect. Reload now?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Reload Now',
                            cancelButtonText: 'Later',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-secondary'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    }, 1500);
                }
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Failed to save settings',
                    icon: 'error'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'Network error: ' + error.message,
                icon: 'error'
            });
        }
    },
    
    // Reset to defaults
    async resetToDefaults() {
        const result = await Swal.fire({
            title: 'Reset to Defaults?',
            text: 'This will restore all settings to their default values.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reset',
            cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        });
        
        if (!result.isConfirmed) return;
        
        try {
            const response = await fetch('{{ route('admin.llm.workspace.preferences.reset') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    title: 'Reset Complete',
                    text: data.message,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Siempre reload después de reset
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Failed to reset settings',
                    icon: 'error'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'Network error: ' + error.message,
                icon: 'error'
            });
        }
    }
}" style="max-width: 100%; overflow-x: hidden-;">
    {{-- SECTION: Monitor Settings --}}
    <div class="mb-8">
        <h3 class="mb-5">
            {!! getIcon('ki-satellite', 'fs-2 me-2', '', 'i') !!}
            Monitor Settings
        </h3>
        
        {{-- Enable Monitor --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="monitor_enabled" x-model="monitorEnabled" checked>
            <label class="form-check-label fw-semibold text-gray-700" for="monitor_enabled">
                Enable Monitor Panel
            </label>
            <div class="text-muted fs-7 mt-1">
                Show/hide the monitor panel with real-time metrics and logs.
            </div>
        </div>

        {{-- Monitor Tabs --}}
        <h5 class="mt-6 mb-4">Monitor Tabs</h5>
        
        {{-- Console Tab --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="tab_console" checked :disabled="!monitorEnabled">
            <label class="form-check-label fw-semibold text-gray-700" for="tab_console">
                Console Tab
            </label>
            <div class="text-muted fs-7 mt-1">
                Real-time streaming logs and debug information.
            </div>
        </div>

        {{-- Request Inspector Tab --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="tab_request_inspector" checked :disabled="!monitorEnabled">
            <label class="form-check-label fw-semibold text-gray-700" for="tab_request_inspector">
                Request Inspector Tab
            </label>
            <div class="text-muted fs-7 mt-1">
                Inspect LLM request payloads (metadata, context, parameters).
            </div>
        </div>

        {{-- Activity Log Tab --}}
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="tab_activity_log" checked :disabled="!monitorEnabled">
            <label class="form-check-label fw-semibold text-gray-700" for="tab_activity_log">
                Activity Log Tab
            </label>
            <div class="text-muted fs-7 mt-1">
                Historical activity logs with filtering options.
            </div>
        </div>
    </div>

    <div class="separator separator-dashed my-6"></div>

    {{-- SECTION: UI Preferences --}}
    <div class="mb-8">
        <h3 class="mb-5">
            {!! getIcon('ki-element-11', 'fs-2 me-2', '', 'i') !!}
            UI Preferences
        </h3>
        
        {{-- Chat Layout --}}
        <div class="mb-5">
            <label class="form-label fw-semibold text-gray-700">Chat Layout</label>
            <select class="form-select form-select-solid" id="chat_layout">
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
            <input type="text" class="form-control form-control-solid" id="custom_css_class" placeholder="e.g., custom-chat-theme">
            <div class="text-muted fs-7 mt-1">
                Add custom CSS class to the chat container.
            </div>
        </div>

        {{-- Toolbar Buttons --}}
        <h5 class="mt-6 mb-4">Toolbar Buttons</h5>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_new_chat" checked>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_new_chat">
                        New Chat Button
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_clear" checked>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_clear">
                        Clear Chat Button
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_download" checked>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_download">
                        Download History Button
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" id="btn_monitor_toggle" checked>
                    <label class="form-check-label fw-semibold text-gray-700" for="btn_monitor_toggle">
                        Monitor Toggle Button
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="separator separator-dashed my-6"></div>

    {{-- SECTION: Performance --}}
    <div class="pb-8 mb-n8">
        <h3 class="mb-5">
            {!! getIcon('ki-rocket', 'fs-2 me-2', '', 'i') !!}
            Performance
        </h3>
        
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="lazy_load_tabs" checked>
            <label class="form-check-label fw-semibold text-gray-700" for="lazy_load_tabs">
                Lazy Load Tabs
            </label>
            <div class="text-muted fs-7 mt-1">
                Load tab content only when activated (reduces initial load time).
            </div>
        </div>

        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="cache_preferences" checked>
            <label class="form-check-label fw-semibold text-gray-700" for="cache_preferences">
                Cache Preferences
            </label>
            <div class="text-muted fs-7 mt-1">
                Save settings to localStorage for faster loading.
            </div>
        </div>
        
        {{-- Clear localStorage Button --}}
        <div class="mt-6">
            <button type="button" class="btn btn-sm btn-light-danger" 
                    onclick="Swal.fire({
                        title: '¿Limpiar LocalStorage?',
                        text: 'Se eliminarán todos los datos guardados del chat. Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-secondary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Object.keys(localStorage).filter(k => k.startsWith('llm_chat_')).forEach(k => localStorage.removeItem(k));
                            Swal.fire({
                                title: 'Limpiado',
                                text: 'LocalStorage limpiado correctamente',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        }
                    })">
                {!! getIcon('ki-trash', 'fs-2 me-1', '', 'i') !!}
                Clear Chat LocalStorage
            </button>
            <div class="text-muted fs-7 mt-2">
                Remove all saved chat preferences, monitor states, and cached data.
            </div>
        </div>
    </div>
</div>
