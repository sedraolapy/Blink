<?php

namespace App\Filament\Resources\InstitutionFlexGroups\Pages;

use App\Filament\Resources\InstitutionFlexGroups\InstitutionFlexGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionFlexGroup extends EditRecord
{
    protected static string $resource = InstitutionFlexGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->disabled(
                    fn (): bool =>
                        $this->getRecord()->billboards()->exists()
                )
                ->tooltip(
                    fn (): ?string =>
                        $this->getRecord()->billboards()->exists()
                            ? __('filament/admin/institution_flex_group_resource.cannot_delete')
                            : null
                )
        ];
    }
}
