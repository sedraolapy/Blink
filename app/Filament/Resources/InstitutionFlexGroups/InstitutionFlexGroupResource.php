<?php

namespace App\Filament\Resources\InstitutionFlexGroups;

use App\Filament\Resources\InstitutionFlexGroups\Pages\CreateInstitutionFlexGroup;
use App\Filament\Resources\InstitutionFlexGroups\Pages\EditInstitutionFlexGroup;
use App\Filament\Resources\InstitutionFlexGroups\Pages\ListInstitutionFlexGroups;
use App\Filament\Resources\InstitutionFlexGroups\Pages\ViewInstitutionFlexGroup;
use App\Filament\Resources\InstitutionFlexGroups\Schemas\InstitutionFlexGroupForm;
use App\Filament\Resources\InstitutionFlexGroups\Schemas\InstitutionFlexGroupInfolist;
use App\Filament\Resources\InstitutionFlexGroups\Tables\InstitutionFlexGroupsTable;
use App\Models\InstitutionFlexGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstitutionFlexGroupResource extends Resource
{
    protected static ?string $model = InstitutionFlexGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.advertising_assets');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/institution_flex_group_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/institution_flex_group_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/institution_flex_group_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return InstitutionFlexGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstitutionFlexGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionFlexGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstitutionFlexGroups::route('/'),
            'create' => CreateInstitutionFlexGroup::route('/create'),
            'view' => ViewInstitutionFlexGroup::route('/{record}'),
            'edit' => EditInstitutionFlexGroup::route('/{record}/edit'),
        ];
    }
}
