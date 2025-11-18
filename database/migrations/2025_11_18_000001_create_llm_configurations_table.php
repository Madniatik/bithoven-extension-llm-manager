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
        Schema::create('llm_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->enum('provider', ['ollama', 'openai', 'anthropic', 'custom'])->default('ollama');
            $table->string('model', 100);
            $table->string('api_endpoint')->nullable();
            $table->text('api_key')->nullable(); // Encrypted
            $table->json('default_parameters')->nullable(); // {temperature, max_tokens, top_p, etc.}
            $table->json('capabilities')->nullable(); // {vision, function_calling, streaming, etc.}
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['provider', 'is_active']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_configurations');
    }
};
