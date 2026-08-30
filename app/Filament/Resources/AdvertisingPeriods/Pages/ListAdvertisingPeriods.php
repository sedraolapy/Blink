<?php

namespace App\Filament\Resources\AdvertisingPeriods\Pages;

use App\Filament\Resources\AdvertisingPeriods\AdvertisingPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdvertisingPeriods extends ListRecords
{
    protected static string $resource = AdvertisingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
           //
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_advertising_periods.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_advertising_periods.title');
    }

}
