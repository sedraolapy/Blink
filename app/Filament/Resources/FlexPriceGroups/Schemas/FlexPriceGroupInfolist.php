<?php

namespace App\Filament\Resources\FlexPriceGroups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FlexPriceGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('governorate.name')
                    ->label(__('filament/admin/flex_price_group_resource.governorate')),

                TextEntry::make('billboards_count')
                    ->label(__('filament/admin/flex_price_group_resource.billboards_count'))
                    ->numeric(),

                TextEntry::make('local_price')
                    ->label(__('filament/admin/flex_price_group_resource.local_price'))
                    ->numeric(decimalPlaces: 2),

                TextEntry::make('foreign_price')
                    ->label(__('filament/admin/flex_price_group_resource.foreign_price'))
                    ->numeric(decimalPlaces: 2),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/flex_price_group_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/flex_price_group_resource.updated_at'))
                    ->dateTime(),
            ]);
    }
}