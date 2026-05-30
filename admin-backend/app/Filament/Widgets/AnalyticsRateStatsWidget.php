<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsRateStatsWidget extends StatsOverviewWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 4;

    protected function getHeading(): ?string
    {
        return '转化率（'.$this->rangeLabel().'）';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $r = $this->miniappAnalytics()->rangeRates($this->now(), $this->analyticsDays());

        return [
            Stat::make('首页生成率', $this->formatPct($r['home_generate_rate_pct']))
                ->description('PV → 点击生成')
                ->icon(Heroicon::OutlinedSparkles),
            Stat::make('生成成功率', $this->formatPct($r['home_success_rate_pct']))
                ->description('生成 → 成功')
                ->icon(Heroicon::OutlinedCheckCircle),
            Stat::make('收藏率', $this->formatPct($r['home_favorite_rate_pct']))
                ->description('成功 → 收藏')
                ->icon(Heroicon::OutlinedHeart),
            Stat::make('登录成功率', $this->formatPct($r['me_login_success_rate_pct']))
                ->description('点击 → 成功')
                ->icon(Heroicon::OutlinedShieldCheck),
            Stat::make('赞助支付成功率', $this->formatPct($r['me_sponsor_pay_rate_pct']))
                ->description('发起 → 成功')
                ->icon(Heroicon::OutlinedGift),
            Stat::make('反馈提交成功率', $this->formatPct($r['me_feedback_submit_rate_pct']))
                ->description('提交 → 成功')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight),
        ];
    }
}
