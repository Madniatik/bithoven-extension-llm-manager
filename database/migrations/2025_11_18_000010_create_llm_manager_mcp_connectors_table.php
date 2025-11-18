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
        Schema::create('llm_manager_mcp_connectors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->enum('type', ['bundled', 'external'])->default('bundled');
            $table->string('server_path')->nullable(); // For bundled servers
            $table->string('server_url')->nullable(); // For external servers
            $table->enum('protocol', ['stdio', 'http', 'websocket'])->default('stdio');
            $table->json('capabilities')->nullable(); // Available tools/functions
            $table->json('configuration')->nullable(); // Server-specific config
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_start')->default(false);
            $table->integer('priority')->default(0); // For tool resolution order
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['type', 'is_active'], 'llm_mcp_type_active_idx');
            $table->index('slug', 'llm_mcp_slug_idx');
            $table->index('priority', 'llm_mcp_priority_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_mcp_connectors');
    }
};
