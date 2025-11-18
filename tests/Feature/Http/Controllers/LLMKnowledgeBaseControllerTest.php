<?php

namespace Bithoven\LLMManager\Tests\Feature\Http\Controllers;

use App\Models\User;
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;
use Bithoven\LLMManager\Services\LLMRAGService;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class LLMKnowledgeBaseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable middleware for testing
        $this->withoutMiddleware();
        
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    /** @test */
    public function it_displays_knowledge_base_index()
    {
        $response = $this->get(route('admin.llm.knowledge-base.index'));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.knowledge-base.index');
    }

    /** @test */
    public function it_displays_create_document_form()
    {
        $response = $this->get(route('admin.llm.knowledge-base.create'));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.knowledge-base.create');
        $response->assertSee('Add Document');
    }

    /** @test */
    public function it_can_create_a_document()
    {
        $data = [
            'title' => 'Laravel Best Practices',
            'content' => 'This document contains best practices for Laravel development...',
            'extension_slug' => 'llm-manager',
            'metadata' => [
                'author' => 'John Doe',
                'category' => 'PHP',
            ],
        ];

        $response = $this->post(route('admin.llm.knowledge-base.store'), $data);

        $response->assertRedirect(route('admin.llm.knowledge-base.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('llm_document_knowledge_base', [
            'title' => 'Laravel Best Practices',
            'extension_slug' => 'llm-manager',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post(route('admin.llm.knowledge-base.store'), []);

        $response->assertSessionHasErrors(['title', 'content']);
    }

    /** @test */
    public function it_displays_document_details()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Document',
            'content' => 'Test content...',
            'extension_slug' => 'llm-manager',
        ]);

        $response = $this->get(route('admin.llm.knowledge-base.show', $document));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.knowledge-base.show');
        $response->assertSee('Test Document');
    }

    /** @test */
    public function it_can_edit_a_document()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'extension_slug' => 'llm-manager',
        ]);

        $response = $this->get(route('admin.llm.knowledge-base.edit', $document));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.knowledge-base.edit');
        $response->assertSee('Original Title');
    }

    /** @test */
    public function it_can_update_a_document()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'extension_slug' => 'llm-manager',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content with more information',
            'extension_slug' => 'llm-manager',
            'metadata' => ['updated' => true],
        ];

        $response = $this->put(route('admin.llm.knowledge-base.update', $document), $updateData);

        $response->assertRedirect(route('admin.llm.knowledge-base.index'));

        $this->assertDatabaseHas('llm_document_knowledge_base', [
            'id' => $document->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_can_delete_a_document()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'To Delete',
            'content' => 'Content to delete',
            'extension_slug' => 'llm-manager',
        ]);

        $response = $this->delete(route('admin.llm.knowledge-base.destroy', $document));

        $response->assertRedirect(route('admin.llm.knowledge-base.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('llm_document_knowledge_base', ['id' => $document->id]);
    }

    /** @test */
    public function it_can_index_a_document()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Test Document',
            'content' => 'This is a test document for indexing with RAG service.',
            'extension_slug' => 'llm-manager',
            'is_indexed' => false,
        ]);

        // Mock RAG service
        $ragService = Mockery::mock(LLMRAGService::class);
        $ragService->shouldReceive('indexDocument')
            ->once()
            ->with($document->id);

        $this->app->instance(LLMRAGService::class, $ragService);

        $response = $this->post(route('admin.llm.knowledge-base.index-doc', $document));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function it_displays_indexed_status()
    {
        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Indexed Doc',
            'content' => 'Content',
            'extension_slug' => 'llm-manager',
            'is_indexed' => true,
            'indexed_at' => now(),
        ]);

        $response = $this->get(route('admin.llm.knowledge-base.show', $document));

        $response->assertSee('Indexed');
    }

    /** @test */
    public function it_displays_chunks_when_indexed()
    {
        $chunks = ['Chunk 1 content', 'Chunk 2 content', 'Chunk 3 content'];

        $document = LLMDocumentKnowledgeBase::create([
            'title' => 'Chunked Doc',
            'content' => 'Full content',
            'content_chunks' => $chunks,
            'extension_slug' => 'llm-manager',
            'is_indexed' => true,
        ]);

        $response = $this->get(route('admin.llm.knowledge-base.show', $document));

        $response->assertSee('Chunk 1 content');
        $response->assertSee('Chunk 2 content');
    }

    /** @test */
    public function unauthorized_users_cannot_access_knowledge_base()
    {
        auth()->logout();

        $response = $this->get(route('admin.llm.knowledge-base.index'));

        $response->assertRedirect(route('login'));
    }
}
