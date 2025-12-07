{{--
    LLM Streaming Handler (Shared)
    
    EventSource wrapper with automatic monitor integration
--}}

@push('scripts')
<script>
    window.LLMStreamingHandler = {
        eventSource: null,
        isStreaming: false,
        currentProvider: null,
        currentModel: null,
        
        start(url, params = {}, callbacks = {}) {
            if (this.isStreaming) {
                console.warn('Already streaming');
                return;
            }
            
            // Build URL with query params
            const urlParams = new URLSearchParams(params);
            const fullUrl = url + '?' + urlParams.toString();
            
            // Create EventSource
            this.eventSource = new EventSource(fullUrl);
            this.isStreaming = true;
            
            // Start monitor
            if (window.LLMMonitor && params.provider && params.model) {
                this.currentProvider = params.provider;
                this.currentModel = params.model;
                window.LLMMonitor.start(params.provider, params.model, params.sessionId);
            }
            
            // Handle ALL messages via onmessage (SSE default handler)
            this.eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);
                
                // Route based on data.type
                switch (data.type) {
                    case 'chunk':
                        if (window.LLMMonitor) {
                            window.LLMMonitor.trackChunk(data.content || data.chunk, data.tokens || 0, params.sessionId);
                        }
                        if (callbacks.onChunk) {
                            callbacks.onChunk(data);
                        }
                        break;
                        
                    case 'done':
                        if (window.LLMMonitor) {
                            window.LLMMonitor.complete(
                                this.currentProvider || data.provider || 'unknown',
                                this.currentModel || data.model || 'unknown',
                                data.usage || null,
                                data.cost || null,
                                data.execution_time_ms || null,
                                params.sessionId
                            );
                        }
                        if (callbacks.onComplete) {
                            callbacks.onComplete(data);
                        }
                        this.stop();
                        break;
                        
                    case 'error':
                        if (window.LLMMonitor) {
                            window.LLMMonitor.error(data.message || 'Unknown error', params.sessionId);
                        }
                        if (callbacks.onError) {
                            callbacks.onError(data);
                        }
                        this.stop();
                        break;
                        
                    case 'progress':
                        if (callbacks.onProgress) {
                            callbacks.onProgress(data);
                        }
                        break;
                        
                    default:
                        console.warn('Unknown SSE event type:', data.type);
                }
            };
            
            // EventSource native error handler (connection errors)
            this.eventSource.onerror = (error) => {
                console.error('EventSource error:', error);
                
                if (window.LLMMonitor) {
                    window.LLMMonitor.error('Connection error', params.sessionId);
                }
                
                if (callbacks.onError) {
                    callbacks.onError({ message: 'Connection error' });
                }
                
                this.stop();
            };
        },
        
        stop() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
            }
            this.isStreaming = false;
            this.currentProvider = null;
            this.currentModel = null;
        },
        
        isActive() {
            return this.isStreaming;
        }
    };
</script>
@endpush
