<?php

namespace App\Filament\Resources\LedScreens;

use App\Filament\Resources\LedScreens\Pages\CreateLedScreen;
use App\Filament\Resources\LedScreens\Pages\EditLedScreen;
use App\Filament\Resources\LedScreens\Pages\ListLedScreens;
use App\Filament\Resources\LedScreens\Pages\ViewLedScreen;
use App\Filament\Resources\LedScreens\Schemas\LedScreenForm;
use App\Filament\Resources\LedScreens\Schemas\LedScreenInfolist;
use App\Filament\Resources\LedScreens\Tables\LedScreensTable;
use App\Models\LedScreen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LedScreenResource extends Resource
{
    protected static ?string $model = LedScreen::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/led_screen_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/led_screen_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/led_screen_resource.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return LedScreenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LedScreenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LedScreensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLedScreens::route('/'),
            'create' => CreateLedScreen::route('/create'),
            'view' => ViewLedScreen::route('/{record}'),
            'edit' => EditLedScreen::route('/{record}/edit'),
        ];
    }
}