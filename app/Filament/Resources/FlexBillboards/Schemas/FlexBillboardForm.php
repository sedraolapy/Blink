<?php

namespace App\Filament\Resources\FlexBillboards\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
class FlexBillboardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('location_name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/flex_billboard_resource.arabic'))
                            ->schema([
                                TextInput::make('location_name.ar')
                                    ->label(__('filament/admin/flex_billboard_resource.location_name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/flex_billboard_resource.english'))
                            ->schema([
                                TextInput::make('location_name.en')
                                    ->label(__('filament/admin/flex_billboard_resource.location_name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label(__('filament/admin/flex_billboard_resource.code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('area_id')
                    ->label(__('filament/admin/flex_billboard_resource.area_id'))
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('latitude')
                    ->label(__('filament/admin/flex_billboard_resource.latitude'))
                    ->numeric()
                    ->required()
                    ->minValue(-90)
                    ->maxValue(90),

                TextInput::make('longitude')
                    ->label(__('filament/admin/flex_billboard_resource.longitude'))
                    ->numeric()
                    ->required()
                    ->minValue(-180)
                    ->maxValue(180),

                TextInput::make('width')
                    ->label(__('filament/admin/flex_billboard_resource.width'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('height')
                    ->label(__('filament/admin/flex_billboard_resource.height'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                SpatieMediaLibraryFileUpload::make('flex_billboard_video')
                    ->label(__('filament/admin/flex_billboard_resource.video'))
                    ->collection('flex_billboard_video')
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