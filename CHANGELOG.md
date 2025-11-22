# Changelog

All notable changes to the LLM Manager extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] - v1.1.0

### Added - Real-Time Streaming Support

#### Streaming Infrastructure
- **SSE (Server-Sent Events) Controller**
  - `LLMStreamController` with 3 endpoints:
    - `test()` - Interactive test page for streaming
    - `stream()` - Simple streaming endpoint with validation
    - `conversationStream()` - Streaming with session history context
  - Response headers: `text/event-stream`, `no-cache`, `X-Accel-Buffering: no`
  - Real-time token counting and statistics tracking
  - Event types: `chunk`, `done`, `error`

- **Provider Streaming Implementation**
  - `LLMProviderInterface::stream()` method (BREAKING CHANGE)
    - Signature: `stream(string $prompt, array $context, array $parameters, callable $callback): void`
    - Context format: `[{role: 'user|assistant', content: 'text'}]`
    - Feature detection: `supports(string $feature): bool`
  
  - `OllamaProvider` full NDJSON streaming
    - Line-by-line JSON parsing with `fgets()`
    - Context conversion to formatted prompt
    - Chunk extraction from `response` field
    - Completion detection via `done` flag
    - Parameters: `temperature`, `num_predict`, `top_p`
  
  - `OpenAIProvider` enhanced streaming
    - Message array construction from context
    - Uses SDK `createStreamed()` method
    - Delta content extraction
    - Multi-turn conversation support

#### Frontend Components
- **Interactive Test UI** (`resources/views/admin/llm/stream/test.blade.php`)
  - EventSource JavaScript client
  - Configuration selector (streaming-capable providers only)
  - Real-time statistics panel (tokens, chunks, duration)
  - Parameter controls (temperature: 0-2, max_tokens: 1-4000)
  - Auto-scroll and animated cursor
  - SweetAlert2 notifications for status
  - Clear Response and Start Streaming buttons

#### Routes & Configuration
- Routes registered in `routes/web.php`:
  - `GET /admin/llm/stream/test` - Streaming test page
  - `GET /admin/llm/stream/stream` - SSE endpoint
  - `GET /admin/llm/stream/conversation` - SSE with history
- Breadcrumbs configured for navigation
- CSRF exceptions added for SSE endpoints (`admin/llm/stream/*`)

#### Database Updates
- Updated seeders with streaming-ready configurations:
  - Ollama Qwen 3 (qwen3:4b) - ID 1
  - Ollama DeepSeek Coder (deepseek-coder:6.7b) - ID 2
  - Base endpoint: `http://localhost:11434` (provider appends `/api/generate`)

### Changed
- **BREAKING:** `LLMProviderInterface` now requires `stream()` method
- Provider implementations updated to support `$context` parameter
- Ollama endpoint configuration simplified (no duplicate `/api/generate`)

### Fixed
- Validation table name corrected (`llm_manager_configurations`)
- Ollama endpoint duplication issue resolved
- CSRF verification properly excluded for streaming routes

### In Progress
- Integration with Conversations UI (streaming toggle, stop button)
- Testing suite for streaming functionality
- Documentation for streaming API

### Notes
- Requires active Ollama instance on `localhost:11434`
- Browser must support EventSource API (all modern browsers)
- Streaming disabled for Anthropic, OpenRouter, Custom providers (stubs implemented)

---

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
