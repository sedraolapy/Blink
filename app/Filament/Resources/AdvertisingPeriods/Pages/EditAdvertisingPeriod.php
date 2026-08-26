<?php

namespace App\Filament\Resources\AdvertisingPeriods\Pages;

use App\Filament\Resources\AdvertisingPeriods\AdvertisingPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisingPeriod extends EditRecord
{
    protected static string $resource = AdvertisingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_advertising_period.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_advertising_period.title');
    }

}
