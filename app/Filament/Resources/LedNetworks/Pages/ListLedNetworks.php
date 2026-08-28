<?php

namespace App\Filament\Resources\LedNetworks\Pages;

use App\Filament\Resources\LedNetworks\LedNetworkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLedNetworks extends ListRecords
{
    protected static string $resource = LedNetworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
