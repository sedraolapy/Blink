<?php

namespace App\Filament\Resources\AdvertisingPeriods;

use App\Filament\Resources\AdvertisingPeriods\Pages\CreateAdvertisingPeriod;
use App\Filament\Resources\AdvertisingPeriods\Pages\EditAdvertisingPeriod;
use App\Filament\Resources\AdvertisingPeriods\Pages\ListAdvertisingPeriods;
use App\Filament\Resources\AdvertisingPeriods\Pages\ViewAdvertisingPeriod;
use App\Filament\Resources\AdvertisingPeriods\Schemas\AdvertisingPeriodForm;
use App\Filament\Resources\AdvertisingPeriods\Schemas\AdvertisingPeriodInfolist;
use App\Filament\Resources\AdvertisingPeriods\Tables\AdvertisingPeriodsTable;
use App\Models\AdvertisingPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvertisingPeriodResource extends Resource
{
    protected static ?string $model = AdvertisingPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Schema $schema): Schema
    {
        return AdvertisingPeriodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdvertisingPeriodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvertisingPeriodsTable::configure($table);
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
            'index' => ListAdvertisingPeriods::route('/'),
            'create' => CreateAdvertisingPeriod::route('/create'),
            'view' => ViewAdvertisingPeriod::route('/{record}'),
            'edit' => EditAdvertisingPeriod::route('/{record}/edit'),
        ];
    }
}
