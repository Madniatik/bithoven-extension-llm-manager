{{--
    TAB NAVIGATION - Chat Workspace Tabs
    
    Props:
    - $sessionId (int|null) - Session ID para scope único
    - $showSettings (bool) - Si mostrar tab de Settings
--}}

@php
    // Manejar sessionId de forma segura (puede ser int, null, o no estar definido)
    $sessionId = $sessionId ?? ($session->id ?? 'default');
    $showSettings = $showSettings ?? true;
@endphp

<div class="card-toolbar border-bottom border-gray-300 pb-2">
    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold" role="tablist">
        {{-- Tab: Conversación | Monitor --}}
        <li class="nav-item" role="presentation">
            <a class="nav-link text-active-primary active" 
               @click="activeMainTab = 'conversation'" 
               :class="{ 'active': activeMainTab === 'conversation' }"
               href="#"
               role="tab">
                {!! getIcon('ki-messages', 'fs-2 me-2', '', 'i') !!}
                Conversación
            </a>
        </li>

        @if($showSettings)
            {{-- Tab: Chat Settings --}}
            <li class="nav-item" role="presentation">
                <a class="nav-link text-active-primary" 
                   @click="activeMainTab = 'settings'" 
                   :class="{ 'active': activeMainTab === 'settings' }"
                   href="#"
                   role="tab">
                    {!! getIcon('ki-setting-2', 'fs-2 me-2', '', 'i') !!}
                    Chat Settings
                </a>
            </li>
        @endif
    </ul>
</div>
