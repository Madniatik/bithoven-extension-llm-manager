{{--
    Split Horizontal Layout Styles
    Estilos para el layout con split vertical (chat arriba, monitor abajo)
--}}

@push('styles')
<style>
.split-horizontal-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 400px); /* Ajustado para header + footer */
    min-height: 500px;
    gap: 0;
}

/* Messages Container Responsive Heights */
/* Desktop: sin overflow-y (el parent .split-pane ya tiene scroll) */
.messages-container {
    overflow-y: visible;
}

@media (max-width: 991.98px) {
    .messages-container {
        max-height: calc(100vh - 450px); /* Mobile: con offset para header/footer */
        overflow-y: auto; /* Mobile: necesita su propio scroll */
    }
}

    .split-pane {
        overflow-y: auto;
        scroll-behavior: smooth;
        position: relative;
    }
    
    /* Scroll to bottom floating button */
    .scroll-to-bottom-btn {
        position: absolute; /* Absolute respecto al .split-horizontal-container (position: relative) */
        bottom: 20px; /* Desde el bottom del container, encima del chat */
        right: 20px; /* Desde el right del container */
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--bs-primary);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        animation: fadeInUp 0.3s ease;
    }
    
    .scroll-to-bottom-btn:hover {
        background: var(--bs-primary-active);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }
    
    .scroll-to-bottom-btn:active {
        transform: translateY(0);
    }
    
    .scroll-to-bottom-btn .unread-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 20px;
        height: 20px;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 6px;
        animation: scaleIn 0.3s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }
    
    /* Saved checkmark animation */
    .saved-checkmark {
        display: inline-flex;
        align-items: center;
        margin-left: 8px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .saved-checkmark.show {
        opacity: 1;
        animation: checkmarkBounce 0.6s ease;
    }
    
    .saved-checkmark.hide {
        opacity: 0;
        /* Sin transform en hide - solo fade out */
    }
    
    @keyframes checkmarkBounce {
        0% {
            transform: scale(0.5);
        }
        50% {
            transform: scale(1.2);
        }
        100% {
            transform: scale(1);
        }
    }.split-chat {
    flex: 70%;
    min-height: 250px;
    overflow-y: auto; /* Scroll en desktop split mode */
}

.split-monitor {
    flex: 30%;
    min-height: 150px;
    background: var(--bs-body-bg);
    display: flex;
    flex-direction: column;
}

/* Monitor Header Sticky */
.monitor-header-sticky {
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    flex-shrink: 0;
}

/* Monitor Console Body (scrollable) */
.monitor-console-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.split-resizer {
    height: 8px;
    background: var(--bs-border-color);
    cursor: row-resize;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
    user-select: none;
}

.split-resizer:hover,
.split-resizer.resizing {
    background: var(--bs-primary);
}

.split-resizer-handle {
    width: 60px;
    height: 8px;
    background: var(--bs-gray-400);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.split-resizer:hover .split-resizer-handle,
.split-resizer.resizing .split-resizer-handle {
    background: var(--bs-primary);
    transform: scaleX(1.2);
}

.split-resizer-handle i {
    color: var(--bs-white);
}

/* Mobile: Convert to stacked layout */
@media (max-width: 991.98px) {
    .split-horizontal-container {
        height: auto;
        min-height: auto;
    }
    
    .split-resizer {
        display: none !important;
    }
    
    .split-chat,
    .split-monitor {
        flex: none;
        height: auto;
    }
}

/* Drag state */
body.split-resizing {
    cursor: row-resize;
    user-select: none;
}

body.split-resizing * {
    cursor: row-resize !important;
}

/* Context Window Visual Indicator */
/* Aplicar a inner wrapper (no al outer .message-bubble) */
.message-bubble > div.in-context {
    border-left: 3px solid var(--bs-primary);
    opacity: 1;
}

.message-bubble > div.out-of-context {
    border-left: 3px solid var(--bs-gray-300);
    opacity: 0.5;
}

/* Badge circular para numeración */
.badge-circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>
@endpush
