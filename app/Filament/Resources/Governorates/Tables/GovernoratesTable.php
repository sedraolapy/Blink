<?php

namespace App\Filament\Resources\Governorates\Tables;

use App\Enums\DisplayGroupEnum;
use App\Enums\InstallationDayEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GovernoratesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/governorate_resource.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('display_group')
                    ->label(__('filament/admin/governorate_resource.display_group'))
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof DisplayGroupEnum
                                ? $state->label()
                                : (DisplayGroupEnum::tryFrom($state)?->label() ?? $state)
                    ),

                TextColumn::make('installation_day')
                    ->label(__('filament/admin/governorate_resource.installation_day'))
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof InstallationDayEnum
                                ? $state->label()
                                : (InstallationDayEnum::tryFrom($state)?->label() ?? $state)
                    ),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/governorate_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/governorate_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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