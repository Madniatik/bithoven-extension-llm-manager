/**
 * Monitor Panel - Sistema de logs reutilizable tipo terminal
 * 
 * Uso:
 * Monitor.log('monitor-id', 'mensaje', 'info|success|warning|error|debug');
 * Monitor.success('monitor-id', 'mensaje');
 * Monitor.error('monitor-id', 'mensaje');
 * Monitor.clear('monitor-id');
 */

const Monitor = {
    /**
     * Agregar log a un monitor específico
     * @param {string} monitorId - ID del contenedor del monitor
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo de log: info, success, warning, error, debug
     * @param {string|null} timestamp - Timestamp opcional (auto si es null)
     */
    log(monitorId, message, type = 'info', timestamp = null) {
        const container = document.getElementById(monitorId);
        if (!container) {
            console.error(`Monitor container with id "${monitorId}" not found`);
            return;
        }

        // Primera vez: limpiar placeholder
        if (container.querySelector('.text-center')) {
            container.innerHTML = '';
        }

        const time = timestamp || new Date().toLocaleTimeString('es-ES');
        const colors = {
            'info': 'text-gray-700',
            'success': 'text-success',
            'warning': 'text-warning',
            'error': 'text-danger',
            'debug': 'text-muted',
        };

        const entry = document.createElement('div');
        entry.className = `mb-1 ${colors[type] || colors.info}`;
        entry.innerHTML = `<span class="text-muted">[${time}]</span> ${message}`;

        container.appendChild(entry);
        
        // Auto-scroll al final
        container.scrollTop = container.scrollHeight;
    },

    /**
     * Limpiar monitor
     * @param {string} monitorId - ID del contenedor del monitor
     */
    clear(monitorId) {
        const container = document.getElementById(monitorId);
        if (!container) {
            console.error(`Monitor container with id "${monitorId}" not found`);
            return;
        }

        container.innerHTML = `
            <div class="text-muted text-center py-5">
                <i class="ki-duotone ki-information-2 fs-3x mb-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div>Waiting for events...</div>
            </div>
        `;
    },

    /**
     * Log success - Shortcut
     */
    success(monitorId, message) {
        this.log(monitorId, '✅ ' + message, 'success');
    },

    /**
     * Log error - Shortcut
     */
    error(monitorId, message) {
        this.log(monitorId, '❌ ' + message, 'error');
    },

    /**
     * Log warning - Shortcut
     */
    warning(monitorId, message) {
        this.log(monitorId, '⚠️ ' + message, 'warning');
    },

    /**
     * Log info - Shortcut
     */
    info(monitorId, message) {
        this.log(monitorId, 'ℹ️ ' + message, 'info');
    },

    /**
     * Log debug - Shortcut
     */
    debug(monitorId, message) {
        this.log(monitorId, '🔍 ' + message, 'debug');
    },

    /**
     * Log batch - Procesar múltiples logs del servidor
     * @param {string} monitorId - ID del contenedor
     * @param {Array} logs - Array de objetos {message, type, timestamp}
     */
    processBatch(monitorId, logs) {
        if (!logs || !Array.isArray(logs)) return;
        
        logs.forEach(log => {
            this.log(monitorId, log.message, log.type || 'info', log.timestamp || null);
        });
    }
};

/**
 * Función global para limpiar monitor (usada en onclick del componente)
 */
function clearMonitor(id) {
    Monitor.clear(id);
}

// Export para módulos (opcional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Monitor;
}
