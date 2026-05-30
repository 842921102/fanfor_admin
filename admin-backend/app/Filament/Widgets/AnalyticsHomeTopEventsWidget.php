<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use App\Support\MiniappAnalyticsEventCatalog;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsHomeTopEventsWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('首页热门事件（'.$this->rangeLabel().'）')
            ->records(function (): Collection {
                $rows = $this->miniappAnalytics()->topEvents(
                    MiniappAnalyticsEventCatalog::PAGE_HOME,
                    $this->now(),
                    $this->analyticsDays(),
                );

                return collect($rows)->values()->map(fn (array $row, int $i): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'home-top-'.$i.'-'.($row['event_name'] ?? ''),
                ]);
            })
            ->columns([
                TextColumn::make('label')->label('事件'),
                TextColumn::make('count')->label('次数')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
