<?php

namespace App\Filament\Resources\FlexBillboards\Pages;

use App\Filament\Resources\FlexBillboards\FlexBillboardResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFlexBillboard extends EditRecord
{
    protected static string $resource = FlexBillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/edit_flex_billboard.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/edit_flex_billboard.title');
    }

}
