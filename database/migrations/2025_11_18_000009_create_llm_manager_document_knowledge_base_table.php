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
        Schema::create('llm_manager_document_knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('extension_slug', 100);
            $table->string('document_type', 50)->nullable(); // Nullable for testing - 'manual', 'faq', 'api_doc', 'code', etc.
            $table->string('title', 255);
            $table->longText('content'); // Original document content
            $table->longText('content_chunks'); // JSON array of chunked content
            $table->json('embeddings')->nullable(); // Vector embeddings for semantic search
            $table->string('embedding_model', 100)->nullable();
            $table->json('metadata')->nullable(); // Source, author, version, tags, etc.
            $table->boolean('is_indexed')->default(false);
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['extension_slug', 'document_type', 'is_indexed'], 'llm_kb_ext_type_indexed_idx');
            $table->index('is_indexed', 'llm_kb_indexed_idx');
            
            // Fulltext index only for MySQL (not supported in SQLite)
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['title', 'content'], 'llm_kb_title_content_ft');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_document_knowledge_base');
    }
};
