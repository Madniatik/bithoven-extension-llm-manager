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
        Schema::create('llm_manager_conversation_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('extension_slug', 100)->nullable();
            $table->foreignId('llm_configuration_id')->constrained('llm_manager_configurations')->onDelete('cascade');
            $table->string('title', 255)->nullable();
            $table->json('metadata')->nullable(); // Context, tags, etc.
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes (shortened names for MySQL 64-char limit)
            $table->index(['user_id', 'is_active'], 'llm_cs_user_active_idx');
            $table->index(['extension_slug', 'is_active'], 'llm_cs_ext_active_idx');
            $table->index('session_id', 'llm_cs_session_idx');
            $table->index('expires_at', 'llm_cs_expires_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_manager_conversation_sessions');
    }
};
