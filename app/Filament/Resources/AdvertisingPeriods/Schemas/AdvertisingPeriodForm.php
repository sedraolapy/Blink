<?php

namespace App\Filament\Resources\AdvertisingPeriods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdvertisingPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label(__('filament/admin/advertising_period_resource.number'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(26)
                    ->unique(ignoreRecord: true),
            ]);
    }
}