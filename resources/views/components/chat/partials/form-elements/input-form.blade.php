<form id="quick-chat-form-{{ $session?->id ?? 'default' }}" class="d-flex flex-column gap-3">
    @csrf
    {{-- Message Input --}}
    <textarea id="quick-chat-message-input-{{ $session?->id ?? 'default' }}"
        class="form-control form-control-flush mb-3 bg-light" rows="1" data-kt-element="input" data-kt-autosize="true"
        placeholder="Type your message"></textarea>

    <!--begin:Toolbar-->
    <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3">
        {{-- Action Buttons --}}
        <div class="d-flex align-items-center gap-2">
            @include('llm-manager::components.chat.partials.buttons.action-buttons')
        </div>

        {{-- Model Selection + Send/Delete Buttons (same row on mobile, inline on desktop) --}}
        <div class="d-flex align-items-center gap-2 w-100 flex-lg-grow-1">
            {{-- Model Selection (flex-grow para ocupar espacio disponible) --}}
            <div class="flex-grow-1">
                @include('llm-manager::components.chat.partials.form-elements.select-models')
            </div>

            {{-- Send/Stop/Clear Buttons --}}
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button class="btn btn-sm btn-icon btn-active-secondary" disabled type="button" data-bs-toggle="tooltip"
                    title="Record Voice">
                    <i class="bi bi-mic-fill fs-1"></i>
                </button>
                <button type="button" id="send-btn-{{ $session?->id ?? 'default' }}"
                    class="btn btn-icon btn-sm btn-active-light-primary" data-bs-toggle="tooltip" title="Send Message">
                    {!! getIcon('ki-send', 'fs-2x', '', 'i') !!}
                </button>
                <button type="button" id="stop-btn-{{ $session?->id ?? 'default' }}"
                    class="btn btn-icon btn-sm btn-active-light-secondary pulse pulse-danger d-none" data-bs-toggle="tooltip" title="Stop Streaming">
                    {{-- <div class="spinner-border spinner-border-sm fs-2x"></div> --}}
                    <i class="bi bi-stop-fill fs-2hx text-danger"></i>
                    <span class="pulse-ring border-4"></span>
                    {{-- {!! getIcon('ki-cross', 'fs-4', '', 'i') !!} --}}
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
</form>
