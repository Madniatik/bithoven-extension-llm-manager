<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Http\Request;

class LLMActivityController extends Controller
{
    /**
     * Display activity logs with filters
     */
    public function index(Request $request)
    {
        $query = LLMUsageLog::with(['configuration', 'user'])
            ->latest('executed_at');

        // Filter by provider
        if ($request->filled('provider')) {
            $query->whereHas('configuration', function ($q) use ($request) {
                $q->where('provider', $request->provider);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('executed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('executed_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search in prompt or response
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prompt', 'like', "%{$search}%")
                    ->orWhere('response', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20);

        // Get providers for filter
        $providers = LLMConfiguration::select('provider')
            ->distinct()
            ->pluck('provider');

        return view('llm-manager::admin.activity.index', compact('logs', 'providers'));
    }

    /**
     * Show single log details
     */
    public function show($id)
    {
        $log = LLMUsageLog::with(['configuration', 'user'])->findOrFail($id);

        return view('llm-manager::admin.activity.show', compact('log'));
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = LLMUsageLog::with(['configuration', 'user'])
            ->latest('executed_at');

        // Apply same filters as index
        if ($request->filled('provider')) {
            $query->whereHas('configuration', function ($q) use ($request) {
                $q->where('provider', $request->provider);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('executed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('executed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->get();

        $filename = 'llm-activity-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Date/Time',
                'Provider',
                'Model',
                'User',
                'Prompt',
                'Response',
                'Prompt Tokens',
                'Completion Tokens',
                'Total Tokens',
                'Cost USD',
                'Duration (ms)',
                'Status',
                'Error Message',
            ]);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->executed_at,
                    $log->configuration->provider ?? 'N/A',
                    $log->configuration->model ?? 'N/A',
                    $log->user->name ?? 'N/A',
                    substr($log->prompt, 0, 200),
                    substr($log->response, 0, 200),
                    $log->prompt_tokens,
                    $log->completion_tokens,
                    $log->total_tokens,
                    $log->cost_usd,
                    $log->execution_time_ms,
                    $log->status,
                    $log->error_message ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export logs to JSON
     */
    public function exportJson(Request $request)
    {
        $query = LLMUsageLog::with(['configuration', 'user'])
            ->latest('executed_at');

        // Apply same filters as index
        if ($request->filled('provider')) {
            $query->whereHas('configuration', function ($q) use ($request) {
                $q->where('provider', $request->provider);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('executed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('executed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->get();

        $filename = 'llm-activity-' . date('Y-m-d-His') . '.json';

        return response()->json($logs, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
