<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Models\User;
use App\Support\AdminActionLogger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('删除')
                ->visible(fn (): bool => AdminUserResource::canDelete($this->getRecord()))
                ->before(function (): void {
                    $record = $this->getRecord();
                    if (! $record instanceof User) {
                        return;
                    }

                    AdminActionLogger::record('admin_user.deleted', $record, [
                        'email' => $record->email,
                        'role' => $record->role,
                    ]);
                }),
        ];
    }

    protected function afterSave(): void
    {
        AdminActionLogger::record('admin_user.updated', $this->getRecord(), [
            'role' => $this->getRecord()->role,
            'is_active' => $this->getRecord()->is_active,
        ]);
    }
}
