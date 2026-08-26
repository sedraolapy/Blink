<?php

namespace App\Filament\Resources\Areas;

use App\Filament\Resources\Areas\Pages\CreateArea;
use App\Filament\Resources\Areas\Pages\EditArea;
use App\Filament\Resources\Areas\Pages\ListAreas;
use App\Filament\Resources\Areas\Pages\ViewArea;
use App\Filament\Resources\Areas\Schemas\AreaForm;
use App\Filament\Resources\Areas\Schemas\AreaInfolist;
use App\Filament\Resources\Areas\Tables\AreasTable;
use App\Models\Area;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/area_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/area_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/area_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return AreaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AreaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AreasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAreas::route('/'),
            'create' => CreateArea::route('/create'),
            'view' => ViewArea::route('/{record}'),
            'edit' => EditArea::route('/{record}/edit'),
        ];
    }
}