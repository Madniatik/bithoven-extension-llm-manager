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
        Schema::create('llm_manager_provider_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')
                ->constrained('llm_manager_providers')
                ->onDelete('restrict')
                ->comment('FK to providers table');
            
            $table->string('name', 100)->comment('Configuration display name (GPT-4o, Claude 3.5, etc.)');
            $table->string('slug', 100)->unique()->index()->comment('URL-friendly identifier');
            $table->string('model', 100)->comment('Model identifier (gpt-4o, claude-3-5-sonnet, etc.)');
            $table->text('api_key')->nullable()->comment('Encrypted API key');
            
            // Advanced endpoint configuration
            $table->string('api_endpoint', 255)->nullable()->comment('Override provider default endpoint');
            $table->string('endpoint_chat', 100)->nullable()->comment('Custom chat/completions endpoint path');
            $table->string('endpoint_embeddings', 100)->nullable()->comment('Custom embeddings endpoint path');
            $table->string('endpoint_models', 100)->nullable()->comment('Custom models list endpoint path');
            
            $table->json('default_parameters')->nullable()->comment('Default parameters (temperature, max_tokens, etc.)');
            $table->json('capabilities')->nullable()->comment('Model-specific capabilities override');
            
            // Request configuration
            $table->json('custom_headers')->nullable()->comment('Custom HTTP headers for API requests');
            $table->unsignedInteger('timeout')->nullable()->comment('Request timeout in seconds (5-300)');
            $table->unsignedTinyInteger('retry_attempts')->nullable()->comment('Number of retry attempts on failure (0-10)');
            
            // Multi-currency support (consolidated from add_multi_currency migration)
            $table->decimal('cost_per_1k_input_tokens', 10, 6)->nullable()->comment('Cost per 1K input tokens');
            $table->decimal('cost_per_1k_output_tokens', 10, 6)->nullable()->comment('Cost per 1K output tokens');
            $table->string('currency', 3)->default('USD')->comment('Currency code (USD, EUR, etc.)');
            
            $table->boolean('is_active')->default(true)->index()->comment('Configuration is available for use');
            $table->boolean('is_default')->default(false)->index()->comment('Default configuration for provider');
            $table->text('description')->nullable()->comment('Configuration description');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['provider_id', 'is_active']);
            $table->index(['is_active', 'is_default']);
            
            // Unique constraint: Only one default per provider
            $table->unique(['provider_id', 'is_default', 'is_active'], 'unique_default_per_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_provider_configurations');
    }
};
