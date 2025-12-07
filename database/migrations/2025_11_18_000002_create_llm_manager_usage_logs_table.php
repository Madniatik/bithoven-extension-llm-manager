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
        Schema::create('llm_manager_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('llm_configuration_id')->constrained('llm_manager_configurations')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('session_id')->nullable(); // Foreign key added later (after conversation_sessions table exists)
            $table->unsignedBigInteger('message_id')->nullable(); // Foreign key added later (after conversation_messages table exists)
            $table->string('extension_slug', 100)->nullable();
            $table->text('prompt')->nullable(); // Nullable for testing
            $table->longText('response')->nullable(); // Nullable for testing
            $table->json('parameters_used')->nullable(); // Effective parameters after override
            $table->integer('prompt_tokens')->unsigned()->default(0);
            $table->integer('completion_tokens')->unsigned()->default(0);
            $table->integer('total_tokens')->unsigned()->default(0);
            $table->decimal('cost_usd', 10, 6)->nullable();
            $table->integer('execution_time_ms')->unsigned()->nullable(); // Milliseconds
            $table->enum('status', ['success', 'error', 'timeout'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['llm_configuration_id', 'executed_at'], 'llm_ul_cfg_exec_idx');
            $table->index(['user_id', 'executed_at'], 'llm_ul_user_exec_idx');
            $table->index(['extension_slug', 'executed_at'], 'llm_ul_ext_exec_idx');
            $table->index('session_id', 'llm_ul_session_idx');
            $table->index('message_id', 'llm_ul_message_idx');
            $table->index('status', 'llm_ul_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_usage_logs');
    }
};
