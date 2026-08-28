<?php

namespace App\Filament\Resources\LedScreens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LedScreensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('filament/admin/led_screen_resource.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location_name')
                    ->label(__('filament/admin/led_screen_resource.location_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.name')
                    ->label(__('filament/admin/led_screen_resource.area'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.governorate.name')
                    ->label(__('filament/admin/led_screen_resource.governorate'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('is_in_network')
                    ->label(__('filament/admin/led_screen_resource.is_in_network'))
                    ->state(fn ($record) => $record->network_id ? __('filament/admin/led_screen_resource.yes') : __('filament/admin/led_screen_resource.no'))
                    ->badge(),
                    
                TextColumn::make('width')
                    ->label(__('filament/admin/led_screen_resource.width')),

                TextColumn::make('height')
                    ->label(__('filament/admin/led_screen_resource.height')),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/led_screen_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/led_screen_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('area_id')
                    ->label(__('filament/admin/led_screen_resource.area'))
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('network_id')
                    ->label(__('filament/admin/led_screen_resource.network'))
                    ->relationship('network', 'location_name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}