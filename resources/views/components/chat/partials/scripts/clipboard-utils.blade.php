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
     * Show raw message JSON in modal with tabs (metadata + raw_response)
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
                // Populate metadata tab
                const metadataElement = document.getElementById('metadataContent');
                const metadataString = JSON.stringify(data.metadata || {}, null, 2);
                metadataElement.textContent = metadataString;
                
                // Populate raw_response tab
                const rawResponseElement = document.getElementById('rawResponseContent');
                const rawResponseString = JSON.stringify(data.raw_response || {}, null, 2);
                rawResponseElement.textContent = rawResponseString;
                
                // Apply syntax highlighting to both
                if (typeof Prism !== 'undefined') {
                    Prism.highlightElement(metadataElement);
                    Prism.highlightElement(rawResponseElement);
                }
                
                // Reset to metadata tab (first tab)
                const metadataTab = document.getElementById('metadata-tab');
                const metadataTabInstance = new bootstrap.Tab(metadataTab);
                metadataTabInstance.show();
                
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
     * Copy active tab JSON to clipboard
     */
    function copyActiveTabJSON() {
        // Find active tab
        const activeTab = document.querySelector('#rawDataTabs .tab-pane.active');
        const codeElement = activeTab?.querySelector('code');
        
        if (!codeElement) {
            toastr.error('No content to copy');
            return;
        }
        
        const text = codeElement.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
            // Get tab name for feedback
            const tabName = activeTab.id === 'metadata-content' ? 'Metadata' : 'Raw Response';
            toastr.success(`${tabName} copied to clipboard!`);
        }).catch(err => {
            console.error('Failed to copy:', err);
            toastr.error('Failed to copy to clipboard');
        });
    }
    
    /**
     * Legacy function - kept for backward compatibility
     * @deprecated Use copyActiveTabJSON() instead
     */
    function copyRawMessage() {
        copyActiveTabJSON();
    }
</script>
@endpush
