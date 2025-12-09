/**
 * LLM Streaming Handler
 * 
 * Maneja conexiones SSE (Server-Sent Events) para streaming de LLM
 * Se integra automáticamente con LLMMonitor si está disponible
 * 
 * @usage
 * window.LLMStreamingHandler.start(url, params, {
 *   onStart: (data) => {},
 *   onChunk: (data) => {},
 *   onProgress: (data) => {},
 *   onComplete: (data) => {},
 *   onError: (data) => {}
 * });
 */

window.LLMStreamingHandler = {
    eventSource: null,
    isStreaming: false,
    startTime: null,
    durationInterval: null,
    metrics: {
        tokenCount: 0,
        chunkCount: 0,
        cost: 0,
        duration: 0
    },
    
    /**
     * Inicia streaming desde una URL
     * 
     * @param {string} url - Endpoint SSE
     * @param {object} params - Query parameters
     * @param {object} callbacks - Event callbacks
     */
    start(url, params = {}, callbacks = {}) {
        if (this.isStreaming) {
            console.warn('[LLMStreamingHandler] Already streaming');
            return;
        }
        
        // Reset metrics
        this.reset();
        
        // Build URL with query params
        const urlParams = new URLSearchParams(params);
        const fullUrl = url + '?' + urlParams.toString();
        
        console.log('[LLMStreamingHandler] Starting stream:', fullUrl);
        
        // Create EventSource
        this.eventSource = new EventSource(fullUrl);
        this.isStreaming = true;
        this.startTime = Date.now();
        
        // Start duration counter
        this.durationInterval = setInterval(() => {
            if (this.startTime) {
                this.metrics.duration = Math.floor((Date.now() - this.startTime) / 1000);
                
                // Update monitor if available
                if (window.LLMMonitor) {
                    const durationEl = document.getElementById('monitor-duration');
                    if (durationEl) {
                        durationEl.textContent = this.metrics.duration + 's';
                    }
                }
            }
        }, 1000);
        
        // Event: start
        this.eventSource.addEventListener('start', (event) => {
            const data = JSON.parse(event.data);
            console.log('[LLMStreamingHandler] Stream started', data);
            
            if (window.LLMMonitor) {
                window.LLMMonitor.start();
            }
            
            if (callbacks.onStart) {
                callbacks.onStart(data);
            }
        });
        
        // Event: chunk
        this.eventSource.addEventListener('chunk', (event) => {
            const data = JSON.parse(event.data);
            
            this.metrics.chunkCount++;
            if (data.tokens) {
                this.metrics.tokenCount = data.tokens;
            }
            
            if (window.LLMMonitor) {
                window.LLMMonitor.trackChunk(data.content, data.tokens || 0);
            }
            
            if (callbacks.onChunk) {
                callbacks.onChunk(data);
            }
        });

        // Event: request_data (Request Inspector - COMPLETE DATA with context_messages)
        this.eventSource.addEventListener('request_data', (event) => {
            const data = JSON.parse(event.data);
            console.log('[LLMStreamingHandler] Request data received (COMPLETE with context)', data);
            
            // UPDATE Request Inspector with COMPLETE data (including context_messages from backend)
            if (typeof window.populateRequestInspector === 'function') {
                window.populateRequestInspector(data);
                console.log('[LLMStreamingHandler] Request Inspector updated with context_messages');
            } else {
                console.warn('[LLMStreamingHandler] populateRequestInspector function not found');
            }
        });
        
        // Event: progress
        this.eventSource.addEventListener('progress', (event) => {
            const data = JSON.parse(event.data);
            
            if (callbacks.onProgress) {
                callbacks.onProgress(data);
            }
        });
        
        // Event: complete (done)
        this.eventSource.addEventListener('done', (event) => {
            const data = JSON.parse(event.data);
            console.log('[LLMStreamingHandler] Stream complete', data);
            
            // Store final metrics
            if (data.usage) {
                this.metrics.tokenCount = data.usage.total_tokens || this.metrics.tokenCount;
            }
            if (data.cost !== undefined) {
                this.metrics.cost = parseFloat(data.cost);
            }
            
            if (window.LLMMonitor) {
                window.LLMMonitor.complete(
                    params.provider || 'unknown',
                    params.model || 'unknown'
                );
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
            
            console.error('[LLMStreamingHandler] Stream error', errorData);
            
            if (window.LLMMonitor) {
                window.LLMMonitor.error(errorData.message);
            }
            
            if (callbacks.onError) {
                callbacks.onError(errorData);
            }
            
            this.stop();
        });
        
        // EventSource native error handler
        this.eventSource.onerror = (error) => {
            console.error('[LLMStreamingHandler] EventSource error:', error);
            
            if (window.LLMMonitor) {
                window.LLMMonitor.error('Connection error');
            }
            
            this.stop();
        };
    },
    
    /**
     * Detiene el streaming actual
     */
    stop() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
        
        if (this.durationInterval) {
            clearInterval(this.durationInterval);
            this.durationInterval = null;
        }
        
        this.isStreaming = false;
        console.log('[LLMStreamingHandler] Stream stopped');
    },
    
    /**
     * Resetea métricas
     */
    reset() {
        this.metrics = {
            tokenCount: 0,
            chunkCount: 0,
            cost: 0,
            duration: 0
        };
        this.startTime = null;
    },
    
    /**
     * Obtiene métricas actuales
     * 
     * @returns {object}
     */
    getMetrics() {
        return { ...this.metrics };
    },
    
    /**
     * Verifica si está streaming
     * 
     * @returns {boolean}
     */
    isActive() {
        return this.isStreaming;
    }
};

console.log('[LLMStreamingHandler] Loaded');
