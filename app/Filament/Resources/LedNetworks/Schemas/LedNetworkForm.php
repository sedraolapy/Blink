<?php

namespace App\Filament\Resources\LedNetworks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class LedNetworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('location_name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/led_network_resource.arabic'))
                            ->schema([
                                TextInput::make('location_name.ar')
                                    ->label(__('filament/admin/led_network_resource.location_name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/led_network_resource.english'))
                            ->schema([
                                TextInput::make('location_name.en')
                                    ->label(__('filament/admin/led_network_resource.location_name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                TextInput::make('local_price')
                    ->label(__('filament/admin/led_network_resource.local_price'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('foreign_price')
                    ->label(__('filament/admin/led_network_resource.foreign_price'))
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
}