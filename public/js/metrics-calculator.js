/**
 * LLM Metrics Calculator
 * 
 * Utilidades para cálculo y formateo de métricas de LLM
 */

window.LLMMetrics = {
    /**
     * Tarifas por modelo (USD por 1K tokens)
     * TODO: Mover a configuración dinámica
     */
    pricing: {
        'gpt-4': {
            input: 0.03,
            output: 0.06
        },
        'gpt-4-turbo': {
            input: 0.01,
            output: 0.03
        },
        'gpt-3.5-turbo': {
            input: 0.0015,
            output: 0.002
        },
        'claude-3-opus': {
            input: 0.015,
            output: 0.075
        },
        'claude-3-sonnet': {
            input: 0.003,
            output: 0.015
        },
        'default': {
            input: 0.002,
            output: 0.002
        }
    },
    
    /**
     * Calcula el costo basado en tokens y modelo
     * 
     * @param {number} promptTokens - Tokens del prompt
     * @param {number} completionTokens - Tokens de la completion
     * @param {string} model - Nombre del modelo
     * @returns {number} Costo en USD
     */
    calculateCost(promptTokens, completionTokens, model = 'default') {
        const rates = this.pricing[model] || this.pricing.default;
        
        const promptCost = (promptTokens / 1000) * rates.input;
        const completionCost = (completionTokens / 1000) * rates.output;
        
        return promptCost + completionCost;
    },
    
    /**
     * Formatea costo como moneda
     * 
     * @param {number} cost - Costo en USD
     * @param {number} decimals - Decimales (default: 6)
     * @returns {string}
     */
    formatCost(cost, decimals = 6) {
        return '$' + parseFloat(cost).toFixed(decimals);
    },
    
    /**
     * Formatea duración en segundos
     * 
     * @param {number} seconds - Duración en segundos
     * @returns {string}
     */
    formatDuration(seconds) {
        if (seconds < 60) {
            return seconds + 's';
        }
        
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}m ${secs}s`;
    },
    
    /**
     * Formatea duración desde milisegundos
     * 
     * @param {number} ms - Duración en milisegundos
     * @returns {string}
     */
    formatDurationMs(ms) {
        const seconds = Math.floor(ms / 1000);
        return this.formatDuration(seconds);
    },
    
    /**
     * Formatea número de tokens
     * 
     * @param {number} tokens - Número de tokens
     * @returns {string}
     */
    formatTokens(tokens) {
        return tokens.toLocaleString();
    },
    
    /**
     * Calcula tokens por segundo
     * 
     * @param {number} tokens - Total de tokens
     * @param {number} durationMs - Duración en milisegundos
     * @returns {number}
     */
    calculateTokensPerSecond(tokens, durationMs) {
        const seconds = durationMs / 1000;
        return seconds > 0 ? (tokens / seconds).toFixed(2) : 0;
    },
    
    /**
     * Calcula costo por token
     * 
     * @param {number} cost - Costo total
     * @param {number} tokens - Total de tokens
     * @returns {number}
     */
    calculateCostPerToken(cost, tokens) {
        return tokens > 0 ? (cost / tokens).toFixed(8) : 0;
    },
    
    /**
     * Genera resumen de métricas
     * 
     * @param {object} data - Datos de streaming
     * @returns {object}
     */
    generateSummary(data) {
        const {
            usage = {},
            cost = 0,
            execution_time_ms = 0,
            chunks = 0
        } = data;
        
        const totalTokens = usage.total_tokens || 0;
        const tokensPerSecond = this.calculateTokensPerSecond(totalTokens, execution_time_ms);
        const costPerToken = this.calculateCostPerToken(cost, totalTokens);
        
        return {
            tokens: {
                prompt: usage.prompt_tokens || 0,
                completion: usage.completion_tokens || 0,
                total: totalTokens,
                formatted: this.formatTokens(totalTokens)
            },
            cost: {
                raw: cost,
                formatted: this.formatCost(cost),
                perToken: costPerToken
            },
            duration: {
                ms: execution_time_ms,
                seconds: Math.floor(execution_time_ms / 1000),
                formatted: this.formatDurationMs(execution_time_ms)
            },
            performance: {
                tokensPerSecond: parseFloat(tokensPerSecond),
                chunksReceived: chunks
            }
        };
    },
    
    /**
     * Calcula estimación de costo antes de ejecutar
     * 
     * @param {number} estimatedTokens - Tokens estimados
     * @param {string} model - Modelo a usar
     * @param {number} ratio - Ratio prompt/completion (default: 0.5)
     * @returns {object}
     */
    estimateCost(estimatedTokens, model, ratio = 0.5) {
        const promptTokens = Math.floor(estimatedTokens * ratio);
        const completionTokens = estimatedTokens - promptTokens;
        const cost = this.calculateCost(promptTokens, completionTokens, model);
        
        return {
            estimatedTokens,
            promptTokens,
            completionTokens,
            estimatedCost: cost,
            formattedCost: this.formatCost(cost)
        };
    }
};

console.log('[LLMMetrics] Loaded');
