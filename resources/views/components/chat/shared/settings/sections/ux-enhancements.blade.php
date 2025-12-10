{{--
    UX ENHANCEMENTS SECTION
    
    Configuración de animaciones, sonidos y atajos de teclado
    Nueva sección para PLAN-v1.0.7-chat-ux.md
--}}

@php
    $sessionId = $sessionId ?? 'default';
@endphp

<div class="mb-8">
    <h3 class="mb-5">
        {!! getIcon('ki-magic-star', 'fs-2 me-2', '', 'i') !!}
        UX Enhancements
    </h3>
    
    {{-- Fancy Animations --}}
    <h5 class="mt-6 mb-4">Fancy Animations</h5>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="fancy_enabled_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="fancy_enabled_{{ $sessionId }}">
                    Enable Fancy Animations
                </label>
                <div class="text-muted fs-7 mt-1">
                    Master toggle for all fancy animations.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="checkmark_bounce_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="checkmark_bounce_{{ $sessionId }}">
                    Checkmark Bounce Effect
                </label>
                <div class="text-muted fs-7 mt-1">
                    Bounce animation for "Saved" checkmark.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="scroll_button_fade_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="scroll_button_fade_{{ $sessionId }}">
                    Scroll Button Fade
                </label>
                <div class="text-muted fs-7 mt-1">
                    Fade in/out for scroll-to-bottom button.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" id="hover_effects_{{ $sessionId }}" checked>
                <label class="form-check-label fw-semibold text-gray-700" for="hover_effects_{{ $sessionId }}">
                    Hover Effects
                </label>
                <div class="text-muted fs-7 mt-1">
                    Highlight and scale effects on hover.
                </div>
            </div>
        </div>
    </div>

    {{-- System Notifications --}}
    <h5 class="mt-6 mb-4">System Notifications</h5>
    
    <div class="mb-5">
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="system_notification_enabled_{{ $sessionId }}" checked>
            <label class="form-check-label fw-semibold text-gray-700" for="system_notification_enabled_{{ $sessionId }}">
                Enable System Notifications
            </label>
            <div class="text-muted fs-7 mt-1">
                Show native OS notification when AI response is ready (requires browser permission).
            </div>
        </div>
    </div>

    <div class="mb-5" id="notification_permission_status_{{ $sessionId }}">
        {{-- Dynamic permission status will be inserted here --}}
    </div>

    <div class="d-flex gap-2 mb-5">
        <button type="button" class="btn btn-sm btn-light-primary" id="request_notification_permission_{{ $sessionId }}">
            {!! getIcon('ki-notification', 'fs-3 me-1', '', 'i') !!}
            Request Notification Permission
        </button>
        
        <button type="button" class="btn btn-sm btn-light-success" id="test_notification_{{ $sessionId }}">
            {!! getIcon('ki-message-text-2', 'fs-3 me-1', '', 'i') !!}
            Test Notification
        </button>
    </div>

    {{-- Sound Notifications --}}
    <h5 class="mt-6 mb-4">Sound Notifications</h5>
    
    <div class="mb-5">
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="sound_enabled_{{ $sessionId }}" checked>
            <label class="form-check-label fw-semibold text-gray-700" for="sound_enabled_{{ $sessionId }}">
                Enable Sound Notifications
            </label>
            <div class="text-muted fs-7 mt-1">
                Play sound when receiving assistant response.
            </div>
        </div>
    </div>

    <div class="mb-5">
        <label class="form-label fw-semibold text-gray-700">Sound File</label>
        <select class="form-select form-select-solid" id="sound_file_{{ $sessionId }}">
            <option value="notification.mp3" selected>Notification (Default)</option>
            <option value="ping.mp3">Ping</option>
            <option value="chime.mp3">Chime</option>
            <option value="beep.mp3">Beep</option>
            <option value="swoosh.mp3">Swoosh</option>
        </select>
        <div class="text-muted fs-7 mt-1">
            Sound to play on message received.
        </div>
    </div>

    <div class="mb-5">
        <div class="form-check form-check-custom form-check-solid mb-4">
            <input class="form-check-input" type="checkbox" id="vibrate_enabled_{{ $sessionId }}">
            <label class="form-check-label fw-semibold text-gray-700" for="vibrate_enabled_{{ $sessionId }}">
                Enable Vibration (Mobile)
            </label>
            <div class="text-muted fs-7 mt-1">
                Vibrate device on message received (mobile only).
            </div>
        </div>
    </div>

    {{-- Keyboard Shortcuts --}}
    <h5 class="mt-6 mb-4">Keyboard Shortcuts</h5>
    
    <div class="mb-5">
        <label class="form-label fw-semibold text-gray-700">Enter Key Behavior</label>
        <select class="form-select form-select-solid" id="shortcuts_mode_{{ $sessionId }}">
            <option value="A" selected>Mode A: Enter sends, Shift+Enter new line</option>
            <option value="B" id="mode_b_option_{{ $sessionId }}">Mode B: Enter new line, MOD+Enter sends</option>
        </select>
        <div class="text-muted fs-7 mt-1" id="shortcuts_help_{{ $sessionId }}">
            Choose Enter key behavior for message input.
        </div>
    </div>
    
    {{-- Update Mode B description with correct modifier key --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionId = '{{ $sessionId }}';
        
        // ===== SYSTEM NOTIFICATIONS PERMISSION HANDLER =====
        const updateNotificationPermissionStatus = () => {
            const statusDiv = document.getElementById(`notification_permission_status_${sessionId}`);
            if (!statusDiv) return;
            
            // Check if Notifications API is supported
            if (!('Notification' in window)) {
                statusDiv.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center">
                        {!! getIcon('ki-information', 'fs-2 me-3', '', 'i') !!}
                        <div>
                            <strong>Not Supported</strong><br>
                            <span class="text-muted fs-7">Your browser doesn't support system notifications.</span>
                        </div>
                    </div>
                `;
                // Disable button and checkbox
                document.getElementById(`request_notification_permission_${sessionId}`).disabled = true;
                document.getElementById(`system_notification_enabled_${sessionId}`).disabled = true;
                return;
            }
            
            const permission = Notification.permission;
            const statusHTML = {
                'granted': `
                    <div class="alert alert-success d-flex align-items-center">
                        {!! getIcon('ki-check-circle', 'fs-2 me-3', '', 'i') !!}
                        <div>
                            <strong>Notifications Enabled</strong><br>
                            <span class="text-muted fs-7">You will receive OS notifications when responses are ready.</span>
                        </div>
                    </div>
                `,
                'denied': `
                    <div class="alert alert-danger d-flex align-items-center">
                        {!! getIcon('ki-cross-circle', 'fs-2 me-3', '', 'i') !!}
                        <div>
                            <strong>Notifications Blocked</strong><br>
                            <span class="text-muted fs-7">You have blocked notifications. Please enable them in your browser settings.</span>
                        </div>
                    </div>
                `,
                'default': `
                    <div class="alert alert-warning d-flex align-items-center">
                        {!! getIcon('ki-information', 'fs-2 me-3', '', 'i') !!}
                        <div>
                            <strong>Permission Required</strong><br>
                            <span class="text-muted fs-7">Click the button below to enable system notifications.</span>
                        </div>
                    </div>
                `
            };
            
            statusDiv.innerHTML = statusHTML[permission];
            
            // Hide request button if already granted or denied
            const requestBtn = document.getElementById(`request_notification_permission_${sessionId}`);
            if (requestBtn) {
                requestBtn.style.display = (permission === 'default') ? 'inline-flex' : 'none';
            }
        };
        
        // Request notification permission
        const requestBtn = document.getElementById(`request_notification_permission_${sessionId}`);
        if (requestBtn) {
            requestBtn.addEventListener('click', async () => {
                if (!('Notification' in window)) {
                    toastr.error('Your browser doesn\'t support notifications');
                    return;
                }
                
                try {
                    const permission = await Notification.requestPermission();
                    updateNotificationPermissionStatus();
                    
                    if (permission === 'granted') {
                        toastr.success('System notifications enabled successfully!');
                        
                        // Show test notification
                        new Notification('LLM Manager', {
                            body: 'Notifications are now enabled! You will be notified when AI responses are ready.',
                            icon: '/vendor/llm-manager/images/logo.png',
                            tag: 'test-notification'
                        });
                    } else if (permission === 'denied') {
                        toastr.warning('Notifications permission denied. You can enable them later in browser settings.');
                    }
                } catch (error) {
                    console.error('[Notifications] Request permission error:', error);
                    toastr.error('Failed to request notification permission');
                }
            });
        }
        
        // Initialize permission status on load
        updateNotificationPermissionStatus();
        
        // ===== TEST NOTIFICATION BUTTON =====
        const testBtn = document.getElementById(`test_notification_${sessionId}`);
        if (testBtn) {
            testBtn.addEventListener('click', () => {
                if (!('Notification' in window)) {
                    toastr.error('Your browser doesn\'t support notifications');
                    return;
                }
                
                if (Notification.permission !== 'granted') {
                    toastr.warning('Please grant notification permission first');
                    return;
                }
                
                // Get current settings
                const systemEnabled = localStorage.getItem(`llm_system_notification_enabled_${sessionId}`) !== 'false';
                const soundEnabled = localStorage.getItem(`llm_sound_enabled_${sessionId}`) !== 'false';
                const soundFile = localStorage.getItem(`llm_sound_file_${sessionId}`) || 'notification.mp3';
                const vibrateEnabled = localStorage.getItem(`llm_vibrate_enabled_${sessionId}`) === 'true';
                
                console.log('[Test Notification] Settings:', {
                    system: systemEnabled,
                    sound: soundEnabled,
                    soundFile,
                    vibrate: vibrateEnabled
                });
                
                // System notification
                if (systemEnabled) {
                    const notification = new Notification('LLM Manager - Test Notification', {
                        body: 'This is a test notification. Your settings are working correctly!',
                        icon: '/vendor/llm-manager/images/logo.png',
                        badge: '/vendor/llm-manager/images/badge.png',
                        tag: `test-notification-${Date.now()}`,
                        requireInteraction: false,
                        silent: !soundEnabled
                    });
                    
                    notification.onclick = () => {
                        window.focus();
                        notification.close();
                    };
                    
                    // Auto-close after 5 seconds
                    setTimeout(() => notification.close(), 5000);
                    
                    console.log('[Test Notification] System notification sent');
                }
                
                // Sound notification
                if (soundEnabled) {
                    try {
                        const audio = new Audio(`/vendor/llm-manager/sounds/${soundFile}`);
                        audio.volume = 0.5;
                        audio.play().catch(err => {
                            console.warn('[Test Notification] Sound play failed (file may not exist):', soundFile, err);
                        });
                        console.log('[Test Notification] Sound played:', soundFile);
                    } catch (error) {
                        console.error('[Test Notification] Sound error:', error);
                    }
                }
                
                // Vibration (mobile)
                if (vibrateEnabled && 'vibrate' in navigator) {
                    navigator.vibrate([200, 100, 200]);
                    console.log('[Test Notification] Vibration triggered');
                }
                
                toastr.success('Test notification sent!');
            });
        }
        
        // ===== SETTINGS PERSISTENCE =====
        // Lista de todos los settings que se deben guardar/cargar
        const settingsConfig = [
            // Fancy Animations
            { id: 'fancy_enabled', type: 'checkbox', defaultValue: true },
            { id: 'checkmark_bounce', type: 'checkbox', defaultValue: true },
            { id: 'scroll_button_fade', type: 'checkbox', defaultValue: true },
            { id: 'hover_effects', type: 'checkbox', defaultValue: true },
            
            // System Notifications
            { id: 'system_notification_enabled', type: 'checkbox', defaultValue: true },
            
            // Sound Notifications
            { id: 'sound_enabled', type: 'checkbox', defaultValue: true },
            { id: 'sound_file', type: 'select', defaultValue: 'notification.mp3' },
            { id: 'vibrate_enabled', type: 'checkbox', defaultValue: false },
        ];
        
        // Función para cargar settings desde localStorage
        const loadSettings = () => {
            console.log('[Settings] Loading UX settings from localStorage...');
            
            settingsConfig.forEach(setting => {
                const element = document.getElementById(`${setting.id}_${sessionId}`);
                if (!element) return;
                
                const storageKey = `llm_${setting.id}_${sessionId}`;
                const savedValue = localStorage.getItem(storageKey);
                
                if (setting.type === 'checkbox') {
                    // Parse boolean value
                    const value = savedValue !== null ? savedValue === 'true' : setting.defaultValue;
                    element.checked = value;
                    console.log(`[Settings] Loaded ${setting.id}: ${value}`);
                } else if (setting.type === 'select') {
                    // Set select value
                    const value = savedValue !== null ? savedValue : setting.defaultValue;
                    element.value = value;
                    console.log(`[Settings] Loaded ${setting.id}: ${value}`);
                }
            });
        };
        
        // Función para guardar un setting específico
        const saveSetting = (settingId, value) => {
            const storageKey = `llm_${settingId}_${sessionId}`;
            localStorage.setItem(storageKey, value);
            console.log(`[Settings] Saved ${settingId}: ${value}`);
        };
        
        // Agregar event listeners para auto-save
        settingsConfig.forEach(setting => {
            const element = document.getElementById(`${setting.id}_${sessionId}`);
            if (!element) return;
            
            if (setting.type === 'checkbox') {
                element.addEventListener('change', (e) => {
                    saveSetting(setting.id, e.target.checked);
                });
            } else if (setting.type === 'select') {
                element.addEventListener('change', (e) => {
                    saveSetting(setting.id, e.target.value);
                });
            }
        });
        
        // Cargar settings al inicializar
        loadSettings();
        
        // ===== KEYBOARD SHORTCUTS (EXISTING CODE) =====
        if (typeof PlatformUtils !== 'undefined') {
            const modifier = PlatformUtils.getModifierKey(); // 'Cmd' o 'Ctrl'
            
            // Update Mode B option text (replace MOD placeholder)
            const modeBOption = document.getElementById(`mode_b_option_${sessionId}`);
            if (modeBOption) {
                modeBOption.textContent = modeBOption.textContent.replace('MOD', modifier);
            }
            
            // Update help text to show current platform
            const helpText = document.getElementById(`shortcuts_help_${sessionId}`);
            if (helpText) {
                const os = PlatformUtils.currentOS;
                const osLabel = os === 'mac' ? 'macOS' : os === 'windows' ? 'Windows' : os.charAt(0).toUpperCase() + os.slice(1);
                helpText.textContent = `Choose Enter key behavior for message input. Detected: ${osLabel}`;
            }
        }
    });
    </script>
</div>