#!/bin/bash

# Script para verificar que el logging de streaming funciona correctamente

cd /Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL

echo "🔍 VERIFICANDO ÚLTIMO REGISTRO DE STREAMING..."
echo ""

php artisan tinker --execute="
\$latest = DB::table('llm_manager_usage_logs')->latest('id')->first();

if (!\$latest) {
    echo '❌ No hay registros en llm_manager_usage_logs' . PHP_EOL;
    echo 'Ve a http://localhost:8000/admin/llm/stream/test y haz un streaming' . PHP_EOL;
    exit;
}

echo '✅ ÚLTIMO REGISTRO DE STREAMING:' . PHP_EOL;
echo '══════════════════════════════════════════════════════════' . PHP_EOL;
echo 'ID: ' . \$latest->id . PHP_EOL;
echo 'Configuration ID: ' . \$latest->llm_configuration_id . PHP_EOL;
echo 'User ID: ' . (\$latest->user_id ?? 'NULL') . PHP_EOL;
echo '──────────────────────────────────────────────────────────' . PHP_EOL;
echo 'Prompt: ' . substr(\$latest->prompt, 0, 80) . (\strlen(\$latest->prompt) > 80 ? '...' : '') . PHP_EOL;
echo 'Response: ' . substr(\$latest->response, 0, 80) . (\strlen(\$latest->response) > 80 ? '...' : '') . PHP_EOL;
echo '──────────────────────────────────────────────────────────' . PHP_EOL;
echo '📊 MÉTRICAS:' . PHP_EOL;
echo '  • Prompt Tokens: ' . \$latest->prompt_tokens . PHP_EOL;
echo '  • Completion Tokens: ' . \$latest->completion_tokens . PHP_EOL;
echo '  • Total Tokens: ' . \$latest->total_tokens . PHP_EOL;
echo '──────────────────────────────────────────────────────────' . PHP_EOL;
echo '💰 COSTO:' . PHP_EOL;
echo '  • Cost USD: $' . number_format(\$latest->cost_usd, 6) . PHP_EOL;
if (\$latest->currency && \$latest->currency !== 'USD') {
    echo '  • Original: ' . \$latest->currency . ' ' . \$latest->cost_original . PHP_EOL;
}
echo '──────────────────────────────────────────────────────────' . PHP_EOL;
echo '⏱️  RENDIMIENTO:' . PHP_EOL;
echo '  • Execution Time: ' . \$latest->execution_time_ms . 'ms (' . round(\$latest->execution_time_ms / 1000, 2) . 's)' . PHP_EOL;
echo '  • Status: ' . \$latest->status . PHP_EOL;
if (\$latest->error_message) {
    echo '  • Error: ' . \$latest->error_message . PHP_EOL;
}
echo '──────────────────────────────────────────────────────────' . PHP_EOL;
echo '📅 TIMESTAMPS:' . PHP_EOL;
echo '  • Executed At: ' . \$latest->executed_at . PHP_EOL;
echo '  • Created At: ' . \$latest->created_at . PHP_EOL;
echo '══════════════════════════════════════════════════════════' . PHP_EOL;
echo '' . PHP_EOL;

// Verificar configuración
\$config = DB::table('llm_manager_configurations')->find(\$latest->llm_configuration_id);
if (\$config) {
    echo '🔧 CONFIGURACIÓN USADA:' . PHP_EOL;
    echo '  • Provider: ' . \$config->provider . PHP_EOL;
    echo '  • Model: ' . \$config->model . PHP_EOL;
    echo '  • Name: ' . \$config->name . PHP_EOL;
    echo '' . PHP_EOL;
}

// Estadísticas generales
\$totalLogs = DB::table('llm_manager_usage_logs')->count();
\$totalTokens = DB::table('llm_manager_usage_logs')->sum('total_tokens');
\$totalCost = DB::table('llm_manager_usage_logs')->sum('cost_usd');

echo '📈 ESTADÍSTICAS GENERALES:' . PHP_EOL;
echo '  • Total Logs: ' . number_format(\$totalLogs) . PHP_EOL;
echo '  • Total Tokens: ' . number_format(\$totalTokens) . PHP_EOL;
echo '  • Total Cost: $' . number_format(\$totalCost, 4) . PHP_EOL;
echo '' . PHP_EOL;
"

echo ""
echo "✨ Para ver más detalles, ve a: http://localhost:8000/admin/llm/stats"
echo ""
