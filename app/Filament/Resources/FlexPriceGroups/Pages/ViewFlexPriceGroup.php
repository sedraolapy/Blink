<?php

namespace App\Filament\Resources\FlexPriceGroups\Pages;

use App\Filament\Resources\FlexPriceGroups\FlexPriceGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFlexPriceGroup extends ViewRecord
{
    protected static string $resource = FlexPriceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
