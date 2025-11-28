<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    <div class="card">
        <div class="card-body">
            <h1 class="mb-4">Quick Chat</h1>
            <p class="text-muted">Configuraciones activas: <strong>{{ $configurations->count() }}</strong></p>
            
            <div class="alert alert-info mt-5">
                <div class="d-flex align-items-center">
                    <i class="ki-duotone ki-information-5 fs-2x text-info me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <div>
                        <h4 class="mb-1">Fase 1: Estructura & Routing ✅</h4>
                        <p class="mb-0">Controller, routes y breadcrumbs configurados correctamente. Próximo paso: Fase 2 - Diseño HTML/CSS completo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
