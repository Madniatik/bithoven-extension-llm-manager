<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

class LLMKnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed essential knowledge base documents
     */
    public function run(): void
    {
        $documents = [
            [
                'extension_slug' => 'llm-manager',
                'document_type' => 'documentation',
                'title' => 'LLM Manager Quick Start Guide',
                'content' => <<<'MARKDOWN'
# LLM Manager Quick Start Guide

## Introduction

LLM Manager is a comprehensive multi-provider LLM orchestration platform for Laravel applications. It provides a unified interface for working with multiple LLM providers (Ollama, OpenAI, Anthropic, OpenRouter) and includes advanced features like RAG, conversations, and prompt templates.

## Basic Usage

### Simple Generation

```php
use Bithoven\LLMManager\Facades\LLM;

$response = LLM::execute('What is Laravel?');
echo $response;
```

### Using Specific Configuration

```php
$response = LLM::useConfig('ollama-qwen3')
    ->execute('Explain dependency injection');
```

### With Custom Parameters

```php
$response = LLM::execute('Write a creative poem', [
    'temperature' => 1.2,  // More creative
    'max_tokens' => 500,
]);
```

## Prompt Templates

### Using Templates

```php
$response = LLM::template('code-review')
    ->variables([
        'language' => 'php',
        'code' => $yourCode,
    ])
    ->execute();
```

### Creating Templates

```php
use Bithoven\LLMManager\Models\LLMPromptTemplate;

LLMPromptTemplate::create([
    'name' => 'My Template',
    'slug' => 'my-template',
    'template' => 'Analyze this: {{content}}',
    'variables' => ['content'],
]);
```

## Conversations

### Starting a Conversation

```php
$session = LLM::conversation('my-extension')
    ->start('Help me with Laravel routing');
```

### Continuing Conversation

```php
$session->message('What about middleware?');
$session->message('Show me an example');
```

## RAG (Retrieval-Augmented Generation)

### Indexing Documents

```php
use Bithoven\LLMManager\Services\LLMRAGService;

$rag = app(LLMRAGService::class);

$rag->indexDocument(
    title: 'Laravel Documentation',
    content: $documentContent,
    extension: 'my-extension'
);
```

### Searching

```php
$results = $rag->search('How to create a controller?', extension: 'my-extension');
```

## Real-Time Streaming

### Test Page

Visit: `/admin/llm/stream/test`

### Programmatic Usage

```php
use Bithoven\LLMManager\Services\LLMStreamService;

$stream = app(LLMStreamService::class);

$stream->stream($configId, 'Your prompt', function($chunk) {
    echo $chunk; // Real-time output
});
```

## Configuration

### Environment Variables

```env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
CUSTOM_LLM_ENDPOINT=http://localhost:8080
```

### Admin Panel

Configure providers at: `/admin/llm/configurations`

## Best Practices

1. **Use Prompt Templates** for consistency
2. **Monitor Costs** via Statistics dashboard
3. **Test Configurations** before production use
4. **Use RAG** for domain-specific knowledge
5. **Enable Streaming** for better UX

## Support

- Documentation: `/vendor/bithoven/llm-manager/docs/`
- Admin Panel: `/admin/llm`
- GitHub: https://github.com/Madniatik/bithoven-extension-llm-manager

MARKDOWN
                ,
                'content_chunks' => [],
                'embeddings' => null,
                'embedding_model' => null,
                'metadata' => [
                    'source' => 'system',
                    'version' => '1.0.0',
                    'author' => 'BITHOVEN Team',
                    'language' => 'en',
                ],
                'is_indexed' => false,
                'indexed_at' => null,
            ],
            [
                'extension_slug' => 'llm-manager',
                'document_type' => 'guide',
                'title' => 'LLM Provider Configuration Guide',
                'content' => <<<'MARKDOWN'
# LLM Provider Configuration Guide

## Ollama (Local)

### Installation

```bash
# macOS
brew install ollama

# Linux
curl https://ollama.ai/install.sh | sh
```

### Start Server

```bash
ollama serve
```

### Pull Models

```bash
ollama pull qwen3:4b
ollama pull deepseek-coder:6.7b
ollama pull llama3:8b
```

### Configuration

```env
# No API key needed for local Ollama
OLLAMA_ENDPOINT=http://localhost:11434
```

## OpenAI

### Get API Key

1. Visit: https://platform.openai.com/api-keys
2. Create new secret key
3. Add to `.env`

### Configuration

```env
OPENAI_API_KEY=sk-proj-...
```

### Models

- `gpt-4o` - Latest GPT-4 with vision (recommended)
- `gpt-4o-mini` - Faster, cheaper variant
- `gpt-3.5-turbo` - Legacy, cost-effective

## Anthropic (Claude)

### Get API Key

1. Visit: https://console.anthropic.com/
2. Create API key
3. Add to `.env`

### Configuration

```env
ANTHROPIC_API_KEY=sk-ant-...
```

### Models

- `claude-3-5-sonnet-20241022` - Best performance (recommended)
- `claude-3-opus-20240229` - Highest capability
- `claude-3-haiku-20240307` - Fastest, cheapest

## OpenRouter

### Get API Key

1. Visit: https://openrouter.ai/keys
2. Create API key
3. Add to `.env`

### Configuration

```env
OPENROUTER_API_KEY=sk-or-v1-...
```

### Benefits

- Access to 100+ models via single API
- Automatic fallback
- Unified pricing

## Cost Optimization

### Tips

1. Use local Ollama for development
2. Use cheaper models for simple tasks
3. Set `max_tokens` limits
4. Monitor usage via Statistics dashboard
5. Use caching for repeated queries

### Price Comparison (per 1M tokens)

| Provider | Model | Input | Output |
|----------|-------|-------|--------|
| Ollama | Any | $0.00 | $0.00 |
| OpenAI | GPT-4o | $2.50 | $10.00 |
| OpenAI | GPT-4o-mini | $0.15 | $0.60 |
| Anthropic | Claude 3.5 Sonnet | $3.00 | $15.00 |
| OpenRouter | Varies | Varies | Varies |

MARKDOWN
                ,
                'content_chunks' => [],
                'embeddings' => null,
                'embedding_model' => null,
                'metadata' => [
                    'source' => 'system',
                    'version' => '1.0.0',
                    'author' => 'BITHOVEN Team',
                    'language' => 'en',
                ],
                'is_indexed' => false,
                'indexed_at' => null,
            ],
        ];

        foreach ($documents as $document) {
            LLMDocumentKnowledgeBase::create($document);
        }

        $this->command->info('✅ Created 2 essential knowledge base documents');
        $this->command->info('   - LLM Manager Quick Start Guide');
        $this->command->info('   - LLM Provider Configuration Guide');
    }
}
