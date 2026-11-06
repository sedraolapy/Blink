<?php

namespace App\Filament\Resources\FlexBillboards\Pages;

use App\Filament\Resources\FlexBillboards\FlexBillboardResource;
use App\Filament\Resources\FlexPriceGroups\FlexPriceGroupResource;
use App\Filament\Resources\InstitutionFlexGroups\InstitutionFlexGroupResource;
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

            Action::make('manage_institutions')
                ->label(
                    __('filament/admin/institution_flex_group_resource.navigation_label')
                )
                ->icon('heroicon-o-building-office-2')
                ->url(
                    InstitutionFlexGroupResource::getUrl('index')
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
