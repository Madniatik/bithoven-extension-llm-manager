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
                </div>
                
                <div class="text-muted fs-7">
                    <i class="ki-duotone ki-information-5 fs-5 me-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Quick Chat - Auto-saved to database
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
        :monitor-open="false"
        monitor-layout="split-horizontal"
    />

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const newChatBtn = document.getElementById('newChatBtn');
        
        newChatBtn?.addEventListener('click', () => {
            if (confirm('Start a new chat? This will create a new conversation.')) {
                window.location.href = '{{ route("admin.llm.quick-chat.new") }}';
            }
        });
    });
    </script>
    @endpush
</x-default-layout>
