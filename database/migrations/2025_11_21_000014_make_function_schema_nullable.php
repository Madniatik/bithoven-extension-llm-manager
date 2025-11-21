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
        Schema::table('llm_manager_tool_definitions', function (Blueprint $table) {
            $table->json('function_schema')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llm_manager_tool_definitions', function (Blueprint $table) {
            $table->json('function_schema')->nullable(false)->change();
        });
    }
};
