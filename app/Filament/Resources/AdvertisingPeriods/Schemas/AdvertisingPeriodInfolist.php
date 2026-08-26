<?php

namespace App\Filament\Resources\AdvertisingPeriods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdvertisingPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('number')
                    ->label(__('filament/admin/advertising_period_resource.number'))
                    ->formatStateUsing(
                        fn ($state): string =>
                            __('filament/admin/advertising_period_resource.period') . ' ' . $state
                    )
                    ->badge()
                    ->columnSpanFull(),
            ]);
    }
}