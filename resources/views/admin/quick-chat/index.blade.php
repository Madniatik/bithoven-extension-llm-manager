<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    {{-- Quick Chat Toolbar --}}
    <div class="card mb-5">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" id="newChatBtn" class="btn btn-sm btn-primary">
                        <i class="ki-duotone ki-plus fs-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        New Chat
                    </button>
                    
                    <button type="button" id="saveChatBtn" class="btn btn-sm btn-light-success" disabled>
                        <i class="ki-duotone ki-save-2 fs-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Save Chat
                    </button>
                </div>
                
                <div class="text-muted fs-7">
                    <i class="ki-duotone ki-information-5 fs-5 me-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Quick Chat - Temporary session (not saved to database by default)
                </div>
            </div>
        </div>
    </div>

    {{-- 
        Chat Workspace Component
        
        Layouts disponibles:
        - 'sidebar': Monitor fijo a la derecha (60% chat + 40% monitor)
        - 'split-horizontal': Chat arriba (70%), Monitor abajo (30%) con resizer draggable
    --}}
    <x-llm-manager-chat-workspace
        :session="$session"
        :configurations="$configurations"
        :show-monitor="true"
        :monitor-open="true"
        monitor-layout="split-horizontal"
    />

    @push('scripts')
    <script>
    /**
     * Quick Chat JavaScript
     * Handles New Chat and Save Chat functionality
     */
    document.addEventListener('DOMContentLoaded', () => {
        const newChatBtn = document.getElementById('newChatBtn');
        const saveChatBtn = document.getElementById('saveChatBtn');
        
        // New Chat: Reload page (creates new temp session)
        newChatBtn?.addEventListener('click', () => {
            if (confirm('Start a new chat? Current conversation will be lost unless you save it.')) {
                window.location.href = '{{ route("admin.llm.quick-chat") }}';
            }
        });
        
        // Save Chat: Persist to database
        saveChatBtn?.addEventListener('click', async () => {
            const title = prompt('Enter a title for this chat:', 'Quick Chat - {{ now()->format("Y-m-d H:i") }}');
            if (!title) return;
            
            // Get messages from localStorage (populated by streaming)
            const messagesKey = 'llm_quick_chat_messages_{{ $session->session_id }}';
            const messagesJson = localStorage.getItem(messagesKey);
            
            if (!messagesJson) {
                toastr.error('No messages to save');
                return;
            }
            
            const messages = JSON.parse(messagesJson);
            if (messages.length === 0) {
                toastr.error('No messages to save');
                return;
            }
            
            try {
                saveChatBtn.disabled = true;
                saveChatBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                
                const response = await fetch('{{ route("admin.llm.quick-chat.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        title: title,
                        configuration_id: document.getElementById('quick-chat-model-selector-{{ $session->id ?? "default" }}')?.value,
                        messages: messages
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    toastr.success('Chat saved successfully!');
                    // Clear localStorage
                    localStorage.removeItem(messagesKey);
                    // Redirect to conversation
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to save chat');
                }
            } catch (error) {
                console.error('Save error:', error);
                toastr.error('Failed to save chat: ' + error.message);
                saveChatBtn.disabled = false;
                saveChatBtn.innerHTML = '<i class="ki-duotone ki-save-2 fs-4"><span class="path1"></span><span class="path2"></span></i> Save Chat';
            }
        });
        
        // Enable Save button when messages exist
        const checkMessages = () => {
            const messagesKey = 'llm_quick_chat_messages_{{ $session->session_id }}';
            const messages = JSON.parse(localStorage.getItem(messagesKey) || '[]');
            saveChatBtn.disabled = messages.length === 0;
        };
        
        // Check on load and periodically
        checkMessages();
        setInterval(checkMessages, 2000);
    });
    </script>
    @endpush
</x-default-layout>
