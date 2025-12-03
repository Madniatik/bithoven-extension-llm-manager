@push('styles')
<style>
    /**
     * Markdown Content Styling
     * Styles for assistant messages rendered with marked.js
     */
    
    /* Headers */
    .message-content[data-role="assistant"] h1,
    .message-content[data-role="assistant"] h2,
    .message-content[data-role="assistant"] h3,
    .message-content[data-role="assistant"] h4 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    /* Paragraphs */
    .message-content[data-role="assistant"] p {
        margin-bottom: 0.75rem;
    }
    
    /* Lists */
    .message-content[data-role="assistant"] ul,
    .message-content[data-role="assistant"] ol {
        margin-bottom: 0.75rem;
        padding-left: 1.5rem;
    }
    
    .message-content[data-role="assistant"] li {
        margin-bottom: 0.25rem;
    }
    
    /* Inline code */
    .message-content[data-role="assistant"] code {
        background-color: rgba(0, 0, 0, 0.05);
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }
    
    /* Code blocks */
    .message-content[data-role="assistant"] pre {
        background-color: #2d2d2d;
        padding: 1rem;
        border-radius: 5px;
        overflow-x: auto;
        margin-bottom: 1rem;
    }
    
    .message-content[data-role="assistant"] pre code {
        background-color: transparent;
        padding: 0;
        color: #f8f8f2;
    }
    
    /* Blockquotes */
    .message-content[data-role="assistant"] blockquote {
        border-left: 4px solid #ddd;
        padding-left: 1rem;
        margin-left: 0;
        color: #666;
        font-style: italic;
    }
    
    /* Horizontal rules */
    .message-content[data-role="assistant"] hr {
        margin: 1rem 0;
        border: none;
        border-top: 1px solid #ddd;
    }
    
    /* Links */
    .message-content[data-role="assistant"] a {
        color: #007bff;
        text-decoration: underline;
    }
    
    /* Text emphasis */
    .message-content[data-role="assistant"] strong {
        font-weight: 600;
    }
    
    .message-content[data-role="assistant"] em {
        font-style: italic;
    }
</style>
@endpush
