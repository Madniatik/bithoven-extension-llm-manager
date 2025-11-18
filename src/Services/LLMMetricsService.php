<?php

namespace Bithoven\LLMManager\Services;

use Bithoven\LLMManager\Models\LLMCustomMetric;
use Bithoven\LLMManager\Models\LLMUsageLog;

class LLMMetricsService
{
    /**
     * Record a custom metric
     */
    public function record(
        int $usageLogId,
        string $extensionSlug,
        string $key,
        mixed $value,
        string $type = 'string',
        ?array $metadata = null
    ): LLMCustomMetric {
        return LLMCustomMetric::create([
            'usage_log_id' => $usageLogId,
            'extension_slug' => $extensionSlug,
            'metric_key' => $key,
            'metric_value' => (string) $value,
            'metric_type' => $type,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get metrics statistics
     */
    public function getStats(string $extensionSlug, string $metricKey): array
    {
        return LLMCustomMetric::getStats($extensionSlug, $metricKey);
    }

    /**
     * Get all metrics for an extension
     */
    public function getByExtension(string $extensionSlug): \Illuminate\Database\Eloquent\Collection
    {
        return LLMCustomMetric::byExtension($extensionSlug)
            ->with('usageLog')
            ->latest()
            ->get();
    }

    /**
     * Get metrics grouped by key
     */
    public function getGroupedByKey(string $extensionSlug): array
    {
        $metrics = LLMCustomMetric::byExtension($extensionSlug)
            ->select('metric_key')
            ->distinct()
            ->pluck('metric_key');

        $result = [];

        foreach ($metrics as $key) {
            $result[$key] = $this->getStats($extensionSlug, $key);
        }

        return $result;
    }

    /**
     * Delete old metrics (cleanup)
     */
    public function cleanup(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return LLMCustomMetric::whereHas('usageLog', function ($query) use ($cutoffDate) {
            $query->where('executed_at', '<', $cutoffDate);
        })->delete();
    }
}
