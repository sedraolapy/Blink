<?php

namespace App\Filament\Resources\FlexPriceGroups\Pages;

use App\Filament\Resources\FlexPriceGroups\FlexPriceGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFlexPriceGroup extends EditRecord
{
    protected static string $resource = FlexPriceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
