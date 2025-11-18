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
        Schema::create('llm_manager_tool_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique()->nullable(); // Nullable for testing
            $table->enum('type', ['function_calling', 'mcp'])->default('function_calling');
            $table->foreignId('mcp_connector_id')->nullable()->constrained('llm_manager_mcp_connectors')->onDelete('cascade');
            $table->json('function_schema'); // OpenAI/Anthropic function definition
            $table->string('handler_class')->nullable(); // PHP class for function_calling
            $table->string('handler_method')->nullable(); // PHP method
            $table->json('validation_rules')->nullable(); // Laravel validation rules
            $table->json('security_policy')->nullable(); // Allowed paths, rate limits, etc.
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['type', 'is_active'], 'llm_td_type_active_idx');
            $table->index('slug', 'llm_td_slug_idx');
            $table->index('mcp_connector_id', 'llm_td_mcp_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_tool_definitions');
    }
};
