{{--
    STREAMING STATUS INDICATOR
    
    Indicador visual del estado del streaming (connecting, thinking, typing, completed)
    Ubicación: Sticky top del messages-container
    Configurable: Settings → UX Enhancements
--}}

@php
    $sessionId = $sessionId ?? 'default';
@endphp

<div class="streaming-status-indicator" 
     id="streaming_status_indicator_{{ $sessionId }}"
     style="display: none;">
    
    <div class="d-flex align-items-center gap-2">
        {{-- Spinner/Icon --}}
        <div class="status-icon" id="streaming_status_icon_{{ $sessionId }}">
            {{-- Dynamic icon will be inserted here --}}
        </div>
        
        {{-- Status text --}}
        <span class="status-text fw-semibold" id="streaming_status_text_{{ $sessionId }}">
            {{-- Dynamic text will be inserted here --}}
        </span>
    </div>
    
    {{-- Progress bar at bottom --}}
    <div class="streaming-progress-bar" id="streaming_progress_bar_{{ $sessionId }}">
        <div class="streaming-progress-fill" id="streaming_progress_fill_{{ $sessionId }}"></div>
    </div>
    
</div>

<style>
/* ===== STREAMING STATUS INDICATOR STYLES ===== */
.streaming-status-indicator {
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 0.5rem 1rem;
    border-bottom: 1px solid var(--bs-border-color);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    font-size: 0.8125rem;
    animation: slideDown 0.3s ease-out;
}

.streaming-status-indicator .status-icon {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.streaming-status-indicator .status-text {
    letter-spacing: 0.01em;
}

/* Progress bar at bottom */
.streaming-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background-color: rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.streaming-progress-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease;
    background: linear-gradient(90deg, 
        var(--bs-primary) 0%, 
        var(--bs-primary-active) 100%);
}

/* Progress bar colors per state */
.streaming-status-indicator.state-connecting .streaming-progress-fill {
    background: linear-gradient(90deg, 
        var(--bs-warning) 0%, 
        var(--bs-warning-active) 100%);
}

.streaming-status-indicator.state-thinking .streaming-progress-fill {
    background: linear-gradient(90deg, 
        var(--bs-info) 0%, 
        var(--bs-info-active) 100%);
}

.streaming-status-indicator.state-typing .streaming-progress-fill,
.streaming-status-indicator.state-completed .streaming-progress-fill {
    background: linear-gradient(90deg, 
        var(--bs-success) 0%, 
        var(--bs-success-active) 100%);
}

/* ===== ANIMATIONS ===== */

/* Slide down entrance */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Blinking dots (typing) */
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.status-icon .dot {
    width: 5px;
    height: 5px;
    background: currentColor;
    border-radius: 50%;
    display: inline-block;
    margin: 0 2px;
}

.status-icon .dot:nth-child(1) {
    animation: blink 1.4s infinite 0s;
}

.status-icon .dot:nth-child(2) {
    animation: blink 1.4s infinite 0.2s;
}

.status-icon .dot:nth-child(3) {
    animation: blink 1.4s infinite 0.4s;
}

/* Fade out (completed) */
@keyframes fadeOut {
    0% { 
        opacity: 1;
        transform: translateY(0);
    }
    100% { 
        opacity: 0;
        transform: translateY(-10px);
    }
}

.streaming-status-indicator.fading-out {
    animation: fadeOut 0.5s ease-out forwards;
}

/* ===== STATE COLORS ===== */

/* Connecting - Warning/Amber */
.streaming-status-indicator.state-connecting {
    background-color: var(--bs-warning-light) !important;
    border-bottom-color: var(--bs-warning);
}

.streaming-status-indicator.state-connecting .status-text {
    color: var(--bs-warning);
}

.streaming-status-indicator.state-connecting .status-icon {
    color: var(--bs-warning);
}

/* Thinking - Info/Blue */
.streaming-status-indicator.state-thinking {
    background-color: var(--bs-info-light) !important;
    border-bottom-color: var(--bs-info);
}

.streaming-status-indicator.state-thinking .status-text {
    color: var(--bs-info);
}

.streaming-status-indicator.state-thinking .status-icon {
    color: var(--bs-info);
}

/* Typing - Success/Green */
.streaming-status-indicator.state-typing {
    background-color: var(--bs-success-light) !important;
    border-bottom-color: var(--bs-success);
}

.streaming-status-indicator.state-typing .status-text {
    color: var(--bs-success);
}

.streaming-status-indicator.state-typing .status-icon {
    color: var(--bs-success);
}

/* Completed - Success bright */
.streaming-status-indicator.state-completed {
    background-color: var(--bs-success-light) !important;
    border-bottom-color: var(--bs-success);
}

.streaming-status-indicator.state-completed .status-text {
    color: var(--bs-success);
}

.streaming-status-indicator.state-completed .status-icon {
    color: var(--bs-success);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 991.98px) {
    .streaming-status-indicator {
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .streaming-status-indicator .status-icon {
        width: 14px;
        height: 14px;
    }
}
</style>

<script>
// ===== STREAMING STATUS INDICATOR MODULE =====
// Initialize global registry for multiple instances
if (!window.StreamingStatusIndicators) {
    window.StreamingStatusIndicators = {};
}

// Create instance for this session
window.StreamingStatusIndicators['{{ $sessionId }}'] = (() => {
    const sessionId = '{{ $sessionId }}';
    let currentState = null;
    let hideTimeout = null;
    let progressInterval = null;
    let progressValue = 0;
    
    // DOM elements
    const indicator = document.getElementById(`streaming_status_indicator_${sessionId}`);
    const icon = document.getElementById(`streaming_status_icon_${sessionId}`);
    const text = document.getElementById(`streaming_status_text_${sessionId}`);
    const progressBar = document.getElementById(`streaming_progress_bar_${sessionId}`);
    const progressFill = document.getElementById(`streaming_progress_fill_${sessionId}`);
    
    // State configuration
    const states = {
        connecting: {
            icon: '<div class="spinner-border spinner-border-sm" role="status"></div>',
            text: 'Connecting to AI...',
            iconClass: '',
            className: 'state-connecting'
        },
        thinking: {
            icon: '<div class="spinner-border spinner-border-sm" role="status"></div>',
            text: 'Thinking...',
            iconClass: '',
            className: 'state-thinking'
        },
        typing: {
            icon: '<span class="dot"></span><span class="dot"></span><span class="dot"></span>',
            text: 'Typing response...',
            iconClass: '',
            className: 'state-typing'
        },
        completed: {
            icon: '{!! getIcon("ki-check-circle", "fs-3", "", "i") !!}',
            text: 'Response completed',
            iconClass: '',
            className: 'state-completed'
        }
    };
    
    /**
     * Check if indicator is enabled in settings
     */
    const isEnabled = () => {
        const enabled = localStorage.getItem(`llm_streaming_indicator_enabled_${sessionId}`);
        return enabled !== 'false'; // Default: true
    };
    
    /**
     * Set indicator state
     */
    const setState = (state) => {
        if (!isEnabled() || !indicator || !states[state]) {
            return;
        }
        
        // Clear any pending hide timeout
        if (hideTimeout) {
            clearTimeout(hideTimeout);
            hideTimeout = null;
        }
        
        // Remove previous state classes
        Object.values(states).forEach(s => {
            indicator.classList.remove(s.className);
        });
        indicator.classList.remove('fading-out');
        
        // Set new state
        currentState = state;
        const stateConfig = states[state];
        
        // Update icon
        icon.innerHTML = stateConfig.icon;
        icon.className = `status-icon ${stateConfig.iconClass}`;
        
        // Update text
        text.textContent = stateConfig.text;
        
        // Add state class
        indicator.classList.add(stateConfig.className);
        
        // Show indicator
        indicator.style.display = 'block';
        
        // Start progress animation
        startProgress();
        
        console.log(`[StreamingIndicator] State: ${state}`);
        
        // Auto-hide completed state after 1.5 seconds
        if (state === 'completed') {
            completeProgress(); // Jump to 100%
            hideTimeout = setTimeout(() => {
                hide();
            }, 1500);
        }
    };
    
    /**
     * Start progress animation
     */
    const startProgress = () => {
        // Clear any existing interval
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        
        // Reset progress
        progressValue = 0;
        if (progressFill) {
            progressFill.style.width = '0%';
        }
        
        // Animate progress from 0% to 90% over time
        // Slow progress that never reaches 100% (indicates ongoing process)
        progressInterval = setInterval(() => {
            if (progressValue < 90) {
                // Exponential slowdown as it approaches 90%
                const increment = (90 - progressValue) * 0.02;
                progressValue += increment;
                
                if (progressFill) {
                    progressFill.style.width = `${progressValue}%`;
                }
            }
        }, 100); // Update every 100ms
    };
    
    /**
     * Complete progress (jump to 100%)
     */
    const completeProgress = () => {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        
        if (progressFill) {
            progressValue = 100;
            progressFill.style.width = '100%';
        }
    };
    
    /**
     * Hide indicator with fade out
     */
    const hide = () => {
        if (!indicator) return;
        
        // Stop progress
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        
        indicator.classList.add('fading-out');
        
        setTimeout(() => {
            indicator.style.display = 'none';
            indicator.classList.remove('fading-out');
            currentState = null;
            progressValue = 0;
            if (progressFill) {
                progressFill.style.width = '0%';
            }
        }, 500);
        
        console.log('[StreamingIndicator] Hidden');
    };
    
    /**
     * Show indicator (if was hidden)
     */
    const show = () => {
        if (!isEnabled() || !indicator) return;
        
        indicator.style.display = 'block';
        console.log('[StreamingIndicator] Shown');
    };
    
    /**
     * Get current state
     */
    const getState = () => currentState;
    
    // Public API
    return {
        setState,
        hide,
        show,
        getState,
        isEnabled,
        completeProgress
    };
})();

// Backward compatibility: Expose first/default instance as StreamingStatusIndicator
if (!window.StreamingStatusIndicator) {
    window.StreamingStatusIndicator = window.StreamingStatusIndicators['{{ $sessionId }}'];
}
</script>
