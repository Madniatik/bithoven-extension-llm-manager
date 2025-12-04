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
        Schema::create('llm_manager_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('llm_manager_conversation_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User who sent the message
            $table->foreignId('llm_configuration_id')->nullable()->constrained('llm_manager_configurations')->onDelete('set null'); // LLM config used for this message
            $table->enum('role', ['system', 'user', 'assistant', 'tool'])->default('user');
            $table->longText('content');
            $table->json('metadata')->nullable(); // Tool calls, function results, LLM config, streaming info, etc.
            $table->integer('tokens')->unsigned()->nullable();
            $table->decimal('response_time', 8, 3)->nullable(); // Response time in seconds (e.g., 2.456s)
            $table->decimal('cost_usd', 10, 6)->nullable(); // Cost in USD (e.g., 0.001234)
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('sent_at')->nullable(); // When user/system sent the message
            $table->timestamp('started_at')->nullable(); // When LLM started processing
            $table->timestamp('completed_at')->nullable(); // When LLM finished response
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['session_id', 'created_at'], 'llm_cm_session_created_idx');
            $table->index('role', 'llm_cm_role_idx');
            $table->index('started_at', 'llm_cm_started_idx');
            $table->index('completed_at', 'llm_cm_completed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_conversation_messages');
    }
};
