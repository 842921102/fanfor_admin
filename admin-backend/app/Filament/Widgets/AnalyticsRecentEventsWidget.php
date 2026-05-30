<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsRecentEventsWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('最近事件快照')
            ->description('完整明细请前往「事件明细」菜单筛选查询。')
            ->records(function (): Collection {
                $rows = $this->miniappAnalytics()->recentEvents($this->now(), 30);

                return collect($rows)->values()->map(fn (array $row): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'recent-'.($row['id'] ?? 0),
                ]);
            })
            ->columns([
                TextColumn::make('created_at')->label('时间'),
                TextColumn::make('page_label')->label('页面'),
                TextColumn::make('category_label')->label('类型'),
                TextColumn::make('label')->label('事件'),
                TextColumn::make('event_value')->label('值')->placeholder('—')->limit(24),
                TextColumn::make('user_id')->label('用户 ID')->placeholder('游客')->alignEnd(),
                TextColumn::make('client_session_id')->label('会话')->placeholder('—')->limit(12)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
