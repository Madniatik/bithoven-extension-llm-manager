@push('styles')
<style>
    /**
     * Button Styles for Quick Chat
     * Copy buttons, action containers, and animations
     */
    
    /* Message bubble positioning */
    .message-bubble {
        position: relative;
    }
    
    .bubble-content-wrapper {
        position: relative;
    }
    
    /* Message actions container (hover fade-in) */
    .message-actions-container {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
        z-index: 5;
    }
    
    .bubble-content-wrapper:hover .message-actions-container {
        opacity: 1;
    }
    
    /* Code block copy button */
    .copy-code-btn {
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 10;
    }
    
    pre:hover .copy-code-btn {
        opacity: 1;
    }

    /* Streaming cursor animation */
    .streaming-cursor {
        animation: blink 1s infinite;
        font-weight: bold;
        color: #009ef7;
    }

    @keyframes blink {
        0%, 49% { opacity: 1; }
        50%, 100% { opacity: 0; }
    }
</style>
@endpush
