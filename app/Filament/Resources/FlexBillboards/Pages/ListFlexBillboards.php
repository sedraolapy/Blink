<?php

namespace App\Filament\Resources\FlexBillboards\Pages;

use App\Filament\Resources\FlexBillboards\FlexBillboardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFlexBillboards extends ListRecords
{
    protected static string $resource = FlexBillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_flex_billboards.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_flex_billboards.title');
    }

}
