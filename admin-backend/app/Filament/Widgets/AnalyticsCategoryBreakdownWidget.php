<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsCategoryBreakdownWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('事件类型分布（'.$this->rangeLabel().'）')
            ->records(function (): Collection {
                $rows = $this->miniappAnalytics()->eventsByCategory($this->now(), $this->analyticsDays());

                return collect($rows)->values()->map(fn (array $row, int $i): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'cat-breakdown-'.$i,
                ]);
            })
            ->columns([
                TextColumn::make('category_label')->label('类型'),
                TextColumn::make('count')->label('事件数')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
