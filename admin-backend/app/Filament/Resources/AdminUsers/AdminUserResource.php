<?php

namespace App\Filament\Resources\AdminUsers;

use App\Filament\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Filament\Resources\AdminUsers\Pages\EditAdminUser;
use App\Filament\Resources\AdminUsers\Pages\ListAdminUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AdminUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = '后台账号管理';

    protected static string|UnitEnum|null $navigationGroup = '用户管理';

    protected static ?string $modelLabel = '后台账号';

    protected static ?string $pluralModelLabel = '后台账号';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema, true);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table, true);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminUsers::route('/'),
            'create' => CreateAdminUser::route('/create'),
            'edit' => EditAdminUser::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<User>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['viewer', 'operator', 'super_admin'])
            ->withCount('favorites')
            ->withCount('histories')
            ->with('profile');
    }
}
