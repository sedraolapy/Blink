<?php

namespace App\Filament\Resources\LedScreens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LedScreenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('filament/admin/led_screen_resource.code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Tabs::make('location_name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/led_screen_resource.arabic'))
                            ->schema([
                                TextInput::make('location_name.ar')
                                    ->label(__('filament/admin/led_screen_resource.location_name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/led_screen_resource.english'))
                            ->schema([
                                TextInput::make('location_name.en')
                                    ->label(__('filament/admin/led_screen_resource.location_name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                Select::make('area_id')
                    ->label(__('filament/admin/led_screen_resource.area_id'))
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                    Select::make('network_id')
                    ->label(__('filament/admin/led_screen_resource.network_id'))
                    ->relationship('network', 'location_name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live()

                    ->createOptionForm([
                        Tabs::make('network_location_name_translations')
                            ->tabs([
                                Tab::make(__('filament/admin/led_screen_resource.arabic'))
                                    ->schema([
                                        TextInput::make('location_name.ar')
                                            ->label(__('filament/admin/led_screen_resource.location_name.ar'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Tab::make(__('filament/admin/led_screen_resource.english'))
                                    ->schema([
                                        TextInput::make('location_name.en')
                                            ->label(__('filament/admin/led_screen_resource.location_name.en'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        TextInput::make('local_price')
                            ->label(__('filament/admin/led_screen_resource.local_price'))
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        TextInput::make('foreign_price')
                            ->label(__('filament/admin/led_screen_resource.foreign_price'))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                ]),
                TextInput::make('latitude')
                    ->label(__('filament/admin/led_screen_resource.latitude'))
                    ->numeric()
                    ->required()
                    ->minValue(-90)
                    ->maxValue(90),

                TextInput::make('longitude')
                    ->label(__('filament/admin/led_screen_resource.longitude'))
                    ->numeric()
                    ->required()
                    ->minValue(-180)
                    ->maxValue(180),

                TextInput::make('width')
                    ->label(__('filament/admin/led_screen_resource.width'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('height')
                    ->label(__('filament/admin/led_screen_resource.height'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('width_px')
                    ->label(__('filament/admin/led_screen_resource.width_px'))
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('height_px')
                    ->label(__('filament/admin/led_screen_resource.height_px'))
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('local_price')
                    ->label(__('filament/admin/led_screen_resource.local_price'))
                    ->numeric()
                    ->minValue(0)
                    ->required(
                        fn (Get $get): bool => blank($get('network_id'))
                    )
                    ->disabled(
                        fn (Get $get): bool => filled($get('network_id'))
                    )
                    ->dehydrated(
                        fn (Get $get): bool => blank($get('network_id'))
                    ),

                TextInput::make('foreign_price')
                    ->label(__('filament/admin/led_screen_resource.foreign_price'))
                    ->numeric()
                    ->minValue(0)
                    ->required(
                        fn (Get $get): bool => blank($get('network_id'))
                    )
                    ->disabled(
                        fn (Get $get): bool => filled($get('network_id'))
                    )
                    ->dehydrated(
                        fn (Get $get): bool => blank($get('network_id'))
                    ),

                SpatieMediaLibraryFileUpload::make('led_screen_video')
                    ->label(__('filament/admin/led_screen_resource.video'))
                    ->collection('led_screen_video')
                    ->acceptedFileTypes([
                        'video/mp4',
                        'video/quicktime',
                        'video/x-msvideo',
                    ])
                    ->maxFiles(1)
                    ->maxSize(102400),
            ]);
    }
}