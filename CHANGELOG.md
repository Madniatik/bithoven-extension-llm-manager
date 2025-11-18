# Changelog

All notable changes to the LLM Manager extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

## [1.0.0] - 2025-11-18

### Added - Initial Release v3.0

#### Core LLM Management
- Multi-provider support (Ollama, OpenAI, Anthropic, Custom)
- Per-extension LLM configurations
- Budget tracking and usage logs
- Provider cache for models auto-discovery
- Automatic fallback between configurations
- LLMManager service with provider abstraction
- Admin UI for configuration management

#### Advanced Features
- **Custom Metrics System**
  - Extensions can create custom metrics (numerical + JSON data)
  - API for recording, querying, and aggregating metrics
  - Dashboard visualization
  - Relationship with entities (bug, ticket, task)

- **Prompt Templates System**
  - Reusable templates with variable replacement (`{{variable}}`)
  - Database storage with versioning
  - Default parameters per template
  - Validation of required variables
  - CRUD API and admin interface

- **Parameter Override System**
  - Runtime override of model parameters (temperature, max_tokens, etc.)
  - Intelligent merge with configuration defaults
  - Per-provider parameter validation
  - Automatic fallback

#### Orchestration Platform
- **Conversation System**
  - Persistent sessions with context management
  - Complete message history
  - Audit logs for debugging
  - Session lifecycle management

- **RAG System (Retrieval-Augmented Generation)**
  - Document chunking with intelligent splitting
  - Vector embeddings (OpenAI API or local)
  - Semantic search over documentation
  - Automatic context injection
  - Artisan commands for indexing and embeddings

- **Multi-Agent Workflows**
  - Workflow definition with state machine
  - Multi-step orchestration
  - Conditional branching
  - Visual workflow builder
  - Agent coordination

#### Hybrid Tools System
- **Function Calling Support**
  - Native OpenAI tools API integration
  - Native Anthropic tools API integration
  - Gemini function calling support
  - Single API call execution (faster)

- **MCP Bundled Servers (4 servers)**
  - `filesystem` - File operations (create, read, list, delete)
  - `database` - Query execution, migrations, seeders
  - `laravel` - Artisan commands, routes, config access
  - `code-generation` - Generate controllers, models, migrations

- **MCP External Support**
  - GitHub API integration (community)
  - Context7 integration (community)
  - Custom user MCP servers
  - Visual management UI

- **Auto-Selection Intelligence**
  - Automatic provider capability detection
  - Function Calling prioritization (faster)
  - MCP fallback when native not available
  - Zero-config for end users

- **Security Layer**
  - Path whitelisting for file operations
  - Extension validation
  - File size limits
  - Command sanitization
  - Execution tracking

#### Database Schema
- 13 tables with `llm_*` prefix:
  - `llm_configurations` - Provider configurations
  - `llm_usage_logs` - Usage tracking
  - `llm_provider_cache` - Models cache
  - `llm_extension_metrics` - Custom metrics
  - `llm_prompt_templates` - Prompt templates
  - `llm_conversation_sessions` - Sessions
  - `llm_conversation_messages` - Messages
  - `llm_conversation_logs` - Audit logs
  - `llm_document_knowledge_base` - RAG documents
  - `llm_mcp_connectors` - MCP registry
  - `llm_agent_workflows` - Workflows
  - `llm_tool_definitions` - Tools registry
  - `llm_tool_executions` - Execution tracking

#### API & Integration
- Public API for extensions (Facade + REST)
- Blade components for UI integration
- Event system (RequestStarted, RequestCompleted, etc.)
- Middleware for validation
- Request validation classes

#### Admin UI
- Configuration management (CRUD)
- Conversations viewer with chat interface
- Knowledge Base management (RAG)
- Workflow builder (visual)
- MCP Servers management
- Statistics and cost reports
- Metrics dashboard

#### Artisan Commands
- `llm-manager:mcp:start` - Start MCP servers
- `llm-manager:mcp:list` - List servers
- `llm-manager:mcp:add` - Add external server
- `llm-manager:index-documents` - Index documents for RAG
- `llm-manager:generate-embeddings` - Generate embeddings
- `llm-manager:test-connection` - Test configuration

#### Documentation
- Complete installation guide
- Configuration reference
- API documentation
- Integration guide for developers
- Conversations guide
- RAG setup guide
- Workflows guide
- Tools development guide
- MCP servers guide

#### Testing
- Unit tests for core services
- Feature tests for API endpoints
- Integration tests with test extension
- Tests for all v3.0 features

### Requirements
- PHP ^8.2
- Laravel ^11.0
- Node.js ^18.0 (for MCP servers)
- Python ^3.9 (for database MCP)

### Migration Notes
- Creates 13 tables with `llm_*` prefix
- Requires permissions setup (13 permissions)
- MCP servers auto-install via post-install script
- Compatible with Fix Extension (IDs 1-N reserved)

### Breaking Changes
- None (initial release)

---

## Future Roadmap

### v3.1.0 (Planned)
- Real-time streaming responses
- WebSocket support for chat
- Advanced workflow templates
- More bundled MCP servers
- Plugin system for custom providers

### v3.2.0 (Planned)
- Multi-model ensemble support
- A/B testing for prompts
- Advanced cost optimization
- Extended analytics

### v4.0.0 (Future)
- Full agent autonomy
- Self-improving workflows
- Advanced RAG strategies
- Distributed agent network

---

## Support

- **Documentation:** `vendor/bithoven/llm-manager/docs/`
- **Issues:** https://github.com/bithoven/llm-manager/issues
- **Discord:** https://discord.gg/bithoven

---

[unreleased]: https://github.com/bithoven/llm-manager/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/bithoven/llm-manager/releases/tag/v1.0.0
