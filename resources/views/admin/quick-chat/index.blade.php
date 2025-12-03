<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    {{-- Chat Workspace Component (con clase PHP) --}}
    <x-llm-manager-chat-workspace
        :session="$session"
        :configurations="$configurations"
        :show-monitor="false"
        :monitor-open="false"
    />
</x-default-layout>
