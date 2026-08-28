<?php

namespace App\Filament\Resources\ExternalAssets;

use App\Filament\Resources\ExternalAssets\Pages\CreateExternalAsset;
use App\Filament\Resources\ExternalAssets\Pages\EditExternalAsset;
use App\Filament\Resources\ExternalAssets\Pages\ListExternalAssets;
use App\Filament\Resources\ExternalAssets\Pages\ViewExternalAsset;
use App\Filament\Resources\ExternalAssets\Schemas\ExternalAssetForm;
use App\Filament\Resources\ExternalAssets\Schemas\ExternalAssetInfolist;
use App\Filament\Resources\ExternalAssets\Tables\ExternalAssetsTable;
use App\Models\ExternalAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExternalAssetResource extends Resource
{
    protected static ?string $model = ExternalAsset::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/external_asset_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/external_asset_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/external_asset_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalAssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExternalAssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalAssetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalAssets::route('/'),
            'create' => CreateExternalAsset::route('/create'),
            'view' => ViewExternalAsset::route('/{record}'),
            'edit' => EditExternalAsset::route('/{record}/edit'),
        ];
    }
}