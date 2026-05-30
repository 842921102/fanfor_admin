<?php

namespace App\Filament\Resources\MiniappAnalyticsEvents;

use App\Filament\Resources\MiniappAnalyticsEvents\Pages\ListMiniappAnalyticsEvents;
use App\Filament\Resources\MiniappAnalyticsEvents\Pages\ViewMiniappAnalyticsEvent;
use App\Models\MiniappAnalyticsEvent;
use App\Support\MiniappAnalyticsEventCatalog;
use BackedEnum;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MiniappAnalyticsEventResource extends Resource
{
    protected static ?string $model = MiniappAnalyticsEvent::class;

    protected static ?string $navigationLabel = '事件明细';

    protected static string|UnitEnum|null $navigationGroup = '数据分析';

    protected static ?string $modelLabel = '埋点事件';

    protected static ?string $pluralModelLabel = '埋点事件';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('事件信息')
                ->schema([
                    TextEntry::make('id')->label('编号'),
                    TextEntry::make('event_name')->label('事件名')->copyable(),
                    TextEntry::make('label_display')
                        ->label('中文名')
                        ->state(fn (MiniappAnalyticsEvent $record): string => MiniappAnalyticsEventCatalog::label($record->event_name)),
                    TextEntry::make('page')
                        ->label('页面')
                        ->formatStateUsing(fn (?string $state): string => MiniappAnalyticsEventCatalog::pageLabel((string) $state)),
                    TextEntry::make('category')
                        ->label('类型')
                        ->formatStateUsing(fn (?string $state): string => MiniappAnalyticsEventCatalog::categoryLabel((string) $state)),
                    TextEntry::make('event_value')->label('事件值')->placeholder('—'),
                    TextEntry::make('user_id')->label('用户 ID')->placeholder('游客'),
                    TextEntry::make('client_session_id')->label('客户端会话')->placeholder('—')->copyable(),
                    TextEntry::make('created_at')->label('发生时间')->dateTime(),
                ])
                ->columns(2),
            Section::make('扩展属性 (meta)')
                ->schema([
                    KeyValueEntry::make('meta')->label('')->columnSpanFull(),
                ])
                ->visible(fn (MiniappAnalyticsEvent $record): bool => is_array($record->meta) && $record->meta !== []),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->columns([
                TextColumn::make('id')->label('编号')->sortable()->copyable(),
                TextColumn::make('created_at')->label('时间')->dateTime()->sortable(),
                TextColumn::make('page')
                    ->label('页面')
                    ->formatStateUsing(fn (?string $state): string => MiniappAnalyticsEventCatalog::pageLabel((string) $state))
                    ->sortable(),
                TextColumn::make('category')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => MiniappAnalyticsEventCatalog::categoryLabel((string) $state))
                    ->color(fn (?string $state): string => match ($state) {
                        MiniappAnalyticsEventCatalog::CATEGORY_CONVERSION => 'success',
                        MiniappAnalyticsEventCatalog::CATEGORY_CLICK => 'info',
                        MiniappAnalyticsEventCatalog::CATEGORY_PAGE_VIEW => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('event_name')
                    ->label('事件')
                    ->formatStateUsing(fn (?string $state): string => MiniappAnalyticsEventCatalog::label((string) $state))
                    ->description(fn (MiniappAnalyticsEvent $record): string => $record->event_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $q->where('event_name', 'like', "%{$search}%")
                                ->orWhere('event_value', 'like', "%{$search}%");
                        });
                    })
                    ->wrap(),
                TextColumn::make('event_value')->label('值')->placeholder('—')->limit(20)->toggleable(),
                TextColumn::make('user_id')->label('用户')->placeholder('游客')->sortable(),
                TextColumn::make('client_session_id')->label('会话')->limit(10)->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('page')
                    ->label('页面')
                    ->options(collect(MiniappAnalyticsEventCatalog::pages())
                        ->mapWithKeys(fn (string $p): array => [$p => MiniappAnalyticsEventCatalog::pageLabel($p)])
                        ->all()),
                SelectFilter::make('category')
                    ->label('类型')
                    ->options([
                        MiniappAnalyticsEventCatalog::CATEGORY_PAGE_VIEW => '页面浏览',
                        MiniappAnalyticsEventCatalog::CATEGORY_CLICK => '点击导航',
                        MiniappAnalyticsEventCatalog::CATEGORY_ACTION => '用户操作',
                        MiniappAnalyticsEventCatalog::CATEGORY_CONVERSION => '转化',
                    ]),
                SelectFilter::make('event_name')
                    ->label('事件名')
                    ->options(collect(MiniappAnalyticsEventCatalog::catalogRows())
                        ->mapWithKeys(fn (array $row): array => [$row['event_name'] => $row['label'].' ('.$row['event_name'].')'])
                        ->all())
                    ->searchable(),
                Filter::make('logged_in')
                    ->label('仅已登录')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('user_id')),
                Filter::make('guest_only')
                    ->label('仅游客')
                    ->query(fn (Builder $query): Builder => $query->whereNull('user_id')),
            ])
            ->recordUrl(fn (MiniappAnalyticsEvent $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMiniappAnalyticsEvents::route('/'),
            'view' => ViewMiniappAnalyticsEvent::route('/{record}'),
        ];
    }
}
