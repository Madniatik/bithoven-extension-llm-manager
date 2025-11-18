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
        Schema::table('llm_manager_usage_logs', function (Blueprint $table) {
            // Add currency code (ISO 4217: USD, EUR, GBP, etc.)
            $table->string('currency', 3)->default('USD')->after('cost_usd');
            
            // Add original cost in original currency
            $table->decimal('cost_original', 10, 6)->nullable()->after('currency');
            
                        // Index for currency filtering (shortened name for MySQL 64-char limit)
            $table->index('currency', 'llm_ul_currency_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llm_manager_usage_logs', function (Blueprint $table) {
            $table->dropIndex('llm_ul_currency_idx');
            $table->dropColumn(['currency', 'cost_original']);
        });
    }
};
