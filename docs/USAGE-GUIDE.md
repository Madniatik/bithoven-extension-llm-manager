# 📖 LLM Manager - Usage Guide

**Version:** 1.0.0  
**Last Updated:** 21 de noviembre de 2025

---

## 📑 Table of Contents

1. [Prompt Templates](#-prompt-templates)
2. [Knowledge Base (RAG)](#-knowledge-base-rag)
3. [Tool Definitions](#-tool-definitions)
4. [Conversations](#-conversations)
5. [Statistics Dashboard](#-statistics-dashboard)
6. [Best Practices](#-best-practices)

---

## 📝 Prompt Templates

### Overview

Prompt Templates allow you to create reusable prompts with variable substitution, making it easy to standardize LLM interactions across your application.

### Creating Templates (Admin UI)

1. Navigate to `/admin/llm/prompts`
2. Click **"Create Template"**
3. Fill in the form:
   - **Name:** Descriptive name
   - **Slug:** unique-identifier
   - **Category:** customer-service, development, etc.
   - **Template Content:** Use `{{variable}}` syntax
   - **Variables:** List required variables
   - **Is Active:** Enable/disable
   - **Is Global:** Available to all extensions

**Example Template:**

```
Name: Email Response Template
Slug: email-response
Category: customer-service
Extension: llm-manager

Template:
Dear {{customer_name}},

Thank you for contacting us about {{issue_topic}}.

{{response_content}}

Best regards,
{{agent_name}}

Variables: customer_name, issue_topic, response_content, agent_name
```

### Using Templates (Code)

#### Method 1: Using Model Directly

```php
use Bithoven\LLMManager\Models\LLMPromptTemplate;

$template = LLMPromptTemplate::where('slug', 'email-response')
    ->active()
    ->first();

$rendered = $template->render([
    'customer_name' => 'Alice Johnson',
    'issue_topic' => 'billing inquiry',
    'response_content' => 'We have reviewed your account...',
    'agent_name' => 'Support Team',
]);

echo $rendered;
// Output:
// Dear Alice Johnson,
//
// Thank you for contacting us about billing inquiry.
//
// We have reviewed your account...
//
// Best regards,
// Support Team
```

#### Method 2: Using LLM Facade

```php
use Bithoven\LLMManager\Facades\LLM;

// Generate response using template + LLM
$response = LLM::template('email-response', [
    'customer_name' => 'Alice Johnson',
    'issue_topic' => 'billing inquiry',
    'response_content' => 'We have reviewed your account...',
    'agent_name' => 'Support Team',
]);

// $response contains LLM-generated response
```

### Template Validation

```php
$template = LLMPromptTemplate::find(1);

// Check if all required variables are provided
$isValid = $template->validateVariables([
    'customer_name' => 'Alice',
    'issue_topic' => 'billing',
]);

// Get missing variables
$missing = $template->getMissingVariables([
    'customer_name' => 'Alice',
    // issue_topic is missing
]);
// Returns: ['issue_topic', 'response_content', 'agent_name']
```

### Categories

Organize templates by category:
- `customer-service` - Customer support responses
- `development` - Code generation, documentation
- `marketing` - Content creation, ads
- `analytics` - Data analysis prompts
- `general` - Miscellaneous

### Best Practices

✅ **Use clear variable names:** `{{customer_name}}` not `{{cn}}`  
✅ **Provide examples:** Include example_values in database  
✅ **Keep templates focused:** One purpose per template  
✅ **Version control:** Use slug + version (e.g., `email-v2`)  
✅ **Test thoroughly:** Validate with edge cases  

❌ **Avoid:** Hardcoded values, overly complex logic, ambiguous variables

---

## 📚 Knowledge Base (RAG)

### Overview

The Knowledge Base provides Retrieval-Augmented Generation (RAG) capabilities, allowing LLMs to access and reference your documentation, guides, and knowledge articles.

### Creating Documents (Admin UI)

1. Navigate to `/admin/llm/knowledge-base`
2. Click **"Add Document"**
3. Fill in the form:
   - **Title:** Document name
   - **Document Type:** documentation, guide, faq, code, other
   - **Extension:** Select extension or leave global
   - **Content:** Markdown or plain text
   - **Metadata:** JSON (optional)
   - **Auto-Index:** Enable for automatic chunking

**Example Document:**

```
Title: LLM Manager Quick Start
Type: documentation
Extension: llm-manager
Auto-Index: ✓

Content:
# LLM Manager Quick Start

## Installation
Follow these steps to install...

## Configuration
1. Set up your provider...
2. Configure API keys...

## First Request
Use the LLM facade to make your first request...

Metadata:
{
  "author": "Bithoven Team",
  "version": "1.0.0",
  "tags": ["quickstart", "installation"]
}
```

### Auto-Indexing

When enabled, documents are automatically:
1. **Chunked** - Split into 1000-character chunks (configurable)
2. **Embedded** - Converted to vector embeddings
3. **Indexed** - Stored for semantic search

**Configuration:**

```php
// config/llm-manager.php
'knowledge_base' => [
    'auto_index' => true,
    'chunk_size' => 1000,
    'chunk_overlap' => 200,
    'embedding_model' => 'text-embedding-3-small',
    'embedding_provider' => 'openai',
],
```

### Using RAG (Code)

#### Search Similar Documents

```php
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

$query = "How do I install the extension?";
$results = LLMDocumentKnowledgeBase::searchSimilar($query, 'llm-manager', 5);

foreach ($results as $doc) {
    echo "Title: {$doc->title}\n";
    echo "Similarity: {$doc->similarity_score}\n";
    echo "Excerpt: " . substr($doc->content, 0, 200) . "...\n\n";
}
```

#### Generate with Context

```php
use Bithoven\LLMManager\Facades\LLM;

$query = "How do I configure OpenAI provider?";

// Search knowledge base
$docs = LLMDocumentKnowledgeBase::searchSimilar($query, 'llm-manager', 3);

// Build context
$context = $docs->pluck('content')->join("\n\n");

// Generate response with context
$response = LLM::generate("Based on this documentation:\n\n{$context}\n\nAnswer: {$query}");

echo $response['content'];
```

#### Using RAG Service

```php
use Bithoven\LLMManager\Services\RAG\LLMRAGService;

$ragService = app(LLMRAGService::class);

// Index a document
$ragService->indexDocument($documentId);

// Search
$results = $ragService->search($query, 'llm-manager', 5);

// Get chunks for document
$chunks = $ragService->getChunks($documentId);
```

### Document Types

- **documentation** - Technical documentation
- **guide** - How-to guides, tutorials
- **faq** - Frequently asked questions
- **code** - Code examples, snippets
- **other** - Miscellaneous content

### Viewing Documents

Navigate to document detail page to see:
- ✅ Full content
- ✅ Generated chunks (accordion view)
- ✅ Indexing status
- ✅ Last indexed timestamp
- ✅ Metadata

### Re-indexing

**When to re-index:**
- Content has been updated
- Chunk settings changed
- Embedding model changed

**How to re-index:**

1. **Via UI:** Click "Re-index" button in document actions
2. **Via Code:**
   ```php
   $ragService->indexDocument($documentId);
   ```
3. **Bulk:**
   ```php
   LLMDocumentKnowledgeBase::where('is_indexed', false)->each(function ($doc) {
       $ragService->indexDocument($doc->id);
   });
   ```

### Best Practices

✅ **Structure content:** Use headings, lists, clear sections  
✅ **Keep updated:** Re-index when content changes  
✅ **Use metadata:** Add tags, version, author for filtering  
✅ **Optimize chunks:** Test chunk_size for your content  
✅ **Monitor embeddings:** Check costs (OpenAI charges per token)  

❌ **Avoid:** Very short documents (<100 words), duplicate content, outdated docs

---

## 🛠️ Tool Definitions

### Overview

Tool Definitions allow you to register custom tools (functions) that LLMs can call to perform actions or retrieve data.

### Creating Tools (Admin UI)

1. Navigate to `/admin/llm/tools`
2. Click **"Register Tool"**
3. Fill in the form:
   - **Name:** Tool name
   - **Slug:** unique-identifier
   - **Description:** What the tool does
   - **Handler Class:** PHP class (e.g., `App\LLM\Tools\WeatherTool`)
   - **Handler Method:** Method name (usually `execute`)
   - **Parameters Schema:** JSON schema
   - **Is Active:** Enable/disable

**Example Tool:**

```
Name: Get Weather
Slug: get-weather
Description: Get current weather for a location
Handler Class: App\LLM\Tools\WeatherTool
Handler Method: execute
Extension: llm-manager

Parameters Schema:
{
  "type": "object",
  "properties": {
    "location": {
      "type": "string",
      "description": "City name or coordinates"
    },
    "units": {
      "type": "string",
      "enum": ["celsius", "fahrenheit"],
      "default": "celsius"
    }
  },
  "required": ["location"]
}
```

### Creating Handler Class

```php
<?php

namespace App\LLM\Tools;

class WeatherTool
{
    public function execute(array $parameters): array
    {
        $location = $parameters['location'];
        $units = $parameters['units'] ?? 'celsius';
        
        // Call weather API (example)
        $data = $this->fetchWeatherData($location, $units);
        
        return [
            'success' => true,
            'location' => $location,
            'temperature' => $data['temp'],
            'conditions' => $data['conditions'],
            'units' => $units,
        ];
    }
    
    private function fetchWeatherData(string $location, string $units): array
    {
        // Implementation here...
        // Call external API, process data, return result
        
        return [
            'temp' => 22,
            'conditions' => 'Sunny',
        ];
    }
}
```

### Using Tools (Code)

#### Direct Execution

```php
$handler = new \App\LLM\Tools\WeatherTool();

$result = $handler->execute([
    'location' => 'San Francisco',
    'units' => 'celsius',
]);

// $result = [
//     'success' => true,
//     'location' => 'San Francisco',
//     'temperature' => 18,
//     'conditions' => 'Foggy',
//     'units' => 'celsius'
// ]
```

#### Via Tool Registry

```php
use Bithoven\LLMManager\Models\LLMToolDefinition;

$tool = LLMToolDefinition::where('slug', 'get-weather')
    ->active()
    ->first();

// Instantiate handler
$handlerClass = $tool->handler_class;
$handlerMethod = $tool->handler_method;

$handler = new $handlerClass();
$result = $handler->$handlerMethod([
    'location' => 'London',
    'units' => 'celsius',
]);
```

#### With LLM Integration (Future)

```php
use Bithoven\LLMManager\Facades\LLM;

// LLM can call tools automatically
$response = LLM::withTools(['get-weather', 'get-stock-price'])
    ->generate("What's the weather in Paris and current AAPL stock price?");

// LLM will:
// 1. Recognize it needs to call tools
// 2. Call get-weather with location="Paris"
// 3. Call get-stock-price with symbol="AAPL"
// 4. Generate response using tool results
```

### Parameter Validation

Tools automatically validate parameters against the JSON schema:

```php
// Valid parameters
$result = $handler->execute([
    'location' => 'Tokyo',
    'units' => 'celsius',
]);
// ✅ Success

// Invalid parameters (missing required)
$result = $handler->execute([
    'units' => 'celsius',
]);
// ❌ Error: Missing required parameter 'location'

// Invalid enum value
$result = $handler->execute([
    'location' => 'Tokyo',
    'units' => 'kelvin',  // Not in enum
]);
// ❌ Error: Invalid value for 'units'
```

### Tool Categories

Common tool patterns:
- **Data Retrieval:** Weather, stock prices, news
- **Database Operations:** Query, insert, update
- **External APIs:** Translate, geocode, payment
- **File Operations:** Read, write, process
- **Calculations:** Math, statistics, analytics

### Best Practices

✅ **Clear descriptions:** Help LLM understand when to use tool  
✅ **Validate inputs:** Use JSON schema required fields  
✅ **Handle errors:** Return error messages in response  
✅ **Log executions:** Track tool usage for debugging  
✅ **Test thoroughly:** Validate with edge cases  

❌ **Avoid:** Blocking operations, unvalidated inputs, missing error handling

---

## 💬 Conversations

### Overview

Conversations provide persistent chat sessions with message history, allowing context-aware multi-turn interactions.

### Creating Conversations (Code)

```php
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Facades\LLM;

// Create session
$session = LLMConversationSession::create([
    'extension_slug' => 'llm-manager',
    'configuration_id' => 1,
    'user_id' => auth()->id(),
    'title' => 'Customer Support Chat',
    'status' => 'active',
    'metadata' => [
        'customer_id' => 12345,
        'issue_type' => 'billing',
    ],
]);

// Add user message
$userMessage = $session->messages()->create([
    'role' => 'user',
    'content' => 'I have a question about my recent invoice.',
]);

// Generate AI response
$response = LLM::conversation($session->id)
    ->generate($userMessage->content);

// Add assistant message
$assistantMessage = $session->messages()->create([
    'role' => 'assistant',
    'content' => $response['content'],
    'tokens_used' => $response['usage']['total_tokens'] ?? 0,
    'cost' => $response['cost'] ?? 0,
]);

// Log activity
$session->logs()->create([
    'event_type' => 'message_sent',
    'event_data' => [
        'message_id' => $userMessage->id,
        'content_length' => strlen($userMessage->content),
    ],
]);
```

### Viewing Conversations (Admin UI)

1. Navigate to `/admin/llm/conversations`
2. View list of all sessions
3. Click session ID to see full conversation
4. View message history, tokens, cost, activity logs

**Session Details Page Shows:**
- ✅ Session info (ID, config, status, timestamps)
- ✅ Complete message history (user ↔ assistant)
- ✅ Token usage and costs
- ✅ Activity logs (events timeline)
- ✅ Metadata

### Exporting Conversations

**Via UI:**
1. Go to conversation detail page
2. Click **"Export"** dropdown
3. Choose format: JSON or CSV
4. Download file

**Via Code:**

```php
$session = LLMConversationSession::with('messages')->find(1);

// Export as JSON
$json = $session->toJson();
file_put_contents('conversation.json', $json);

// Export as array
$data = [
    'session_id' => $session->session_id,
    'title' => $session->title,
    'messages' => $session->messages->map(fn($msg) => [
        'role' => $msg->role,
        'content' => $msg->content,
        'timestamp' => $msg->created_at,
    ]),
];
```

### Conversation Status

- **active** - Currently ongoing
- **ended** - User ended conversation
- **expired** - Auto-ended after inactivity
- **archived** - Moved to archive

### Managing Context

```php
// Get last N messages for context
$context = $session->messages()
    ->latest()
    ->take(10)
    ->get()
    ->reverse()
    ->map(fn($msg) => [
        'role' => $msg->role,
        'content' => $msg->content,
    ])
    ->toArray();

// Send to LLM with context
$response = LLM::generate($newMessage, $context);
```

### Best Practices

✅ **Set meaningful titles:** Help identify conversations  
✅ **Use metadata:** Store context (customer ID, issue type)  
✅ **Manage context window:** Limit messages sent to LLM  
✅ **End sessions:** Mark as 'ended' when done  
✅ **Archive old chats:** Keep database clean  

❌ **Avoid:** Unlimited context (token limits), sensitive data in logs

---

## 📊 Statistics Dashboard

### Overview

The Statistics Dashboard provides insights into LLM usage, costs, and performance across your application.

### Accessing Dashboard

Navigate to: `/admin/llm/statistics`

**Dashboard Shows:**
- **Quick Stats Cards:**
  - Total tokens used
  - Total cost
  - Total sessions
  - Average cost per session
  
- **Provider Distribution Chart:**
  - Pie chart showing usage by provider
  - Ollama, OpenAI, Anthropic, etc.
  
- **Monthly Usage Chart:**
  - Line chart showing tokens over time
  - Tracks daily/weekly/monthly trends

### Date Filters

**Preset ranges:**
- Last 7 days
- Last 30 days
- Last 90 days
- Custom range (date picker)

### Exporting Stats

1. Select date range
2. Click **"Export"** button
3. Choose format: JSON or CSV
4. Download report

**Export includes:**
- Total requests
- Total tokens
- Total cost
- Breakdown by provider
- Breakdown by extension
- Top conversations
- Cost trends

### Tracking Custom Metrics

```php
use Bithoven\LLMManager\Models\LLMCustomMetric;

// Log custom metric
LLMCustomMetric::create([
    'extension_slug' => 'my-extension',
    'metric_name' => 'email_responses_generated',
    'metric_value' => 1,
    'metric_unit' => 'count',
    'metadata' => [
        'category' => 'customer-service',
        'template_used' => 'email-response',
    ],
]);

// Query metrics
$totalEmails = LLMCustomMetric::where('metric_name', 'email_responses_generated')
    ->sum('metric_value');
```

### Usage Logs

All LLM requests are logged:

```php
use Bithoven\LLMManager\Models\LLMUsageLog;

$logs = LLMUsageLog::where('extension_slug', 'llm-manager')
    ->whereBetween('created_at', [now()->subDays(7), now()])
    ->get();

foreach ($logs as $log) {
    echo "Provider: {$log->provider}\n";
    echo "Model: {$log->model}\n";
    echo "Tokens: {$log->total_tokens}\n";
    echo "Cost: \${$log->cost}\n";
}
```

---

## 🎯 Best Practices

### Performance

✅ **Use caching:** Enable provider cache for model lists  
✅ **Batch requests:** Group similar requests when possible  
✅ **Optimize prompts:** Shorter prompts = lower costs  
✅ **Monitor usage:** Set budget limits and alerts  

### Security

✅ **Validate inputs:** Never trust user input in prompts  
✅ **Use permissions:** Restrict access to admin features  
✅ **Secure API keys:** Store in .env, never commit  
✅ **Rate limiting:** Prevent abuse with throttling  

### Cost Management

✅ **Choose models wisely:** Llama3 (local) vs GPT-4 (expensive)  
✅ **Set limits:** Monthly budgets per extension  
✅ **Track usage:** Monitor Statistics Dashboard  
✅ **Use embeddings efficiently:** Cache results when possible  

### Development

✅ **Test locally:** Use Ollama for development  
✅ **Version templates:** Track changes to prompts  
✅ **Document tools:** Clear descriptions for LLM  
✅ **Log everything:** Use conversation logs for debugging  

---

## 📞 Need Help?

- **API Reference:** [API-REFERENCE.md](API-REFERENCE.md)
- **Configuration:** [CONFIGURATION.md](CONFIGURATION.md)
- **Examples:** [EXAMPLES.md](EXAMPLES.md)
- **Support:** support@bithoven.com

---

**Happy coding!** 🚀
