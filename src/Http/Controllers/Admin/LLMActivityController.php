<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMUsageLog;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Illuminate\Http\Request;

class LLMActivityController extends Controller
{
    public function __construct(
        private readonly LLMConfigurationService $configService
    ) {}
    /**
     * Display activity logs with filters
     */
    public function index(Request $request)
    {
        $query = LLMUsageLog::with(['configuration', 'user'])
            ->latest('executed_at');

        // Filter by provider
        if ($request->filled('provider')) {
            $query->whereHas('configuration.provider', function ($q) use ($request) {
                $q->where('slug', $request->provider);
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
        $providers = $this->configService->getProviders();

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
        $query = LLMUsageLog::with(['configuration.provider', 'user', 'session'])
            ->latest('executed_at');

        // Filter by session_id (Monitor context)
        if ($request->filled('session_id')) {
            $session = \Bithoven\LLMManager\Models\LLMConversationSession::findOrFail($request->session_id);
            
            // Security: Verify session belongs to current user
            if ($session->user_id !== auth()->id()) {
                abort(403, 'Unauthorized: This session does not belong to you');
            }
            
            $query->where('session_id', $request->session_id);
        }

        // Filter by user_only (Monitor context - only current user's logs)
        if ($request->filled('user_only') && $request->user_only) {
            $query->where('user_id', auth()->id());
        }

        // Apply same filters as index
        if ($request->filled('provider')) {
            $query->whereHas('configuration.provider', function ($q) use ($request) {
                $q->where('slug', $request->provider);
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

        // Dynamic filename based on context
        $filename = 'llm-activity-';
        if ($request->filled('session_id')) {
            $filename .= 'session-' . $request->session_id . '-';
        } elseif ($request->filled('user_only')) {
            $filename .= 'user-';
        }
        $filename .= date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Session ID',
                'Date/Time',
                'Provider',
                'Model',
                'User',
                'Prompt (Full)',
                'Response (Full)',
                'Prompt Tokens',
                'Completion Tokens',
                'Total Tokens',
                'Cost USD',
                'Duration (ms)',
                'Duration (s)',
                'Status',
                'Error Message',
            ]);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->session_id ?? 'N/A',
                    $log->executed_at,
                    $log->configuration->provider->slug ?? 'N/A',
                    $log->configuration->model ?? 'N/A',
                    $log->user->name ?? 'N/A',
                    $log->prompt,  // Full text
                    $log->response,  // Full text
                    $log->prompt_tokens,
                    $log->completion_tokens,
                    $log->total_tokens,
                    $log->cost_usd,
                    $log->execution_time_ms,
                    round($log->execution_time_ms / 1000, 2),  // Duration in seconds
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
        $query = LLMUsageLog::with(['configuration', 'user', 'session'])
            ->latest('executed_at');

        // Filter by session_id (Monitor context)
        if ($request->filled('session_id')) {
            $session = \Bithoven\LLMManager\Models\LLMConversationSession::findOrFail($request->session_id);
            
            // Security: Verify session belongs to current user
            if ($session->user_id !== auth()->id()) {
                abort(403, 'Unauthorized: This session does not belong to you');
            }
            
            $query->where('session_id', $request->session_id);
        }

        // Filter by user_only (Monitor context - only current user's logs)
        if ($request->filled('user_only') && $request->user_only) {
            $query->where('user_id', auth()->id());
        }

        // Apply same filters as index
        if ($request->filled('provider')) {
            $query->whereHas('configuration.provider', function ($q) use ($request) {
                $q->where('slug', $request->provider);
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

        // Dynamic filename based on context
        $filename = 'llm-activity-';
        if ($request->filled('session_id')) {
            $filename .= 'session-' . $request->session_id . '-';
        } elseif ($request->filled('user_only')) {
            $filename .= 'user-';
        }
        $filename .= date('Y-m-d-His') . '.json';

        return response()->json($logs, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export logs to SQL
     */
    public function exportSql(Request $request)
    {
        $query = LLMUsageLog::with(['configuration.provider', 'user', 'session'])
            ->latest('executed_at');

        // Filter by session_id (Monitor context)
        if ($request->filled('session_id')) {
            $session = \Bithoven\LLMManager\Models\LLMConversationSession::findOrFail($request->session_id);
            
            // Security: Verify session belongs to current user
            if ($session->user_id !== auth()->id()) {
                abort(403, 'Unauthorized: This session does not belong to you');
            }
            
            $query->where('session_id', $request->session_id);
        }

        // Filter by user_only (Monitor context - only current user's logs)
        if ($request->filled('user_only') && $request->user_only) {
            $query->where('user_id', auth()->id());
        }

        // Apply same filters as index
        if ($request->filled('provider')) {
            $query->whereHas('configuration.provider', function ($q) use ($request) {
                $q->where('slug', $request->provider);
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

        // Dynamic filename based on context
        $filename = 'llm-activity-';
        if ($request->filled('session_id')) {
            $filename .= 'session-' . $request->session_id . '-';
        } elseif ($request->filled('user_only')) {
            $filename .= 'user-';
        }
        $filename .= date('Y-m-d-His') . '.sql';

        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs, $request) {
            $file = fopen('php://output', 'w');

            // SQL Header Comments
            fwrite($file, "-- LLM Activity Export\n");
            fwrite($file, "-- Date: " . date('Y-m-d H:i:s') . "\n");
            if ($request->filled('session_id')) {
                fwrite($file, "-- Session ID: {$request->session_id}\n");
            }
            fwrite($file, "-- Total Records: " . $logs->count() . "\n\n");

            if ($logs->isEmpty()) {
                fwrite($file, "-- No records found\n");
                fclose($file);
                return;
            }

            // INSERT statements
            fwrite($file, "INSERT INTO `llm_manager_usage_logs` \n");
            fwrite($file, "(`id`, `session_id`, `user_id`, `configuration_id`, `provider`, `model`, `prompt`, `response`, `prompt_tokens`, `completion_tokens`, `total_tokens`, `cost_usd`, `execution_time_ms`, `status`, `error_message`, `executed_at`, `created_at`, `updated_at`) \n");
            fwrite($file, "VALUES\n");

            $totalLogs = $logs->count();
            foreach ($logs as $index => $log) {
                $isLast = ($index + 1) === $totalLogs;

                // Escape values for SQL
                $sessionId = $log->session_id ? "'{$log->session_id}'" : 'NULL';
                $userId = $log->user_id ? "'{$log->user_id}'" : 'NULL';
                $configId = $log->configuration_id ? "'{$log->configuration_id}'" : 'NULL';
                $provider = addslashes($log->configuration->provider->slug ?? 'N/A');
                $model = addslashes($log->configuration->model ?? 'N/A');
                $prompt = addslashes($log->prompt);
                $response = addslashes($log->response);
                $errorMessage = $log->error_message ? "'" . addslashes($log->error_message) . "'" : 'NULL';
                $executedAt = $log->executed_at ? "'{$log->executed_at}'" : 'NULL';
                $createdAt = $log->created_at ? "'{$log->created_at}'" : 'NULL';
                $updatedAt = $log->updated_at ? "'{$log->updated_at}'" : 'NULL';

                fwrite($file, "({$log->id}, {$sessionId}, {$userId}, {$configId}, '{$provider}', '{$model}', '{$prompt}', '{$response}', {$log->prompt_tokens}, {$log->completion_tokens}, {$log->total_tokens}, {$log->cost_usd}, {$log->execution_time_ms}, '{$log->status}', {$errorMessage}, {$executedAt}, {$createdAt}, {$updatedAt})");
                fwrite($file, $isLast ? ";\n" : ",\n");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
