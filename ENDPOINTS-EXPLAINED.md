# 🔌 LLM Streaming - Endpoints Explained

## Your Questions Answered

### ❓ Pregunta 1: "¿Cuántos endpoints hay para conectar con los modelos?"

**Respuesta: 5+ endpoints por proveedor**

No es un único endpoint, sino múltiples según la operación:

```
OLLAMA:
├── /api/generate        → Generar texto
├── /api/embeddings      → Generar embeddings
├── /api/tags            → Listar modelos disponibles
├── /api/pull            → Descargar modelo
└── /api/show            → Info del modelo

OPENAI:
├── /v1/chat/completions     → Chat streaming
├── /v1/completions         → Completions legacy
├── /v1/embeddings          → Embeddings
└── /v1/models              → Listar modelos

OPENROUTER (OpenAI-compatible):
├── /api/v1/chat/completions → Chat streaming
└── (same as OpenAI)

ANTHROPIC:
├── /v1/messages            → Generar texto
└── (diferente estructura)
```

---

### ❓ Pregunta 2: "¿Cómo sabe el sistema qué endpoint usar?"

**Respuesta: El sistema está TOTALMENTE DINÁMICO - NO hay hardcoding**

#### Flow Completo:

```
1. USER selects model in dropdown
    → selection: "OpenRoute" (ID=6)
    
2. BROWSER sends request with ?configuration_id=6
    
3. CONTROLLER receives request
    → $configurationId = request('configuration_id')
    
4. DATABASE QUERY
    → SELECT * FROM llm_manager_configurations WHERE id=6
    → Gets:
        {
            provider: "openrouter",
            api_endpoint: "https://openrouter.ai/api/v1",
            api_key: "encrypted-key-123",
            model: "anthropic/claude-sonnet-4.5",
            default_parameters: {"temperature": 0.7, ...}
        }

5. MATCH PROVIDER TYPE
    → if provider == "openrouter"
    → return new OpenRouterProvider($configuration)
    
6. PROVIDER USES ENDPOINT
    → OpenRouterProvider knows to append: /chat/completions
    → Final URL: https://openrouter.ai/api/v1/chat/completions
    → Sends request with api_key from config
    
7. STREAM RESPONSE
    → Receives chunks from endpoint
    → Sends to browser
```

---

## 🎯 Key Points

### ✅ NO Hardcoding - Everything is Dynamic

| Component | Source | Example |
|-----------|--------|---------|
| Which provider to use | Database `provider` field | `"openrouter"` |
| Where to send request | Database `api_endpoint` field | `"https://openrouter.ai/api/v1"` |
| Authentication | Database `api_key` field (encrypted) | `encrypt("sk-or-...")` |
| Model name | Database `model` field | `"anthropic/claude-sonnet-4.5"` |
| Parameters | Database `default_parameters` JSON | `{"temperature": 0.7}` |

### ✅ Database-Driven Architecture

```php
// In Controller
$configuration = LLMConfiguration::findOrFail($configurationId);

// Now we have everything we need:
$configuration->provider         // → Determines PHP class
$configuration->api_endpoint     // → HTTP endpoint
$configuration->api_key          // → Authentication
$configuration->model            // → Model identifier
$configuration->default_parameters // → Parameters
```

### ✅ Provider-Specific Logic

Each provider class knows how to use its endpoint:

```php
// OllamaProvider
$endpoint = rtrim($api_endpoint, '/') . '/api/generate';
// http://localhost:11434 + /api/generate

// OpenAIProvider  
$client = OpenAI::factory()
    ->withBaseUri($api_endpoint)  // https://api.openai.com/v1
    ->make();
// SDK appends /chat/completions

// OpenRouterProvider (same as OpenAI)
$client = OpenAI::factory()
    ->withBaseUri($api_endpoint)  // https://openrouter.ai/api/v1
    ->make();
// SDK appends /chat/completions
```

---

## 📊 Visual: How Parameters Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         DATABASE                                 │
├─────────────────────────────────────────────────────────────────┤
│ ID=6 | OpenRoute | openrouter | https://openrouter.ai/api/v1   │
│      |           |            | {"temp": 0.7, "max_tokens": 4k} │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ Query by ID
                      ↓
┌─────────────────────────────────────────────────────────────────┐
│                      CONTROLLER                                  │
│  $config = LLMConfiguration::find(6)                            │
│  $provider = $manager->config(6)->getProvider()                 │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ Match provider="openrouter"
                      ↓
┌─────────────────────────────────────────────────────────────────┐
│                    PROVIDER CLASS                                │
│  new OpenRouterProvider($config)                                │
│  • Receives $config with all parameters                         │
│  • Knows how to construct endpoint from $config->api_endpoint   │
│  • Extracts $config->api_key for auth                           │
│  • Uses $config->default_parameters as defaults                 │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ Build request
                      ↓
┌─────────────────────────────────────────────────────────────────┐
│                    HTTP REQUEST                                  │
│  POST https://openrouter.ai/api/v1/chat/completions             │
│  Headers: Authorization: Bearer {$config->api_key}              │
│  Body: {                                                         │
│    "model": "anthropic/claude-sonnet-4.5",                       │
│    "temperature": 0.7,        ← from $config->default_parameters│
│    "max_tokens": 4000,        ← from $config->default_parameters│
│    "messages": [...]                                             │
│  }                                                               │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ Streaming response
                      ↓
┌─────────────────────────────────────────────────────────────────┐
│                    BROWSER (user)                                │
│  Real-time chunks arrive as SSE                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔍 Why Ollama is Failing

### Your Ollama Configuration (Database):
```json
{
    "id": 1,
    "provider": "ollama",
    "api_endpoint": "http://localhost:11434",
    "model": "qwen3:4b"
}
```

### When you click "Send with Streaming":
```
1. Controller gets configuration_id=1
2. Loads from DB: provider="ollama", endpoint="http://localhost:11434"
3. Creates OllamaProvider with this config
4. OllamaProvider builds: http://localhost:11434 + /api/generate
5. Tries to connect: fopen("http://localhost:11434/api/generate")
6. ❌ FAILS because:
   - Ollama service NOT RUNNING
   - OR wrong IP/port
   - OR port 11434 blocked by firewall
```

### The Fix:
```bash
# 1. Start Ollama
/Applications/Ollama.app/Contents/MacOS/Ollama serve

# 2. Verify connection
curl http://localhost:11434/api/tags

# 3. If different machine/port, update database:
php artisan tinker
DB::table('llm_manager_configurations')
    ->where('id', 1)
    ->update(['api_endpoint' => 'http://NEW_IP:PORT']);
```

---

## 🎛️ How to Add Multiple LLM Providers

### Add Local Ollama:
```php
[
    'provider' => 'ollama',
    'api_endpoint' => 'http://localhost:11434',
    'model' => 'qwen3:4b',
]
```

### Add Remote Ollama:
```php
[
    'provider' => 'ollama',
    'api_endpoint' => 'http://192.168.1.50:11434',  // ← Different
    'model' => 'mistral:7b',
]
```

### Add OpenAI:
```php
[
    'provider' => 'openai',
    'api_endpoint' => 'https://api.openai.com/v1',  // ← Different
    'api_key' => env('OPENAI_API_KEY'),
    'model' => 'gpt-4o',
]
```

### Add Custom LLM Server:
```php
[
    'provider' => 'custom',
    'api_endpoint' => 'http://my-llm.com/api/generate',  // ← Your URL
    'api_key' => 'custom-token',
    'model' => 'my-model',
]
```

---

## 📋 Architecture Summary

### Everything is Configured in Database ✅
- No hardcoded endpoints
- No environment variables needed
- Just add a row to `llm_manager_configurations`

### Dynamic Routing ✅
- User selects model → Gets configuration from DB
- Configuration determines provider class
- Provider builds endpoint from configuration
- Request sent to configured endpoint

### Provider-Agnostic ✅
- Same flow works for Ollama, OpenAI, OpenRouter, etc.
- Each provider knows its endpoint structure
- System doesn't care about provider specifics

### Fully Extensible ✅
- Add new provider? Create new PHP class
- Add new endpoint? Update database row
- Add new server? Create new configuration

---

## 🚀 Testing Flow

```bash
# 1. Check database config
php artisan tinker << 'EOF'
$config = DB::table('llm_manager_configurations')->find(1);
echo "Endpoint: {$config->api_endpoint}\n";
EOF

# 2. Test curl
curl http://localhost:11434/api/tags

# 3. Try web UI
# Select model → Send message → Check if streaming works

# 4. If fails, debug
# Check Ollama: lsof -i :11434
# Check network: ping localhost
# Check models: ollama list
```

---

## 💡 Key Takeaways

| Question | Answer |
|----------|--------|
| Endpoints hardcoded? | **NO** - all from database |
| How does it know which endpoint? | **Reads provider field → selects class** |
| Can I use multiple servers? | **YES** - create multiple configs |
| Can I switch providers dynamically? | **YES** - dropdown selector in UI |
| What if Ollama is down? | **Switch to OpenRouter/OpenAI** (configured) |
| Can I use custom LLM server? | **YES** - "custom" provider type |

---

### Next Steps:
1. Start Ollama: `/Applications/Ollama.app/Contents/MacOS/Ollama serve`
2. Verify: `curl http://localhost:11434/api/tags`
3. Pull models: `ollama pull qwen3:4b`
4. Try web UI: Select "Ollama Qwen 3" → Send message
5. Should work! ✅

If still fails, check the detailed debugging guide: `OLLAMA-DEBUG-GUIDE.md`
