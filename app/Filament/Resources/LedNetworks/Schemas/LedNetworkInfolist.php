<?php

namespace App\Filament\Resources\LedNetworks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LedNetworkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('location_name')
                    ->label(__('filament/admin/led_network_resource.location_name')),

                TextEntry::make('local_price')
                    ->label(__('filament/admin/led_network_resource.local_price'))
                    ->numeric(),

                TextEntry::make('foreign_price')
                    ->label(__('filament/admin/led_network_resource.foreign_price'))
                    ->numeric(),

                TextEntry::make('screens_count')
                    ->label(__('filament/admin/led_network_resource.screens_count'))
                    ->state(
                        fn ($record) => $record->screens()->count()
                    ),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/led_network_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/led_network_resource.updated_at'))
                    ->dateTime(),
            ]);
    }
}