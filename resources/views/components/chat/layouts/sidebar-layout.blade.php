{{--
    SIDEBAR LAYOUT
    Monitor fijo a la derecha (60% chat + 40% monitor)
--}}

<div class="row g-5">
    {{-- CHAT PRINCIPAL (Desktop: 100% o 60% | Mobile: 100%) --}}
    <div :class="showMonitor && monitorOpen ? 'col-lg-8' : 'col-12'" class="chat-column">
        @include('llm-manager::components.chat.partials.chat-card')
    </div>

    {{-- MONITOR SIDEBAR (Desktop: 40% collapsible | Mobile: Modal) --}}
    @if($showMonitor)
        {{-- Desktop: Columna collapsible --}}
        <div 
            x-show="monitorOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-4"
            :class="monitorOpen ? 'col-lg-4 d-none d-lg-block' : 'd-none'"
            class="monitor-column">
            @include('llm-manager::components.chat.shared.monitor', ['monitorId' => $monitorId])
        </div>

        {{-- Mobile: Modal --}}
        <div class="modal fade" id="monitorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ki-duotone ki-chart-line-down fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Monitor de Streaming
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('llm-manager::components.chat.shared.monitor', ['monitorId' => $monitorId])
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Message Bubble Template (hidden, for cloning via JS) --}}
<template id="message-bubble-template-{{ $session?->id ?? 'default' }}">
    @include('llm-manager::components.chat.partials.bubble.message-bubble-template')
</template>
