<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsPageBreakdownWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('页面事件分布（'.$this->rangeLabel().'）')
            ->records(function (): Collection {
                $rows = $this->miniappAnalytics()->eventsByPage($this->now(), $this->analyticsDays());

                return collect($rows)->values()->map(fn (array $row, int $i): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'page-breakdown-'.$i,
                ]);
            })
            ->columns([
                TextColumn::make('page_label')->label('页面'),
                TextColumn::make('count')->label('事件数')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
