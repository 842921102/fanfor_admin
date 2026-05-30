<?php

namespace App\Filament\Resources\MiniappAnalyticsEvents\Pages;

use App\Filament\Resources\MiniappAnalyticsEvents\MiniappAnalyticsEventResource;
use Filament\Resources\Pages\ListRecords;

class ListMiniappAnalyticsEvents extends ListRecords
{
    protected static string $resource = MiniappAnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
