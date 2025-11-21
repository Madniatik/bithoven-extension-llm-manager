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
            $table->string('extension_slug')->nullable()->after('type');
            $table->string('tool_type')->nullable()->after('extension_slug'); // Alias for 'type' (for backward compat)
            $table->json('parameters_schema')->nullable()->after('function_schema'); // Alias for function_schema
            $table->string('implementation')->nullable()->after('handler_method'); // Combined handler
            $table->json('metadata')->nullable()->after('security_policy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llm_manager_tool_definitions', function (Blueprint $table) {
            $table->dropColumn([
                'extension_slug',
                'tool_type',
                'parameters_schema',
                'implementation',
                'metadata',
            ]);
        });
    }
};
