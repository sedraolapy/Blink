<?php

namespace App\Filament\Resources\Governorates;

use App\Filament\Resources\Governorates\Pages\CreateGovernorate;
use App\Filament\Resources\Governorates\Pages\EditGovernorate;
use App\Filament\Resources\Governorates\Pages\ListGovernorates;
use App\Filament\Resources\Governorates\Pages\ViewGovernorate;
use App\Filament\Resources\Governorates\Schemas\GovernorateForm;
use App\Filament\Resources\Governorates\Schemas\GovernorateInfolist;
use App\Filament\Resources\Governorates\Tables\GovernoratesTable;
use App\Models\Governorate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GovernorateResource extends Resource
{
    protected static ?string $model = Governorate::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/governorate_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/governorate_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/governorate_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return GovernorateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GovernorateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GovernoratesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGovernorates::route('/'),
            'create' => CreateGovernorate::route('/create'),
            'view' => ViewGovernorate::route('/{record}'),
            'edit' => EditGovernorate::route('/{record}/edit'),
        ];
    }
}