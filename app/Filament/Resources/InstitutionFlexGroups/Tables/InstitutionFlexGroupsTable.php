<?php

namespace App\Filament\Resources\InstitutionFlexGroups\Tables;

use App\Models\InstitutionFlexGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstitutionFlexGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/institution_flex_group_resource.name'))
                    ->getStateUsing(fn ($record): string =>
                        $record->getTranslation('name', app()->getLocale())
                    )
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(
                            'name->' . app()->getLocale(),
                            'like',
                            "%{$search}%"
                        );
                    }),

                TextColumn::make('billboards_count')
                    ->label(__('filament/admin/institution_flex_group_resource.billboards_count'))
                    ->counts('billboards')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/institution_flex_group_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/institution_flex_group_resource.updated_at'))
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
                DeleteAction::make()
                    ->disabled(
                        fn (InstitutionFlexGroup $record): bool =>
                            $record->billboards()->exists()
                    )
                    ->tooltip(
                        fn (InstitutionFlexGroup $record): ?string =>
                            $record->billboards()->exists()
                                ? __('filament/admin/institution_flex_group_resource.cannot_delete')
                                : null
                    )
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
