<div id="messages-container-{{ $session?->id ?? 'default' }}" class="scroll-y me-n5 pe-5 h-lg-auto" data-kt-element="messages" data-kt-scroll="true"
    data-kt-scroll-activate="{default: true, xs: false, lg: true}" data-kt-scroll-max-height="auto"
    data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_app_toolbar, #kt_toolbar, #kt_footer, #kt_app_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
    data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_messenger_body-{{ $session?->id ?? 'default' }}"
    data-kt-scroll-offset="{default: '35px', lg: '5px'}">
    <!--begin::Messages-->
    @include('llm-manager::components.chat.partials.chat-messages')
    <!--end::Messages-->
</div>
