{{-- 
    MESSAGE BUBBLE TEMPLATE
    HTML template for cloning via JavaScript (cloneNode)
    
    Data attributes for JS population:
    - data-role: 'user' | 'assistant'
    - data-message-id: unique message ID
    - data-hidden: 'true' | 'false'
--}}
<div class="d-flex mb-10 message-bubble" data-role="" data-message-id="">
    {{-- Inner wrapper with alignment --}}
    <div class="d-flex flex-column" data-bubble-alignment="" style="width: 100%; max-width: 85%;">
        
        {{-- Header: Avatar + Name/Model + Timestamp --}}
        <div class="d-flex align-items-center mb-2">
            {{-- Assistant avatar (left side) --}}
            <div class="symbol symbol-35px symbol-circle me-3 assistant-avatar d-none">
                <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
            </div>
            
            {{-- Name/Model + Timestamp --}}
            <div>
                <span class="text-gray-600 fw-semibold fs-8" data-bubble-header-text=""></span>
                <span class="text-gray-500 fw-semibold fs-8 ms-2" data-bubble-timestamp=""></span>
            </div>
            
            {{-- User avatar (right side) --}}
            <div class="symbol symbol-35px symbol-circle ms-3 user-avatar d-none">
                @if(auth()->user() && auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" />
                @else
                    <span class="symbol-label bg-light-success text-success fw-bold">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
        </div>
        
        {{-- Content wrapper --}}
        <div class="p-5 rounded bubble-content-wrapper" data-bubble-bg-class="" style="max-width: 85%">
            <div class="message-content text-gray-800 fw-semibold fs-6" data-bubble-content=""></div>
        </div>
        
        {{-- Footer (solo assistant) --}}
        <div class="bubble-footer-container d-none">
            @include('llm-manager::components.chat.partials.bubble.bubble-footer')
        </div>
    </div>
</div>
