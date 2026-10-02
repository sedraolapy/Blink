<?php

namespace App\Filament\Resources\FlexPriceGroups\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlexPriceGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('governorate.name')
                    ->label(
                        __('filament/admin/flex_price_group_resource.governorate')
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('billboards_count')
                    ->label(
                        __('filament/admin/flex_price_group_resource.billboards_count')
                    )
                    ->numeric()
                    ->sortable(),

                TextColumn::make('local_price')
                    ->label(
                        __('filament/admin/flex_price_group_resource.local_price')
                    )
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('foreign_price')
                    ->label(
                        __('filament/admin/flex_price_group_resource.foreign_price')
                    )
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(
                        __('filament/admin/flex_price_group_resource.created_at')
                    )
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(
                        __('filament/admin/flex_price_group_resource.updated_at')
                    )
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}