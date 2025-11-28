# 🔌 LLM Providers - Endpoint Architecture

## 📊 How the System Routes Requests to LLM Providers

### 1️⃣ ENDPOINT RESOLUTION FLOW

```
User sends message
    ↓
Controller receives request
    ↓
Get configuration_id from request
    ↓
Query: LLMConfiguration::find($configuration_id)
    ↓
Get provider name: $configuration->provider
    ↓
Match provider type to PHP class
    ↓
Instantiate provider: new OllamaProvider($configuration)
    ↓
Call provider->stream() method
    ↓
Provider builds endpoint URL from $configuration->api_endpoint
    ↓
Send HTTP request to endpoint
    ↓
Stream response back to user
```

---

## 🎯 KEY CONCEPT: Where Endpoints Are Stored

**Answer: In the database `llm_manager_configurations` table**

```sql
SELECT 
    id, 
    name, 
    provider, 
    model, 
    api_endpoint 
FROM llm_manager_configurations;
```

| ID | Name | Provider | Model | API Endpoint |
|----|------|----------|-------|--------------|
| 1 | Ollama Qwen 3 | `ollama` | `qwen3:4b` | `http://localhost:11434` |
| 2 | Ollama DeepSeek | `ollama` | `deepseek-coder:6.7b` | `http://localhost:11434` |
| 3 | OpenAI GPT-4o | `openai` | `gpt-4o` | `https://api.openai.com/v1` |
| 4 | Anthropic Claude | `anthropic` | `claude-3-5-sonnet-20241022` | `https://api.anthropic.com/v1` |
| 6 | OpenRouter Claude | `openrouter` | `anthropic/claude-sonnet-4.5` | `https://openrouter.ai/api/v1` |

---

## 🏗️ PROVIDER ARCHITECTURE: How Each Provider Uses Endpoints

### 1. OLLAMA PROVIDER
**File:** `src/Services/Providers/OllamaProvider.php`

```php
// Constructor receives configuration object
public function __construct(protected LLMConfiguration $configuration) {}

// In stream() method:
$endpoint = rtrim($this->configuration->api_endpoint, '/') . '/api/generate';
// Result: http://localhost:11434 + /api/generate
//        = http://localhost:11434/api/generate

// Streams response via fopen()
$stream = @fopen($endpoint, 'r', false, $context);
```

**Endpoints used by Ollama:**
| Purpose | Endpoint | Method |
|---------|----------|--------|
| Generate text | `/api/generate` | POST |
| Generate embeddings | `/api/embeddings` | POST |
| List models | `/api/tags` | GET |
| Pull model | `/api/pull` | POST |
| Show model info | `/api/show` | POST |

---

### 2. OPENAI PROVIDER
**File:** `src/Services/Providers/OpenAIProvider.php`

```php
// Uses OpenAI SDK (which manages endpoints internally)
public function __construct(protected LLMConfiguration $configuration) {
    $this->client = OpenAI::factory()
        ->withApiKey($configuration->api_key)
        ->withBaseUri($configuration->api_endpoint)  // https://api.openai.com/v1
        ->make();
}

// In stream() method:
$stream = $this->client->chat()->createStreamed([
    'model' => $this->configuration->model,
    'messages' => $messages,
    // ... other params
]);

// OpenAI SDK constructs: https://api.openai.com/v1/chat/completions
```

**Endpoints used by OpenAI:**
| Purpose | Full Endpoint |
|---------|---------------|
| Chat completions | `https://api.openai.com/v1/chat/completions` |
| Embeddings | `https://api.openai.com/v1/embeddings` |
| Models | `https://api.openai.com/v1/models` |
| Completions (legacy) | `https://api.openai.com/v1/completions` |

---

### 3. OPENROUTER PROVIDER
**File:** `src/Services/Providers/OpenRouterProvider.php`

```php
// Uses OpenAI SDK but with OpenRouter's base URL
public function __construct(protected LLMConfiguration $configuration) {
    $this->client = OpenAI::factory()
        ->withApiKey($configuration->api_key)
        ->withBaseUri('https://openrouter.ai/api/v1')  // OpenRouter endpoint
        ->make();
}

// Streams via OpenAI SDK interface (OpenRouter is OpenAI-compatible)
$stream = $this->client->chat()->createStreamed([...]);

// Constructs: https://openrouter.ai/api/v1/chat/completions
```

**Note:** OpenRouter uses the same API structure as OpenAI!

---

### 4. ANTHROPIC PROVIDER
**File:** `src/Services/Providers/AnthropicProvider.php`

```php
// Uses native Anthropic SDK (different from OpenAI)
public function __construct(protected LLMConfiguration $configuration) {
    $this->client = Anthropic::factory()
        ->withApiKey($configuration->api_key)
        ->make();
}

// Endpoint is managed by SDK (not configurable)
// Streams from: https://api.anthropic.com/v1/messages
```

---

### 5. CUSTOM PROVIDER
**File:** `src/Services/Providers/CustomProvider.php`

```php
// Fully configurable - allows any endpoint
public function __construct(protected LLMConfiguration $configuration) {
    // Expects endpoint URL in configuration
}

// Allows posting to custom URL
$response = Http::post($this->configuration->api_endpoint, [
    'model' => $this->configuration->model,
    'messages' => $messages,
    // ... params expected by the custom API
]);
```

---

## 🔍 HOW THE SYSTEM KNOWS WHICH ENDPOINT TO USE

### Step-by-step flow:

1. **User selects model** (e.g., "OpenRoute") → `configuration_id = 6`

2. **Controller receives request:**
   ```php
   public function streamReply(int $id, Request $request) {
       $configurationId = $validated['configuration_id'] ?? $conversation->configuration->id;
       $configuration = LLMConfiguration::findOrFail($configurationId);  // ← Query DB
   }
   ```

3. **Configuration object contains:**
   ```php
   $configuration->provider      // "openrouter" → determines which class
   $configuration->api_endpoint  // "https://openrouter.ai/api/v1" → where to send
   $configuration->api_key       // encrypted key → authentication
   $configuration->model         // "anthropic/claude-sonnet-4.5" → which model
   ```

4. **Controller instantiates provider:**
   ```php
   $provider = $this->llmManager
       ->config($configuration->id)  // ← Tells LLMManager which config
       ->getProvider();             // ← Returns correct provider class
   ```

5. **LLMManager matches provider type:**
   ```php
   public function getProvider(): LLMProviderInterface {
       return match ($this->configuration->provider) {
           'ollama' => new OllamaProvider($this->configuration),
           'openai' => new OpenAIProvider($this->configuration),
           'openrouter' => new OpenRouterProvider($this->configuration),
           'anthropic' => new AnthropicProvider($this->configuration),
           'custom' => new CustomProvider($this->configuration),
           default => throw new \Exception("Unsupported provider"),
       };
   }
   ```

6. **Provider uses `$configuration->api_endpoint`:**
   ```php
   // In OllamaProvider::stream()
   $endpoint = rtrim($this->configuration->api_endpoint, '/') . '/api/generate';
   // Uses the stored URL, constructs full endpoint
   
   // In OpenAIProvider::stream()
   // Passes to SDK, which appends /chat/completions
   ```

---

## 📈 ENDPOINT MATRIX: All Providers vs All Endpoints

```
┌─────────────────┬──────────────────────────────┬──────────────────────┬────────────────────┐
│ PROVIDER        │ BASE ENDPOINT                │ GENERATION ENDPOINT  │ EMBEDDINGS ENDPOINT│
├─────────────────┼──────────────────────────────┼──────────────────────┼────────────────────┤
│ Ollama          │ http://localhost:11434       │ /api/generate        │ /api/embeddings    │
│ OpenAI          │ https://api.openai.com/v1    │ /chat/completions    │ /embeddings        │
│ OpenRouter      │ https://openrouter.ai/api/v1 │ /chat/completions    │ NOT SUPPORTED      │
│ Anthropic       │ https://api.anthropic.com/v1 │ /messages            │ NOT SUPPORTED      │
│ Custom          │ User-defined                 │ User-defined         │ User-defined       │
└─────────────────┴──────────────────────────────┴──────────────────────┴────────────────────┘
```

---

## 🎛️ CONFIGURATION FIELDS: What Controls Endpoint Routing

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `provider` | string | Determines which PHP class to use | `"openrouter"` |
| `api_endpoint` | string | Base URL for HTTP requests | `"https://openrouter.ai/api/v1"` |
| `api_key` | string (encrypted) | Authentication token | `encrypt("sk-...")` |
| `model` | string | Model identifier/name | `"anthropic/claude-sonnet-4.5"` |
| `default_parameters` | array (JSON) | Default temp, tokens, top_p, etc. | `{"temperature": 0.7, ...}` |
| `capabilities` | array (JSON) | Features like streaming, vision, etc. | `{"streaming": true, ...}` |
| `is_active` | boolean | Whether config is available | `true/false` |

---

## 🚨 WHY OLLAMA CONNECTION FAILS

### Current error:
```
Streaming Error: Failed to connect to Ollama at http://localhost:11434/api/generate
```

### Root causes:
1. **Ollama not running** → Server not listening on port 11434
2. **Wrong hostname** → Config has `localhost` but Ollama is on different machine
3. **Wrong port** → Ollama running on different port (e.g., 11435)
4. **Firewall/Network** → Port 11434 is blocked
5. **Model not pulled** → Model exists in config but not downloaded in Ollama

### How to debug:
```bash
# 1. Check if Ollama is running
curl http://localhost:11434/api/tags

# 2. If error, check if it's running at all
lsof -i :11434

# 3. Pull the model manually
ollama pull qwen3:4b

# 4. Test generation
curl -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "model": "qwen3:4b",
    "prompt": "Hello",
    "stream": false
  }'
```

---

## 🔗 MULTIPLE ENDPOINTS: How System Decides Which One to Use

### Three levels of endpoint resolution:

### Level 1: Provider Type
```php
// From database: $config->provider = "openrouter"
// This determines: Use OpenRouterProvider class
```

### Level 2: Base Endpoint
```php
// From database: $config->api_endpoint = "https://openrouter.ai/api/v1"
// OpenRouterProvider uses this as base URL
```

### Level 3: Specific Endpoint
```php
// Provider appends to base:
// OpenRouter + OpenAI SDK = /chat/completions
// Result: https://openrouter.ai/api/v1/chat/completions
```

### If you have multiple Ollama instances:
```
Config 1: api_endpoint = http://localhost:11434     → Local Ollama
Config 2: api_endpoint = http://192.168.1.100:11434 → Remote Ollama
Config 3: api_endpoint = http://other-machine:8000  → Custom service

User selects which → Controller uses that config -> Connects to that endpoint
```

---

## 📋 SUMMARY TABLE

| Question | Answer |
|----------|--------|
| How many endpoints? | **5+ per provider**, varies by provider type |
| How does system know which? | **Reads `provider` field → Selects PHP class → Calls provider methods** |
| Are URLs hardcoded? | **NO** - stored in DB `api_endpoint` field |
| Can you use multiple servers? | **YES** - create multiple configs with different endpoints |
| Which takes precedence? | **Database config > Hardcoded defaults > Environment variables** |
| How does it handle parameters? | **`default_parameters` JSON → Merged with request params → Passed to provider** |

---

## 🎯 Configuration Examples

### To connect to LOCAL Ollama:
```php
[
    'provider' => 'ollama',
    'api_endpoint' => 'http://localhost:11434',
    'model' => 'qwen3:4b',
]
```

### To connect to REMOTE Ollama:
```php
[
    'provider' => 'ollama',
    'api_endpoint' => 'http://192.168.1.50:11434',  // ← Different IP
    'model' => 'mistral:7b',
]
```

### To connect to Custom LLM Server:
```php
[
    'provider' => 'custom',
    'api_endpoint' => 'http://my-llm-server.com/v1/generate',
    'api_key' => 'custom-token-123',
    'model' => 'my-model-name',
]
```

### To connect to OpenRouter (OpenAI-compatible):
```php
[
    'provider' => 'openrouter',
    'api_endpoint' => 'https://openrouter.ai/api/v1',
    'api_key' => env('OPENROUTER_API_KEY'),
    'model' => 'anthropic/claude-sonnet-4.5',
]
```

---

## 🔧 How to Add a New Provider with Custom Endpoints

1. Create new provider class: `src/Services/Providers/MyProviderProvider.php`
2. Implement `LLMProviderInterface`
3. Use `$this->configuration->api_endpoint` to get URL
4. Add case to `LLMManager::getProvider()` match statement
5. Create database config with `provider = 'my-provider'`
6. System automatically routes requests to your provider

---
