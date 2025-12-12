# MCP Servers Directory

**Status:** 🔜 **PLANNED FOR v1.2.0**

This directory will contain Model Context Protocol (MCP) server implementations for the LLM Manager extension.

---

## 📋 Planned Features (v1.2.0)

### Bundled MCP Servers
1. **Filesystem Server** - File operations (read, write, search)
2. **Database Server** - Query execution, schema introspection
3. **Laravel Server** - Artisan commands, config access
4. **Code Generation Server** - Template scaffolding, code analysis

### External MCP Integrations
- GitHub MCP (issues, PRs, code search)
- Context7 MCP (documentation search)
- Custom MCP servers (user-defined)

---

## 🔧 Architecture (Planned)

**Hybrid Tools System:**
- Native function calling (OpenAI/Anthropic/Gemini)
- MCP protocol support (via stdio/HTTP)
- Intelligent fallback: Native → MCP
- Security: Whitelisting, validation, execution tracking

---

## 📚 Documentation

See [PLAN-v1.2.0.md](../plans/PLAN-v1.2.0.md) for detailed roadmap (when created).

---

**Note:** This directory is reserved for future development. No MCP servers are currently implemented in v0.3.0.
