<?php

namespace App\Filament\Resources\HelpChooseRecords;

use App\Filament\Resources\FeatureDataRecords\Schemas\FeatureDataRecordInfolist;
use App\Filament\Resources\FeatureDataRecords\Tables\FeatureDataRecordsTable;
use App\Filament\Resources\HelpChooseRecords\Pages\ListHelpChooseRecords;
use App\Models\FeatureDataRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class HelpChooseRecordResource extends Resource
{
    protected static ?string $model = FeatureDataRecord::class;

    protected static string|UnitEnum|null $navigationGroup = '数据管理';

    protected static ?string $navigationLabel = '帮忙选择';

    protected static ?string $modelLabel = '帮忙选择记录';

    protected static ?string $pluralModelLabel = '帮忙选择记录';

    protected static ?int $navigationSort = 22;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    public static function infolist(Schema $schema): Schema
    {
        return FeatureDataRecordInfolist::configureHelpChoose($schema);
    }

    public static function table(Table $table): Table
    {
        return FeatureDataRecordsTable::configureHelpChoose($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpChooseRecords::route('/'),
        ];
    }

    /**
     * @return Builder<FeatureDataRecord>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('feature_type', 'help_choose');
    }
}
