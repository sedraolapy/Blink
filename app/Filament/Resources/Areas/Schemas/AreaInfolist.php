<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AreaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('filament/admin/area_resource.name')),

                TextEntry::make('governorate.name')
                    ->label(__('filament/admin/area_resource.governorate')),

                TextEntry::make('created_at')
                    ->label(__('filament/admin/area_resource.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('filament/admin/area_resource.updated_at'))
                    ->dateTime(),
            ]);
    }
}