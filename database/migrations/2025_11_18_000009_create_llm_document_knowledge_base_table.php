<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('llm_document_knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('extension_slug', 100);
            $table->string('document_type', 50); // 'manual', 'faq', 'api_doc', 'code', etc.
            $table->string('title', 255);
            $table->longText('content'); // Original document content
            $table->longText('content_chunks'); // JSON array of chunked content
            $table->json('embeddings')->nullable(); // Vector embeddings for semantic search
            $table->string('embedding_model', 100)->nullable();
            $table->json('metadata')->nullable(); // Source, author, version, tags, etc.
            $table->boolean('is_indexed')->default(false);
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['extension_slug', 'document_type', 'is_indexed']);
            $table->index('is_indexed');
            $table->fullText(['title', 'content']); // For traditional search fallback
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_document_knowledge_base');
    }
};
