<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/area_resource.arabic'))
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label(__('filament/admin/area_resource.name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/area_resource.english'))
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('filament/admin/area_resource.name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                Select::make('governorate_id')
                    ->label(__('filament/admin/area_resource.governorate_id'))
                    ->relationship('governorate', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
