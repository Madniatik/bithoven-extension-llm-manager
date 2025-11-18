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
        Schema::create('llm_custom_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usage_log_id')->constrained('llm_usage_logs')->onDelete('cascade');
            $table->string('extension_slug', 100);
            $table->string('metric_key', 100); // e.g., 'ticket_category', 'sentiment_score'
            $table->string('metric_value', 255); // e.g., 'billing', '0.85'
            $table->enum('metric_type', ['string', 'integer', 'float', 'boolean', 'json'])->default('string');
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();
            
            // Indexes
            $table->index(['extension_slug', 'metric_key']);
            $table->index(['usage_log_id', 'metric_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_custom_metrics');
    }
};
