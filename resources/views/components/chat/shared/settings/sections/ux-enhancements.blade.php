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
        if (typeof PlatformUtils !== 'undefined') {
            const modifier = PlatformUtils.getModifierKey(); // 'Cmd' o 'Ctrl'
            const sessionId = '{{ $sessionId }}';
            
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

