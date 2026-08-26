<?php

namespace App\Filament\Resources\AdvertisingPeriods\RelationManagers;

use App\Enums\DisplayGroupEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class RangesRelationManager extends RelationManager
{
    protected static string $relationship = 'ranges';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('filament/admin/advertising_period_resource.ranges');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('display_group')
                    ->label(__('filament/admin/advertising_period_resource.display_group'))
                    ->options(DisplayGroupEnum::options())
                    ->required()
                    ->rule(function (?object $record) {
                        return Rule::unique(
                            'advertising_period_ranges',
                            'display_group'
                        )
                            ->where(
                                'advertising_period_id',
                                $this->getOwnerRecord()->id
                            )
                            ->ignore($record?->id);
                    }),

                TextInput::make('start_month')
                    ->label(__('filament/admin/advertising_period_resource.start_month'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(12),

                TextInput::make('start_day')
                    ->label(__('filament/admin/advertising_period_resource.start_day'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(31),

                TextInput::make('end_month')
                    ->label(__('filament/admin/advertising_period_resource.end_month'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(12),

                TextInput::make('end_day')
                    ->label(__('filament/admin/advertising_period_resource.end_day'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(31),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_group')
                    ->label(__('filament/admin/advertising_period_resource.display_group'))
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof DisplayGroupEnum
                                ? $state->label()
                                : (DisplayGroupEnum::tryFrom($state)?->label() ?? $state)
                    ),

                TextColumn::make('start_date')
                    ->label(__('filament/admin/advertising_period_resource.start_date'))
                    ->state(
                        fn ($record): string => sprintf(
                            '%02d/%02d',
                            $record->start_day,
                            $record->start_month
                        )
                    ),

                TextColumn::make('end_date')
                    ->label(__('filament/admin/advertising_period_resource.end_date'))
                    ->state(
                        fn ($record): string => sprintf(
                            '%02d/%02d',
                            $record->end_day,
                            $record->end_month
                        )
                    ),
            ])

            ->headerActions([
                CreateAction::make()
                    ->label(
                        __('filament/admin/advertising_period_resource.create_range')
                    )
                    ->modalHeading(
                        __('filament/admin/advertising_period_resource.create_range')
                    ),
            ])

            ->recordActions([
                EditAction::make()
                    ->label(
                        __('filament/admin/advertising_period_resource.edit_range')
                    )
                    ->modalHeading(
                        __('filament/admin/advertising_period_resource.edit_range')
                    ),

                DeleteAction::make()
                    ->label(
                        __('filament/admin/advertising_period_resource.delete_range')
                    )
                    ->modalHeading(
                        __('filament/admin/advertising_period_resource.delete_range')
                    ),
            ]);
    }
}