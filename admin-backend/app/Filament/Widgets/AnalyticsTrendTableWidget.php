<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsTrendTableWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $days = $this->analyticsDays();

        return $table
            ->heading('每日趋势（'.$this->rangeLabel().'）')
            ->records(function () use ($days): Collection {
                $rows = $this->miniappAnalytics()->dailyTrend($this->now(), $days);

                return collect($rows)->values()->map(fn (array $row, int $i): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'analytics-trend-'.$i.'-'.($row['date'] ?? ''),
                ]);
            })
            ->columns([
                TextColumn::make('date')->label('日期'),
                TextColumn::make('home_page_views')->label('首页 PV')->numeric()->alignEnd(),
                TextColumn::make('me_page_views')->label('个人中心 PV')->numeric()->alignEnd(),
                TextColumn::make('total_events')->label('总事件')->numeric()->alignEnd(),
                TextColumn::make('home_generate_success')->label('生成成功')->numeric()->alignEnd(),
                TextColumn::make('me_login_success')->label('登录成功')->numeric()->alignEnd(),
                TextColumn::make('conversion_events')->label('转化事件')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
