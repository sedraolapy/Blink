<?php

namespace App\Filament\Resources\FlexPriceGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FlexPriceGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('governorate_id')
                    ->label(
                        __('filament/admin/flex_price_group_resource.governorate')
                    )
                    ->relationship('governorate', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('billboards_count')
                    ->label(
                        __('filament/admin/flex_price_group_resource.billboards_count')
                    )
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(1),

                TextInput::make('local_price')
                    ->label(
                        __('filament/admin/flex_price_group_resource.local_price')
                    )
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('foreign_price')
                    ->label(
                        __('filament/admin/flex_price_group_resource.foreign_price')
                    )
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
}