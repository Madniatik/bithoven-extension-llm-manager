# ⚙️ Configuration Guide

**LLM Manager Extension v1.0**

---

## 📋 Table of Contents

1. [Configuration File](#configuration-file)
2. [Provider Configurations](#provider-configurations)
3. [Budget Controls](#budget-controls)
4. [RAG System](#rag-system)
5. [MCP Servers](#mcp-servers)
6. [Advanced Settings](#advanced-settings)

---

## 📄 Configuration File

Location: `config/llm-manager.php`

Publish with:
```bash
php artisan vendor:publish --tag=llm-config
```

### Default Configuration

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('LLM_DEFAULT_PROVIDER', 'ollama'),
    'default_model' => env('LLM_DEFAULT_MODEL', 'llama3.2'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'timeout' => 60,
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'timeout' => 30,
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'timeout' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('LLM_CACHE_ENABLED', true),
        'ttl' => env('LLM_CACHE_TTL', 3600), // seconds
        'driver' => env('LLM_CACHE_DRIVER', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Budget Controls
    |--------------------------------------------------------------------------
    */
    'budget' => [
        'enabled' => true,
        'monthly_limit' => env('LLM_BUDGET_MONTHLY_LIMIT', 100.00),
        'daily_limit' => env('LLM_BUDGET_DAILY_LIMIT', 10.00),
        'alert_threshold' => env('LLM_BUDGET_ALERT_THRESHOLD', 80), // %
        'currency' => 'USD',
    ],

    /*
    |--------------------------------------------------------------------------
    | RAG System
    |--------------------------------------------------------------------------
    */
    'rag' => [
        'enabled' => env('LLM_RAG_ENABLED', true),
        'embedding_model' => env('LLM_RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chunking' => [
            'strategy' => 'semantic', // 'semantic' or 'fixed'
            'size' => 1000,
            'overlap' => 200,
        ],
        'retrieval' => [
            'top_k' => 5,
            'similarity_threshold' => 0.7,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Servers
    |--------------------------------------------------------------------------
    */
    'mcp' => [
        'enabled' => env('LLM_MCP_ENABLED', true),
        'auto_start' => env('LLM_MCP_AUTO_START', false),
        'bundled_servers' => [
            'filesystem',
            'database',
            'laravel',
            'code-generation',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => true,
        'store_requests' => true,
        'store_responses' => true,
        'log_level' => 'info',
    ],
];
```

---

## 🔧 Provider Configurations

### Ollama (Local LLM)

**Setup Ollama:**
```bash
# Install Ollama
curl -fsSL https://ollama.com/install.sh | sh

# Pull model
ollama pull llama3.2
ollama pull codellama
ollama pull mistral

# Start server
ollama serve
```

**Create Configuration:**

Via Admin UI: `/admin/llm/configurations/create`

```php
LLMConfiguration::create([
    'name' => 'Ollama Llama 3.2',
    'slug' => 'ollama-llama3',
    'provider' => 'ollama',
    'model' => 'llama3.2',
    'base_url' => 'http://localhost:11434',
    'temperature' => 0.7,
    'max_tokens' => 2000,
    'top_p' => 0.9,
    'frequency_penalty' => 0.0,
    'presence_penalty' => 0.0,
    'is_active' => true,
    'is_default' => true,
    'extension_slug' => null, // Global
]);
```

**Available Models:**
- `llama3.2` - General purpose (4GB)
- `llama3.2:70b` - Large model (40GB)
- `codellama` - Code generation
- `mistral` - Fast and efficient
- `phi3` - Small but capable (2GB)

---

### OpenAI

**Get API Key:** https://platform.openai.com/api-keys

**Environment:**
```bash
OPENAI_API_KEY=sk-proj-...
OPENAI_ORGANIZATION=org-...  # Optional
```

**Create Configuration:**

```php
LLMConfiguration::create([
    'name' => 'OpenAI GPT-4o',
    'slug' => 'openai-gpt4o',
    'provider' => 'openai',
    'model' => 'gpt-4o',
    'api_key' => env('OPENAI_API_KEY'),
    'temperature' => 0.3,
    'max_tokens' => 4000,
    'top_p' => 1.0,
    'is_active' => true,
    'is_default' => false,
    'extension_slug' => 'bugs', // Specific to extension
]);
```

**Available Models:**
- `gpt-4o` - Most capable (128K context)
- `gpt-4o-mini` - Fast and cheap (128K context)
- `gpt-4-turbo` - Previous flagship (128K context)
- `gpt-3.5-turbo` - Legacy, cheap (16K context)

**Pricing (per 1M tokens):**
| Model | Input | Output |
|-------|-------|--------|
| gpt-4o | $2.50 | $10.00 |
| gpt-4o-mini | $0.15 | $0.60 |
| gpt-3.5-turbo | $0.50 | $1.50 |

---

### Anthropic (Claude)

**Get API Key:** https://console.anthropic.com/settings/keys

**Environment:**
```bash
ANTHROPIC_API_KEY=sk-ant-...
```

**Create Configuration:**

```php
LLMConfiguration::create([
    'name' => 'Claude 3.5 Sonnet',
    'slug' => 'claude-sonnet',
    'provider' => 'anthropic',
    'model' => 'claude-3-5-sonnet-20241022',
    'api_key' => env('ANTHROPIC_API_KEY'),
    'temperature' => 0.7,
    'max_tokens' => 8000,
    'top_p' => 1.0,
    'is_active' => true,
    'is_default' => false,
]);
```

**Available Models:**
- `claude-3-5-sonnet-20241022` - Most intelligent (200K context)
- `claude-3-5-haiku-20241022` - Fastest (200K context)
- `claude-3-opus-20240229` - Previous flagship (200K context)

**Pricing (per 1M tokens):**
| Model | Input | Output |
|-------|-------|--------|
| Claude 3.5 Sonnet | $3.00 | $15.00 |
| Claude 3.5 Haiku | $0.80 | $4.00 |
| Claude 3 Opus | $15.00 | $75.00 |

---

### Custom Provider

For any HTTP-based LLM API:

```php
LLMConfiguration::create([
    'name' => 'Custom LLM',
    'slug' => 'custom-llm',
    'provider' => 'custom',
    'model' => 'custom-model-v1',
    'base_url' => 'https://api.custom-llm.com/v1',
    'api_key' => 'your-api-key',
    'headers' => [
        'X-Custom-Header' => 'value',
    ],
    'temperature' => 0.7,
    'max_tokens' => 2000,
    'is_active' => true,
]);
```

---

## 💰 Budget Controls

### Enable Budget Tracking

```php
// config/llm-manager.php
'budget' => [
    'enabled' => true,
    'monthly_limit' => 100.00, // USD
    'daily_limit' => 10.00,    // USD
    'alert_threshold' => 80,   // % (send alert at 80% usage)
    'currency' => 'USD',
],
```

### Set Per-Extension Budgets

```php
use Bithoven\LLMManager\Models\LLMBudgetControl;

LLMBudgetControl::create([
    'extension_slug' => 'bugs',
    'budget_type' => 'monthly',
    'budget_limit' => 50.00,
    'alert_threshold' => 80,
    'is_active' => true,
]);
```

### Cost Tracking

Costs are automatically logged in `llm_usage_logs`:

```php
use Bithoven\LLMManager\Models\LLMUsageLog;

// Get total cost for extension
$totalCost = LLMUsageLog::where('extension_slug', 'bugs')
    ->whereBetween('created_at', [now()->startOfMonth(), now()])
    ->sum('cost_usd');

// Get usage by model
$usage = LLMUsageLog::where('extension_slug', 'bugs')
    ->selectRaw('model, SUM(cost_usd) as total_cost, COUNT(*) as requests')
    ->groupBy('model')
    ->get();
```

---

## 📚 RAG System

### Enable RAG

```php
// config/llm-manager.php
'rag' => [
    'enabled' => true,
    'embedding_model' => 'text-embedding-3-small',
    'chunking' => [
        'strategy' => 'semantic',  // or 'fixed'
        'size' => 1000,            // characters
        'overlap' => 200,          // characters
    ],
    'retrieval' => [
        'top_k' => 5,              // return top 5 chunks
        'similarity_threshold' => 0.7,  // minimum similarity
    ],
],
```

### Chunking Strategies

**Semantic Chunking (Recommended):**
- Splits by paragraphs and sentences
- Preserves context
- Better for long documents

**Fixed Chunking:**
- Fixed-size chunks with overlap
- Faster processing
- Better for uniform content

### Embedding Models

**OpenAI:**
- `text-embedding-3-small` - $0.02 per 1M tokens (1536 dims)
- `text-embedding-3-large` - $0.13 per 1M tokens (3072 dims)
- `text-embedding-ada-002` - $0.10 per 1M tokens (1536 dims, legacy)

**Usage:**
```bash
# Index all documents
php artisan llm-manager:index-documents

# Generate embeddings
php artisan llm-manager:generate-embeddings
```

---

## 🛠️ MCP Servers

### Bundled Servers

Located in: `vendor/bithoven/llm-manager/mcp-servers/`

**1. Filesystem** (`@modelcontextprotocol/server-filesystem`)
```json
{
  "name": "filesystem",
  "command": "npx",
  "args": ["-y", "@modelcontextprotocol/server-filesystem", "/allowed/path"]
}
```

**2. Database** (`@modelcontextprotocol/server-database`)
```json
{
  "name": "database",
  "command": "uvx",
  "args": ["mcp-server-database", "--db-path", "database.sqlite"]
}
```

**3. Laravel** (`@bithoven/laravel-mcp-server`)
```json
{
  "name": "laravel",
  "command": "node",
  "args": ["vendor/bithoven/llm-manager/mcp-servers/laravel/index.js"]
}
```

**4. Code Generation** (`@bithoven/code-generation-mcp`)
```json
{
  "name": "code-generation",
  "command": "node",
  "args": ["vendor/bithoven/llm-manager/mcp-servers/code-gen/index.js"]
}
```

### External Servers

Add via Admin UI or CLI:

```bash
php artisan llm-manager:mcp:add github "npx -y @modelcontextprotocol/server-github"
```

Or programmatically:

```php
use Bithoven\LLMManager\Models\LLMMCPConnector;

LLMMCPConnector::create([
    'name' => 'GitHub',
    'slug' => 'github',
    'command' => 'npx',
    'args' => ['-y', '@modelcontextprotocol/server-github'],
    'env' => [
        'GITHUB_TOKEN' => env('GITHUB_TOKEN'),
    ],
    'is_active' => true,
]);
```

### Auto-Start on Boot

```php
// config/llm-manager.php
'mcp' => [
    'auto_start' => true,  // Start all enabled servers
],
```

Or start manually:
```bash
# Start all
php artisan llm-manager:mcp:start

# Start specific
php artisan llm-manager:mcp:start filesystem
```

---

## 🔐 Security Settings

### API Key Management

**Never commit API keys!**

```bash
# .env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GITHUB_TOKEN=ghp_...

# .gitignore
.env
.env.local
.env.*.local
```

### Tool Whitelisting

```php
// config/llm-manager.php
'tools' => [
    'whitelisting' => [
        'enabled' => true,
        'allowed_tools' => [
            'filesystem.read',
            'database.query',
            'laravel.artisan',
        ],
        'blocked_tools' => [
            'filesystem.delete',
            'database.drop',
        ],
    ],
],
```

### Rate Limiting

```php
// config/llm-manager.php
'rate_limiting' => [
    'enabled' => true,
    'max_requests_per_minute' => 60,
    'max_tokens_per_minute' => 100000,
],
```

---

## 🎯 Advanced Settings

### Conversation Memory

```php
'conversations' => [
    'max_messages' => 100,       // Max messages per session
    'memory_window' => 10,       // Last N messages for context
    'auto_summarize' => true,    // Summarize old messages
    'ttl_days' => 30,            // Delete after 30 days
],
```

### Workflow Engine

```php
'workflows' => [
    'enabled' => true,
    'max_steps' => 50,           // Max steps per workflow
    'timeout' => 300,            // 5 minutes
    'retry_failed_steps' => 3,   // Retry count
],
```

### Performance Tuning

```php
'performance' => [
    'connection_pool_size' => 10,
    'request_timeout' => 30,
    'max_retries' => 3,
    'retry_delay' => 1000,  // ms
],
```

---

## 📊 Monitoring

### Enable Logging

```php
// config/llm-manager.php
'logging' => [
    'enabled' => true,
    'store_requests' => true,    // Log prompts
    'store_responses' => true,   // Log completions
    'log_level' => 'info',       // debug|info|warning|error
    'channel' => 'llm',          // Laravel log channel
],
```

### View Logs

```bash
# Via Admin UI
http://your-app.test/admin/llm/statistics

# Via database
php artisan tinker
```

```php
use Bithoven\LLMManager\Models\LLMUsageLog;

LLMUsageLog::latest()->limit(10)->get();
```

---

## ✅ Recommended Setup

### Development Environment

```bash
# .env.local
LLM_DEFAULT_PROVIDER=ollama
LLM_DEFAULT_MODEL=llama3.2
OLLAMA_BASE_URL=http://localhost:11434
LLM_BUDGET_MONTHLY_LIMIT=10.00
LLM_CACHE_ENABLED=false
LLM_MCP_AUTO_START=true
```

### Production Environment

```bash
# .env.production
LLM_DEFAULT_PROVIDER=openai
LLM_DEFAULT_MODEL=gpt-4o-mini
OPENAI_API_KEY=sk-proj-...
LLM_BUDGET_MONTHLY_LIMIT=500.00
LLM_CACHE_ENABLED=true
LLM_CACHE_DRIVER=redis
LLM_MCP_AUTO_START=false
```

---

**Last Updated:** 18 de noviembre de 2025  
**Version:** 0.1.0
