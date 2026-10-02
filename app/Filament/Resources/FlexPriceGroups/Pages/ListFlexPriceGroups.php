<?php

namespace App\Filament\Resources\FlexPriceGroups\Pages;

use App\Filament\Resources\FlexPriceGroups\FlexPriceGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFlexPriceGroups extends ListRecords
{
    protected static string $resource = FlexPriceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
