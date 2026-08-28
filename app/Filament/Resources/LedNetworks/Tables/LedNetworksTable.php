<?php

namespace App\Filament\Resources\LedNetworks\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LedNetworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('location_name')
                    ->label(__('filament/admin/led_network_resource.location_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('local_price')
                    ->label(__('filament/admin/led_network_resource.local_price'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('foreign_price')
                    ->label(__('filament/admin/led_network_resource.foreign_price'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('screens_count')
                    ->label(__('filament/admin/led_network_resource.screens_count'))
                    ->counts('screens')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/led_network_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/led_network_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),

                DeleteAction::make()
                    ->visible(
                        fn ($record): bool => ! $record->screens()->exists()
                    ),
            ]);
    }
}