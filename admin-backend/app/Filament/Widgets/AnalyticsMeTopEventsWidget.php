<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsMeTopEventsWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('个人中心链路热门事件（'.$this->rangeLabel().'）')
            ->records(function (): Collection {
                $rows = $this->miniappAnalytics()->topEvents(
                    null,
                    $this->now(),
                    $this->analyticsDays(),
                    15,
                );
                $filtered = array_values(array_filter(
                    $rows,
                    fn (array $row): bool => str_starts_with((string) ($row['event_name'] ?? ''), 'me')
                        || str_starts_with((string) ($row['event_name'] ?? ''), 'me_'),
                ));

                return collect($filtered)->values()->map(fn (array $row, int $i): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'me-top-'.$i.'-'.($row['event_name'] ?? ''),
                ]);
            })
            ->columns([
                TextColumn::make('label')->label('事件'),
                TextColumn::make('count')->label('次数')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
