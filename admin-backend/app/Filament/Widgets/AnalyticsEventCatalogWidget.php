<?php

namespace App\Filament\Widgets;

use App\Support\MiniappAnalyticsEventCatalog;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsEventCatalogWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('埋点字典（已注册事件）')
            ->description('小程序端仅可上报白名单内事件；新增事件需同步更新 Catalog 与小程序常量。')
            ->records(fn (): Collection => collect(MiniappAnalyticsEventCatalog::catalogRows())
                ->values()
                ->map(fn (array $row, int $i): array => [
                    ...$row,
                    ArrayRecord::getKeyName() => 'catalog-'.$i.'-'.($row['event_name'] ?? ''),
                ]))
            ->columns([
                TextColumn::make('event_name')->label('事件名')->copyable()->searchable(),
                TextColumn::make('label')->label('中文名')->searchable(),
                TextColumn::make('page_label')->label('页面'),
                TextColumn::make('category_label')->label('类型'),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }
}
