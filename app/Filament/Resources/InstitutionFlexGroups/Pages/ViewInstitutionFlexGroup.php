<?php

namespace App\Filament\Resources\InstitutionFlexGroups\Pages;

use App\Filament\Resources\InstitutionFlexGroups\InstitutionFlexGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInstitutionFlexGroup extends ViewRecord
{
    protected static string $resource = InstitutionFlexGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
