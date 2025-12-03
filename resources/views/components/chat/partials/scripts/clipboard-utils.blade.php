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
        // Fetch message data from backend with credentials
        fetch(`/admin/llm/messages/${messageId}/raw`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
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
                
                // Show user-friendly error with SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Message',
                    text: `Failed to load message data: ${error.message}`,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
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
