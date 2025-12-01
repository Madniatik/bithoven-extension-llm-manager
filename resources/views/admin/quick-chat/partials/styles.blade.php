@push('styles')
<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js"></script>
<!-- Prism.js for syntax highlighting -->
<link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js"></script>
<!-- markup-templating is required for PHP -->
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-markup-templating.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-php.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-python.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-sql.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-bash.min.js"></script>

<style>
    /* Markdown content styling */
    .message-content[data-role="assistant"] h1,
    .message-content[data-role="assistant"] h2,
    .message-content[data-role="assistant"] h3,
    .message-content[data-role="assistant"] h4 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .message-content[data-role="assistant"] p {
        margin-bottom: 0.75rem;
    }
    
    .message-content[data-role="assistant"] ul,
    .message-content[data-role="assistant"] ol {
        margin-bottom: 0.75rem;
        padding-left: 1.5rem;
    }
    
    .message-content[data-role="assistant"] li {
        margin-bottom: 0.25rem;
    }
    
    .message-content[data-role="assistant"] code {
        background-color: rgba(0, 0, 0, 0.05);
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }
    
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
    
    .message-content[data-role="assistant"] blockquote {
        border-left: 4px solid #ddd;
        padding-left: 1rem;
        margin-left: 0;
        color: #666;
        font-style: italic;
    }
    
    .message-content[data-role="assistant"] hr {
        margin: 1rem 0;
        border: none;
        border-top: 1px solid #ddd;
    }
    
    .message-content[data-role="assistant"] a {
        color: #007bff;
        text-decoration: underline;
    }
    
    .message-content[data-role="assistant"] strong {
        font-weight: 600;
    }
    
    .message-content[data-role="assistant"] em {
        font-style: italic;
    }

    /* Streaming cursor animation */
    .streaming-cursor {
        animation: blink 1s infinite;
        font-weight: bold;
        color: #009ef7;
    }

    @keyframes blink {
        0%, 49% { opacity: 1; }
        50%, 100% { opacity: 0; }
    }
    
    /* Copy buttons */
    .message-bubble {
        position: relative;
    }
    
    .bubble-content-wrapper {
        position: relative;
    }
    
    .copy-bubble-btn,
    .raw-view-btn {
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .bubble-content-wrapper:hover .copy-bubble-btn,
    .bubble-content-wrapper:hover .raw-view-btn {
        opacity: 1;
    }
    
    .copy-code-btn {
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 10;
    }
    
    pre:hover .copy-code-btn {
        opacity: 1;
    }
</style>
@endpush
