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
        Schema::create('llm_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('llm_conversation_sessions')->onDelete('cascade');
            $table->enum('role', ['system', 'user', 'assistant', 'tool'])->default('user');
            $table->longText('content');
            $table->json('metadata')->nullable(); // Tool calls, function results, etc.
            $table->integer('tokens')->unsigned()->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['session_id', 'created_at']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_conversation_messages');
    }
};
