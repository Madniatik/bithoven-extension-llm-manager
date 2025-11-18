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
        Schema::create('llm_manager_parameter_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('extension_slug', 100);
            $table->foreignId('llm_configuration_id')->nullable()->constrained('llm_manager_configurations')->onDelete('cascade');
            $table->string('context', 100)->nullable(); // e.g., 'ticket_summary', 'product_description'
            $table->json('override_parameters'); // {temperature: 0.7, max_tokens: 500}
            $table->enum('merge_strategy', ['replace', 'merge', 'deep_merge'])->default('merge');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority overrides are applied last
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['extension_slug', 'context', 'is_active']);
            $table->index(['llm_configuration_id', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_parameter_overrides');
    }
};
