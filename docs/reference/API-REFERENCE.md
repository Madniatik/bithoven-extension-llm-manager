# 🔧 LLM Manager - API Reference

**Version:** 0.1.0  
**Last Updated:** 21 de noviembre de 2025

---

## 📑 Table of Contents

1. [Facades](#-facades)
2. [Services](#-services)
3. [Models](#-models)
4. [Events](#-events)
5. [Configuration](#-configuration)
6. [Helpers](#-helpers)

---

## 🎭 Facades

### `LLM`

Main facade for interacting with LLM providers.

```php
use Bithoven\LLMManager\Facades\LLM;
```

#### `provider(string $provider): self`

Set the LLM provider.

```php
LLM::provider('openai')->generate('Hello!');
LLM::provider('anthropic')->generate('Hello!');
LLM::provider('ollama')->generate('Hello!');
```

**Parameters:**
- `$provider` (string) - Provider slug: `openai`, `anthropic`, `ollama`, `groq`, `google`

**Returns:** `self` (chainable)

---

#### `model(string $model): self`

Set the model to use.

```php
LLM::provider('openai')
    ->model('gpt-4')
    ->generate('Hello!');
    
LLM::provider('ollama')
    ->model('llama3:8b')
    ->generate('Hello!');
```

**Parameters:**
- `$model` (string) - Model identifier

**Returns:** `self` (chainable)

---

#### `generate(string $prompt, array $context = []): array`

Generate a completion.

```php
$response = LLM::generate('Write a poem about Laravel.');

// With context
$response = LLM::generate('Continue the conversation.', [
    ['role' => 'user', 'content' => 'Hi!'],
    ['role' => 'assistant', 'content' => 'Hello! How can I help?'],
]);
```

**Parameters:**
- `$prompt` (string) - The prompt text
- `$context` (array) - Optional conversation context

**Returns:** `array`
```php
[
    'content' => 'The generated response...',
    'usage' => [
        'prompt_tokens' => 10,
        'completion_tokens' => 50,
        'total_tokens' => 60,
    ],
    'cost' => 0.0012,
    'model' => 'gpt-4',
    'provider' => 'openai',
]
```

**Throws:**
- `LLMException` - If generation fails

---

#### `template(string $slug, array $variables = []): array`

Generate using a prompt template.

```php
$response = LLM::template('email-response', [
    'customer_name' => 'Alice',
    'issue_topic' => 'billing',
    'response_content' => 'We have reviewed...',
    'agent_name' => 'Support Team',
]);
```

**Parameters:**
- `$slug` (string) - Template slug
- `$variables` (array) - Variables to replace in template

**Returns:** `array` - Same as `generate()`

**Throws:**
- `TemplateNotFoundException` - If template not found
- `MissingVariablesException` - If required variables missing

---

#### `conversation(int $sessionId): self`

Set conversation session for context.

```php
$response = LLM::conversation(123)
    ->generate('What did we discuss earlier?');
```

**Parameters:**
- `$sessionId` (int) - Conversation session ID

**Returns:** `self` (chainable)

---

#### `withTools(array $tools): self`

Enable tool calling (function calling).

```php
$response = LLM::withTools(['get-weather', 'search-database'])
    ->generate('What is the weather in Paris?');
```

**Parameters:**
- `$tools` (array) - Array of tool slugs

**Returns:** `self` (chainable)

---

#### `maxTokens(int $tokens): self`

Set max tokens for completion.

```php
LLM::maxTokens(500)->generate('Write a summary.');
```

**Parameters:**
- `$tokens` (int) - Maximum tokens

**Returns:** `self` (chainable)

---

#### `temperature(float $temperature): self`

Set temperature (creativity level).

```php
LLM::temperature(0.7)->generate('Be creative!');
```

**Parameters:**
- `$temperature` (float) - Temperature 0.0 to 2.0

**Returns:** `self` (chainable)

---

## 🔧 Services

### `LLMManager`

Core service for managing LLM interactions.

```php
use Bithoven\LLMManager\Services\LLMManager;

$manager = app(LLMManager::class);
```

#### `getProviders(): Collection`

Get all available providers.

```php
$providers = $manager->getProviders();
// Returns collection of LLMConfiguration models
```

---

#### `getProviderModels(string $provider): array`

Get available models for a provider.

```php
$models = $manager->getProviderModels('openai');
// Returns: ['gpt-4', 'gpt-3.5-turbo', ...]
```

---

#### `validateConfiguration(LLMConfiguration $config): bool`

Validate provider configuration.

```php
$config = LLMConfiguration::find(1);
$isValid = $manager->validateConfiguration($config);
```

---

### `PromptService`

Service for managing prompt templates.

```php
use Bithoven\LLMManager\Services\PromptService;

$service = app(PromptService::class);
```

#### `render(string $slug, array $variables): string`

Render a template.

```php
$rendered = $service->render('email-response', [
    'customer_name' => 'Alice',
]);
```

---

#### `validate(string $slug, array $variables): array`

Validate variables.

```php
$missing = $service->validate('email-response', [
    'customer_name' => 'Alice',
]);
// Returns: ['issue_topic', 'response_content', 'agent_name']
```

---

### `RAGService`

Service for Retrieval-Augmented Generation.

```php
use Bithoven\LLMManager\Services\RAG\LLMRAGService;

$rag = app(LLMRAGService::class);
```

#### `indexDocument(int $documentId): void`

Index a document for semantic search.

```php
$rag->indexDocument(1);
```

---

#### `search(string $query, ?string $extension = null, int $limit = 5): Collection`

Search similar documents.

```php
$results = $rag->search('How to install?', 'llm-manager', 5);

foreach ($results as $doc) {
    echo $doc->title;
    echo $doc->similarity_score;
}
```

---

#### `getChunks(int $documentId): Collection`

Get all chunks for a document.

```php
$chunks = $rag->getChunks(1);
```

---

### `ConversationService`

Service for managing conversation sessions.

```php
use Bithoven\LLMManager\Services\ConversationService;

$conversations = app(ConversationService::class);
```

#### `create(array $data): LLMConversationSession`

Create a new conversation session.

```php
$session = $conversations->create([
    'extension_slug' => 'llm-manager',
    'configuration_id' => 1,
    'user_id' => auth()->id(),
    'title' => 'Support Chat',
]);
```

---

#### `addMessage(int $sessionId, string $role, string $content): LLMConversationMessage`

Add a message to session.

```php
$message = $conversations->addMessage(1, 'user', 'Hello!');
```

---

#### `getContext(int $sessionId, int $limit = 10): array`

Get conversation context for LLM.

```php
$context = $conversations->getContext(1, 10);
// Returns array suitable for LLM::generate($prompt, $context)
```

---

#### `end(int $sessionId): void`

End a conversation.

```php
$conversations->end(1);
```

---

## 📦 Models

### `LLMConfiguration`

Provider configurations.

#### Attributes

- `id` (int)
- `extension_slug` (string)
- `provider` (string) - openai, anthropic, ollama, etc.
- `model` (string)
- `api_key` (string, encrypted)
- `api_url` (string, nullable)
- `is_active` (boolean)
- `settings` (array, JSON)
- `created_at` (datetime)
- `updated_at` (datetime)

#### Methods

##### `isValid(): bool`

Check if configuration is valid.

```php
$config = LLMConfiguration::find(1);
if ($config->isValid()) {
    // Use configuration
}
```

---

##### `getModels(): array`

Get available models for this provider.

```php
$models = $config->getModels();
```

---

##### Scopes

```php
// Get active configurations
LLMConfiguration::active()->get();

// Get by provider
LLMConfiguration::provider('openai')->get();

// Get by extension
LLMConfiguration::forExtension('llm-manager')->get();
```

---

### `LLMPromptTemplate`

Reusable prompt templates.

#### Attributes

- `id` (int)
- `extension_slug` (string)
- `name` (string)
- `slug` (string, unique)
- `description` (text, nullable)
- `template` (text) - Template with {{variables}}
- `variables` (array, JSON)
- `example_values` (array, JSON, nullable)
- `category` (string, nullable)
- `is_active` (boolean)
- `is_global` (boolean)
- `usage_count` (int)
- `created_at` (datetime)
- `updated_at` (datetime)

#### Methods

##### `render(array $variables): string`

Render template with variables.

```php
$template = LLMPromptTemplate::where('slug', 'greeting')->first();
$rendered = $template->render(['name' => 'Alice']);
```

---

##### `validateVariables(array $variables): bool`

Check if all required variables are provided.

```php
$isValid = $template->validateVariables(['name' => 'Alice']);
```

---

##### `getMissingVariables(array $variables): array`

Get list of missing required variables.

```php
$missing = $template->getMissingVariables(['name' => 'Alice']);
```

---

##### Scopes

```php
// Get active templates
LLMPromptTemplate::active()->get();

// Get global templates
LLMPromptTemplate::global()->get();

// Get by category
LLMPromptTemplate::category('customer-service')->get();

// Get by extension
LLMPromptTemplate::forExtension('llm-manager')->get();
```

---

### `LLMDocumentKnowledgeBase`

Knowledge base documents for RAG.

#### Attributes

- `id` (int)
- `extension_slug` (string, nullable)
- `title` (string)
- `content` (text)
- `document_type` (string) - documentation, guide, faq, code, other
- `metadata` (array, JSON, nullable)
- `content_chunks` (array, JSON, nullable)
- `embedding` (array, JSON, nullable)
- `is_indexed` (boolean)
- `last_indexed_at` (datetime, nullable)
- `auto_index` (boolean)
- `created_at` (datetime)
- `updated_at` (datetime)

#### Methods

##### `index(): void`

Index document (create chunks, generate embeddings).

```php
$doc = LLMDocumentKnowledgeBase::find(1);
$doc->index();
```

---

##### `static searchSimilar(string $query, ?string $extension = null, int $limit = 5): Collection`

Search similar documents.

```php
$results = LLMDocumentKnowledgeBase::searchSimilar(
    'How to install?', 
    'llm-manager', 
    5
);
```

---

##### Scopes

```php
// Get indexed documents
LLMDocumentKnowledgeBase::indexed()->get();

// Get by type
LLMDocumentKnowledgeBase::type('documentation')->get();

// Get by extension
LLMDocumentKnowledgeBase::forExtension('llm-manager')->get();
```

---

### `LLMToolDefinition`

Custom tool definitions.

#### Attributes

- `id` (int)
- `extension_slug` (string)
- `name` (string)
- `slug` (string, unique)
- `description` (text)
- `handler_class` (string) - PHP class name
- `handler_method` (string) - Method name
- `parameters_schema` (array, JSON)
- `is_active` (boolean)
- `usage_count` (int)
- `created_at` (datetime)
- `updated_at` (datetime)

#### Methods

##### `execute(array $parameters): array`

Execute the tool.

```php
$tool = LLMToolDefinition::where('slug', 'calculator')->first();
$result = $tool->execute(['operation' => 'add', 'a' => 5, 'b' => 3]);
```

---

##### `validateParameters(array $parameters): bool`

Validate parameters against schema.

```php
$isValid = $tool->validateParameters(['operation' => 'add', 'a' => 5]);
```

---

##### Scopes

```php
// Get active tools
LLMToolDefinition::active()->get();

// Get by extension
LLMToolDefinition::forExtension('llm-manager')->get();
```

---

### `LLMConversationSession`

Conversation sessions.

#### Attributes

- `id` (int)
- `session_id` (string, unique UUID)
- `extension_slug` (string)
- `configuration_id` (int)
- `user_id` (int, nullable)
- `title` (string, nullable)
- `status` (string) - active, ended, expired, archived
- `metadata` (array, JSON, nullable)
- `total_tokens` (int)
- `total_cost` (decimal)
- `started_at` (datetime)
- `ended_at` (datetime, nullable)
- `created_at` (datetime)
- `updated_at` (datetime)

#### Relationships

```php
$session->messages; // HasMany
$session->logs; // HasMany
$session->configuration; // BelongsTo
$session->user; // BelongsTo
```

#### Methods

##### `end(): void`

End the session.

```php
$session->end();
```

---

##### Scopes

```php
// Get active sessions
LLMConversationSession::active()->get();

// Get by status
LLMConversationSession::status('ended')->get();

// Get by user
LLMConversationSession::forUser(auth()->id())->get();
```

---

### `LLMConversationMessage`

Messages in conversation sessions.

#### Attributes

- `id` (int)
- `session_id` (int)
- `role` (string) - user, assistant, system
- `content` (text)
- `metadata` (array, JSON, nullable)
- `tokens_used` (int)
- `cost` (decimal)
- `created_at` (datetime)
- `updated_at` (datetime)

#### Relationships

```php
$message->session; // BelongsTo
```

---

### `LLMUsageLog`

Usage tracking for all LLM requests.

#### Attributes

- `id` (int)
- `extension_slug` (string)
- `provider` (string)
- `model` (string)
- `prompt_tokens` (int)
- `completion_tokens` (int)
- `total_tokens` (int)
- `cost` (decimal)
- `user_id` (int, nullable)
- `session_id` (int, nullable)
- `created_at` (datetime)

#### Scopes

```php
// Get by date range
LLMUsageLog::whereBetween('created_at', [$start, $end])->get();

// Get by provider
LLMUsageLog::where('provider', 'openai')->get();

// Get by extension
LLMUsageLog::where('extension_slug', 'llm-manager')->get();

// Calculate total cost
$totalCost = LLMUsageLog::sum('cost');
```

---

## 🔔 Events

### `PromptRendered`

Fired when a prompt template is rendered.

```php
namespace Bithoven\LLMManager\Events;

class PromptRendered
{
    public string $slug;
    public array $variables;
    public string $rendered;
}
```

**Listener Example:**

```php
Event::listen(PromptRendered::class, function ($event) {
    Log::info("Template {$event->slug} rendered", [
        'variables' => $event->variables,
    ]);
});
```

---

### `DocumentIndexed`

Fired when a knowledge base document is indexed.

```php
namespace Bithoven\LLMManager\Events;

class DocumentIndexed
{
    public int $documentId;
    public int $chunksCreated;
    public float $processingTime;
}
```

**Listener Example:**

```php
Event::listen(DocumentIndexed::class, function ($event) {
    Log::info("Document {$event->documentId} indexed", [
        'chunks' => $event->chunksCreated,
        'time' => $event->processingTime,
    ]);
});
```

---

### `ToolExecuted`

Fired when a tool is executed.

```php
namespace Bithoven\LLMManager\Events;

class ToolExecuted
{
    public string $slug;
    public array $parameters;
    public array $result;
    public float $executionTime;
}
```

**Listener Example:**

```php
Event::listen(ToolExecuted::class, function ($event) {
    Log::info("Tool {$event->slug} executed", [
        'parameters' => $event->parameters,
        'execution_time' => $event->executionTime,
    ]);
});
```

---

### `ConversationStarted`

Fired when a conversation session is created.

```php
namespace Bithoven\LLMManager\Events;

class ConversationStarted
{
    public int $sessionId;
    public string $sessionUuid;
    public ?int $userId;
}
```

---

### `ConversationEnded`

Fired when a conversation session is ended.

```php
namespace Bithoven\LLMManager\Events;

class ConversationEnded
{
    public int $sessionId;
    public int $totalMessages;
    public int $totalTokens;
    public float $totalCost;
}
```

---

## ⚙️ Configuration

### `config/llm-manager.php`

#### Provider Settings

```php
'providers' => [
    'openai' => [
        'enabled' => true,
        'models' => ['gpt-4', 'gpt-3.5-turbo'],
        'default_model' => 'gpt-3.5-turbo',
    ],
    'anthropic' => [
        'enabled' => true,
        'models' => ['claude-3-opus', 'claude-3-sonnet'],
        'default_model' => 'claude-3-sonnet',
    ],
    'ollama' => [
        'enabled' => true,
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
    ],
],
```

---

#### Knowledge Base Settings

```php
'knowledge_base' => [
    'auto_index' => true,
    'chunk_size' => 1000,
    'chunk_overlap' => 200,
    'embedding_model' => 'text-embedding-3-small',
    'embedding_provider' => 'openai',
],
```

---

#### Cost Tracking

```php
'cost_tracking' => [
    'enabled' => true,
    'currency' => 'USD',
    'pricing' => [
        'openai' => [
            'gpt-4' => [
                'prompt' => 0.03 / 1000,      // per token
                'completion' => 0.06 / 1000,
            ],
            'gpt-3.5-turbo' => [
                'prompt' => 0.0015 / 1000,
                'completion' => 0.002 / 1000,
            ],
        ],
    ],
],
```

---

#### Conversation Settings

```php
'conversations' => [
    'max_context_messages' => 20,
    'auto_title' => true,
    'expiration_days' => 30,
],
```

---

## 🛠️ Helpers

### `llm_generate(string $prompt, ?string $provider = null): array`

Quick LLM generation.

```php
$response = llm_generate('Hello!', 'openai');
```

---

### `llm_template(string $slug, array $variables): array`

Quick template rendering + generation.

```php
$response = llm_template('greeting', ['name' => 'Alice']);
```

---

### `llm_search(string $query, ?string $extension = null): Collection`

Quick knowledge base search.

```php
$results = llm_search('How to install?', 'llm-manager');
```

---

## 📚 Error Handling

### Exceptions

#### `LLMException`

Base exception for all LLM Manager errors.

```php
try {
    $response = LLM::generate('Hello!');
} catch (\Bithoven\LLMManager\Exceptions\LLMException $e) {
    Log::error('LLM error: ' . $e->getMessage());
}
```

---

#### `ProviderNotFoundException`

Provider not found or not configured.

```php
try {
    LLM::provider('invalid')->generate('Hello!');
} catch (\Bithoven\LLMManager\Exceptions\ProviderNotFoundException $e) {
    // Handle missing provider
}
```

---

#### `TemplateNotFoundException`

Template not found.

```php
try {
    LLM::template('nonexistent', []);
} catch (\Bithoven\LLMManager\Exceptions\TemplateNotFoundException $e) {
    // Handle missing template
}
```

---

#### `MissingVariablesException`

Required template variables not provided.

```php
try {
    LLM::template('email', []); // Missing required variables
} catch (\Bithoven\LLMManager\Exceptions\MissingVariablesException $e) {
    $missing = $e->getMissingVariables();
    // Handle missing variables
}
```

---

## 📞 Need Help?

- **Usage Guide:** [USAGE-GUIDE.md](USAGE-GUIDE.md)
- **Configuration:** [CONFIGURATION.md](CONFIGURATION.md)
- **Examples:** [EXAMPLES.md](EXAMPLES.md)
- **Support:** support@bithoven.com

---

**Happy coding!** 🚀
