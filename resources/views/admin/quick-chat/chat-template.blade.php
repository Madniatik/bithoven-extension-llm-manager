<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

	<div class="card" id="kt_chat_messenger">
        <!--begin::Card header-->
        <div class="card-header" id="kt_chat_messenger_header">
            <!--begin::Title-->
            <div class="card-title">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Quick Chat</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-7">
                        @if ($session)
                            Conversación #{{ $session->id }} - {{ $session->messages->count() }} mensajes
                        @else
                            Conversación rápida con IA
                        @endif
                    </span>
                </h3>
                {{-- @include('llm-manager::admin.quick-chat.partials.drafts.chat-users') --}}
            </div>
            <!--end::Title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                @include('llm-manager::admin.quick-chat.partials.menu.chat-menu')
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body py-0" id="kt_chat_messenger_body">
            <div id="messages-container" class="scroll-y me-n5 pe-5 h-lg-auto" data-kt-element="messages"
                data-kt-scroll="true" data-kt-scroll-activate="{default: true, lg: true}"
                data-kt-scroll-max-height="auto"
                data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_app_toolbar, #kt_toolbar, #kt_footer, #kt_app_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_messenger_body"
                data-kt-scroll-offset="35px">
                <!--begin::Messages-->
                {{-- @include('llm-manager::admin.quick-chat.partials.drafts.chat-messages') --}}
                @include('llm-manager::admin.quick-chat.partials.chat-messages')
                <!--end::Messages-->
            </div>
        </div>
        <!--end::Card body-->
        <!--begin::Card footer-->
        <div class="card-footer pt-4" id="kt_chat_messenger_footer">
            <form id="quick-chat-form" class="d-flex flex-column gap-3">
                @csrf
                {{-- Message Input --}}
                <textarea id="chat-template-message-input" class="form-control form-control-flush mb-3 bg-light" rows="1" data-kt-element="input" data-kt-autosize="true" 
                    placeholder="Type a message"></textarea>

                <!--begin:Toolbar-->
                <div class="d-flex flex-stack">
                    {{-- Action Buttons --}}
                    <div class="d-flex align-items-center me-2">
                        <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button"
                            data-bs-toggle="tooltip" title="Attach File">
							{!! getIcon('ki-paper-clip', 'fs-3', '', 'i') !!}
                        </button>
                        <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button"
                            data-bs-toggle="tooltip" title="Coming soon">
							{!! getIcon('ki-exit-up', 'fs-3', '', 'i') !!}
                        </button>
                        <button id="clear-btn" class="btn btn-sm btn-icon btn-active-light-danger me-1" type="button"
                            data-bs-toggle="tooltip" title="Clear Chat">
							{!! getIcon('ki-trash', 'fs-3', '', 'i') !!}
                        </button>
                    </div>
                    <!--end::Actions-->
                    <!--begin::Send-->
                    <button id="send-btn" class="btn btn-primary" type="button" data-kt-element="send">
                        {!! getIcon('ki-send', 'fs-3 me-1', '', 'i') !!} Send
                    </button>
                    <!--end::Send-->
                </div>
                <!--end::Toolbar-->
            </form>
        </div>
        <!--end::Card footer-->
    </div>

    {{-- Raw Message Modal --}}
    @include('llm-manager::admin.quick-chat.partials.modal-raw-message')

    {{-- Styles --}}
    @include('llm-manager::admin.quick-chat.partials.styles')

    {{-- Scripts --}}
    @include('llm-manager::admin.quick-chat.partials.scripts')
</x-default-layout>
