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
                <button type="button" id="send-btn-{{ $session?->id ?? 'default' }}"
                    class="btn btn-icon btn-sm btn-primary" data-bs-toggle="tooltip" title="Send Message">
                    <i class="ki-duotone ki-send fs-4"><span class="path1"></span><span class="path2"></span></i>
                </button>
                <button type="button" id="stop-btn-{{ $session?->id ?? 'default' }}"
                    class="btn btn-icon btn-sm btn-danger d-none" data-bs-toggle="tooltip" title="Stop Streaming">
                    <i class="ki-duotone ki-cross-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                </button>
                <button type="button" id="clear-btn-{{ $session?->id ?? 'default' }}"
                    class="btn btn-icon btn-sm btn-light-danger" data-bs-toggle="tooltip" title="Delete Chat">
                    <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span
                            class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
</form>
