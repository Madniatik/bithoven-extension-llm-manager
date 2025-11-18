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
        Schema::create('llm_manager_conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('llm_manager_conversation_sessions')->onDelete('cascade');
            $table->enum('event_type', ['started', 'message_sent', 'response_received', 'error', 'summarized', 'ended'])->default('message_sent');
            $table->text('event_data')->nullable(); // JSON with event details
            $table->integer('tokens_used')->unsigned()->nullable();
            $table->decimal('cost_usd', 10, 6)->nullable();
            $table->integer('execution_time_ms')->unsigned()->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['session_id', 'event_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_conversation_logs');
    }
};
