{{--
    Split Resizer Component (Alpine.js)
    Lógica para manejar el drag & drop del separador horizontal
--}}

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
            // Restore saved split sizes from localStorage
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
