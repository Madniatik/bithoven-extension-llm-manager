# 💡 LLM Manager - Code Examples

**Version:** 1.0.0  
**Last Updated:** 21 de noviembre de 2025

---

## 📑 Table of Contents

1. [Quick Start](#-quick-start)
2. [Prompt Templates](#-prompt-templates)
3. [Knowledge Base (RAG)](#-knowledge-base-rag)
4. [Tool Definitions](#-tool-definitions)
5. [Conversations](#-conversations)
6. [Statistics & Monitoring](#-statistics--monitoring)
7. [Advanced Use Cases](#-advanced-use-cases)

---

## 🚀 Quick Start

### Basic LLM Request

```php
use Bithoven\LLMManager\Facades\LLM;

// Simple generation
$response = LLM::generate('What is Laravel?');
echo $response['content'];

// With specific provider and model
$response = LLM::provider('openai')
    ->model('gpt-4')
    ->generate('Explain dependency injection.');
    
// With parameters
$response = LLM::provider('anthropic')
    ->model('claude-3-sonnet')
    ->temperature(0.7)
    ->maxTokens(500)
    ->generate('Write a creative story.');
```

---

### Local LLM with Ollama

```php
// No API key needed!
$response = LLM::provider('ollama')
    ->model('llama3:8b')
    ->generate('Hello, how are you?');

echo $response['content'];
// Output: "I'm doing well, thank you for asking! ..."
```

---

## 📝 Prompt Templates

### Example 1: Customer Support Email

**Create Template (Admin UI or Migration):**

```php
// database/seeders/PromptTemplateSeeder.php
use Bithoven\LLMManager\Models\LLMPromptTemplate;

LLMPromptTemplate::create([
    'extension_slug' => 'support-system',
    'name' => 'Support Email Response',
    'slug' => 'support-email',
    'category' => 'customer-service',
    'template' => <<<'TEMPLATE'
Dear {{customer_name}},

Thank you for contacting us regarding: {{issue_type}}

{{#if is_urgent}}
We understand this is urgent and have prioritized your request.
{{/if}}

Issue Details:
{{issue_description}}

Our Response:
{{response_content}}

{{#if requires_followup}}
We will follow up with you within {{followup_days}} business days.
{{/if}}

Best regards,
{{agent_name}}
{{company_name}} Support Team
TEMPLATE,
    'variables' => [
        'customer_name',
        'issue_type',
        'issue_description',
        'response_content',
        'agent_name',
        'company_name',
        'is_urgent' => false,      // Optional with default
        'requires_followup' => false,
        'followup_days' => 2,
    ],
    'is_active' => true,
]);
```

**Usage:**

```php
$response = LLM::template('support-email', [
    'customer_name' => 'Jane Smith',
    'issue_type' => 'Billing Inquiry',
    'issue_description' => 'Double charge on invoice #12345',
    'response_content' => 'We have reviewed your account and confirmed the duplicate charge. A refund of $49.99 has been processed and will appear in your account within 3-5 business days.',
    'agent_name' => 'Mike Johnson',
    'company_name' => 'Acme Corp',
    'is_urgent' => true,
    'requires_followup' => true,
    'followup_days' => 1,
]);

echo $response['content'];
```

---

### Example 2: Code Documentation Generator

```php
LLMPromptTemplate::create([
    'slug' => 'code-doc-generator',
    'name' => 'Code Documentation Generator',
    'category' => 'development',
    'template' => <<<'TEMPLATE'
Generate comprehensive documentation for the following {{language}} code:

```{{language}}
{{code}}
```

Include:
1. Function/class description
2. Parameters and return types
3. Usage examples
4. Edge cases and exceptions

Output format: {{format}}
TEMPLATE,
    'variables' => ['language', 'code', 'format'],
]);

// Usage
$code = <<<'PHP'
public function calculateDiscount(float $amount, string $couponCode): float
{
    $coupon = Coupon::where('code', $couponCode)->firstOrFail();
    
    if ($coupon->isExpired()) {
        throw new ExpiredCouponException();
    }
    
    return $amount * ($coupon->percentage / 100);
}
PHP;

$response = LLM::provider('openai')
    ->model('gpt-4')
    ->template('code-doc-generator', [
        'language' => 'PHP',
        'code' => $code,
        'format' => 'PHPDoc',
    ]);

echo $response['content'];
```

---

### Example 3: Dynamic Template Rendering

```php
use Bithoven\LLMManager\Models\LLMPromptTemplate;

// Get template
$template = LLMPromptTemplate::where('slug', 'product-description')->first();

// Validate before rendering
$variables = [
    'product_name' => 'Wireless Headphones',
    'features' => 'Noise cancellation, 30h battery',
];

if (!$template->validateVariables($variables)) {
    $missing = $template->getMissingVariables($variables);
    throw new Exception("Missing: " . implode(', ', $missing));
}

// Render without LLM
$rendered = $template->render($variables);

// Or render + generate
$response = LLM::template('product-description', $variables);
```

---

## 📚 Knowledge Base (RAG)

### Example 1: Document Upload & Auto-Index

```php
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

// Upload documentation
$doc = LLMDocumentKnowledgeBase::create([
    'extension_slug' => 'my-app',
    'title' => 'API Authentication Guide',
    'document_type' => 'documentation',
    'content' => <<<'MARKDOWN'
# API Authentication

## OAuth 2.0

Our API uses OAuth 2.0 for authentication.

### Getting Access Token

```bash
curl -X POST https://api.example.com/oauth/token \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_SECRET"
```

Response:
```json
{
  "access_token": "eyJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

### Using Token

Include token in Authorization header:
```bash
curl https://api.example.com/users \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```
MARKDOWN,
    'metadata' => [
        'version' => '2.0',
        'author' => 'API Team',
        'tags' => ['authentication', 'oauth', 'api'],
    ],
    'auto_index' => true, // Automatically create chunks
]);

// Manual indexing if auto_index = false
if (!$doc->is_indexed) {
    $doc->index();
}
```

---

### Example 2: Semantic Search

```php
// Search knowledge base
$query = "How do I authenticate API requests?";
$results = LLMDocumentKnowledgeBase::searchSimilar($query, 'my-app', 5);

foreach ($results as $doc) {
    echo "📄 {$doc->title}\n";
    echo "   Relevance: {$doc->similarity_score}%\n";
    echo "   Type: {$doc->document_type}\n";
    echo "   Excerpt: " . substr($doc->content, 0, 150) . "...\n\n";
}

// Output:
// 📄 API Authentication Guide
//    Relevance: 98.5%
//    Type: documentation
//    Excerpt: # API Authentication\n\nOur API uses OAuth 2.0...
```

---

### Example 3: RAG-Enhanced Question Answering

```php
use Bithoven\LLMManager\Facades\LLM;
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

function answerWithContext(string $question, string $extension): array
{
    // 1. Search knowledge base
    $docs = LLMDocumentKnowledgeBase::searchSimilar($question, $extension, 3);
    
    // 2. Build context from top results
    $context = $docs->map(function ($doc) {
        return "Document: {$doc->title}\n\n{$doc->content}";
    })->join("\n\n---\n\n");
    
    // 3. Generate answer with context
    $prompt = <<<PROMPT
You are a helpful assistant. Use the following documentation to answer the user's question accurately.

Documentation:
{$context}

User Question: {$question}

Provide a clear, accurate answer based on the documentation. If the documentation doesn't contain the answer, say so.
PROMPT;

    return LLM::provider('openai')
        ->model('gpt-4')
        ->temperature(0.3) // Lower temperature for accuracy
        ->generate($prompt);
}

// Usage
$response = answerWithContext(
    "What authentication method does the API use?",
    "my-app"
);

echo $response['content'];
// Output: "The API uses OAuth 2.0 for authentication. To get an access token..."
```

---

### Example 4: Chunking Strategy

```php
use Bithoven\LLMManager\Services\RAG\LLMRAGService;

$rag = app(LLMRAGService::class);

// Custom chunking
$doc = LLMDocumentKnowledgeBase::find(1);

// Get chunks (automatically created)
$chunks = $rag->getChunks($doc->id);

foreach ($chunks as $chunk) {
    echo "Chunk #{$chunk['index']} ({$chunk['size']} chars)\n";
    echo substr($chunk['content'], 0, 100) . "...\n\n";
}

// Re-index with different settings
config(['llm-manager.knowledge_base.chunk_size' => 500]);
config(['llm-manager.knowledge_base.chunk_overlap' => 100]);

$rag->indexDocument($doc->id);
```

---

## 🛠️ Tool Definitions

### Example 1: Weather Tool

**Create Tool Definition:**

```php
use Bithoven\LLMManager\Models\LLMToolDefinition;

LLMToolDefinition::create([
    'extension_slug' => 'weather-app',
    'name' => 'Get Current Weather',
    'slug' => 'get-weather',
    'description' => 'Get current weather conditions for a specific location',
    'handler_class' => 'App\\LLM\\Tools\\WeatherTool',
    'handler_method' => 'execute',
    'parameters_schema' => [
        'type' => 'object',
        'properties' => [
            'location' => [
                'type' => 'string',
                'description' => 'City name or coordinates (lat,lon)',
            ],
            'units' => [
                'type' => 'string',
                'enum' => ['celsius', 'fahrenheit', 'kelvin'],
                'default' => 'celsius',
                'description' => 'Temperature units',
            ],
        ],
        'required' => ['location'],
    ],
    'is_active' => true,
]);
```

**Handler Implementation:**

```php
<?php

namespace App\LLM\Tools;

use Illuminate\Support\Facades\Http;

class WeatherTool
{
    public function execute(array $parameters): array
    {
        $location = $parameters['location'];
        $units = $parameters['units'] ?? 'celsius';
        
        try {
            // Call weather API (example using OpenWeatherMap)
            $apiKey = config('services.openweather.key');
            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $location,
                'appid' => $apiKey,
                'units' => $this->convertUnits($units),
            ]);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch weather data',
                ];
            }
            
            $data = $response->json();
            
            return [
                'success' => true,
                'location' => $data['name'],
                'country' => $data['sys']['country'],
                'temperature' => round($data['main']['temp']),
                'feels_like' => round($data['main']['feels_like']),
                'humidity' => $data['main']['humidity'],
                'conditions' => $data['weather'][0]['description'],
                'wind_speed' => $data['wind']['speed'],
                'units' => $units,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    private function convertUnits(string $units): string
    {
        return match($units) {
            'celsius' => 'metric',
            'fahrenheit' => 'imperial',
            'kelvin' => 'standard',
        };
    }
}
```

**Usage:**

```php
use Bithoven\LLMManager\Models\LLMToolDefinition;

$tool = LLMToolDefinition::where('slug', 'get-weather')->first();

$result = $tool->execute([
    'location' => 'Paris',
    'units' => 'celsius',
]);

print_r($result);
// Output:
// Array (
//     [success] => 1
//     [location] => Paris
//     [country] => FR
//     [temperature] => 18
//     [feels_like] => 17
//     [humidity] => 65
//     [conditions] => partly cloudy
//     [wind_speed] => 12
//     [units] => celsius
// )
```

---

### Example 2: Database Query Tool

```php
LLMToolDefinition::create([
    'slug' => 'query-users',
    'name' => 'Query Users Database',
    'description' => 'Search and retrieve user information from database',
    'handler_class' => 'App\\LLM\\Tools\\UserQueryTool',
    'handler_method' => 'execute',
    'parameters_schema' => [
        'type' => 'object',
        'properties' => [
            'search_term' => [
                'type' => 'string',
                'description' => 'Name or email to search for',
            ],
            'limit' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 100,
                'default' => 10,
            ],
        ],
        'required' => ['search_term'],
    ],
]);

// Handler
namespace App\LLM\Tools;

use App\Models\User;

class UserQueryTool
{
    public function execute(array $parameters): array
    {
        $search = $parameters['search_term'];
        $limit = $parameters['limit'] ?? 10;
        
        $users = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->limit($limit)
            ->get(['id', 'name', 'email', 'created_at']);
        
        return [
            'success' => true,
            'count' => $users->count(),
            'users' => $users->toArray(),
        ];
    }
}
```

---

### Example 3: Calculator Tool (Math Operations)

```php
// Create tool
LLMToolDefinition::create([
    'slug' => 'calculator',
    'name' => 'Calculator',
    'description' => 'Perform mathematical calculations',
    'handler_class' => 'App\\LLM\\Tools\\CalculatorTool',
    'handler_method' => 'execute',
    'parameters_schema' => [
        'type' => 'object',
        'properties' => [
            'operation' => [
                'type' => 'string',
                'enum' => ['add', 'subtract', 'multiply', 'divide', 'power', 'sqrt'],
            ],
            'a' => ['type' => 'number'],
            'b' => ['type' => 'number'],
        ],
        'required' => ['operation', 'a'],
    ],
]);

// Handler
namespace App\LLM\Tools;

class CalculatorTool
{
    public function execute(array $parameters): array
    {
        $operation = $parameters['operation'];
        $a = $parameters['a'];
        $b = $parameters['b'] ?? null;
        
        try {
            $result = match($operation) {
                'add' => $a + $b,
                'subtract' => $a - $b,
                'multiply' => $a * $b,
                'divide' => $b != 0 ? $a / $b : throw new \Exception('Division by zero'),
                'power' => pow($a, $b),
                'sqrt' => sqrt($a),
            };
            
            return [
                'success' => true,
                'operation' => $operation,
                'result' => $result,
                'formula' => $this->getFormula($operation, $a, $b),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    private function getFormula(string $op, float $a, ?float $b): string
    {
        return match($op) {
            'add' => "{$a} + {$b}",
            'subtract' => "{$a} - {$b}",
            'multiply' => "{$a} × {$b}",
            'divide' => "{$a} ÷ {$b}",
            'power' => "{$a}^{$b}",
            'sqrt' => "√{$a}",
        };
    }
}

// Usage
$tool = LLMToolDefinition::where('slug', 'calculator')->first();

$result = $tool->execute(['operation' => 'multiply', 'a' => 7, 'b' => 8]);
// Result: ['success' => true, 'result' => 56, 'formula' => '7 × 8']
```

---

## 💬 Conversations

### Example 1: Basic Chat Session

```php
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConversationMessage;
use Bithoven\LLMManager\Facades\LLM;

// Create session
$session = LLMConversationSession::create([
    'extension_slug' => 'support-chat',
    'configuration_id' => 1, // OpenAI GPT-3.5
    'user_id' => auth()->id(),
    'title' => 'Product Support',
    'status' => 'active',
]);

// User message
$session->messages()->create([
    'role' => 'user',
    'content' => 'How do I reset my password?',
]);

// Get context
$context = $session->messages()
    ->orderBy('created_at')
    ->get()
    ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
    ->toArray();

// Generate response
$response = LLM::provider('openai')
    ->model('gpt-3.5-turbo')
    ->generate('', $context);

// Save assistant response
$session->messages()->create([
    'role' => 'assistant',
    'content' => $response['content'],
    'tokens_used' => $response['usage']['total_tokens'],
    'cost' => $response['cost'],
]);

// Update session totals
$session->increment('total_tokens', $response['usage']['total_tokens']);
$session->increment('total_cost', $response['cost']);
```

---

### Example 2: Multi-Turn Conversation

```php
class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:llm_conversation_sessions,id',
            'message' => 'required|string|max:2000',
        ]);
        
        $session = LLMConversationSession::findOrFail($validated['session_id']);
        
        // Add user message
        $userMessage = $session->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);
        
        // Get last 10 messages for context
        $context = $session->messages()
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();
        
        // Generate response
        $response = LLM::conversation($session->id)
            ->generate($validated['message'], $context);
        
        // Save assistant message
        $assistantMessage = $session->messages()->create([
            'role' => 'assistant',
            'content' => $response['content'],
            'tokens_used' => $response['usage']['total_tokens'] ?? 0,
            'cost' => $response['cost'] ?? 0,
        ]);
        
        // Log activity
        $session->logs()->create([
            'event_type' => 'message_exchange',
            'event_data' => [
                'user_message_id' => $userMessage->id,
                'assistant_message_id' => $assistantMessage->id,
                'tokens' => $response['usage']['total_tokens'] ?? 0,
            ],
        ]);
        
        return response()->json([
            'message' => $assistantMessage->content,
            'session_id' => $session->id,
            'total_messages' => $session->messages()->count(),
        ]);
    }
}
```

---

### Example 3: Conversation with System Prompt

```php
// Create session with system prompt
$session = LLMConversationSession::create([
    'extension_slug' => 'tech-support',
    'configuration_id' => 1,
    'title' => 'Technical Support Chat',
    'metadata' => [
        'system_prompt' => 'You are a helpful technical support assistant specializing in Laravel. Be concise and provide code examples when relevant.',
        'user_level' => 'intermediate',
    ],
]);

// Add system message
$session->messages()->create([
    'role' => 'system',
    'content' => $session->metadata['system_prompt'],
]);

// Function to send message
function sendChatMessage(LLMConversationSession $session, string $message): string
{
    // Add user message
    $session->messages()->create([
        'role' => 'user',
        'content' => $message,
    ]);
    
    // Get full context (including system prompt)
    $context = $session->messages()
        ->orderBy('created_at')
        ->get()
        ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
        ->toArray();
    
    // Generate
    $response = LLM::generate('', $context);
    
    // Save assistant response
    $session->messages()->create([
        'role' => 'assistant',
        'content' => $response['content'],
        'tokens_used' => $response['usage']['total_tokens'] ?? 0,
    ]);
    
    return $response['content'];
}

// Usage
$response1 = sendChatMessage($session, "How do I create a migration?");
$response2 = sendChatMessage($session, "What about seeders?");
$response3 = sendChatMessage($session, "Show me an example.");
```

---

## 📊 Statistics & Monitoring

### Example 1: Usage Dashboard

```php
use Bithoven\LLMManager\Models\LLMUsageLog;
use Carbon\Carbon;

class LLMStatisticsController extends Controller
{
    public function dashboard()
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        // Total stats
        $totalTokens = LLMUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_tokens');
            
        $totalCost = LLMUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->sum('cost');
            
        $totalRequests = LLMUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        // By provider
        $byProvider = LLMUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('provider, COUNT(*) as count, SUM(total_tokens) as tokens, SUM(cost) as cost')
            ->groupBy('provider')
            ->get();
        
        // Daily usage
        $dailyUsage = LLMUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_tokens) as tokens, SUM(cost) as cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('llm.statistics.dashboard', compact(
            'totalTokens',
            'totalCost',
            'totalRequests',
            'byProvider',
            'dailyUsage'
        ));
    }
}
```

---

### Example 2: Cost Tracking

```php
// Get cost breakdown
$costBreakdown = LLMUsageLog::selectRaw('
        extension_slug,
        provider,
        model,
        SUM(prompt_tokens) as total_prompt_tokens,
        SUM(completion_tokens) as total_completion_tokens,
        SUM(total_tokens) as total_tokens,
        SUM(cost) as total_cost,
        COUNT(*) as request_count
    ')
    ->whereBetween('created_at', [now()->startOfMonth(), now()])
    ->groupBy('extension_slug', 'provider', 'model')
    ->get();

foreach ($costBreakdown as $item) {
    echo "{$item->extension_slug} / {$item->provider} / {$item->model}\n";
    echo "  Requests: {$item->request_count}\n";
    echo "  Tokens: " . number_format($item->total_tokens) . "\n";
    echo "  Cost: $" . number_format($item->total_cost, 4) . "\n\n";
}
```

---

### Example 3: Budget Alerts

```php
use Bithoven\LLMManager\Models\LLMUsageLog;
use Illuminate\Support\Facades\Mail;

class LLMBudgetMonitor
{
    public function checkBudget(string $extension): void
    {
        $monthlyBudget = config("llm-manager.budgets.{$extension}", 100.00);
        
        $currentSpend = LLMUsageLog::where('extension_slug', $extension)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('cost');
        
        $percentageUsed = ($currentSpend / $monthlyBudget) * 100;
        
        if ($percentageUsed >= 90) {
            $this->sendBudgetAlert($extension, $currentSpend, $monthlyBudget, $percentageUsed);
        }
    }
    
    private function sendBudgetAlert($extension, $current, $budget, $percent): void
    {
        Mail::to(config('llm-manager.admin_email'))->send(
            new BudgetAlertMail($extension, $current, $budget, $percent)
        );
    }
}

// Schedule in app/Console/Kernel.php
$schedule->call(function () {
    $monitor = new LLMBudgetMonitor();
    $monitor->checkBudget('llm-manager');
    $monitor->checkBudget('support-system');
})->hourly();
```

---

## 🎯 Advanced Use Cases

### Example 1: Multi-Step Workflow

```php
class ContentGenerationWorkflow
{
    public function generateBlogPost(string $topic): array
    {
        // Step 1: Generate outline
        $outline = LLM::template('blog-outline', ['topic' => $topic]);
        
        // Step 2: Search knowledge base for reference
        $references = LLMDocumentKnowledgeBase::searchSimilar($topic, null, 3);
        $context = $references->pluck('content')->join("\n\n");
        
        // Step 3: Generate introduction
        $intro = LLM::template('blog-intro', [
            'topic' => $topic,
            'outline' => $outline['content'],
            'references' => $context,
        ]);
        
        // Step 4: Generate body sections
        $sections = [];
        $outlineSections = explode("\n", $outline['content']);
        
        foreach ($outlineSections as $section) {
            $content = LLM::template('blog-section', [
                'section_title' => $section,
                'topic' => $topic,
                'references' => $context,
            ]);
            $sections[] = $content['content'];
        }
        
        // Step 5: Generate conclusion
        $conclusion = LLM::template('blog-conclusion', [
            'topic' => $topic,
            'intro' => $intro['content'],
            'sections' => implode("\n\n", $sections),
        ]);
        
        return [
            'title' => $topic,
            'outline' => $outline['content'],
            'introduction' => $intro['content'],
            'sections' => $sections,
            'conclusion' => $conclusion['content'],
            'full_post' => $intro['content'] . "\n\n" . implode("\n\n", $sections) . "\n\n" . $conclusion['content'],
        ];
    }
}

// Usage
$workflow = new ContentGenerationWorkflow();
$post = $workflow->generateBlogPost('Modern PHP Development Best Practices');
```

---

### Example 2: Streaming Responses

```php
// Note: Streaming support available since v1.0.4

use Bithoven\LLMManager\Facades\LLM;

Route::get('/stream-response', function () {
    return response()->stream(function () {
        LLM::provider('openai')
            ->model('gpt-4')
            ->stream('Write a long story...', function ($chunk) {
                echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            });
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
});

// Frontend (JavaScript)
const eventSource = new EventSource('/stream-response');
eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    document.getElementById('output').innerText += data.content;
};
```

---

### Example 3: A/B Testing Prompts

```php
class PromptABTesting
{
    public function runTest(string $variantA, string $variantB, array $testCases): array
    {
        $results = [
            'variant_a' => [],
            'variant_b' => [],
        ];
        
        foreach ($testCases as $testCase) {
            // Test Variant A
            $responseA = LLM::template($variantA, $testCase);
            $results['variant_a'][] = [
                'input' => $testCase,
                'output' => $responseA['content'],
                'tokens' => $responseA['usage']['total_tokens'],
                'cost' => $responseA['cost'],
            ];
            
            // Test Variant B
            $responseB = LLM::template($variantB, $testCase);
            $results['variant_b'][] = [
                'input' => $testCase,
                'output' => $responseB['content'],
                'tokens' => $responseB['usage']['total_tokens'],
                'cost' => $responseB['cost'],
            ];
        }
        
        // Calculate metrics
        $results['metrics'] = [
            'variant_a' => [
                'avg_tokens' => collect($results['variant_a'])->avg('tokens'),
                'avg_cost' => collect($results['variant_a'])->avg('cost'),
            ],
            'variant_b' => [
                'avg_tokens' => collect($results['variant_b'])->avg('tokens'),
                'avg_cost' => collect($results['variant_b'])->avg('cost'),
            ],
        ];
        
        return $results;
    }
}

// Usage
$tester = new PromptABTesting();
$results = $tester->runTest(
    'email-response-v1',
    'email-response-v2',
    [
        ['customer_name' => 'Alice', 'issue_type' => 'billing'],
        ['customer_name' => 'Bob', 'issue_type' => 'technical'],
        ['customer_name' => 'Carol', 'issue_type' => 'general'],
    ]
);

print_r($results['metrics']);
```

---

## 📞 Need Help?

- **Usage Guide:** [USAGE-GUIDE.md](USAGE-GUIDE.md)
- **API Reference:** [API-REFERENCE.md](API-REFERENCE.md)
- **Configuration:** [CONFIGURATION.md](CONFIGURATION.md)
- **Support:** support@bithoven.com

---

**Happy coding!** 🚀
