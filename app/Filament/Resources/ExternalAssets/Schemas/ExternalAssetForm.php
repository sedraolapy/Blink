<?php

namespace App\Filament\Resources\ExternalAssets\Schemas;

use App\Enums\ExternalAssetTypeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ExternalAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('filament/admin/external_asset_resource.code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                Select::make('type')
                    ->label(__('filament/admin/external_asset_resource.type'))
                    ->options(ExternalAssetTypeEnum::options())
                    ->required()
                    ->native(false),

                Tabs::make('location_name_translations')
                    ->tabs([
                        Tab::make(__('filament/admin/external_asset_resource.arabic'))
                            ->schema([
                                TextInput::make('location_name.ar')
                                    ->label(__('filament/admin/external_asset_resource.location_name.ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Tab::make(__('filament/admin/external_asset_resource.english'))
                            ->schema([
                                TextInput::make('location_name.en')
                                    ->label(__('filament/admin/external_asset_resource.location_name.en'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                Select::make('area_id')
                    ->label(__('filament/admin/external_asset_resource.area_id'))
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('latitude')
                    ->label(__('filament/admin/external_asset_resource.latitude'))
                    ->numeric()
                    ->required()
                    ->minValue(-90)
                    ->maxValue(90),

                TextInput::make('longitude')
                    ->label(__('filament/admin/external_asset_resource.longitude'))
                    ->numeric()
                    ->required()
                    ->minValue(-180)
                    ->maxValue(180),

                TextInput::make('width')
                    ->label(__('filament/admin/external_asset_resource.width'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('height')
                    ->label(__('filament/admin/external_asset_resource.height'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('local_price')
                    ->label(__('filament/admin/external_asset_resource.local_price'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('foreign_price')
                    ->label(__('filament/admin/external_asset_resource.foreign_price'))
                    ->numeric()
                    ->required()
                    ->minValue(0),

                SpatieMediaLibraryFileUpload::make('external_asset_video')
                    ->label(__('filament/admin/external_asset_resource.video'))
                    ->collection('external_asset_video')
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