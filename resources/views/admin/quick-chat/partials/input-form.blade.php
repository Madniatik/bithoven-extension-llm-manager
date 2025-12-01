<form id="quick-chat-form" class="d-flex flex-column gap-3">
    @csrf

    {{-- Message Input --}}
    <div>
        <textarea id="message-input" name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex gap-2">
        <button type="button" id="send-btn" class="btn btn-icon btn-sm btn-primary" data-bs-toggle="tooltip" title="Send Message">
            <i class="ki-duotone ki-send fs-4"><span class="path1"></span><span class="path2"></span></i>
        </button>
        <button type="button" id="clear-btn" class="btn btn-icon btn-sm btn-light-danger" data-bs-toggle="tooltip" title="Clear Chat">
            <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
        </button>
    </div>
</form>
