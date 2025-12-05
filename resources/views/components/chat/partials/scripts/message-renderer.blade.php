@push('scripts')
<script>
    /**
     * Message Renderer for Quick Chat
     * Handles Markdown rendering, syntax highlighting, and button injection
     */
    
    document.addEventListener('DOMContentLoaded', () => {
        // Configure Marked.js
        marked.setOptions({
            breaks: true,
            gfm: true,
            pedantic: false,    // Don't convert indented text to code blocks
            headerIds: false,
            mangle: false
        });
        
        // Render existing assistant messages (OLD bubbles)
        document.querySelectorAll('.message-content[data-role="assistant"]:not([data-rendered="true"])').forEach(element => {
            const markdownText = element.textContent.trim();
            element.innerHTML = marked.parse(markdownText);
            element.setAttribute('data-rendered', 'true');
            
            // Apply syntax highlighting to code blocks
            element.querySelectorAll('pre code').forEach(block => {
                try {
                    if (typeof Prism !== 'undefined') {
                        Prism.highlightElement(block);
                    }
                } catch (error) {
                    console.warn('Prism highlighting failed:', error);
                }
            });
            
            // Add copy buttons to code blocks
            element.querySelectorAll('pre').forEach(pre => {
                if (!pre.querySelector('.copy-code-btn')) {
                    const copyBtn = document.createElement('button');
                    copyBtn.className = 'btn btn-icon btn-sm btn-light-primary position-absolute top-0 end-0 m-1 copy-code-btn';
                    copyBtn.setAttribute('data-bs-toggle', 'tooltip');
                    copyBtn.setAttribute('title', 'Copy code');
                    copyBtn.onclick = function() { copyCodeBlock(this); };
                    copyBtn.innerHTML = '<i class="ki-duotone ki-copy fs-7"><span class="path1"></span><span class="path2"></span></i>';
                    pre.style.position = 'relative';
                    pre.appendChild(copyBtn);
                }
            });
        });
        
        // Add copy buttons to message bubbles (inside the content area)
        document.querySelectorAll('.message-bubble').forEach(bubble => {
            const bubbleContent = bubble.querySelector('.bubble-content-wrapper');
            if (bubbleContent && !bubbleContent.querySelector('.copy-bubble-btn')) {
                // Get message ID from data attribute
                const messageId = bubble.getAttribute('data-message-id');
                
                // Create button container
                const btnContainer = document.createElement('div');
                btnContainer.className = 'message-actions-container position-absolute top-0 end-0 m-2 d-flex gap-1';
                
                // Raw view button (only if message ID exists)
                if (messageId) {
                    const rawBtn = document.createElement('button');
                    rawBtn.className = 'btn btn-icon btn-sm btn-light-info raw-view-btn';
                    rawBtn.setAttribute('data-bs-toggle', 'tooltip');
                    rawBtn.setAttribute('title', 'View raw data');
                    rawBtn.onclick = function() { showRawMessage(messageId); };
                    rawBtn.innerHTML = '<i class="ki-duotone ki-code fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>';
                    btnContainer.appendChild(rawBtn);
                }
                
                // Copy button
                const copyBtn = document.createElement('button');
                copyBtn.className = 'btn btn-icon btn-sm btn-light copy-bubble-btn';
                copyBtn.setAttribute('data-bs-toggle', 'tooltip');
                copyBtn.setAttribute('title', 'Copy message');
                copyBtn.onclick = function() { copyBubbleContent(this); };
                copyBtn.innerHTML = '<i class="ki-duotone ki-copy fs-6"><span class="path1"></span><span class="path2"></span></i>';
                btnContainer.appendChild(copyBtn);
                
                bubbleContent.style.position = 'relative';
                bubbleContent.appendChild(btnContainer);
            }
        });
        
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Re-initialize tooltips for dynamically added buttons
        const newTooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        newTooltips.map(el => new bootstrap.Tooltip(el));
    });
</script>
@endpush
