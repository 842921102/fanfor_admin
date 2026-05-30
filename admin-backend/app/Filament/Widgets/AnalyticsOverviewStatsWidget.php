<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsOverviewStatsWidget extends StatsOverviewWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 4;

    protected function getHeading(): ?string
    {
        return '概览（'.$this->rangeLabel().'）';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $o = $this->miniappAnalytics()->rangeOverview($this->now(), $this->analyticsDays());

        return [
            Stat::make('总事件数', $o['total_events'])->icon(Heroicon::OutlinedChartBarSquare),
            Stat::make('首页 PV', $o['home_page_views'])->description('UV '.$o['home_uv'])->icon(Heroicon::OutlinedHome),
            Stat::make('个人中心 PV', $o['me_page_views'])->description('UV '.$o['me_uv'])->icon(Heroicon::OutlinedUser),
            Stat::make('子页面 PV', $o['me_sub_page_views'])->description('转化 '.$o['conversion_events'])->icon(Heroicon::OutlinedDocumentDuplicate),
            Stat::make('已登录事件', $o['logged_in_events'])->description('游客 '.$o['guest_events'])->icon(Heroicon::OutlinedIdentification),
        ];
    }
}
