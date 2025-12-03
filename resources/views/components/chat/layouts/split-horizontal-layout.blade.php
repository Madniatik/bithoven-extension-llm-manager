{{--
    SPLIT HORIZONTAL LAYOUT
    Chat arriba (70%) + Monitor Console abajo (30%) con resizer draggable
--}}

<div class="split-horizontal-container" id="llm-split-view" x-data="splitResizer()" x-init="init()">
    {{-- CHAT PANE (70% default) - Incluye TODA la card con textarea --}}
    <div class="split-pane split-chat" id="split-chat-pane">
        @include('llm-manager::components.chat.partials.chat-card')
    </div>
    
    @if($showMonitor)
        {{-- RESIZER BAR (draggable) --}}
        <div 
            x-show="monitorOpen"
            class="split-resizer" 
            id="split-resizer"
            @mousedown="startResize($event)"
            style="display: none;">
            <div class="split-resizer-handle">
                <i class="ki-duotone ki-row-vertical fs-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>
        
        {{-- MONITOR PANE (30% default) - SOLO CONSOLA --}}
        <div 
            x-show="monitorOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="split-pane split-monitor" 
            id="split-monitor-pane"
            style="display: none;">
            
            {{-- Console Header con botón cerrar --}}
            <div class="d-flex justify-content-between align-items-center mb-2 px-3 pt-3">
                <h6 class="mb-0 text-gray-700">
                    <i class="ki-duotone ki-code fs-3 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    Monitor Console
                </h6>
                <button 
                    type="button" 
                    class="btn btn-sm btn-icon btn-light-dark" 
                    @click="monitorOpen = false; localStorage.setItem('llm_chat_monitor_open', 'false')"
                    data-bs-toggle="tooltip"
                    title="Cerrar Monitor">
                    <i class="ki-duotone ki-cross fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>
            </div>
            
            {{-- Solo la consola (fondo negro) --}}
            <div class="px-3 pb-3" style="height: calc(100% - 60px);">
                @include('llm-manager::components.chat.shared.monitor-console')
            </div>
        </div>
    @endif
</div>

{{-- Split Horizontal Styles --}}
@push('styles')
<style>
.split-horizontal-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    min-height: 600px;
    gap: 0;
}

.split-pane {
    overflow-y: auto;
    position: relative;
}

.split-chat {
    flex: 70%;
    min-height: 300px;
    display: flex;
    flex-direction: column;
}

.split-monitor {
    flex: 30%;
    min-height: 150px;
    background: var(--bs-body-bg);
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
    
    .split-monitor {
        display: none !important;
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

{{-- Split Resizer JavaScript --}}
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('splitResizer', () => ({
        isResizing: false,
        startY: 0,
        startChatHeight: 0,
        startMonitorHeight: 0,
        
        startResize(event) {
            if (window.innerWidth < 992) return;
            
            this.isResizing = true;
            this.startY = event.clientY;
            
            const chatPane = document.getElementById('split-chat-pane');
            const monitorPane = document.getElementById('split-monitor-pane');
            const resizer = document.getElementById('split-resizer');
            
            this.startChatHeight = chatPane.offsetHeight;
            this.startMonitorHeight = monitorPane.offsetHeight;
            
            document.body.classList.add('split-resizing');
            resizer.classList.add('resizing');
            
            const moveHandler = this.handleResize.bind(this);
            const upHandler = this.stopResize.bind(this);
            
            document.addEventListener('mousemove', moveHandler);
            document.addEventListener('mouseup', upHandler);
            
            this._moveHandler = moveHandler;
            this._upHandler = upHandler;
        },
        
        handleResize(event) {
            if (!this.isResizing) return;
            
            const deltaY = event.clientY - this.startY;
            const container = document.getElementById('llm-split-view');
            const containerHeight = container.offsetHeight;
            
            let newChatHeight = this.startChatHeight + deltaY;
            let newMonitorHeight = this.startMonitorHeight - deltaY;
            
            const minHeight = containerHeight * 0.2;
            const maxChatHeight = containerHeight - minHeight - 8;
            
            newChatHeight = Math.max(minHeight, Math.min(newChatHeight, maxChatHeight));
            newMonitorHeight = containerHeight - newChatHeight - 8;
            
            const chatFlex = (newChatHeight / containerHeight) * 100;
            const monitorFlex = (newMonitorHeight / containerHeight) * 100;
            
            const chatPane = document.getElementById('split-chat-pane');
            const monitorPane = document.getElementById('split-monitor-pane');
            
            chatPane.style.flex = `${chatFlex}%`;
            monitorPane.style.flex = `${monitorFlex}%`;
            
            localStorage.setItem('llm_split_chat_flex', chatFlex);
            localStorage.setItem('llm_split_monitor_flex', monitorFlex);
        },
        
        stopResize() {
            if (!this.isResizing) return;
            
            this.isResizing = false;
            
            document.body.classList.remove('split-resizing');
            const resizer = document.getElementById('split-resizer');
            if (resizer) resizer.classList.remove('resizing');
            
            document.removeEventListener('mousemove', this._moveHandler);
            document.removeEventListener('mouseup', this._upHandler);
        },
        
        init() {
            const savedChatFlex = localStorage.getItem('llm_split_chat_flex');
            const savedMonitorFlex = localStorage.getItem('llm_split_monitor_flex');
            
            if (savedChatFlex && savedMonitorFlex) {
                const chatPane = document.getElementById('split-chat-pane');
                const monitorPane = document.getElementById('split-monitor-pane');
                
                if (chatPane) chatPane.style.flex = `${savedChatFlex}%`;
                if (monitorPane) monitorPane.style.flex = `${savedMonitorFlex}%`;
            }
        }
    }));
});
</script>
@endpush
