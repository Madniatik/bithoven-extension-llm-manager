<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea la tabla llm_user_workspace_preferences para almacenar
     * la configuración personalizada de cada usuario en el workspace LLM.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('llm_user_workspace_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('config');
            $table->timestamps();

            // Índice único: un usuario solo puede tener una preferencia
            $table->unique('user_id', 'unique_user_workspace_pref');

            // Foreign key con cascada
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Elimina la tabla llm_user_workspace_preferences.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_user_workspace_preferences');
    }
};
