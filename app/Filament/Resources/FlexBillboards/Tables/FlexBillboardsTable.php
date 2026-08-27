<?php

namespace App\Filament\Resources\FlexBillboards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FlexBillboardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('filament/admin/flex_billboard_resource.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location_name')
                    ->label(__('filament/admin/flex_billboard_resource.location_name'))
                    ->limit(35)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.name')
                    ->label(__('filament/admin/flex_billboard_resource.area.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.governorate.name')
                    ->label(__('filament/admin/flex_billboard_resource.area.governorate.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('width')
                    ->label(__('filament/admin/flex_billboard_resource.width')),

                TextColumn::make('height')
                    ->label(__('filament/admin/flex_billboard_resource.height')),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/flex_billboard_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/flex_billboard_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('governorate')
                    ->label(__('filament/admin/flex_billboard_resource.governorate'))
                    ->relationship('area.governorate', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('area_id')
                    ->label(__('filament/admin/flex_billboard_resource.area'))
                    ->relationship('area', 'name')
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