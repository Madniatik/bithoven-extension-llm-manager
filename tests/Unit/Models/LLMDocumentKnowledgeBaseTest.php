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
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Laravel Best Practices',
            'content' => 'This document contains Laravel best practices...',
            'extension_slug' => 'llm-manager',
            'metadata' => ['author' => 'John Doe', 'version' => '1.0'],
        ]);

        $this->assertDatabaseHas('llm_document_knowledge_base', [
            'title' => 'Laravel Best Practices',
            'extension_slug' => 'llm-manager',
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

        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'API Development Guide',
            'content' => 'Guide content...',
            'extension_slug' => 'llm-manager',
            'metadata' => $metadata,
        ]);

        $document->refresh();

        $this->assertEquals($metadata, $document->metadata);
        $this->assertEquals(['laravel', 'backend', 'api'], $document->metadata['tags']);
    }

    /** @test */
    public function it_tracks_indexing_status()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Document',
            'content' => 'Content...',
            'extension_slug' => 'llm-manager',
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

        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Chunked Document',
            'content' => 'Full content...',
            'content_chunks' => $chunks,
            'extension_slug' => 'llm-manager',
        ]);

        $document->refresh();

        $this->assertEquals($chunks, $document->content_chunks);
        $this->assertCount(3, $document->content_chunks);
    }

    /** @test */
    public function it_has_chunk_count_accessor()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Doc',
            'content' => 'Content...',
            'content_chunks' => ['chunk1', 'chunk2', 'chunk3', 'chunk4'],
            'extension_slug' => 'llm-manager',
        ]);

        $this->assertEquals(4, $document->chunk_count);
    }

    /** @test */
    public function it_filters_by_extension_slug()
    {
        LLMDocumentKnowledgeBase::create([
            'title' => 'Doc 1',
            'content' => 'Content 1',
            'extension_slug' => 'extension-a',
        ]);

        LLMDocumentKnowledgeBase::create([
            'title' => 'Doc 2',
            'content' => 'Content 2',
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
        LLMDocumentKnowledgeBase::create([
            'title' => 'Indexed Doc',
            'content' => 'Content',
            'extension_slug' => 'llm-manager',
            'is_indexed' => true,
            'indexed_at' => now(),
        ]);

        LLMDocumentKnowledgeBase::create([
            'title' => 'Not Indexed Doc',
            'content' => 'Content',
            'extension_slug' => 'llm-manager',
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
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Empty Chunks',
            'content' => 'Short content',
            'content_chunks' => null,
            'extension_slug' => 'llm-manager',
        ]);

        $this->assertEquals(0, $document->chunk_count);
        $this->assertEmpty($document->content_chunks ?? []);
    }

    /** @test */
    public function it_can_soft_delete()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Document',
            'content' => 'Content',
            'extension_slug' => 'llm-manager',
        ]);

        $document->delete();

        $this->assertSoftDeleted('llm_document_knowledge_base', ['id' => $document->id]);
        
        $this->assertNotNull(LLMDocumentKnowledgeBase::withTrashed()->find($document->id));
    }
}
