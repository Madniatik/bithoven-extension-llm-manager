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
        Schema::create('llm_manager_providers', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique()->index()->comment('Provider identifier (ollama, anthropic, openai, etc.)');
            $table->string('name', 100)->comment('Display name (Ollama, Anthropic, OpenAI, etc.)');
            $table->string('package', 255)->nullable()->comment('Composer package name (bithoven/llm-provider-*)');
            $table->string('version', 20)->nullable()->comment('Package version (0.1.0, 1.1.0, etc.)');
            $table->string('api_endpoint', 255)->nullable()->comment('Default API endpoint');
            $table->json('capabilities')->nullable()->comment('Provider capabilities (vision, streaming, function_calling, etc.)');
            $table->boolean('is_active')->default(true)->index()->comment('UI visibility');
            $table->boolean('is_installed')->default(true)->index()->comment('Package currently installed');
            $table->timestamp('archived_at')->nullable()->comment('When provider was uninstalled/archived');
            $table->json('metadata')->nullable()->comment('Additional metadata (archived_reason, restore_count, etc.)');
            $table->timestamps();
            
            // Index for queries
            $table->index(['is_active', 'is_installed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_providers');
    }
};
