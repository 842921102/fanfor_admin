<?php

namespace App\Filament\Widgets\Concerns;

use App\Services\MiniappAnalyticsService;
use Illuminate\Support\Carbon;

trait UsesMiniappAnalytics
{
    protected function miniappAnalytics(): MiniappAnalyticsService
    {
        return app(MiniappAnalyticsService::class);
    }

    protected function now(): Carbon
    {
        return Carbon::now();
    }

    protected function analyticsDays(): int
    {
        return max(1, min(90, (int) session('analytics_dashboard_days', 7)));
    }

    protected function rangeLabel(): string
    {
        $days = $this->analyticsDays();

        return $days === 1 ? '今日' : "近 {$days} 天";
    }

    protected function formatPct(?float $v): string
    {
        if ($v === null) {
            return '—';
        }

        return $v.'%';
    }
}
