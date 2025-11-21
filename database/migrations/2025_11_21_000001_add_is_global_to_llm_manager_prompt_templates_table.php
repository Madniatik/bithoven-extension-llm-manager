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
        Schema::table('llm_manager_prompt_templates', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('extension_slug');
        });

        // Migrate existing data: if extension_slug is null, it's global
        DB::table('llm_manager_prompt_templates')
            ->whereNull('extension_slug')
            ->update(['is_global' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llm_manager_prompt_templates', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });
    }
};
