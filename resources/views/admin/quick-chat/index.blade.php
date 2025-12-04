<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

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
        monitor-layout="sidebar"
    />
</x-default-layout>
