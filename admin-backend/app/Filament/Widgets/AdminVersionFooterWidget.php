<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminVersionFooterWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 9999;

    protected string $view = 'filament.widgets.admin-version-footer';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'version' => (string) config('fanfor_release.version', 'dev'),
        ];
    }
}
