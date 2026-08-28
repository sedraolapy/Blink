<?php

namespace App\Filament\Resources\ExternalAssets\Tables;

use App\Enums\ExternalAssetTypeEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExternalAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('filament/admin/external_asset_resource.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('filament/admin/external_asset_resource.type'))
                    ->formatStateUsing(
                        fn ($state) => $state instanceof ExternalAssetTypeEnum
                            ? $state->label()
                            : ExternalAssetTypeEnum::from($state)->label()
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('location_name')
                    ->label(__('filament/admin/external_asset_resource.location_name'))
                    ->searchable()
                    ->limit(35)
                    ->sortable(),

                TextColumn::make('area.name')
                    ->label(__('filament/admin/external_asset_resource.area.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.governorate.name')
                    ->label(__('filament/admin/external_asset_resource.area.governorate.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('width')
                    ->label(__('filament/admin/external_asset_resource.width')),

                TextColumn::make('height')
                    ->label(__('filament/admin/external_asset_resource.height')),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/external_asset_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/external_asset_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('filament/admin/external_asset_resource.type'))
                    ->options(ExternalAssetTypeEnum::options()),

                SelectFilter::make('area_id')
                    ->label(__('filament/admin/external_asset_resource.area'))
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