{{-- 
    Bubble Footer Component
    Unified footer for assistant message bubbles
    
    Expected classes on parent spans:
    - .footer-tokens
    - .footer-response-time
    - .footer-ttft
    - .footer-cost
--}}
<div class="bubble-footer text-gray-500 fw-semibold fs-8 mt-1 d-flex align-items-center gap-3 flex-wrap">
    <span class="footer-tokens">
        <i class="ki-duotone ki-calculator fs-7 text-gray-400">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        0 tokens <span class="text-gray-400" title="Sent / Received">(↑0 / ↓0)</span>
    </span>
    <span class="footer-response-time text-gray-400">
        <i class="ki-duotone ki-timer fs-7 text-gray-400">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        ...
    </span>
    <span class="footer-ttft text-gray-400">
        <i class="ki-duotone ki-flash-circle fs-7 text-gray-400">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        TTFT: ...
    </span>
    <span class="footer-cost text-gray-400">
        <i class="ki-duotone ki-dollar fs-7 text-gray-400">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        $...
    </span>
</div>
