<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL no permite modificar ENUM directamente, hay que usar ALTER TABLE con MODIFY
        DB::statement("ALTER TABLE llm_manager_configurations MODIFY COLUMN provider ENUM('ollama', 'openai', 'anthropic', 'openrouter', 'local', 'custom') DEFAULT 'ollama'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al estado anterior (sin openrouter)
        DB::statement("ALTER TABLE llm_manager_configurations MODIFY COLUMN provider ENUM('ollama', 'openai', 'anthropic', 'local', 'custom') DEFAULT 'ollama'");
    }
};
