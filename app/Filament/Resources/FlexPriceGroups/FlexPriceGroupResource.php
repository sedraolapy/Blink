<?php

namespace App\Filament\Resources\FlexPriceGroups;

use App\Filament\Resources\FlexPriceGroups\Pages\CreateFlexPriceGroup;
use App\Filament\Resources\FlexPriceGroups\Pages\EditFlexPriceGroup;
use App\Filament\Resources\FlexPriceGroups\Pages\ListFlexPriceGroups;
use App\Filament\Resources\FlexPriceGroups\Pages\ViewFlexPriceGroup;
use App\Filament\Resources\FlexPriceGroups\Schemas\FlexPriceGroupForm;
use App\Filament\Resources\FlexPriceGroups\Schemas\FlexPriceGroupInfolist;
use App\Filament\Resources\FlexPriceGroups\Tables\FlexPriceGroupsTable;
use App\Models\FlexPriceGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FlexPriceGroupResource extends Resource
{
    protected static ?string $model = FlexPriceGroup::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/flex_price_group_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/flex_price_group_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/flex_price_group_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return FlexPriceGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlexPriceGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlexPriceGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlexPriceGroups::route('/'),
            'create' => CreateFlexPriceGroup::route('/create'),
            'view' => ViewFlexPriceGroup::route('/{record}'),
            'edit' => EditFlexPriceGroup::route('/{record}/edit'),
        ];
    }
}