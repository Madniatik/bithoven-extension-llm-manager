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
        Schema::create('llm_manager_agent_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('extension_slug', 100)->nullable();
            $table->json('workflow_definition'); // State machine definition
            $table->json('agents_config'); // Multiple agent configurations
            $table->foreignId('llm_configuration_id')->nullable()->constrained('llm_manager_configurations')->onDelete('set null');
            $table->integer('max_steps')->default(20);
            $table->integer('timeout_seconds')->default(120);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['extension_slug', 'is_active']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_agent_workflows');
    }
};
