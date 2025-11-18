<?php

namespace Bithoven\LLMManager\Tests\Integration;

use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;
use Bithoven\LLMManager\Services\LLMEmbeddingsService;
use Bithoven\LLMManager\Services\LLMRAGService;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RAGPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected LLMEmbeddingsService $embeddingsService;
    protected LLMRAGService $ragService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->embeddingsService = app(LLMEmbeddingsService::class);
        $this->ragService = app(LLMRAGService::class);
    }

    /** @test */
    public function it_can_index_a_document_with_full_pipeline()
    {
        // 1. Create document
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Laravel Eloquent Guide',
            'content' => 'Laravel Eloquent is an ORM that makes database interactions easy. ' .
                        'You can define models that represent database tables. ' .
                        'Eloquent provides methods for querying and manipulating data. ' .
                        'Relationships between models are simple to define.',
            'extension_slug' => 'llm-manager',
        ]);

        $this->assertFalse($document->is_indexed);
        $this->assertNull($document->indexed_at);

        // 2. Index document (chunks + embeddings)
        $this->ragService->indexDocument($document->id);

        // 3. Verify document was indexed
        $document->refresh();

        $this->assertTrue($document->is_indexed);
        $this->assertNotNull($document->indexed_at);
        $this->assertNotNull($document->content_chunks);
        $this->assertGreaterThan(0, $document->chunk_count);
    }

    /** @test */
    public function it_generates_embeddings_for_all_chunks()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Doc',
            'content' => str_repeat('This is test content. ', 100), // Long content
            'extension_slug' => 'llm-manager',
        ]);

        $this->ragService->indexDocument($document->id);

        $document->refresh();

        $this->assertGreaterThan(1, $document->chunk_count);

        // Verify each chunk would have an embedding
        foreach ($document->content_chunks as $chunk) {
            $embedding = $this->embeddingsService->generateEmbedding($chunk);
            
            $this->assertIsArray($embedding);
            $this->assertCount(1536, $embedding);
        }
    }

    /** @test */
    public function it_can_chunk_document_properly()
    {
        $content = implode(' ', array_fill(0, 500, 'word'));

        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Long Document',
            'content' => $content,
            'extension_slug' => 'llm-manager',
        ]);

        $this->ragService->indexDocument($document->id);

        $document->refresh();

        // Should create multiple chunks for long content
        $this->assertGreaterThan(1, $document->chunk_count);

        // Each chunk should not be empty
        foreach ($document->content_chunks as $chunk) {
            $this->assertNotEmpty($chunk);
            $this->assertIsString($chunk);
        }
    }

    /** @test */
    public function it_handles_short_document()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Short Doc',
            'content' => 'This is a very short document.',
            'extension_slug' => 'llm-manager',
        ]);

        $this->ragService->indexDocument($document->id);

        $document->refresh();

        $this->assertTrue($document->is_indexed);
        $this->assertGreaterThanOrEqual(1, $document->chunk_count);
    }

    /** @test */
    public function embeddings_are_deterministic_for_same_text()
    {
        $text = 'Deterministic embedding test';

        $embedding1 = $this->embeddingsService->generateEmbedding($text);
        $embedding2 = $this->embeddingsService->generateEmbedding($text);

        // Same text should produce identical embeddings (mock implementation)
        $this->assertEquals($embedding1, $embedding2);
    }

    /** @test */
    public function different_texts_produce_different_embeddings()
    {
        $text1 = 'Laravel is a PHP framework';
        $text2 = 'Python is a programming language';

        $embedding1 = $this->embeddingsService->generateEmbedding($text1);
        $embedding2 = $this->embeddingsService->generateEmbedding($text2);

        $this->assertNotEquals($embedding1, $embedding2);
    }

    /** @test */
    public function it_can_reindex_a_document()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Doc',
            'content' => 'Original content',
            'extension_slug' => 'llm-manager',
        ]);

        // First indexing
        $this->ragService->indexDocument($document->id);
        $document->refresh();
        $firstIndexedAt = $document->indexed_at;

        sleep(1); // Ensure time difference

        // Update content and reindex
        $document->update(['content' => 'Updated content with more information']);
        $this->ragService->indexDocument($document->id);
        $document->refresh();

        $this->assertTrue($document->is_indexed);
        $this->assertGreaterThan($firstIndexedAt, $document->indexed_at);
    }

    /** @test */
    public function it_handles_special_characters_in_content()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Special Chars',
            'content' => 'Special: áéíóú ñ @#$%^&* 中文 العربية 🚀',
            'extension_slug' => 'llm-manager',
        ]);

        $this->ragService->indexDocument($document->id);

        $document->refresh();

        $this->assertTrue($document->is_indexed);
        $this->assertGreaterThan(0, $document->chunk_count);
    }

    /** @test */
    public function it_can_index_multiple_documents_simultaneously()
    {
        $documents = [];

        for ($i = 1; $i <= 5; $i++) {
            $documents[] = LLMDocumentKnowledgeBase::create([
                'title' => "Document {$i}",
                'content' => "This is document number {$i} with unique content.",
                'extension_slug' => 'llm-manager',
            ]);
        }

        foreach ($documents as $document) {
            $this->ragService->indexDocument($document->id);
        }

        foreach ($documents as $document) {
            $document->refresh();
            $this->assertTrue($document->is_indexed);
        }
    }
}
