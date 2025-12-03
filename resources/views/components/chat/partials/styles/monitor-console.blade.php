{{--
    Monitor Console Styles
    
    Dark theme styling for monitor console logs
    Used by both monitor.blade.php and monitor-console.blade.php
--}}

<style>
.monitor-console-dark {
    background-color: #1e1e1e;
    color: #d4d4d4;
    padding: 1rem;
    border-radius: 0.475rem;
}

.monitor-console-dark .text-muted {
    color: #6c757d !important;
}

.monitor-console-dark .text-gray-400 {
    color: #9ca3af !important;
}

.monitor-console-dark .text-gray-500 {
    color: #6b7280 !important;
}

.monitor-console-dark .text-success {
    color: #4ade80 !important;
}

.monitor-console-dark .text-danger {
    color: #f87171 !important;
}

.monitor-console-dark .text-warning {
    color: #fbbf24 !important;
}

/* Scrollbar oscuro */
.monitor-console-dark::-webkit-scrollbar {
    width: 8px;
}

.monitor-console-dark::-webkit-scrollbar-track {
    background: #2d2d2d;
}

.monitor-console-dark::-webkit-scrollbar-thumb {
    background: #4a4a4a;
    border-radius: 4px;
}

.monitor-console-dark::-webkit-scrollbar-thumb:hover {
    background: #5a5a5a;
}
</style>
