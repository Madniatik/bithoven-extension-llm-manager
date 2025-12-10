# 🚀 LLM Manager Extension v1.0.7

**Multi-Provider LLM Orchestration Platform**

[![Version](https://img.shields.io/badge/version-1.0.7-blue.svg)](https://github.com/Madniatik/bithoven-extension-llm-manager)
[![Laravel](https://img.shields.io/badge/Laravel-11+-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Enterprise-grade LLM management platform for Laravel applications with real-time streaming, multi-agent orchestration, RAG system, database-driven activity logs, comprehensive admin UI, and granular chat configuration system.

**✨ v1.0.7 Released:** Chat Workspace Configuration System (23 docs, 3376+ lines), Monitor Export (CSV/JSON/SQL), UX Enhancements (21 items), Database Activity Logs, Settings Panel with DB Persistence - Production Ready (10 Dec 2025)

---

## ✨ Features

### 🎯 Core LLM Management
- ✅ **Multi-Provider Support** - Ollama, OpenAI, Anthropic, OpenRouter, Custom endpoints
- ✅ **Real-Time Streaming** - SSE-based streaming with live token counting and cost tracking
- ✅ **Per-Extension Configurations** - Isolated configs for each extension/module
- ✅ **Budget Tracking** - Cost monitoring with usage logs and alerts
- ✅ **Provider Cache** - Auto-discovery of available models
- ✅ **Fallback System** - Automatic failover between configurations

### 📊 Advanced Features
- ✅ **Custom Metrics** - Extensions create domain-specific metrics with JSON metadata
- ✅ **Prompt Templates** - Reusable templates with variable substitution (`{{var}}`)
- ✅ **Parameter Override** - Runtime customization of temperature, max_tokens, etc.
- ✅ **Permissions Protocol v2.0** - Granular role-based access control (12 permissions)
- ✅ **Monitor Export** - Export Activity Logs in 3 formats (CSV/JSON/SQL) with session filtering (NEW v1.0.7)

### 🤖 Orchestration Platform
- ✅ **Conversations** - Persistent multi-turn sessions with context management
- ✅ **RAG System** - Document chunking + embeddings + semantic search
- ✅ **Multi-Agent Workflows** - State machine orchestration (planned v1.2.0)
- ✅ **Activity Monitoring** - Database-driven execution logs with auto-refresh, cross-device persistence, and unlimited history (NEW v1.0.7)

### 🛠️ Hybrid Tools System (Planned v1.2.0)
- ⏳ **Function Calling** - Native OpenAI/Anthropic/Gemini support
- ⏳ **MCP Bundled** - 4 servers (filesystem, database, laravel, code-generation)
- ⏳ **MCP External** - GitHub, Context7, custom integrations
- ⏳ **Auto-Selection** - Native→MCP intelligent fallback
- ⏳ **Security** - Whitelisting, validation, execution tracking

---

## 📚 Documentation

Complete documentation available in the `docs/` directory:

### 📖 User Guides
- **[Installation Guide](docs/INSTALLATION.md)** - Setup, requirements, post-install configuration
- **[Configuration Guide](docs/CONFIGURATION.md)** - Provider setup, streaming, budget controls
- **[Usage Guide](docs/USAGE-GUIDE.md)** - Practical examples for all features
- **[FAQ](docs/FAQ.md)** - Troubleshooting and common questions

### 🔧 Developer Documentation
- **[API Reference](docs/API-REFERENCE.md)** - Facades, services, models, REST endpoints
- **[Code Examples](docs/EXAMPLES.md)** - Copy-paste ready code snippets
- **[Contributing Guide](docs/CONTRIBUTING.md)** - Development workflow and guidelines

### 🧩 Components
- **[Chat Configuration System](docs/components/chat/README.md)** - Chat Workspace Configuration System v1.0.7 (23 docs, production ready)
- **[Chat Workspace Component](docs/reference/components/CHAT-WORKSPACE.md)** - Legacy guide for ChatWorkspace v2.2 (Multi-Instance Support)

### 📊 Project Status
- **[Changelog](CHANGELOG.md)** - Complete version history with Monitor Export Feature (v1.0.0 - v1.0.7)
- **[Project Status](PROJECT-STATUS.md)** - v1.0.7 ready for release (99.5% complete - only GitHub release pending)
- **[Testing Status](#-testing-status)** - Manual testing coverage (33/33 features - 100%)
- **[Roadmap](#-roadmap)** - Future features and releases

### 🎨 Component Features (v1.0.7)

- **Chat Workspace Configuration System v1.0.7** - Granular Configuration (NEW)
  - ✅ Config Array System: Configuración mediante array único (vs legacy props)
  - ✅ ChatWorkspaceConfigValidator: Validación + merge con defaults
  - ✅ Settings Panel UI: Personalización de UI con DB persistence
  - ✅ 4 secciones: features, ui, performance, advanced
  - ✅ Backward Compatible: Legacy props siguen funcionando 100%
  - ✅ Conditional Resource Loading: 15-39% bundle size reduction
  - ✅ Testing Suite: 27/27 tests passing ✅
  - ✅ Documentation: 23 archivos modular (3376+ líneas)
  - [Full documentation](docs/components/chat/README.md)
  - [Quick Start](docs/components/chat/getting-started/quick-start.md)
  - [Examples](docs/components/chat/guides/examples.md) - 10+ ejemplos

- **ChatWorkspace v2.2** - Multi-Instance Support + 63% code reduction (740 → 270 lines)
  - ✅ Multi-instance architecture: Múltiples chats en la misma página
  - ✅ Alpine.js scopes únicos: `chatWorkspace_{{sessionId}}`, `splitResizer_{{sessionId}}`
  - ✅ Factory pattern: `window.LLMMonitorFactory` para monitors independientes
  - ✅ LocalStorage isolation: Configuraciones separadas por sesión
  - ✅ Custom Events con `sessionId` discriminator
  - Split-horizontal layout: 66% reduction
  - Monitor components: 56% reduction
  - 10 reusable partials created
  - [Legacy documentation](docs/reference/components/CHAT-WORKSPACE.md)

- **UX Enhancements v1.0.7** - Chat UX System (21 items - PLAN-v1.0.7-chat-ux.md)
  - ✅ Context Window Visual Indicator: Border + opacity para mensajes en contexto
  - ✅ Smart Auto-Scroll System: 6 features ChatGPT-style (scroll detection, counter, badge)
  - ✅ Browser + Sound Notifications: Dual implementation
  - ✅ Delete Message Feature: Two-column approach (message_id + id fallback)
  - ✅ Request Inspector Tab: Hybrid architecture (immediate + SSE)
  - ✅ Message ID Refactor: Centralized system
  - [Context Window](docs/components/chat/features/context-window.md)
  - [Auto-Scroll](docs/components/chat/features/auto-scroll.md)

- **Activity Log System v1.0.7** - Database-driven Activity History + Monitor Export
  - ✅ Cross-device persistence: Access history from any device
  - ✅ Unlimited history: No localStorage 5MB cap limitation
  - ✅ Auto-refresh: Real-time updates after streaming completion
  - ✅ Server-side filtering: Filter by sessionId in Quick Chat
  - ✅ Monitor Export: CSV/JSON/SQL formats with session-aware filtering
  - ✅ Shared partial: activity-table.blade.php with AJAX loading
  - 132+ commits total (230ba0a → 77373af)
  - [Migration plan](plans/completed/ACTIVITY-LOG-MIGRATION-PLAN.md)
  - [Export analysis](reports/MONITOR-EXPORT-ANALYSIS-2025-12-10.md)
  - [Monitor Export](docs/components/chat/features/monitor-export.md)

---

## 🎯 What Makes This Special

1. **🚀 Real-Time Streaming** - Only Laravel LLM manager with SSE-based streaming and live metrics
2. **📊 Production-Ready** - Complete testing (33/33 features), permissions v2.0, database-driven activity logs
3. **🎨 Laravel-Native** - Built for Laravel 11+ with Blade components, Eloquent, and Artisan commands
4. **🔒 Enterprise Security** - Granular RBAC (12 permissions across 4 roles), encrypted API keys
5. **📦 Zero-Config Installation** - One command setup via BITHOVEN Extension Manager
6. **🔄 Cross-Device Persistence** - Database-driven Activity History accessible from any device (NEW v1.0.7)

---

## 📦 Installation

### Requirements

- **PHP** 8.2 or higher
- **Laravel** 11.x
- **Node.js** 18+ (optional, for future MCP servers)
- **MySQL/PostgreSQL** - For data persistence
- **Composer** 2.x

### Via BITHOVEN Extension Manager (Recommended)

```bash
# From CPANEL directory
php artisan bithoven:extension:install llm-manager

# Or install from GitHub
php artisan bithoven:extension:install llm-manager --from=github
```

The installer will:
1. Download extension from GitHub
2. Install Composer dependencies
3. Run migrations (16 tables)
4. Execute core seeders (permissions, configs, tools, MCP)
5. Create permissions and assign to roles
6. Enable extension automatically

### Manual Installation (Development)

```bash
# 1. Clone to extensions folder
cd /path/to/BITHOVEN/EXTENSIONS
git clone https://github.com/Madniatik/bithoven-extension-llm-manager

# 2. Add to CPANEL composer.json
{
  "repositories": [
    {
      "type": "path",
      "url": "../EXTENSIONS/bithoven-extension-llm-manager",
      "options": { "symlink": true }
    }
  ]
}

# 3. Install
cd /path/to/CPANEL
composer require bithoven/llm-manager:@dev

# 4. Run migrations
php artisan migrate

# 5. Run seeders
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\DatabaseSeeder
```

### Post-Installation

```bash
# Verify installation
php artisan bithoven:extension:list

# Check permissions
php artisan permission:show

# Access admin panel
# Navigate to: http://your-app.com/admin/llm
```

---

## 🚀 Quick Start

### 1. Configure Your First LLM Provider

**Via Admin UI** (Recommended):
```
Navigate to: http://your-app.com/admin/llm/configurations/create
```

**Or Programmatically**:
```php
use Bithoven\LLMManager\Models\LLMConfiguration;

LLMConfiguration::create([
    'name' => 'Ollama Qwen3',
    'slug' => 'ollama-qwen3',
    'provider' => 'ollama',
    'model' => 'qwen3:4b',
    'endpoint' => 'http://localhost:11434',
    'temperature' => 0.7,
    'max_tokens' => 2000,
    'is_active' => true,
    'is_default' => true,
]);
```

### 2. Use LLM in Your Code

**Simple Execution**:
```php
use Bithoven\LLMManager\Facades\LLM;

$response = LLM::execute('Explain Laravel middleware in 50 words');
echo $response; // "Middleware in Laravel acts as..."
```

**With Specific Configuration**:
```php
$response = LLM::useConfig('ollama-qwen3')
    ->execute('Generate a REST API controller for a Blog model');
```

**With Parameter Override**:
```php
$response = LLM::execute('Write creative fiction', [
    'temperature' => 1.2,  // More creative
    'max_tokens' => 4000,
]);
```

### 3. Test Real-Time Streaming

```bash
# Start your LLM provider (e.g., Ollama)
ollama serve

# Navigate to streaming test page
# http://your-app.com/admin/llm/stream/test

# Or programmatically:
use Bithoven\LLMManager\Services\LLMStreamService;

$stream = app(LLMStreamService::class);
$stream->stream($configId, 'Your prompt', function($chunk) {
    echo $chunk; // Real-time output
});
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

**16 migrations creating comprehensive LLM infrastructure:**

| Table | Purpose | Key Features |
|-------|---------|--------------|
| `llm_manager_configurations` | Provider configs | Multi-provider, encrypted keys, per-extension |
| `llm_manager_usage_logs` | Usage tracking | Tokens, cost, duration, status |
| `llm_manager_custom_metrics` | Extension metrics | Numerical + JSON data, aggregation |
| `llm_manager_parameter_overrides` | Runtime params | Temperature, max_tokens, etc. |
| `llm_manager_prompt_templates` | Reusable prompts | Variable substitution, versioning |
| `llm_manager_conversation_sessions` | Chat sessions | Context management, history |
| `llm_manager_conversation_messages` | Chat messages | Role, content, timestamps |
| `llm_manager_conversation_logs` | Audit logs | Debugging, compliance |
| `llm_manager_document_knowledge_base` | RAG documents | Chunking, embeddings, search |
| `llm_manager_mcp_connectors` | MCP registry | Server configs, health checks |
| `llm_manager_agent_workflows` | Orchestration | State machine, multi-agent |
| `llm_manager_tool_definitions` | Tools registry | Schemas, handlers, validation |
| `llm_manager_tool_executions` | Tool tracking | Execution history, results |

**Naming Convention:** All tables use `llm_manager_*` prefix for clean namespace separation.

---

## 🎨 Admin UI

**Access:** `http://your-app.com/admin/llm`

### Dashboard
- Quick stats cards (configs, conversations, tokens, costs)
- Recent activity timeline
- Provider distribution chart
- Usage trends

### Configurations (`/admin/llm/configurations`)
- List all LLM provider configurations
- Create/Edit/Delete configs
- Test connections with preview
- Toggle active/default status
- Export/Import configs (JSON)

### Conversations (`/admin/llm/conversations`)
- View all chat sessions
- Filter by extension, date, status
- Inspect full message history
- Export conversations (JSON/CSV)

### Knowledge Base (`/admin/llm/knowledge-base`)
- Upload and manage documents
- Auto-indexing with RAG
- View document chunks
- Semantic search testing
- Re-index functionality

### Prompt Templates (`/admin/llm/prompts`)
- Template library with CRUD
- Variable substitution (`{{var}}`)
- Global vs extension-specific
- Version management
- API usage examples

### Tool Definitions (`/admin/llm/tools`)
- Register custom tools
- JSON schema validation
- Handler class configuration
- Execution history
- Active/inactive toggle

### Statistics (`/admin/llm/stats`)
- Usage analytics dashboard
- Provider breakdown (Pie chart)
- Monthly trends (Line chart)
- Date range filters (7/30/90 days, custom)
- Cost analysis
- Export reports (CSV/JSON)

### Streaming Test (`/admin/llm/stream/test`)
- Interactive streaming interface
- Real-time token counting
- Live cost calculation
- Activity monitor console
- Configuration selector
- Parameter controls (temperature, max_tokens)

### Activity Logs (`/admin/llm/activity`)
- Complete execution history
- Filter by provider, model, status
- Performance metrics
- Error tracking
- Export functionality

---

## 🔧 Artisan Commands

```bash
# Extension Management
php artisan bithoven:extension:install llm-manager    # Install extension
php artisan bithoven:extension:enable llm-manager     # Enable extension
php artisan bithoven:extension:disable llm-manager    # Disable extension
php artisan bithoven:extension:uninstall llm-manager  # Uninstall (preserves data)

# Configuration Testing
php artisan llm-manager:test-connection {id}          # Test LLM config by ID

# RAG System (Future)
php artisan llm-manager:index-documents               # Index documents for RAG
php artisan llm-manager:generate-embeddings           # Generate vector embeddings

# MCP Servers (Future v1.2.0)
php artisan llm-manager:mcp:start [server]            # Start MCP servers
php artisan llm-manager:mcp:list                      # List available servers
php artisan llm-manager:mcp:add {name} {command}      # Register custom server

# Permissions
php artisan permission:show                           # View all permissions
php artisan permission:cache-reset                    # Clear permission cache
```

---

## 🧪 Testing & Quality

### Manual Testing Coverage: 100% ✅

**Version:** v1.0.7-dev  
**Completed:** 7 de diciembre de 2025  
**Features Tested:** 33/33 (100%)  
**Bugs Fixed:** 18 (including 3 Activity Log bugs)

| Module | Features | Status | Documentation |
|--------|----------|--------|---------------|
| **Prompt Templates** | 8/8 | ✅ Complete | CRUD, variables, validation, API |
| **Knowledge Base** | 8/8 | ✅ Complete | RAG, indexing, chunks, search |
| **Tool Definitions** | 7/7 | ✅ Complete | Registry, schemas, handlers |
| **Conversations** | 4/4 | ✅ Complete | Sessions, messages, export |
| **Statistics** | 6/6 | ✅ Complete | Charts, filters, export |
| **Streaming (v1.0.4)** | ✅ | ✅ Complete | SSE, metrics, activity logs |
| **Activity Logs (v1.0.7)** | ✅ | ✅ Complete | Database-driven, auto-refresh, filtering |

### Test Reports
- **Testing Guide:** `CPANEL/reports/llm-manager-testing-guide.md`
- **Final Summary:** `CPANEL/reports/llm-manager-testing-FINAL-SUMMARY.md`
- **API Scripts:** `CPANEL/temp/test-prompts-api.php`, `test-tools-api.php`

### Automated Testing (Planned v1.2.0)
- PHPUnit test suite (80%+ coverage target)
- Feature tests for all endpoints
- Integration tests with real providers
- Performance benchmarks

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

**Current Version:** v2.2.0 (Production Ready - Multi-Instance Support)

### ✅ Completed (v1.0.0)

**Backend (100%)**
- ✅ 49 Foundation files (ServiceProvider, migrations, models, seeders, config)
- ✅ 5 Core services (Manager, Executor, Budget, Metrics, Prompts)
- ✅ 5 Provider implementations (Ollama, OpenAI, Anthropic, OpenRouter, Custom)
- ✅ 4 Orchestration services (Conversations, RAG, Embeddings, Workflows)
- ✅ 11 Controllers (6 Admin, 5 API)
- ✅ 6 CLI commands (mcp:*, llm:*, test)
- ✅ Permissions Protocol v2.0 (12 permissions, 4-role assignment)

**Frontend (100%)**
- ✅ 20 Admin UI views (Dashboard, Configs, Stats, Prompts, KB, Tools, Conversations)
- ✅ Real-time streaming interface with activity monitor
- ✅ Interactive charts (Chart.js, ApexCharts)
- ✅ DataTables integration for all listings
- ✅ SweetAlert2 notifications
- ✅ Responsive Metronic 8.3.2 theme

**Testing (100%)**
- ✅ Manual testing: 33/33 features (100%)
- ✅ 15 bugs fixed and documented
- ✅ Production deployment verified

### ⏳ Planned Features

**v1.2.0 (Q1 2026)**
- ⏳ Statistics Dashboard enhancements
- ⏳ PHPUnit test suite (80%+ coverage)
- ⏳ Streaming in Conversations UI
- ⏳ Performance optimizations

**v1.3.0 (Q2 2026)**
- ⏳ MCP Servers (4 bundled: filesystem, database, laravel, code-generation)
- ⏳ Function Calling (OpenAI, Anthropic, Gemini)
- ⏳ Hybrid Tools System (Native + MCP)

**v2.0.0 (Q3 2026)**
- ⏳ Multi-Agent Workflows (visual builder)
- ⏳ Advanced RAG strategies
- ⏳ WebSocket streaming
- ⏳ Plugin system

---

## 🗺️ Roadmap

### v1.1.0 (Current - In Progress)
**Focus:** Real-Time Streaming & Enhanced Monitoring

**Completed:**
- ✅ SSE-based streaming with OllamaProvider and OpenAIProvider
- ✅ Interactive streaming test UI with activity monitor
- ✅ Real token counting and cost tracking
- ✅ LLMStreamLogger service for usage metrics
- ✅ Permissions Protocol v2.0 migration
- ✅ Activity history with localStorage
- ✅ Enhanced stats bar (6 metrics)

**In Progress:**
- ⏳ Documentation updates (Usage Guide, API Reference)
- ⏳ Streaming integration in Conversations UI

**Release Target:** December 2025

---

### v1.2.0 (Q1 2026)
**Focus:** Testing, Documentation & Statistics Dashboard

**Planned Features:**
- PHPUnit test suite (80%+ coverage)
- Enhanced Statistics Dashboard:
  - Provider/model breakdown tables
  - Advanced filtering and date ranges
  - Cost analysis and projections
  - Export functionality (CSV, JSON, PDF)
- Complete API documentation with Swagger/OpenAPI
- Video tutorials and screencasts
- Performance optimizations

---

### v1.3.0 (Q2 2026)
**Focus:** Hybrid Tools System

**Planned Features:**
- **MCP Bundled Servers:**
  - `filesystem` - File operations (CRUD, search)
  - `database` - Queries, migrations, seeders
  - `laravel` - Artisan, routes, config
  - `code-generation` - Controllers, models, views
- **Function Calling:**
  - OpenAI tools API integration
  - Anthropic tools API integration
  - Google Gemini function calling
- **Hybrid Intelligence:**
  - Auto-selection (Native → MCP fallback)
  - Zero-config for end users
- **Security Layer:**
  - Path whitelisting
  - Command sanitization
  - Execution tracking

---

### v2.0.0 (Q3 2026)
**Focus:** Multi-Agent Orchestration & Advanced RAG

**Planned Features:**
- **Multi-Agent Workflows:**
  - Visual workflow builder (drag-and-drop)
  - State machine orchestration
  - Conditional branching
  - Agent coordination protocols
- **Advanced RAG:**
  - Multiple embedding strategies
  - Hybrid search (semantic + keyword)
  - Re-ranking algorithms
  - Context compression
- **Real-Time Collaboration:**
  - WebSocket streaming
  - Multi-user conversations
  - Live collaborative editing
- **Plugin System:**
  - Custom provider plugins
  - Custom tool plugins
  - Extension marketplace

---

### Future Vision (v3.0+)
- Agent autonomy and self-improvement
- Distributed agent networks
- Advanced cost optimization with ML
- Multi-modal support (images, audio)
- Enterprise features (SSO, audit trails, compliance)

---

## 🤝 Contributing

We welcome contributions! Please read our [Contributing Guide](CONTRIBUTING.md) for details on:

- Code of Conduct
- Development workflow
- Pull request process
- Testing requirements
- Documentation standards

### Quick Start for Contributors

```bash
# 1. Fork and clone
git clone https://github.com/YOUR-USERNAME/bithoven-extension-llm-manager
cd bithoven-extension-llm-manager

# 2. Create feature branch
git checkout -b feature/your-feature-name

# 3. Make changes and test
# ... your changes ...

# 4. Commit with conventional commits
git commit -m "feat: add streaming support for Anthropic"

# 5. Push and create PR
git push origin feature/your-feature-name
```

---

## 📄 License

This extension is open-sourced software licensed under the [MIT License](LICENSE).

```
MIT License

Copyright (c) 2025 BITHOVEN Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction...
```

See [LICENSE](LICENSE) file for full details.

---

## 🙏 Credits & Acknowledgments

**Built with ❤️ by the BITHOVEN Team**

### Core Technologies
- **[Laravel 11](https://laravel.com)** - The PHP Framework for Web Artisans
- **[Metronic 8.3.2](https://keenthemes.com/metronic)** - Premium Admin Theme
- **[Yajra DataTables](https://github.com/yajra/laravel-datatables)** - Server-side DataTables
- **[Spatie Laravel Permission](https://github.com/spatie/laravel-permission)** - Role-based access control

### LLM Providers
- **[OpenAI](https://openai.com)** - GPT models
- **[Anthropic](https://anthropic.com)** - Claude models  
- **[Ollama](https://ollama.ai)** - Local LLM runtime
- **[OpenRouter](https://openrouter.ai)** - Multi-provider API gateway

### Special Thanks
- Model Context Protocol (MCP) team for the innovative protocol
- Laravel community for continuous inspiration
- All contributors and testers

---

**Version:** v1.0.7 (Production Ready)  
**Last Updated:** 10 de diciembre de 2025, 13:30  
**Status:** ✅ Production Release - Chat Configuration System + Monitor Export + UX Enhancements Complete  
**Latest Features:**
- Chat Workspace Configuration System (23 docs, 3376+ líneas, 27/27 tests passing)
- Monitor Export Feature (CSV/JSON/SQL con session filtering)
- UX Enhancements (21 items: Context Window, Auto-Scroll, Notifications, Delete Message, Request Inspector)
- Settings Panel con DB Persistence
- Documentation modular completa

**GitHub:** https://github.com/Madniatik/bithoven-extension-llm-manager
