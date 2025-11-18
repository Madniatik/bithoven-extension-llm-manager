<?php

namespace Bithoven\LLMManager\Tests\Unit\Models;

use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMDocumentKnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_knowledge_base_document()
    {
        $document = LLMDocumentKnowledgeBase::factory()->create([
            'title' => 'Laravel Best Practices',
            'metadata' => ['author' => 'John Doe', 'version' => '1.0'],
        ]);

        $this->assertDatabaseHas('llm_manager_document_knowledge_base', [
            'title' => 'Laravel Best Practices',
        ]);

        $this->assertEquals(['author' => 'John Doe', 'version' => '1.0'], $document->metadata);
    }

    /** @test */
    public function it_stores_metadata_as_json()
    {
        $metadata = [
            'author' => 'Jane Doe',
            'category' => 'PHP',
            'tags' => ['laravel', 'backend', 'api'],
            'version' => '2.0',
        ];

        $document = LLMDocumentKnowledgeBase::factory()->create([
            'title' => 'API Development Guide',
            'metadata' => $metadata,
        ]);

        $document->refresh();

        $this->assertEquals($metadata, $document->metadata);
        $this->assertEquals(['laravel', 'backend', 'api'], $document->metadata['tags']);
    }

    /** @test */
    public function it_tracks_indexing_status()
    {
        $document = LLMDocumentKnowledgeBase::factory()->create([
            'is_indexed' => false,
        ]);

        $this->assertFalse($document->is_indexed);
        $this->assertNull($document->indexed_at);

        $document->update([
            'is_indexed' => true,
            'indexed_at' => now(),
        ]);

        $this->assertTrue($document->fresh()->is_indexed);
        $this->assertNotNull($document->fresh()->indexed_at);
    }

    /** @test */
    public function it_stores_content_chunks_as_json()
    {
        $chunks = [
            'This is chunk 1',
            'This is chunk 2',
            'This is chunk 3',
        ];

        $document = LLMDocumentKnowledgeBase::factory()->create([
            'content_chunks' => $chunks,
        ]);

        $document->refresh();

        $this->assertEquals($chunks, $document->content_chunks);
        $this->assertCount(3, $document->content_chunks);
    }

    /** @test */
    public function it_has_chunk_count_accessor()
    {
        $document = LLMDocumentKnowledgeBase::factory()->create([
            'content_chunks' => ['chunk1', 'chunk2', 'chunk3', 'chunk4'],
        ]);

        $this->assertEquals(4, $document->chunk_count);
    }

    /** @test */
    public function it_filters_by_extension_slug()
    {
        LLMDocumentKnowledgeBase::factory()->create([
            'extension_slug' => 'extension-a',
        ]);

        LLMDocumentKnowledgeBase::factory()->create([
            'extension_slug' => 'extension-b',
        ]);

        $docsA = LLMDocumentKnowledgeBase::where('extension_slug', 'extension-a')->get();
        $docsB = LLMDocumentKnowledgeBase::where('extension_slug', 'extension-b')->get();

        $this->assertCount(1, $docsA);
        $this->assertCount(1, $docsB);
    }

    /** @test */
    public function it_filters_indexed_documents()
    {
        LLMDocumentKnowledgeBase::factory()->indexed()->create();

        LLMDocumentKnowledgeBase::factory()->create([
            'is_indexed' => false,
        ]);

        $indexedDocs = LLMDocumentKnowledgeBase::where('is_indexed', true)->get();
        $notIndexedDocs = LLMDocumentKnowledgeBase::where('is_indexed', false)->get();

        $this->assertCount(1, $indexedDocs);
        $this->assertCount(1, $notIndexedDocs);
    }

    /** @test */
    public function it_handles_empty_chunks()
    {
        $document = LLMDocumentKnowledgeBase::factory()->create([
            'content_chunks' => null,
        ]);

        $this->assertEquals(0, $document->chunk_count);
        $this->assertEmpty($document->content_chunks ?? []);
    }
}

