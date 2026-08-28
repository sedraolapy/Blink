<?php

namespace App\Filament\Resources\LedNetworks;

use App\Filament\Resources\LedNetworks\Pages\CreateLedNetwork;
use App\Filament\Resources\LedNetworks\Pages\EditLedNetwork;
use App\Filament\Resources\LedNetworks\Pages\ListLedNetworks;
use App\Filament\Resources\LedNetworks\Pages\ViewLedNetwork;
use App\Filament\Resources\LedNetworks\Schemas\LedNetworkForm;
use App\Filament\Resources\LedNetworks\Schemas\LedNetworkInfolist;
use App\Filament\Resources\LedNetworks\Tables\LedNetworksTable;
use App\Models\LedNetwork;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LedNetworkResource extends Resource
{
    protected static ?string $model = LedNetwork::class;

    protected static ?string $recordTitleAttribute = 'location_name';

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('filament/admin/led_network_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/led_network_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return LedNetworkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LedNetworkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LedNetworksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLedNetworks::route('/'),
            'create' => CreateLedNetwork::route('/create'),
            'view' => ViewLedNetwork::route('/{record}'),
            'edit' => EditLedNetwork::route('/{record}/edit'),
        ];
    }
}