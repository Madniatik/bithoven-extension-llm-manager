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
        Schema::create('llm_manager_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique()->nullable(); // Nullable for testing
            $table->string('extension_slug', 100)->nullable(); // Nullable for testing/global templates
            $table->string('category', 50)->nullable(); // e.g., 'analysis', 'generation', 'summarization'
            $table->text('template'); // With variables: "Analyze ticket: {{ticket_content}}"
            $table->json('variables')->nullable(); // ["ticket_content", "user_name"] - auto-extracted if not provided
            $table->json('example_values')->nullable(); // {"ticket_content": "My printer...", "user_name": "John"}
            $table->json('default_parameters')->nullable(); // Optional parameters for this template
            $table->boolean('is_active')->default(true);
            $table->boolean('is_global')->default(false)->comment('Available to all extensions');
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['extension_slug', 'category', 'is_active'], 'llm_pt_ext_cat_active_idx');
            $table->index('slug', 'llm_pt_slug_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_prompt_templates');
    }
};
