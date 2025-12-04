{{--
    LLM Streaming Handler (Shared)
    
    EventSource wrapper with automatic monitor integration
--}}

@push('scripts')
<script>
    window.LLMStreamingHandler = {
        eventSource: null,
        isStreaming: false,
        
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
            
            // Event: start
            this.eventSource.addEventListener('start', (event) => {
                const data = JSON.parse(event.data);
                
                if (window.LLMMonitor) {
                    window.LLMMonitor.start(params.sessionId);
                }
                
                if (callbacks.onStart) {
                    callbacks.onStart(data);
                }
            });
            
            // Event: chunk
            this.eventSource.addEventListener('chunk', (event) => {
                const data = JSON.parse(event.data);
                
                if (window.LLMMonitor) {
                    window.LLMMonitor.trackChunk(data.chunk, data.tokens || 0, params.sessionId);
                }
                
                if (callbacks.onChunk) {
                    callbacks.onChunk(data);
                }
            });
            
            // Event: progress
            this.eventSource.addEventListener('progress', (event) => {
                const data = JSON.parse(event.data);
                
                if (callbacks.onProgress) {
                    callbacks.onProgress(data);
                }
            });
            
            // Event: complete
            this.eventSource.addEventListener('complete', (event) => {
                const data = JSON.parse(event.data);
                
                if (window.LLMMonitor) {
                    window.LLMMonitor.complete(data.provider || 'unknown', data.model || 'unknown', params.sessionId);
                }
                
                if (callbacks.onComplete) {
                    callbacks.onComplete(data);
                }
                
                this.stop();
            });
            
            // Event: error
            this.eventSource.addEventListener('error', (event) => {
                let errorData = { message: 'Unknown error' };
                
                if (event.data) {
                    try {
                        errorData = JSON.parse(event.data);
                    } catch (e) {
                        errorData.message = event.data;
                    }
                }
                
                if (window.LLMMonitor) {
                    window.LLMMonitor.error(errorData.message, params.sessionId);
                }
                
                if (callbacks.onError) {
                    callbacks.onError(errorData);
                }
                
                this.stop();
            });
            
            // EventSource native error handler
            this.eventSource.onerror = (error) => {
                console.error('EventSource error:', error);
                
                if (window.LLMMonitor) {
                    window.LLMMonitor.error('Connection error', params.sessionId);
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
        },
        
        isActive() {
            return this.isStreaming;
        }
    };
</script>
@endpush
