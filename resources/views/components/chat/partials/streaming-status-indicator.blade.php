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
    
</div>

<style>
/* ===== STREAMING STATUS INDICATOR STYLES ===== */
.streaming-status-indicator {
    position: sticky;
    top: 0;
    z-index: 100;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 0.75rem 1.5rem;
    border-bottom: 2px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    font-size: 0.875rem;
    animation: slideDown 0.3s ease-out;
}

.streaming-status-indicator .status-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.streaming-status-indicator .status-text {
    color: #475569;
    letter-spacing: 0.01em;
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

/* Spinner rotation (connecting, thinking) */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.status-icon.spinning {
    animation: spin 1s linear infinite;
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

/* Connecting - Amber/Warning */
.streaming-status-indicator.state-connecting {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-bottom-color: #fbbf24;
}

.streaming-status-indicator.state-connecting .status-text {
    color: #92400e;
}

.streaming-status-indicator.state-connecting .status-icon {
    color: #f59e0b;
}

/* Thinking - Blue/Info */
.streaming-status-indicator.state-thinking {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-bottom-color: #60a5fa;
}

.streaming-status-indicator.state-thinking .status-text {
    color: #1e40af;
}

.streaming-status-indicator.state-thinking .status-icon {
    color: #3b82f6;
}

/* Typing - Green/Success */
.streaming-status-indicator.state-typing {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-bottom-color: #34d399;
}

.streaming-status-indicator.state-typing .status-text {
    color: #065f46;
}

.streaming-status-indicator.state-typing .status-icon {
    color: #10b981;
}

/* Completed - Green bright */
.streaming-status-indicator.state-completed {
    background: linear-gradient(135deg, #d1fae5 0%, #86efac 100%);
    border-bottom-color: #22c55e;
}

.streaming-status-indicator.state-completed .status-text {
    color: #14532d;
}

.streaming-status-indicator.state-completed .status-icon {
    color: #16a34a;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 991.98px) {
    .streaming-status-indicator {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
    }
    
    .streaming-status-indicator .status-icon {
        width: 16px;
        height: 16px;
    }
}
</style>

<script>
// ===== STREAMING STATUS INDICATOR MODULE =====
const StreamingStatusIndicator = (() => {
    const sessionId = '{{ $sessionId }}';
    let currentState = null;
    let hideTimeout = null;
    
    // DOM elements
    const indicator = document.getElementById(`streaming_status_indicator_${sessionId}`);
    const icon = document.getElementById(`streaming_status_icon_${sessionId}`);
    const text = document.getElementById(`streaming_status_text_${sessionId}`);
    
    // State configuration
    const states = {
        connecting: {
            icon: '{!! getIcon("ki-loading", "fs-3", "", "i") !!}',
            text: 'Connecting to AI...',
            iconClass: 'spinning',
            className: 'state-connecting'
        },
        thinking: {
            icon: '{!! getIcon("ki-abstract-26", "fs-3", "", "i") !!}',
            text: 'Thinking...',
            iconClass: 'spinning',
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
        
        console.log(`[StreamingIndicator] State: ${state}`);
        
        // Auto-hide completed state after 1.5 seconds
        if (state === 'completed') {
            hideTimeout = setTimeout(() => {
                hide();
            }, 1500);
        }
    };
    
    /**
     * Hide indicator with fade out
     */
    const hide = () => {
        if (!indicator) return;
        
        indicator.classList.add('fading-out');
        
        setTimeout(() => {
            indicator.style.display = 'none';
            indicator.classList.remove('fading-out');
            currentState = null;
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
        isEnabled
    };
})();

// Make globally accessible
window.StreamingStatusIndicator = StreamingStatusIndicator;
</script>
