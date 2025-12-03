@push('scripts')
<script>
    /**
     * Clipboard Utilities for Quick Chat
     * Handles copying of messages, code blocks, and raw JSON data
     */
    
    /**
     * Copy entire message bubble content
     * @param {HTMLElement} button - The copy button element
     */
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
    
    /**
     * Copy code block content
     * @param {HTMLElement} button - The copy button element
     */
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
    
    /**
     * Show raw message JSON in modal
     * @param {number} messageId - The message ID
     */
    function showRawMessage(messageId) {
        // Fetch message data from backend
        fetch(`/admin/llm/messages/${messageId}/raw`)
            .then(response => response.json())
            .then(data => {
                const jsonString = JSON.stringify(data, null, 2);
                const codeElement = document.getElementById('rawMessageContent');
                codeElement.textContent = jsonString;
                
                // Apply syntax highlighting
                if (typeof Prism !== 'undefined') {
                    Prism.highlightElement(codeElement);
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('rawMessageModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error fetching raw message:', error);
                alert('Error loading message data');
            });
    }
    
    /**
     * Copy raw message JSON to clipboard
     */
    function copyRawMessage() {
        const codeElement = document.getElementById('rawMessageContent');
        const text = codeElement.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
            // Show toast notification with Metronic theme
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'JSON copied to clipboard',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: 'bg-success text-white',
                    title: 'text-white'
                },
                iconColor: 'white'
            });
        });
    }
</script>
@endpush
