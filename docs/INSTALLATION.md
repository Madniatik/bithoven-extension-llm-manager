# 📦 Installation Guide

**LLM Manager Extension v1.0**

---

## 🎯 Requirements

### System Requirements
- **PHP:** 8.2 or higher
- **Laravel:** 11.x
- **Composer:** 2.x
- **Node.js:** 18+ (for MCP servers)
- **Python:** 3.9+ (optional, for database MCP server)
- **Database:** MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

### PHP Extensions
```bash
php -m | grep -E "pdo|json|mbstring|openssl|curl"
```

Required extensions:
- `pdo_mysql` or `pdo_pgsql` or `pdo_sqlite`
- `json`
- `mbstring`
- `openssl`
- `curl`

---

## 🚀 Installation Methods

### Method 1: Via BITHOVEN Extension Manager (Recommended)

```bash
# Install via Extension Manager
php artisan bithoven:extension:install llm-manager

# Enable extension
php artisan bithoven:extension:enable llm-manager

# Verify installation
php artisan bithoven:extension:list
```

This method handles:
- ✅ Composer dependencies
- ✅ Database migrations
- ✅ Asset publishing
- ✅ Permissions seeding
- ✅ MCP servers installation

---

### Method 2: Manual Installation

#### Step 1: Clone Repository

```bash
cd /path/to/BITHOVEN/EXTENSIONS
git clone https://github.com/bithoven/llm-manager.git bithoven-extension-llm-manager
```

#### Step 2: Add to Composer

Edit `CPANEL/composer.json`:

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
    "bithoven/llm-manager": "@dev"
  }
}
```

#### Step 3: Install Dependencies

```bash
cd CPANEL
composer require bithoven/llm-manager
```

#### Step 4: Publish Assets

```bash
# Publish configuration
php artisan vendor:publish --tag=llm-config

# Publish views (optional, for customization)
php artisan vendor:publish --tag=llm-views

# Publish migrations (if you want to customize)
php artisan vendor:publish --tag=llm-migrations
```

#### Step 5: Run Migrations

```bash
php artisan migrate
```

This creates 13 tables:
- `llm_configurations`
- `llm_usage_logs`
- `llm_provider_cache`
- `llm_extension_metrics`
- `llm_prompt_templates`
- `llm_conversation_sessions`
- `llm_conversation_messages`
- `llm_conversation_logs`
- `llm_document_knowledge_base`
- `llm_mcp_connectors`
- `llm_agent_workflows`
- `llm_tool_definitions`
- `llm_tool_executions`

#### Step 6: Seed Database (Optional)

```bash
# Seed demo data
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\DatabaseSeeder

# Or seed individually
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\LLMPermissionsSeeder
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\LLMConfigurationsSeeder
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\LLMPromptsSeeder
```

#### Step 7: Install MCP Servers

```bash
cd vendor/bithoven/llm-manager
bash scripts/install-mcp-servers.sh
```

This installs 4 bundled MCP servers:
- `@modelcontextprotocol/server-filesystem`
- `@modelcontextprotocol/server-database`
- `@bithoven/laravel-mcp-server`
- `@bithoven/code-generation-mcp`

---

## 🔐 Permissions Setup

### Automatic (via Seeder)

```bash
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\LLMPermissionsSeeder
```

### Manual Permission Assignment

```php
use Spatie\Permission\Models\Role;

$admin = Role::findByName('admin');
$admin->givePermissionTo([
    'llm.view-configurations',
    'llm.manage-configurations',
    'llm.view-statistics',
    'llm.manage-prompts',
    'llm.view-conversations',
    'llm.manage-knowledge-base',
    'llm.manage-tools',
    'llm.execute-workflows',
]);

$developer = Role::findByName('developer');
$developer->givePermissionTo([
    'llm.view-configurations',
    'llm.view-statistics',
    'llm.manage-prompts',
]);
```

---

## 🌐 Environment Configuration

Add to `.env`:

```bash
# LLM Configuration
LLM_DEFAULT_PROVIDER=ollama
LLM_DEFAULT_MODEL=llama3.2
LLM_CACHE_ENABLED=true
LLM_CACHE_TTL=3600

# Provider API Keys
OLLAMA_BASE_URL=http://localhost:11434
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...

# Budget Controls
LLM_BUDGET_MONTHLY_LIMIT=100.00
LLM_BUDGET_DAILY_LIMIT=10.00
LLM_BUDGET_ALERT_THRESHOLD=80

# RAG System
LLM_RAG_ENABLED=true
LLM_RAG_CHUNK_SIZE=1000
LLM_RAG_CHUNK_OVERLAP=200
LLM_RAG_EMBEDDING_MODEL=text-embedding-3-small

# MCP Servers
LLM_MCP_ENABLED=true
LLM_MCP_AUTO_START=false
```

---

## 🧪 Verify Installation

### Test LLM Connection

```bash
php artisan llm-manager:test-connection 1
```

### Check Installed MCP Servers

```bash
php artisan llm-manager:mcp:list
```

### Access Admin UI

Navigate to: `http://your-app.test/admin/llm`

---

## 🔧 Post-Installation

### 1. Create First Configuration

Via Admin UI: `/admin/llm/configurations/create`

Or via Artisan:

```bash
php artisan tinker
```

```php
use Bithoven\LLMManager\Models\LLMConfiguration;

LLMConfiguration::create([
    'name' => 'Ollama Llama 3.2',
    'slug' => 'ollama-llama3',
    'provider' => 'ollama',
    'model' => 'llama3.2',
    'base_url' => 'http://localhost:11434',
    'temperature' => 0.7,
    'max_tokens' => 2000,
    'is_active' => true,
    'is_default' => true,
    'extension_slug' => null, // Global config
]);
```

### 2. Start MCP Servers (Optional)

```bash
php artisan llm-manager:mcp:start
```

### 3. Index Documents (for RAG)

```bash
php artisan llm-manager:index-documents
```

---

## 🚨 Troubleshooting

### Issue: Migrations Fail

**Error:** `SQLSTATE[42S01]: Base table or view already exists`

**Solution:**
```bash
php artisan migrate:status
php artisan migrate:rollback --step=13
php artisan migrate
```

### Issue: MCP Servers Not Found

**Error:** `Command not found: npx`

**Solution:**
```bash
# Install Node.js 18+
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
nvm use 18

# Reinstall MCP servers
cd vendor/bithoven/llm-manager
bash scripts/install-mcp-servers.sh
```

### Issue: Permission Denied

**Error:** `User does not have permission to access this resource`

**Solution:**
```bash
# Re-seed permissions
php artisan db:seed --class=Bithoven\\LLMManager\\Database\\Seeders\\LLMPermissionsSeeder

# Assign to your user
php artisan tinker
```

```php
$user = User::find(1);
$user->givePermissionTo('llm.view-configurations');
```

### Issue: API Key Not Working

**Error:** `401 Unauthorized`

**Solution:**
1. Verify API key in `.env`
2. Clear config cache: `php artisan config:clear`
3. Test connection: `php artisan llm-manager:test-connection 1`

---

## 📦 Uninstallation

```bash
# Disable extension
php artisan bithoven:extension:disable llm-manager

# Uninstall (removes database tables)
php artisan bithoven:extension:uninstall llm-manager

# Remove from composer
composer remove bithoven/llm-manager
```

**⚠️ Warning:** Uninstalling will delete all data (configurations, conversations, documents).

---

## ✅ Next Steps

1. **[Configuration Guide](CONFIGURATION.md)** - Configure providers
2. **[Integration Guide](INTEGRATION-GUIDE.md)** - Use in your extensions
3. **[API Reference](API-REFERENCE.md)** - Available methods

---

**Last Updated:** 18 de noviembre de 2025  
**Version:** 1.0.0
