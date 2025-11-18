<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | The default LLM configuration to use when no specific config is requested.
    | This should match the slug of one of your LLM configurations.
    |
    */

    'default' => env('LLM_DEFAULT_CONFIG', 'ollama-llama32'),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Supported LLM providers and their default settings.
    |
    */

    'providers' => [
        'openai' => [
            'endpoint' => 'https://api.openai.com/v1',
            'default_model' => 'gpt-4o',
            'default_temperature' => 0.3,
            'default_max_tokens' => 2000,
        ],
        'anthropic' => [
            'endpoint' => 'https://api.anthropic.com/v1',
            'default_model' => 'claude-3-5-sonnet-20241022',
            'default_temperature' => 0.3,
            'default_max_tokens' => 2000,
        ],
        'ollama' => [
            'endpoint' => env('OLLAMA_ENDPOINT', 'http://localhost:11434'),
            'default_model' => 'llama3.2',
            'default_temperature' => 0.3,
            'default_max_tokens' => 2000,
            'cache_ttl' => 600, // 10 minutes
        ],
        'custom' => [
            'default_temperature' => 0.3,
            'default_max_tokens' => 2000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Budget & Costs
    |--------------------------------------------------------------------------
    |
    | Budget tracking and cost calculation settings.
    |
    */

    'budget' => [
        'enabled' => true,
        'currency' => 'USD',
        'alert_threshold' => 0.8, // Alert when 80% of budget used
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for caching provider responses and model lists.
    |
    */

    'cache' => [
        'enabled' => true,
        'ttl' => 600, // 10 minutes
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tools System Configuration
    |--------------------------------------------------------------------------
    |
    | Hybrid tools system settings (Function Calling + MCP).
    |
    */

    'tools' => [
        'strategy' => env('LLM_TOOLS_STRATEGY', 'auto'), // auto|native|mcp
        'prefer_native' => true, // Use function calling when available
        
        'native' => [
            'enabled' => true,
            'providers' => ['openai', 'anthropic', 'gemini'],
            'max_parallel_calls' => 5,
            'timeout' => 30, // seconds
        ],
        
        'mcp' => [
            'enabled' => true,
            'auto_start' => true, // Auto-start bundled servers
            'timeout' => 30, // seconds
            
            'servers' => [
                'filesystem' => [
                    'command' => 'node',
                    'args' => [__DIR__.'/../mcp-servers/filesystem/index.js'],
                    'auto_start' => true,
                ],
                'database' => [
                    'command' => 'python',
                    'args' => [__DIR__.'/../mcp-servers/database/server.py'],
                    'auto_start' => true,
                ],
                'laravel' => [
                    'command' => 'node',
                    'args' => [__DIR__.'/../mcp-servers/laravel/server.js'],
                    'auto_start' => true,
                ],
                'code-generation' => [
                    'command' => 'node',
                    'args' => [__DIR__.'/../mcp-servers/code-generation/server.js'],
                    'auto_start' => true,
                ],
            ],
        ],
        
        'security' => [
            'whitelisted_paths' => [
                storage_path('app'),
                storage_path('logs'),
                base_path('temp'),
            ],
            'allowed_extensions' => ['.txt', '.md', '.json', '.php', '.js', '.py'],
            'max_file_size' => 1048576, // 1MB
            'validate_paths' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | RAG System Configuration
    |--------------------------------------------------------------------------
    |
    | Retrieval-Augmented Generation settings.
    |
    */

    'rag' => [
        'enabled' => true,
        
        'chunking' => [
            'method' => 'semantic', // semantic|fixed|sliding
            'chunk_size' => 1000, // characters
            'chunk_overlap' => 200,
        ],
        
        'embeddings' => [
            'provider' => env('LLM_EMBEDDINGS_PROVIDER', 'openai'), // openai|local
            'model' => 'text-embedding-3-small',
            'dimensions' => 1536,
            'batch_size' => 100,
        ],
        
        'search' => [
            'top_k' => 5, // Number of results
            'similarity_threshold' => 0.7,
            'rerank' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversations Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for conversation sessions and context management.
    |
    */

    'conversations' => [
        'enabled' => true,
        'session_ttl' => 3600, // 1 hour
        'max_context_messages' => 50,
        'auto_summarize' => true,
        'summarize_threshold' => 30, // messages
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflows Configuration
    |--------------------------------------------------------------------------
    |
    | Multi-agent workflow settings.
    |
    */

    'workflows' => [
        'enabled' => true,
        'max_steps' => 20,
        'step_timeout' => 120, // seconds
        'auto_retry' => true,
        'max_retries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Configuration
    |--------------------------------------------------------------------------
    |
    | Custom metrics system settings.
    |
    */

    'metrics' => [
        'enabled' => true,
        'retention_days' => 90,
        'aggregate_by_default' => 'day', // hour|day|week|month
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Logging configuration for LLM operations.
    |
    */

    'logging' => [
        'enabled' => true,
        'channel' => env('LLM_LOG_CHANNEL', 'daily'),
        'level' => env('LLM_LOG_LEVEL', 'info'),
        'log_requests' => true,
        'log_responses' => true,
        'log_errors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Admin interface settings.
    |
    */

    'ui' => [
        'items_per_page' => 20,
        'date_format' => 'd/m/Y H:i',
        'charts_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    |
    | Performance optimization settings.
    |
    */

    'performance' => [
        'queue_enabled' => false,
        'queue_connection' => 'redis',
        'async_embeddings' => true,
        'batch_operations' => true,
    ],

];
