<?php

namespace App\Filament\Resources\LedScreens\Pages;

use App\Filament\Resources\LedScreens\LedScreenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLedScreens extends ListRecords
{
    protected static string $resource = LedScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_led_screens.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_led_screens.title');
    }

}
