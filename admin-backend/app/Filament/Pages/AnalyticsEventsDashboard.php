<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsCategoryBreakdownWidget;
use App\Filament\Widgets\AnalyticsEventCatalogWidget;
use App\Filament\Widgets\AnalyticsHomeFunnelWidget;
use App\Filament\Widgets\AnalyticsHomeTopEventsWidget;
use App\Filament\Widgets\AnalyticsMeFunnelWidget;
use App\Filament\Widgets\AnalyticsMeTopEventsWidget;
use App\Filament\Widgets\AnalyticsOverviewStatsWidget;
use App\Filament\Widgets\AnalyticsPageBreakdownWidget;
use App\Filament\Widgets\AnalyticsRateStatsWidget;
use App\Filament\Widgets\AnalyticsRecentEventsWidget;
use App\Filament\Widgets\AnalyticsTrendTableWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use UnitEnum;

class AnalyticsEventsDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $navigationLabel = '数据埋点';

    protected static ?string $title = '数据埋点';

    protected static string|UnitEnum|null $navigationGroup = '数据分析';

    protected static ?int $navigationSort = 1;

    public int $days = 7;

    protected ?string $subheading = '小程序首页与个人中心全链路埋点 · 支持切换统计范围';

    public function mount(): void
    {
        $this->days = max(1, min(90, (int) session('analytics_dashboard_days', 7)));
    }

    protected function setAnalyticsDays(int $days): void
    {
        $this->days = max(1, min(90, $days));
        session(['analytics_dashboard_days' => $this->days]);
        $this->redirect(static::getUrl());
    }

    /**
     * @return int | array<string, ?int>
     */
    protected function getAnalyticsGridColumns(): int|array
    {
        return 2;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    protected function getAnalyticsWidgets(): array
    {
        return [
            AnalyticsOverviewStatsWidget::class,
            AnalyticsRateStatsWidget::class,
            AnalyticsTrendTableWidget::class,
            AnalyticsPageBreakdownWidget::class,
            AnalyticsCategoryBreakdownWidget::class,
            AnalyticsHomeFunnelWidget::class,
            AnalyticsMeFunnelWidget::class,
            AnalyticsHomeTopEventsWidget::class,
            AnalyticsMeTopEventsWidget::class,
            AnalyticsEventCatalogWidget::class,
            AnalyticsRecentEventsWidget::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make($this->getAnalyticsGridColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getAnalyticsWidgets())),
            ]);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('days1')
                    ->label('今日')
                    ->action(fn () => $this->setAnalyticsDays(1)),
                Action::make('days7')
                    ->label('近 7 天')
                    ->action(fn () => $this->setAnalyticsDays(7)),
                Action::make('days14')
                    ->label('近 14 天')
                    ->action(fn () => $this->setAnalyticsDays(14)),
                Action::make('days30')
                    ->label('近 30 天')
                    ->action(fn () => $this->setAnalyticsDays(30)),
            ])
                ->label(fn (): string => $this->days === 1 ? '统计范围：今日' : "统计范围：近 {$this->days} 天")
                ->icon(Heroicon::OutlinedCalendarDays)
                ->button(),
            Action::make('refresh')
                ->label('刷新数据')
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(fn () => $this->redirect(static::getUrl())),
        ];
    }
}
