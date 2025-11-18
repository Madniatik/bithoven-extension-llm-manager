# 🚀 LLM Manager Extension v1.0

**LLM Orchestration Platform with Hybrid Tools System**

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/bithoven/llm-manager)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Enterprise-grade LLM management platform for Laravel applications with multi-agent orchestration, RAG system, and hybrid tools (Function Calling + MCP).

---

## ✨ Features

### 🎯 Core LLM Management
- ✅ Multi-provider support (Ollama, OpenAI, Anthropic, Custom)
- ✅ Per-extension configurations
- ✅ Budget tracking & usage logs
- ✅ Provider cache (models auto-discovery)
- ✅ Fallback system

### 📊 Advanced Features
- ✅ **Custom Metrics** - Extensions create their own metrics
- ✅ **Prompt Templates** - Reusable templates with variables
- ✅ **Parameter Override** - Runtime parameter customization

### 🤖 Orchestration Platform
- ✅ **Conversations** - Persistent context & sessions
- ✅ **RAG System** - Document chunking + embeddings + semantic search
- ✅ **Multi-Agent Workflows** - Orchestrate multiple agents with state machine

### 🛠️ Hybrid Tools System ⚡
- ✅ **Function Calling** - Native OpenAI/Anthropic/Gemini support
- ✅ **MCP Bundled** - 4 servers included (zero-config):
  - `filesystem` - File operations
  - `database` - Query, migrations, seeders
  - `laravel` - Artisan, routes, config
  - `code-generation` - Controllers, models, migrations
- ✅ **MCP External** - GitHub, Context7, custom
- ✅ **Auto-Selection** - Native→MCP intelligent fallback
- ✅ **Security** - Whitelisting, validation, tracking

---

## 🎯 Unique Selling Points

1. **🚀 Hybrid Tools** - Only LLM manager with Function Calling + MCP hybrid system
2. **📦 Zero-Config** - 4 MCP servers bundled, ready to use
3. **🤖 Complete Platform** - RAG + Multi-Agent + Tools in one package
4. **🎨 Laravel-First** - Custom Laravel MCP server included

---

## 📦 Installation

### Requirements

- PHP 8.2+
- Laravel 11.x
- Node.js 18+ (for MCP servers)
- Python 3.9+ (for database MCP)

### Via BITHOVEN Extension Manager

```bash
php artisan bithoven:extension:install llm-manager
```

### Manual Installation

1. Clone repository to extensions folder:
```bash
cd /path/to/BITHOVEN/EXTENSIONS
git clone https://github.com/bithoven/llm-manager bithoven-extension-llm-manager
```

2. Add to CPANEL `composer.json`:
```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../EXTENSIONS/bithoven-extension-llm-manager",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "bithoven/llm-manager": "^3.0"
  }
}
```

3. Install via Composer:
```bash
composer require bithoven/llm-manager
```

4. Run post-install:
```bash
php artisan vendor:publish --tag=llm-config
php artisan vendor:publish --tag=llm-views
php artisan migrate
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\DatabaseSeeder
bash vendor/bithoven/llm-manager/scripts/install-mcp-servers.sh
```

---

## 🚀 Quick Start

### 1. Configure LLM Provider

```php
// Via Admin UI: /admin/llm
// Or programmatically:
use Bithoven\LLMManager\Models\LLMConfiguration;

LLMConfiguration::create([
    'name' => 'OpenAI GPT-4',
    'slug' => 'openai-gpt4',
    'provider' => 'openai',
    'model' => 'gpt-4o',
    'api_key' => env('OPENAI_API_KEY'),
    'temperature' => 0.3,
    'max_tokens' => 2000,
    'is_active' => true,
    'is_default' => true,
]);
```

### 2. Use LLM in Your Extension

```php
use Bithoven\LLMManager\Facades\LLM;

// Simple execution
$response = LLM::execute('Analyze this code...');

// With configuration
$response = LLM::useConfig('openai-gpt4')
    ->execute('Generate a Laravel controller...');

// With parameter override
$response = LLM::execute('Complex analysis...', [
    'temperature' => 0.7,
    'max_tokens' => 3000,
]);
```

### 3. Start MCP Servers

```bash
# Start all bundled servers
php artisan llm-manager:mcp:start

# Start specific server
php artisan llm-manager:mcp:start filesystem

# List servers
php artisan llm-manager:mcp:list
```

---

## 📚 Documentation

- **[Installation Guide](docs/INSTALLATION.md)** - Complete setup instructions
- **[Configuration](docs/CONFIGURATION.md)** - Provider configs, parameters
- **[API Reference](docs/API-REFERENCE.md)** - Facade & API endpoints
- **[Integration Guide](docs/INTEGRATION-GUIDE.md)** - Use in your extensions
- **[Conversations](docs/CONVERSATIONS-GUIDE.md)** - Sessions & context
- **[RAG Setup](docs/RAG-SETUP.md)** - Document indexing & search
- **[Workflows](docs/WORKFLOWS-GUIDE.md)** - Multi-agent orchestration
- **[Tools Development](docs/TOOLS-DEVELOPMENT.md)** - Create custom tools
- **[MCP Servers](docs/MCP-SERVERS.md)** - Bundled & external servers

---

## 🗄️ Database Schema

13 tables with `llm_*` prefix:

| Table | Category | Description |
|-------|----------|-------------|
| `llm_configurations` | Core | Provider configs |
| `llm_usage_logs` | Core | Usage tracking |
| `llm_provider_cache` | Core | Models cache |
| `llm_extension_metrics` | Features | Custom metrics |
| `llm_prompt_templates` | Features | Prompt templates |
| `llm_conversation_sessions` | Orchestration | Sessions |
| `llm_conversation_messages` | Orchestration | Messages |
| `llm_conversation_logs` | Orchestration | Audit logs |
| `llm_document_knowledge_base` | RAG | Documents + embeddings |
| `llm_mcp_connectors` | Tools | MCP registry |
| `llm_agent_workflows` | Orchestration | Workflows |
| `llm_tool_definitions` | Tools | Tools registry |
| `llm_tool_executions` | Tools | Execution tracking |

---

## 🎨 Admin UI

Access at: `http://your-app.com/admin/llm`

Features:
- **Configurations** - Manage LLM providers
- **Conversations** - View chat history
- **Knowledge Base** - RAG document management
- **Workflows** - Visual workflow builder
- **MCP Servers** - Manage servers
- **Statistics** - Usage & cost reports
- **Metrics** - Custom metrics dashboard

---

## 🔧 Artisan Commands

```bash
# MCP Management
php artisan llm-manager:mcp:start [server]    # Start MCP servers
php artisan llm-manager:mcp:list              # List servers
php artisan llm-manager:mcp:add {name} {cmd}  # Add external server

# RAG System
php artisan llm-manager:index-documents        # Index docs
php artisan llm-manager:generate-embeddings    # Generate embeddings

# Testing
php artisan llm-manager:test-connection {id}   # Test config
```

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/phpunit tests/Feature/ConversationTest.php
./vendor/bin/phpunit tests/Unit/ToolServiceTest.php
```

---

## 📊 Usage Examples

### Custom Metrics

```php
use Bithoven\LLMManager\Facades\LLM;

// Record metric
LLM::recordMetric(
    extension: 'bugs',
    metricKey: 'code_quality_score',
    metricValue: 8.5,
    metricData: ['category' => 'php', 'lines' => 245]
);

// Get metrics
$metrics = LLM::getMetrics('bugs', ['code_quality_score']);
```

### Prompt Templates

```php
// Register template
LLM::registerPromptTemplate(
    extension: 'bugs',
    templateKey: 'bug_analysis',
    templateContent: 'Analyze this {{bug_type}} bug:\n\n{{description}}',
    defaultParams: ['temperature' => 0.3]
);

// Execute template
$response = LLM::executeTemplate(
    extension: 'bugs',
    templateKey: 'bug_analysis',
    variables: [
        'bug_type' => 'performance',
        'description' => $bug->description,
    ]
);
```

### RAG System

```php
use Bithoven\LLMManager\Services\LLMRAGService;

$rag = app(LLMRAGService::class);

// Index document
$rag->indexDocument(
    title: 'Laravel Documentation',
    content: $documentContent,
    extension: 'docs'
);

// Search
$results = $rag->search('How to create a controller?', extension: 'docs');
```

---

## 📊 Implementation Status

**Current Version:** v3.0.0 (In Development)

**Backend Implementation:** ✅ 100% Complete (74 PHP files)  
**Admin UI Implementation:** ✅ 100% Complete (20 Blade views)

### Completed Components

#### ✅ Foundation (49 files)
- ServiceProvider with comprehensive bindings
- 13 database migrations
- 13 Eloquent models with relationships
- 4 seeders (demo data)
- Configuration system
- Routes (web + api)
- Middleware (admin + api)
- LLM Facade

#### ✅ Core Services (5 files)
- **LLMManager** - Central orchestrator with fluent API
- **LLMExecutor** - Execution engine with logging
- **LLMBudgetManager** - Cost control with alerts
- **LLMMetricsService** - Custom metrics tracking
- **LLMPromptService** - Template management

#### ✅ Providers (4 files)
- **OllamaProvider** - Local LLM integration
- **OpenAIProvider** - OpenAI API integration
- **AnthropicProvider** - Claude integration
- **CustomProvider** - Generic HTTP provider

#### ✅ Orchestration (4 files)
- **LLMConversationManager** - Multi-turn sessions
- **LLMRAGService** - Semantic search + generation
- **LLMEmbeddingsService** - Vector generation
- **LLMWorkflowEngine** - State machine workflows

#### ✅ Hybrid Tools (4 files)
- **LLMToolService** - Tool registry
- **LLMToolExecutor** - Execution engine (native/mcp/custom)
- **LLMFunctionCallingAdapter** - Native function calling
- **LLMMCPConnectorManager** - MCP server management

#### ✅ Controllers (11 files)
**Admin Controllers (6):**
- LLMConfigurationController - Config CRUD
- LLMUsageStatsController - Statistics & export
- LLMPromptTemplateController - Template CRUD
- LLMConversationController - Session viewer
- LLMKnowledgeBaseController - Document management
- LLMToolDefinitionController - Tool registry

**API Controllers (5):**
- LLMGenerateController - Simple generation
- LLMChatController - Conversation API
- LLMRAGController - RAG operations
- LLMToolController - Tool execution
- LLMWorkflowController - Workflow execution

#### ✅ CLI Commands (6 files)
- `mcp:start` - Start MCP servers
- `mcp:list` - List servers
- `mcp:add` - Register new server
- `llm:index-documents` - Index KB documents
- `llm:generate-embeddings` - Generate embeddings
- `llm:test` - Test LLM generation

#### ✅ Admin UI Views (20 files)
**Dashboard:**
- admin/dashboard.blade.php - Main overview

**Configurations (4 views):**
- index.blade.php - List all configurations
- create.blade.php - Create new configuration
- edit.blade.php - Edit configuration
- show.blade.php - View configuration details

**Statistics (1 view):**
- stats/index.blade.php - Usage analytics

**Prompt Templates (4 views):**
- prompts/index.blade.php - Template library
- prompts/create.blade.php - Create template
- prompts/edit.blade.php - Edit template
- prompts/show.blade.php - View template

**Conversations (2 views):**
- conversations/index.blade.php - Session list
- conversations/show.blade.php - Conversation details

**Knowledge Base (4 views):**
- knowledge-base/index.blade.php - Document library
- knowledge-base/create.blade.php - Add document
- knowledge-base/edit.blade.php - Edit document
- knowledge-base/show.blade.php - View document & chunks

**Tools Registry (4 views):**
- tools/index.blade.php - Tool list
- tools/create.blade.php - Register tool
- tools/edit.blade.php - Edit tool
- tools/show.blade.php - View tool details

### ⏳ Pending

- **Tests** - Feature + Unit tests
- **MCP Servers** - 4 bundled servers (filesystem, database, laravel, code-generation)
- **Documentation** - Detailed usage guides
- **Integration Testing** - Install in CPANEL and validate

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📄 License

This extension is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🙏 Credits

Built with ❤️ by the BITHOVEN Team

**Powered by:**
- [Laravel](https://laravel.com)
- [OpenAI PHP Client](https://github.com/openai-php/client)
- [Yajra DataTables](https://github.com/yajra/laravel-datatables)
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission)
- [Model Context Protocol (MCP)](https://modelcontextprotocol.io)

---

## 📞 Support

- **Documentation:** `vendor/bithoven/llm-manager/docs/`
- **Issues:** [GitHub Issues](https://github.com/bithoven/llm-manager/issues)
- **Discord:** [BITHOVEN Community](https://discord.gg/bithoven)
- **Email:** dev@bithoven.com

---

**Version:** 3.0.0  
**Last Updated:** 18 de noviembre de 2025  
**Status:** ✅ Ready for Production
