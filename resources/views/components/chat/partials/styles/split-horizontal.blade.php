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

.split-pane {
    overflow-y: auto;
    position: relative;
}

.split-chat {
    flex: 70%;
    min-height: 250px;
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
</style>
@endpush
