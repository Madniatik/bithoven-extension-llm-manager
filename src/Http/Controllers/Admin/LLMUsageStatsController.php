<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMUsageLog;
// use Bithoven\LLMManager\Models\LLMBudgetAlert; // TODO: Create model

class LLMUsageStatsController extends Controller
{
    public function dashboard()
    {
        $period = 'month';
        $stats = $this->getStatistics($period, null);
        
        return view('llm-manager::admin.stats.dashboard', compact('stats', 'period'));
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $extensionSlug = $request->get('extension');

        $stats = $this->getStatistics($period, $extensionSlug);

        return view('llm-manager::admin.stats.index', compact('stats', 'period', 'extensionSlug'));
    }

    protected function getStatistics(string $period, ?string $extensionSlug): array
    {
        $query = LLMUsageLog::query();

        if ($extensionSlug) {
            $query->byExtension($extensionSlug);
        }

        // Filter by period
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $query->where('created_at', '>=', $startDate);

        // Get aggregated stats
        $totalRequests = $query->count();
        $totalCost = $query->sum('cost_usd');
        $totalTokens = $query->sum('total_tokens');
        $avgExecutionTime = $query->avg('execution_time_ms');

        // Group by configuration
        $byConfiguration = $query->get()
            ->groupBy('llm_provider_configuration_id')
            ->map(function ($logs) {
                return [
                    'count' => $logs->count(),
                    'cost' => $logs->sum('cost_usd'),
                    'tokens' => $logs->sum('total_tokens'),
                    'avg_time' => $logs->avg('execution_time_ms'),
                ];
            });

        // Group by extension
        $byExtension = $query->get()
            ->groupBy('extension_slug')
            ->map(function ($logs) {
                return [
                    'count' => $logs->count(),
                    'cost' => $logs->sum('cost_usd'),
                    'tokens' => $logs->sum('total_tokens'),
                ];
            });

        // Recent budget alerts
        // TODO: Implement LLMBudgetAlert model and migration
        // $recentAlerts = LLMBudgetAlert::latest()->limit(10)->get();
        $recentAlerts = collect();

        return [
            'total_requests' => $totalRequests,
            'total_cost' => $totalCost,
            'total_tokens' => $totalTokens,
            'avg_execution_time' => round($avgExecutionTime, 2),
            'by_configuration' => $byConfiguration,
            'by_extension' => $byExtension,
            'recent_alerts' => $recentAlerts,
            'period' => $period,
            'start_date' => $startDate,
        ];
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'month');
        $extensionSlug = $request->get('extension');

        $stats = $this->getStatistics($period, $extensionSlug);

        return response()->json($stats);
    }
}
