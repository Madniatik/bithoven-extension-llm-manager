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
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite no soporta ENUM ni MODIFY COLUMN
            // En SQLite, 'provider' es un string simple, así que no requiere cambios
            // Los valores ENUM se validan a nivel de aplicación (via validator)
            return;
        }
        
        // MySQL/MariaDB: modificar ENUM para agregar 'openrouter'
        DB::statement("ALTER TABLE llm_manager_configurations MODIFY COLUMN provider ENUM('ollama', 'openai', 'anthropic', 'openrouter', 'local', 'custom') DEFAULT 'ollama'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: no requiere cambios (ver up())
            return;
        }
        
        // MySQL/MariaDB: revertir al estado anterior (sin openrouter)
        DB::statement("ALTER TABLE llm_manager_configurations MODIFY COLUMN provider ENUM('ollama', 'openai', 'anthropic', 'local', 'custom') DEFAULT 'ollama'");
    }
};
