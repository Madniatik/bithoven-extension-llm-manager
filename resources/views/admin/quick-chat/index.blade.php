<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    {{-- 
        Chat Workspace Component
        
        Usa configuración guardada del usuario ($workspaceConfig) en lugar de props hardcodeados.
        El componente carga automáticamente las preferencias del usuario desde la DB.
    --}}
    <x-llm-manager-chat-workspace
        :session="$session"
        :configurations="$configurations"
        :config="$workspaceConfig"
    />
</x-default-layout>
