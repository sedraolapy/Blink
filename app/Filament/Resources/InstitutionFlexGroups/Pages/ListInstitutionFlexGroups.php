<?php

namespace App\Filament\Resources\InstitutionFlexGroups\Pages;

use App\Filament\Resources\InstitutionFlexGroups\InstitutionFlexGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionFlexGroups extends ListRecords
{
    protected static string $resource = InstitutionFlexGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
