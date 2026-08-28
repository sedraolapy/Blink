<?php

namespace App\Filament\Resources\LedNetworks\Pages;

use App\Filament\Resources\LedNetworks\LedNetworkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLedNetwork extends ViewRecord
{
    protected static string $resource = LedNetworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/view_led_network.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/view_led_network.title');
    }

}
