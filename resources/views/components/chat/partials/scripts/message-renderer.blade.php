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
