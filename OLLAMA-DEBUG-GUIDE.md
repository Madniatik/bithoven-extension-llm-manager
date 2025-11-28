# 🔴 DEBUGGING OLLAMA CONNECTION ERROR

## Current Issue
```
Streaming Error: Failed to connect to Ollama at http://localhost:11434/api/generate
```

---

## 🎯 Root Cause Analysis

The error occurs in `OllamaProvider.php` at this line:
```php
$stream = @fopen($endpoint, 'r', false, $context);

if (!$stream) {
    throw new \Exception("Failed to connect to Ollama at {$endpoint}");
    // ← This is where your error comes from
}
```

This means the PHP `fopen()` call cannot connect to the URL stored in the database.

---

## ✅ Step-by-Step Debugging Checklist

### 1. Verify the Configured Endpoint

Check what endpoint is stored in the database:

```bash
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL && php artisan tinker << 'EOF'
$configs = DB::table('llm_manager_configurations')->where('provider', 'ollama')->get();
foreach ($configs as $config) {
    echo "ID: {$config->id}\n";
    echo "Name: {$config->name}\n";
    echo "Endpoint: {$config->api_endpoint}\n";
    echo "Model: {$config->model}\n";
    echo "---\n";
}
EOF
```

**Expected output:**
```
ID: 1
Name: Ollama Qwen 3
Endpoint: http://localhost:11434
Model: qwen3:4b
---
```

---

### 2. Check if Ollama Service is Running

```bash
# Check if port 11434 is listening
lsof -i :11434

# If nothing appears, Ollama is NOT running
```

**Expected output:**
```
COMMAND  PID   USER   FD TYPE             DEVICE SIZE/OFF NODE NAME
ollama   xxx   user   10u IPv4 0x... 0t0 TCP localhost:11434 (LISTEN)
```

**If nothing appears:**
```bash
# Start Ollama (macOS)
/Applications/Ollama.app/Contents/MacOS/Ollama serve

# Or on Linux/Windows:
ollama serve
```

---

### 3. Test Direct HTTP Connection

```bash
# Test if the endpoint is reachable
curl -v http://localhost:11434/api/tags

# Expected: Should return JSON list of models
# Error: "Connection refused" → Ollama not running
# Error: "Empty reply" → Wrong port
```

**Successful response looks like:**
```json
{
  "models": [
    {
      "name": "qwen3:4b:latest",
      "modified_at": "2025-11-28T18:00:00Z",
      "size": 2800000000,
      "digest": "abc123...",
      "details": {...}
    }
  ]
}
```

---

### 4. Check if Required Model is Downloaded

```bash
# List models available locally
ollama list

# Expected output:
# NAME              ID         SIZE   MODIFIED
# qwen3:4b          abc123...  2.8GB  2 days ago
# deepseek-coder    def456...  5.5GB  3 days ago
```

**If models are missing:**
```bash
# Pull the model
ollama pull qwen3:4b
ollama pull deepseek-coder:6.7b
```

---

### 5. Test Ollama API Directly

```bash
# Test generation (non-streaming)
curl -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "model": "qwen3:4b",
    "prompt": "What is 2+2?",
    "stream": false
  }'
```

**Expected response:**
```json
{
  "model": "qwen3:4b:latest",
  "created_at": "2025-11-28T18:45:00Z",
  "response": "2 + 2 = 4",
  "done": true,
  "total_duration": 1234567890,
  "prompt_eval_count": 8,
  "eval_count": 5
}
```

---

### 6. Test PHP fopen() Connection (From Laravel)

```bash
cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL && php artisan tinker << 'EOF'
$endpoint = "http://localhost:11434/api/generate";

// Test if PHP can connect
$stream = @fopen($endpoint, 'r');

if ($stream) {
    echo "✅ Connection successful!\n";
    fclose($stream);
} else {
    echo "❌ Connection failed!\n";
    echo "Check if:\n";
    echo "1. Ollama is running: lsof -i :11434\n";
    echo "2. Endpoint is correct: {$endpoint}\n";
    echo "3. Port is not blocked\n";
}
EOF
```

---

## 🔍 Common Scenarios & Solutions

### Scenario 1: Ollama Not Running

**Symptom:** `Connection refused`

**Solution:**
```bash
# Check if running
ps aux | grep ollama

# If not found, start it
/Applications/Ollama.app/Contents/MacOS/Ollama serve

# Or check if it's a background service
launchctl list | grep ollama
```

---

### Scenario 2: Wrong Port Configuration

**Symptom:** Connection works for one provider but not another

**Check database:**
```bash
php artisan tinker << 'EOF'
$configs = DB::table('llm_manager_configurations')->where('provider', 'ollama')->get();
foreach ($configs as $c) {
    echo "Config {$c->id}: {$c->api_endpoint}\n";
}
EOF
```

**If ports are mixed:**
- Config 1: `http://localhost:11434` ✅
- Config 2: `http://localhost:11435` ❌ (wrong port)

**Fix:**
```bash
php artisan tinker << 'EOF'
$config = DB::table('llm_manager_configurations')->find(2);
DB::table('llm_manager_configurations')
    ->where('id', 2)
    ->update(['api_endpoint' => 'http://localhost:11434']);
echo "✅ Updated config 2\n";
EOF
```

---

### Scenario 3: Model Not Downloaded

**Symptom:** Connection works but model generation fails

**Check what's installed:**
```bash
ollama list
```

**Pull missing model:**
```bash
ollama pull qwen3:4b
ollama pull deepseek-coder:6.7b
```

**Verify model name in config matches downloaded:**
```bash
# Database config says: model = "qwen3:4b"
# Ollama has: "qwen3:4b:latest"

# These are equivalent (Ollama auto-appends :latest)
# But use "qwen3:4b" without version suffix
```

---

### Scenario 4: Ollama on Different Machine

**Current setup (fails):**
```
api_endpoint = "http://localhost:11434"
Your machine: Mac/Windows with Ollama not running
```

**Fix - Remote Ollama:**
```bash
# Step 1: Get IP of Ollama server
ping ollama-server.local

# Step 2: Update config in database
php artisan tinker << 'EOF'
DB::table('llm_manager_configurations')
    ->where('provider', 'ollama')
    ->update(['api_endpoint' => 'http://192.168.1.50:11434']);
echo "✅ Updated to remote Ollama\n";
EOF

# Step 3: Test connection
curl http://192.168.1.50:11434/api/tags
```

---

### Scenario 5: Firewall Blocking Connection

**Test if port is open:**
```bash
nc -zv localhost 11434

# If successful:
# nc: connect to localhost port 11434 (tcp) succeeded!

# If fails:
# nc: connect to localhost port 11434 (tcp) failed: Connection refused
```

**On macOS, allow Ollama through firewall:**
```bash
# System Preferences → Security & Privacy → Firewall Options
# Add Ollama.app to allowed apps
```

---

## 📋 Complete Testing Command Sequence

Run these in order to find the problem:

```bash
#!/bin/bash

echo "🔍 OLLAMA DEBUGGING SEQUENCE"
echo "============================\n"

# 1. Check if port is open
echo "1️⃣ Checking if port 11434 is open..."
nc -zv localhost 11434 && echo "✅ Port is open" || echo "❌ Port is closed"

# 2. Check if Ollama service is running
echo "\n2️⃣ Checking Ollama service..."
lsof -i :11434 | grep ollama && echo "✅ Ollama running" || echo "❌ Ollama not running"

# 3. Test HTTP connection
echo "\n3️⃣ Testing HTTP connection..."
curl -s http://localhost:11434/api/tags > /dev/null && echo "✅ HTTP working" || echo "❌ HTTP failed"

# 4. List available models
echo "\n4️⃣ Available models:"
curl -s http://localhost:11434/api/tags | jq '.models[].name'

# 5. Test generation
echo "\n5️⃣ Testing model generation..."
curl -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "model": "qwen3:4b",
    "prompt": "test",
    "stream": false
  }' \
  -s | jq '.response'

echo "\n✅ All tests passed!"
```

---

## 🛠️ Quick Fixes (Most Common)

### Fix #1: Start Ollama (Most Likely)
```bash
# macOS
/Applications/Ollama.app/Contents/MacOS/Ollama serve

# Or enable as service
brew services start ollama
```

---

### Fix #2: Pull Required Models
```bash
ollama pull qwen3:4b
ollama pull deepseek-coder:6.7b
```

---

### Fix #3: Update Config if Using Remote Ollama
```bash
php artisan tinker << 'EOF'
DB::table('llm_manager_configurations')
    ->where('provider', 'ollama')
    ->update(['api_endpoint' => 'http://YOUR_OLLAMA_IP:11434']);
EOF
```

---

## 📊 Current Status

**Your Ollama Configuration:**
- Endpoint: `http://localhost:11434` (stored in DB)
- Models: `qwen3:4b`, `deepseek-coder:6.7b`
- Status: NOT RUNNING (causing the error)

**Next Steps:**
1. Start Ollama service
2. Verify models are downloaded: `ollama list`
3. Test from terminal: `curl http://localhost:11434/api/tags`
4. Try streaming again in the web UI
5. If still fails, share the error and I'll debug further

---
