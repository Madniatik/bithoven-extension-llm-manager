<?php

namespace Bithoven\LLMManager\Services;

use Bithoven\LLMManager\Models\LLMUsageLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LLMBudgetManager
{
    /**
     * Get total spent for a period
     */
    public function getTotalSpent(?string $extensionSlug = null, ?string $period = 'month'): float
    {
        $startDate = $this->getStartDate($period);

        return LLMUsageLog::query()
            ->when($extensionSlug, fn($q) => $q->where('extension_slug', $extensionSlug))
            ->where('executed_at', '>=', $startDate)
            ->sum('cost_usd');
    }

    /**
     * Check budget and send alerts
     */
    public function checkAndAlert(?string $extensionSlug = null): void
    {
        $limit = config('llm-manager.budget.monthly_limit_usd', 100);
        $threshold = config('llm-manager.budget.alert_threshold_percentage', 80) / 100;

        $spent = $this->getTotalSpent($extensionSlug, 'month');
        $percentage = ($spent / $limit) * 100;

        if ($percentage >= ($threshold * 100)) {
            $this->sendAlert($extensionSlug, $spent, $limit, $percentage);
        }
    }

    /**
     * Send budget alert
     */
    protected function sendAlert(?string $extensionSlug, float $spent, float $limit, float $percentage): void
    {
        Log::warning('LLM Budget Alert', [
            'extension_slug' => $extensionSlug ?? 'global',
            'spent' => $spent,
            'limit' => $limit,
            'percentage' => round($percentage, 2),
        ]);

        // TODO: Send notification to admins
        // Notification::send($admins, new LLMBudgetAlert(...));
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(?string $extensionSlug = null): float
    {
        $limit = config('llm-manager.budget.monthly_limit_usd', 100);
        $spent = $this->getTotalSpent($extensionSlug, 'month');

        return max(0, $limit - $spent);
    }

    /**
     * Get budget statistics
     */
    public function getStats(?string $extensionSlug = null): array
    {
        $limit = config('llm-manager.budget.monthly_limit_usd', 100);
        $spent = $this->getTotalSpent($extensionSlug, 'month');
        $remaining = max(0, $limit - $spent);
        $percentage = ($spent / $limit) * 100;

        return [
            'limit' => $limit,
            'spent' => $spent,
            'remaining' => $remaining,
            'percentage' => round($percentage, 2),
            'currency' => config('llm-manager.budget.currency', 'USD'),
        ];
    }

    /**
     * Get start date for period
     */
    protected function getStartDate(string $period): \DateTime
    {
        return match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }
}
