<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Support\AdminActionLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => '请填写密码。',
            ]);
        }

        $role = (string) ($data['role'] ?? '');
        if (! in_array($role, ['viewer', 'operator', 'super_admin'], true)) {
            throw ValidationException::withMessages([
                'role' => '后台账号角色仅支持：只读审核员、运营人员、超级管理员。',
            ]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->getRecord()->ensureProfile();

        AdminActionLogger::record('admin_user.created', $this->getRecord(), [
            'email' => $this->getRecord()->email,
            'role' => $this->getRecord()->role,
        ]);
    }
}
