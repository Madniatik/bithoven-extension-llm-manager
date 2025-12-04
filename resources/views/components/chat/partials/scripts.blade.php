@push('scripts')
<script>
    // Copy functions
    function copyBubbleContent(button) {
        const bubble = button.closest('.message-bubble');
        const messageContent = bubble.querySelector('.message-content, .text-gray-800');
        const text = messageContent.textContent.trim();
        
        navigator.clipboard.writeText(text).then(() => {
            // Change icon to checkmark
            const icon = button.querySelector('i');
            const originalClasses = icon.className;
            icon.className = 'ki-duotone ki-check fs-6';
            icon.innerHTML = '<span class="path1"></span><span class="path2"></span>';
            
            // Revert after 2 seconds
            setTimeout(() => {
                icon.className = originalClasses;
                icon.innerHTML = '<span class="path1"></span><span class="path2"></span>';
            }, 2000);
        });
    }
    
    function copyCodeBlock(button) {
        const pre = button.closest('pre');
        const code = pre.querySelector('code');
        const text = code.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
            // Change icon to checkmark
            const icon = button.querySelector('i');
            const originalClasses = icon.className;
            icon.className = 'ki-duotone ki-check fs-7';
            icon.innerHTML = '<span class="path1"></span><span class="path2"></span>';
            
            // Revert after 2 seconds
            setTimeout(() => {
                icon.className = originalClasses;
                icon.innerHTML = '<span class="path1"></span><span class="path2"></span>';
            }, 2000);
        });
    }
    
    // Note: showRawMessage() and copyRawMessage() moved to clipboard-utils.blade.php
    // to avoid duplication and maintain single source of truth

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Configure Marked.js
        marked.setOptions({
            breaks: true,
            gfm: true,
            headerIds: false,
            mangle: false
        });
        
        // Render existing assistant messages
        document.querySelectorAll('.message-content[data-role="assistant"]').forEach(element => {
            const markdownText = element.textContent;
            element.innerHTML = marked.parse(markdownText);
            
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
        
        // Re-initialize tooltips for dynamically added buttons
        const newTooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        newTooltips.map(el => new bootstrap.Tooltip(el));
        
        // Placeholder handlers for Phase 3
        document.getElementById('send-btn')?.addEventListener('click', () => {
            console.log('Send button clicked - Ready for Phase 3 implementation');
        });
        
        document.getElementById('clear-btn')?.addEventListener('click', () => {
            console.log('Clear button clicked - Ready for Phase 3 implementation');
        });

        // Auto-scroll to bottom
        const container = document.getElementById('messages-container');
        if (container) {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }
        
        console.log('✅ Quick Chat loaded successfully');
    });
</script>
@endpush
