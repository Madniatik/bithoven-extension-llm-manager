# 📖 LLM Manager - Frequently Asked Questions (FAQ)

**Version:** 0.1.0  
**Last Updated:** 21 de noviembre de 2025

---

## 📑 Table of Contents

1. [General Questions](#general-questions)
2. [Installation & Setup](#installation--setup)
3. [Providers & Models](#providers--models)
4. [Knowledge Base (RAG)](#knowledge-base-rag)
5. [Tools & MCP Servers](#tools--mcp-servers)
6. [Troubleshooting](#troubleshooting)
7. [Performance & Costs](#performance--costs)

---

## General Questions

### What is LLM Manager?

LLM Manager is a comprehensive Laravel extension that provides a unified interface for managing multiple Large Language Models (LLMs). It supports local models (Ollama), cloud providers (OpenAI, Anthropic), and includes advanced features like RAG, tool calling, and conversational AI.

### What versions are supported?

- **Laravel:** 11.x
- **PHP:** 8.2+
- **Node.js:** 18+ (for MCP servers)
- **Python:** 3.9+ (optional, for some MCP servers)

### Is this extension free?

Yes, the LLM Manager extension is free. However, cloud LLM providers (OpenAI, Anthropic) charge for API usage. Local models via Ollama are completely free.

### Can I use multiple LLM providers?

Absolutely! You can configure multiple providers and switch between them programmatically or via the admin UI.

---

## Installation & Setup

### How do I install LLM Manager?

**Via Extension Manager (Recommended):**
```bash
php artisan bithoven:extension:install llm-manager
```

**Manual Installation:**
```bash
composer require bithoven/llm-manager
php artisan migrate
php artisan llm-manager:install
```

See [INSTALLATION.md](INSTALLATION.md) for complete instructions.

### Do I need Ollama installed?

No, Ollama is optional. You can use cloud providers (OpenAI, Anthropic) without Ollama. However, Ollama is recommended for local, cost-free LLM usage.

**Install Ollama:**
```bash
curl -fsSL https://ollama.com/install.sh | sh
ollama pull llama3.2
ollama serve
```

### How do I configure my first provider?

1. Navigate to `/admin/llm/configurations`
2. Click **"Create Configuration"**
3. Fill in provider details (API key, model, etc.)
4. Test connection
5. Save

Or use `.env`:
```bash
OPENAI_API_KEY=sk-proj-...
LLM_DEFAULT_PROVIDER=openai
LLM_DEFAULT_MODEL=gpt-4o-mini
```

### Where do I get API keys?

- **OpenAI:** https://platform.openai.com/api-keys
- **Anthropic:** https://console.anthropic.com/settings/keys
- **Groq:** https://console.groq.com/keys
- **Google:** https://makersuite.google.com/app/apikey

---

## Providers & Models

### Which provider should I use?

**Development:**
- **Ollama (llama3.2)** - Free, local, no API limits

**Production (Budget):**
- **OpenAI (gpt-4o-mini)** - $0.15/$0.60 per 1M tokens
- **Anthropic (claude-3-5-haiku)** - $0.80/$4.00 per 1M tokens

**Production (Quality):**
- **OpenAI (gpt-4o)** - $2.50/$10.00 per 1M tokens
- **Anthropic (claude-3-5-sonnet)** - $3.00/$15.00 per 1M tokens

### Can I use local models only?

Yes! Ollama supports many open-source models:
- `llama3.2` (4GB) - General purpose
- `llama3.2:70b` (40GB) - High quality
- `codellama` - Code generation
- `mistral` - Fast and efficient
- `phi3` (2GB) - Small but capable

No API keys, no costs, complete privacy.

### How do I switch providers dynamically?

```php
use Bithoven\LLMManager\Facades\LLM;

// Development: Use Ollama
$response = LLM::provider('ollama')
    ->model('llama3.2')
    ->generate('Hello!');

// Production: Use OpenAI
$response = LLM::provider('openai')
    ->model('gpt-4o')
    ->generate('Hello!');
```

### What if a provider is down?

Implement fallback logic:

```php
try {
    $response = LLM::provider('openai')->generate($prompt);
} catch (ProviderException $e) {
    // Fallback to Ollama
    $response = LLM::provider('ollama')->generate($prompt);
}
```

---

## Knowledge Base (RAG)

### What is RAG?

RAG (Retrieval-Augmented Generation) allows LLMs to access your documentation, guides, and knowledge articles. The LLM can search relevant information before generating responses, resulting in more accurate, context-aware answers.

### How do I upload documents?

1. Navigate to `/admin/llm/knowledge-base`
2. Click **"Add Document"**
3. Fill in title, type, content
4. Enable **"Auto-Index"**
5. Save

Or programmatically:
```php
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

$doc = LLMDocumentKnowledgeBase::create([
    'title' => 'API Documentation',
    'content' => $markdown,
    'document_type' => 'documentation',
    'auto_index' => true,
]);
```

### What is "chunking"?

Large documents are split into smaller chunks (1000 characters by default) for efficient embedding and retrieval. Chunks overlap (200 characters) to preserve context.

### Do embeddings cost money?

Only if using OpenAI embeddings:
- `text-embedding-3-small` - $0.02 per 1M tokens
- `text-embedding-3-large` - $0.13 per 1M tokens

**Example:** 100 documents × 5000 words = 500K tokens ≈ $0.01 (small model)

### How do I search the knowledge base?

```php
$results = LLMDocumentKnowledgeBase::searchSimilar(
    'How do I authenticate?', 
    'my-app', 
    5
);
```

See [USAGE-GUIDE.md](USAGE-GUIDE.md#knowledge-base-rag) for complete examples.

### Can I use RAG without OpenAI?

Currently, embeddings require OpenAI API. Support for local embedding models (via Ollama) is planned for future versions.

---

## Tools & MCP Servers

### What are Tool Definitions?

Tool Definitions allow LLMs to call custom PHP functions to perform actions or retrieve data. Examples:
- Get weather
- Query database
- Send email
- Calculate math

### How do I create a tool?

1. **Create Tool Definition** (Admin UI or code)
2. **Implement Handler Class:**

```php
namespace App\LLM\Tools;

class WeatherTool
{
    public function execute(array $parameters): array
    {
        // Fetch weather data
        return [
            'success' => true,
            'temperature' => 22,
            'conditions' => 'Sunny',
        ];
    }
}
```

See [USAGE-GUIDE.md](USAGE-GUIDE.md#tool-definitions) for complete guide.

### What are MCP Servers?

MCP (Model Context Protocol) servers provide standardized interfaces for LLMs to interact with external systems (filesystems, databases, APIs, etc.).

**Bundled Servers:**
- Filesystem - Read/write files
- Database - Query databases
- Laravel - Run Artisan commands
- Code Generation - Generate code

### How do I start MCP servers?

```bash
# Start all enabled servers
php artisan llm-manager:mcp:start

# Start specific server
php artisan llm-manager:mcp:start filesystem

# Auto-start on boot (config)
'mcp' => ['auto_start' => true]
```

### Are MCP servers required?

No, MCP servers are optional. You can use LLM Manager without them. They're useful for advanced tool calling and integration features.

---

## Troubleshooting

### LLM request fails with "Provider not found"

**Solution:**
1. Check provider is configured: `/admin/llm/configurations`
2. Verify provider is active: `is_active = true`
3. Test connection: Click "Test" button in config

### Ollama models return "connection refused"

**Solutions:**
1. Verify Ollama is running:
   ```bash
   curl http://localhost:11434/api/tags
   ```
2. Start Ollama:
   ```bash
   ollama serve
   ```
3. Check base URL in config:
   ```php
   'base_url' => 'http://localhost:11434'
   ```

### OpenAI returns "invalid API key"

**Solutions:**
1. Verify key is correct:
   ```bash
   echo $OPENAI_API_KEY
   ```
2. Check key hasn't expired
3. Regenerate key: https://platform.openai.com/api-keys
4. Clear config cache:
   ```bash
   php artisan config:clear
   ```

### Knowledge Base search returns no results

**Solutions:**
1. Verify document is indexed:
   ```php
   $doc = LLMDocumentKnowledgeBase::find(1);
   echo $doc->is_indexed ? 'Yes' : 'No';
   ```
2. Re-index document:
   ```php
   $doc->index();
   ```
3. Check embedding configuration:
   ```php
   config('llm-manager.rag.embedding_model')
   ```

### Conversations lose context

**Solutions:**
1. Increase context window:
   ```php
   'conversations' => ['memory_window' => 20]
   ```
2. Check message retrieval:
   ```php
   $session->messages()->count(); // Should have messages
   ```
3. Verify context is passed to LLM:
   ```php
   $context = $session->messages()
       ->latest()->take(10)
       ->get()->toArray();
   ```

### Database migration fails

**Solutions:**
1. Check database connection:
   ```bash
   php artisan db:show
   ```
2. Run migrations individually:
   ```bash
   php artisan migrate --path=vendor/bithoven/llm-manager/database/migrations
   ```
3. Check for conflicting tables:
   ```sql
   SHOW TABLES LIKE 'llm_%';
   ```

---

## Performance & Costs

### How can I reduce costs?

1. **Use cheaper models:**
   - Development: Ollama (free)
   - Production: gpt-4o-mini instead of gpt-4o

2. **Optimize prompts:**
   - Shorter prompts = fewer tokens
   - Use templates for consistency

3. **Enable caching:**
   ```php
   'cache' => ['enabled' => true, 'ttl' => 3600]
   ```

4. **Set budget limits:**
   ```php
   'budget' => ['monthly_limit' => 100.00]
   ```

### How do I monitor usage?

**Admin UI:**
```
/admin/llm/statistics
```

**Programmatically:**
```php
use Bithoven\LLMManager\Models\LLMUsageLog;

$totalCost = LLMUsageLog::whereBetween('created_at', [$start, $end])
    ->sum('cost');

$totalTokens = LLMUsageLog::sum('total_tokens');
```

### What's the average cost per request?

**Depends on model and prompt length:**

| Model | Avg Prompt | Avg Response | Cost |
|-------|-----------|--------------|------|
| gpt-4o-mini | 500 tokens | 200 tokens | $0.0002 |
| gpt-4o | 500 tokens | 200 tokens | $0.0032 |
| claude-3-5-haiku | 500 tokens | 200 tokens | $0.0012 |
| Ollama (local) | Any | Any | $0.00 |

**Example:** 10,000 requests/month with gpt-4o-mini ≈ $2.00

### How do I improve response speed?

1. **Use faster models:**
   - OpenAI: gpt-4o-mini (fast)
   - Anthropic: claude-3-5-haiku (fast)
   - Ollama: phi3 or mistral (fast, local)

2. **Reduce max_tokens:**
   ```php
   LLM::maxTokens(500)->generate($prompt);
   ```

3. **Enable caching:**
   ```php
   'cache' => ['enabled' => true, 'driver' => 'redis']
   ```

4. **Use async requests (future):**
   ```php
   // Planned for future versions
   LLM::async()->generate($prompt);
   ```

### Can I set usage alerts?

Yes! Configure budget alerts:

```php
'budget' => [
    'alert_threshold' => 80, // Alert at 80% usage
]
```

Alerts are sent via email when threshold is reached.

---

## Need More Help?

- **Documentation:** [README.md](../README.md)
- **Usage Guide:** [USAGE-GUIDE.md](USAGE-GUIDE.md)
- **API Reference:** [API-REFERENCE.md](API-REFERENCE.md)
- **Examples:** [EXAMPLES.md](EXAMPLES.md)
- **Support:** support@bithoven.com
- **Issues:** https://github.com/bithoven/llm-manager/issues

---

**Happy coding!** 🚀
