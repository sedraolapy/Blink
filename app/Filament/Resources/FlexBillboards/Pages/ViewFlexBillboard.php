<?php

namespace App\Filament\Resources\FlexBillboards\Pages;

use App\Filament\Resources\FlexBillboards\FlexBillboardResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFlexBillboard extends ViewRecord
{
    protected static string $resource = FlexBillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/view_flex_billboard.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/view_flex_billboard.title');
    }

}
