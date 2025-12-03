@push('styles')
<style>
    /**
     * Responsive Styles for Quick Chat
     * Mobile breakpoints and adaptive layouts
     */
    
    /* Mobile: 100% width bubbles */
    @media (max-width: 768px) {
        .message-bubble .d-flex.flex-column {
            max-width: 100% !important;
        }
        
        .bubble-content-wrapper {
            max-width: 100% !important;
        }
    }
</style>
@endpush
