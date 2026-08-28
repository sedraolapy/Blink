<?php

namespace App\Filament\Resources\LedScreens\Pages;

use App\Filament\Resources\LedNetworks\LedNetworkResource;
use App\Filament\Resources\LedScreens\LedScreenResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLedScreens extends ListRecords
{
    protected static string $resource = LedScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_networks')
                ->label(__('filament/admin/led_screen_resource.manage_networks'))
                ->icon('heroicon-o-rectangle-group')
                ->url(LedNetworkResource::getUrl('index')),

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
