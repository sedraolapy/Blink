<?php

namespace App\Filament\Resources\FlexBillboards\Pages;

use App\Filament\Resources\FlexBillboards\FlexBillboardResource;
use App\Filament\Resources\FlexPriceGroups\FlexPriceGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListFlexBillboards extends ListRecords
{
    protected static string $resource = FlexBillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('manage_price_groups')
                ->label(
                    __('filament/admin/flex_price_group_resource.manage_price_groups')
                )
                ->icon('heroicon-o-banknotes')
                ->url(
                    FlexPriceGroupResource::getUrl('index')
                ),
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
