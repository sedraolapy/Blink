<?php

namespace App\Filament\Resources\LedNetworks\Pages;

use App\Filament\Resources\LedNetworks\LedNetworkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLedNetwork extends EditRecord
{
    protected static string $resource = LedNetworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
