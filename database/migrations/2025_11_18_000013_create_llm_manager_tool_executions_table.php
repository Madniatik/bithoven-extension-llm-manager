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
        Schema::create('llm_manager_tool_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_definition_id')->constrained('llm_manager_tool_definitions')->onDelete('cascade');
            $table->foreignId('usage_log_id')->nullable()->constrained('llm_manager_usage_logs')->onDelete('set null');
            $table->foreignId('session_id')->nullable()->constrained('llm_manager_conversation_sessions')->onDelete('set null');
            $table->json('input_parameters'); // Arguments passed to the tool
            $table->longText('output_result')->nullable(); // Tool execution result
            $table->enum('status', ['pending', 'running', 'success', 'error'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('execution_time_ms')->unsigned()->nullable();
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['tool_definition_id', 'status'], 'llm_te_tool_status_idx');
            $table->index(['usage_log_id', 'executed_at'], 'llm_te_log_exec_idx');
            $table->index(['session_id', 'executed_at'], 'llm_te_session_exec_idx');
            $table->index('status', 'llm_te_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_tool_executions');
    }
};
