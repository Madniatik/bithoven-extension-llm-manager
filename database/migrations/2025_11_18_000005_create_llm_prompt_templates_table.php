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
        Schema::create('llm_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('extension_slug', 100);
            $table->string('category', 50)->nullable(); // e.g., 'analysis', 'generation', 'summarization'
            $table->text('template'); // With variables: "Analyze ticket: {{ticket_content}}"
            $table->json('variables'); // ["ticket_content", "user_name"]
            $table->json('example_values')->nullable(); // {"ticket_content": "My printer...", "user_name": "John"}
            $table->json('default_parameters')->nullable(); // Optional parameters for this template
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['extension_slug', 'category', 'is_active']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_prompt_templates');
    }
};
