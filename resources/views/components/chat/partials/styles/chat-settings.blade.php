<style>
/**
 * Chat Settings Tabs Styles
 * 
 * Estilos para el sistema de tabs (Conversación | Settings)
 * y el panel de configuración del chat workspace.
 */

/* ===== TAB NAVIGATION ===== */
.card-toolbar.border-bottom {
    background-color: #f9f9f9;
}

.nav-line-tabs .nav-item {
    margin-bottom: -1px;
}

.nav-line-tabs .nav-link {
    color: #7e8299;
    padding: 0.75rem 1.5rem;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.nav-line-tabs .nav-link:hover {
    color: #009ef7;
    background-color: rgba(0, 158, 247, 0.05);
}

.nav-line-tabs .nav-link.active {
    color: #009ef7;
    border-bottom-color: #009ef7;
    font-weight: 600;
}

/* Icono en tabs */
.nav-line-tabs .nav-link .svg-icon {
    margin-right: 0.5rem;
}

/* ===== TAB CONTENT ===== */
.tab-content {
    min-height: 400px;
}

/* ===== SETTINGS FORM ===== */
.settings-panel {
    background-color: #ffffff;
}

/* Secciones colapsables */
.cursor-pointer {
    cursor: pointer;
    user-select: none;
}

.cursor-pointer:hover {
    background-color: #f9f9f9;
}

/* Transitions para collapse */
[x-show] {
    transition: all 0.3s ease;
}

/* Form switches mejorados */
.form-check-input:checked {
    background-color: #009ef7;
    border-color: #009ef7;
}

.form-check-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.form-check-label {
    cursor: pointer;
}

.form-check-label:has(+ .form-check-input:disabled) {
    cursor: not-allowed;
}

/* Alert info en settings */
.alert-info {
    background-color: #e8f4fd;
    border-color: #b8e2f9;
    color: #0c5460;
}

/* Settings card footer */
.settings-panel .card-footer {
    background-color: #f9f9f9;
    border-top: 1px dashed #e4e6ef;
}

/* Separators en secciones */
.separator-dashed {
    height: 0;
    border-top: 1px dashed #e4e6ef;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .nav-line-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .nav-line-tabs .nav-link .svg-icon {
        display: none; /* Ocultar iconos en móvil */
    }
    
    .settings-panel .card-body {
        padding: 1rem;
    }
    
    .settings-panel .ps-5 {
        padding-left: 1.5rem !important;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tab-content > div[x-show] {
    animation: fadeIn 0.3s ease;
}

/* ===== UTILITY CLASSES ===== */
.text-active-primary.active {
    color: #009ef7 !important;
}

/* Ajustar altura del settings form */
.settings-panel .overflow-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.settings-panel .overflow-auto::-webkit-scrollbar {
    width: 8px;
}

.settings-panel .overflow-auto::-webkit-scrollbar-track {
    background: #f7fafc;
}

.settings-panel .overflow-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 4px;
}

.settings-panel .overflow-auto::-webkit-scrollbar-thumb:hover {
    background-color: #a0aec0;
}
</style>
