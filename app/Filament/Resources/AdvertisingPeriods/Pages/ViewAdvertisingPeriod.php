<?php

namespace App\Filament\Resources\AdvertisingPeriods\Pages;

use App\Filament\Resources\AdvertisingPeriods\AdvertisingPeriodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdvertisingPeriod extends ViewRecord
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
        return __('filament/admin/view_advertising_period.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/view_advertising_period.title');
    }

}
