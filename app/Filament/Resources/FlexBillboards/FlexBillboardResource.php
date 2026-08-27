<?php

namespace App\Filament\Resources\FlexBillboards;

use App\Filament\Resources\FlexBillboards\Pages\CreateFlexBillboard;
use App\Filament\Resources\FlexBillboards\Pages\EditFlexBillboard;
use App\Filament\Resources\FlexBillboards\Pages\ListFlexBillboards;
use App\Filament\Resources\FlexBillboards\Pages\ViewFlexBillboard;
use App\Filament\Resources\FlexBillboards\Schemas\FlexBillboardForm;
use App\Filament\Resources\FlexBillboards\Schemas\FlexBillboardInfolist;
use App\Filament\Resources\FlexBillboards\Tables\FlexBillboardsTable;
use App\Models\FlexBillboard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlexBillboardResource extends Resource
{
    protected static ?string $model = FlexBillboard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return FlexBillboardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlexBillboardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlexBillboardsTable::configure($table);
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
            'index' => ListFlexBillboards::route('/'),
            'create' => CreateFlexBillboard::route('/create'),
            'view' => ViewFlexBillboard::route('/{record}'),
            'edit' => EditFlexBillboard::route('/{record}/edit'),
        ];
    }
}
