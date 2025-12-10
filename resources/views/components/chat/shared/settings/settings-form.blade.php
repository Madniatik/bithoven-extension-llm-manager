{{--
    CHAT SETTINGS FORM - Modular Refactored Version
    
    Formulario de configuración del chat workspace.
    Este componente es reutilizable en:
    - Tab "Chat Settings" (split-horizontal-layout)
    - Sidebar layout (futuro)
    - Modal settings (futuro)
    
    Props:
    - $sessionId (int|null) - Session ID
    - $config (array) - Current configuration (futuro, por ahora null)
    
    @version 1.1.0 - Refactored into modular sections
--}}

@php
    // Manejar sessionId de forma segura (puede venir de variable, sesión, o ser default)
    $sessionId = $sessionId ?? ($session->id ?? 'default');
    $settingsId = 'settings-form-' . $sessionId;
@endphp

<div id="{{ $settingsId }}" x-data="workspaceSettingsForm" style="max-width: 100%; overflow-x: hidden;">
    
    {{-- SECTION 1: Monitor Settings --}}
    @include('llm-manager::components.chat.shared.settings.sections.monitor-settings', ['sessionId' => $sessionId])
    
    <div class="separator separator-dashed my-6"></div>
    
    {{-- SECTION 2: UI Preferences --}}
    @include('llm-manager::components.chat.shared.settings.sections.ui-preferences', ['sessionId' => $sessionId])
    
    <div class="separator separator-dashed my-6"></div>
    
    {{-- SECTION 3: UX Enhancements --}}
    @include('llm-manager::components.chat.shared.settings.sections.ux-enhancements', ['sessionId' => $sessionId])
    
    <div class="separator separator-dashed my-6"></div>
    
    {{-- SECTION 4: Performance Settings --}}
    @include('llm-manager::components.chat.shared.settings.sections.performance-settings', ['sessionId' => $sessionId])
    
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('workspaceSettingsForm', () => ({
        monitorEnabled: true,
        sessionId: '{{ $sessionId }}',
        
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
            
            // Listener para cambio de shortcuts_mode (actualizar KeyboardShortcuts inmediatamente)
            const shortcutsModeSelect = this.getElement('shortcuts_mode');
            if (shortcutsModeSelect && typeof KeyboardShortcuts !== 'undefined') {
                shortcutsModeSelect.addEventListener('change', (e) => {
                    const newMode = e.target.value;
                    KeyboardShortcuts.saveMode(newMode, this.sessionId);
                    
                    // Show feedback
                    const tooltip = KeyboardShortcuts.getInputTooltip();
                    toastr.info(`Keyboard shortcuts updated: ${tooltip}`);
                    
                    console.log('[Settings] Keyboard mode changed to:', newMode);
                });
            }
        },
        
        // Helper para obtener elemento por ID con sessionId
        getElement(baseId) {
            return document.getElementById(`${baseId}_${this.sessionId}`);
        },
        
        // Cargar preferencias guardadas del usuario
        async loadUserPreferences() {
            try {
                const response = await fetch('{{ route("admin.llm.workspace.preferences.get") }}');
                const data = await response.json();
                
                if (data.success && data.config) {
                    const config = data.config;
                    
                    // Monitor settings
                    this.monitorEnabled = config.features?.monitor?.enabled ?? true;
                    this.getElement('monitor_enabled').checked = this.monitorEnabled;
                    this.getElement('tab_console').checked = config.features?.monitor?.tabs?.console ?? true;
                    this.getElement('tab_request_inspector').checked = config.features?.monitor?.tabs?.request_inspector ?? true;
                    this.getElement('tab_activity_log').checked = config.features?.monitor?.tabs?.activity_log ?? true;
                    
                    // UI settings
                    this.getElement('chat_layout').value = config.ui?.layout?.chat ?? 'bubble';
                    this.getElement('custom_css_class').value = config.advanced?.custom_css_class ?? '';
                    
                    // Toolbar buttons
                    this.getElement('btn_new_chat').checked = config.ui?.buttons?.new_chat ?? true;
                    this.getElement('btn_clear').checked = config.ui?.buttons?.clear ?? true;
                    this.getElement('btn_download').checked = config.ui?.buttons?.download ?? true;
                    this.getElement('btn_monitor_toggle').checked = config.ui?.buttons?.monitor_toggle ?? true;
                    
                    // UX - Animations
                    this.getElement('fancy_enabled').checked = config.ux?.animations?.fancy_enabled ?? true;
                    this.getElement('checkmark_bounce').checked = config.ux?.animations?.checkmark_bounce ?? true;
                    this.getElement('scroll_button_fade').checked = config.ux?.animations?.scroll_button_fade ?? true;
                    this.getElement('hover_effects').checked = config.ux?.animations?.hover_effects ?? true;
                    
                    // UX - Context Indicator
                    this.getElement('context_indicator_enabled').checked = config.ux?.context_indicator?.enabled ?? true;
                    
                    // UX - Streaming Indicator
                    this.getElement('streaming_indicator_enabled').checked = config.ux?.streaming_indicator?.enabled ?? true;
                    
                    // UX - System Notifications
                    this.getElement('system_notification_enabled').checked = config.ux?.system_notification?.enabled ?? true;
                    
                    // UX - Sound Notifications
                    this.getElement('sound_enabled').checked = config.ux?.notifications?.sound_enabled ?? true;
                    this.getElement('sound_file').value = config.ux?.notifications?.sound_file ?? 'notification.mp3';
                    this.getElement('vibrate_enabled').checked = config.ux?.notifications?.vibrate_enabled ?? false;
                    
                    // UX - Keyboard
                    this.getElement('shortcuts_mode').value = config.ux?.keyboard?.shortcuts_mode ?? 'A';
                    
                    // Performance settings
                    this.getElement('lazy_load_tabs').checked = config.performance?.lazy_load_tabs ?? true;
                    this.getElement('cache_preferences').checked = config.performance?.cache_preferences ?? true;
                }
            } catch (error) {
                console.error('Error loading user preferences:', error);
            }
        },
        
        // Función para capturar todos los valores del formulario
        collectFormValues() {
            const monitorEnabled = this.getElement('monitor_enabled').checked;
            
            return {
                features: {
                    monitor: {
                        enabled: monitorEnabled,
                        tabs: {
                            // Si monitor deshabilitado, forzar todas las tabs a false
                            console: monitorEnabled ? this.getElement('tab_console').checked : false,
                            request_inspector: monitorEnabled ? this.getElement('tab_request_inspector').checked : false,
                            activity_log: monitorEnabled ? this.getElement('tab_activity_log').checked : false,
                        },
                    },
                    settings_panel: true,
                    persistence: true,
                    toolbar: true,
                },
                ui: {
                    layout: {
                        chat: this.getElement('chat_layout').value,
                        monitor: 'split-horizontal',
                    },
                    buttons: {
                        new_chat: this.getElement('btn_new_chat').checked,
                        clear: this.getElement('btn_clear').checked,
                        settings: true,
                        download: this.getElement('btn_download').checked,
                        // Si monitor deshabilitado, forzar monitor_toggle a false
                        monitor_toggle: monitorEnabled ? this.getElement('btn_monitor_toggle').checked : false,
                    },
                    mode: 'full',
                },
                ux: {
                    animations: {
                        fancy_enabled: this.getElement('fancy_enabled').checked,
                        checkmark_bounce: this.getElement('checkmark_bounce').checked,
                        scroll_button_fade: this.getElement('scroll_button_fade').checked,
                        hover_effects: this.getElement('hover_effects').checked,
                    },
                    context_indicator: {
                        enabled: this.getElement('context_indicator_enabled').checked,
                    },
                    streaming_indicator: {
                        enabled: this.getElement('streaming_indicator_enabled').checked,
                    },
                    system_notification: {
                        enabled: this.getElement('system_notification_enabled').checked,
                    },
                    notifications: {
                        sound_enabled: this.getElement('sound_enabled').checked,
                        sound_file: this.getElement('sound_file').value,
                        vibrate_enabled: this.getElement('vibrate_enabled').checked,
                    },
                    keyboard: {
                        shortcuts_mode: this.getElement('shortcuts_mode').value,
                    },
                },
                performance: {
                    lazy_load_tabs: this.getElement('lazy_load_tabs').checked,
                    minify_assets: false,
                    cache_preferences: this.getElement('cache_preferences').checked,
                },
                advanced: {
                    multi_instance: false,
                    custom_css_class: this.getElement('custom_css_class').value,
                    debug_mode: false,
                },
            };
        },
        
        // Guardar settings
        async saveSettings() {
            const config = this.collectFormValues();
            
            try {
                const response = await fetch('{{ route("admin.llm.workspace.preferences.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
                const response = await fetch('{{ route("admin.llm.workspace.preferences.reset") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
    }));
});
</script>
