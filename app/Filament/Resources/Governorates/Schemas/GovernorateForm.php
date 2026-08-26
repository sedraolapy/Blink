<?php

namespace App\Filament\Resources\Governorates\Schemas;

use App\Enums\DisplayGroupEnum;
use App\Enums\InstallationDayEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class GovernorateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/governorate_resource.arabic'))
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label(__('filament/admin/governorate_resource.name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/governorate_resource.english'))
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('filament/admin/governorate_resource.name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                Select::make('display_group')
                    ->label(__('filament/admin/governorate_resource.display_group'))
                    ->options(DisplayGroupEnum::options())
                    ->required(),

                Select::make('installation_day')
                    ->label(__('filament/admin/governorate_resource.installation_day'))
                    ->options(InstallationDayEnum::options())
                    ->required(),
            ]);
    }
}