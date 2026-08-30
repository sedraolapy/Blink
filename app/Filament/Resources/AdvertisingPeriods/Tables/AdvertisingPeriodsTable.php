<?php

namespace App\Filament\Resources\AdvertisingPeriods\Tables;

use App\Enums\DisplayGroupEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvertisingPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('filament/admin/advertising_period_resource.number'))
                    ->formatStateUsing(
                        fn ($state): string =>
                            __('filament/admin/advertising_period_resource.period') . ' ' . $state
                    )
                    ->badge()
                    ->sortable(),

                    TextColumn::make('months')
                    ->label(__('filament/admin/advertising_period_resource.months'))
                    ->state(fn ($record): string => match ($record->number) {
                        1, 2 => __('filament/admin/advertising_period_resource.month_names.jan'),
                        3 => __('filament/admin/advertising_period_resource.month_names.jan')
                            . ' + ' .
                            __('filament/admin/advertising_period_resource.month_names.feb'),

                        4 => __('filament/admin/advertising_period_resource.month_names.feb'),
                        5 => __('filament/admin/advertising_period_resource.month_names.feb')
                            . ' + ' .
                            __('filament/admin/advertising_period_resource.month_names.mar'),

                        6 => __('filament/admin/advertising_period_resource.month_names.mar'),
                        7 => __('filament/admin/advertising_period_resource.month_names.mar')
                            . ' + ' .
                            __('filament/admin/advertising_period_resource.month_names.apr'),

                        8, 9 => __('filament/admin/advertising_period_resource.month_names.apr'),

                        10, 11 => __('filament/admin/advertising_period_resource.month_names.may'),

                        12, 13 => __('filament/admin/advertising_period_resource.month_names.jun'),

                        14, 15 => __('filament/admin/advertising_period_resource.month_names.jul'),
                        16 => __('filament/admin/advertising_period_resource.month_names.jul')
                            . ' + ' .
                            __('filament/admin/advertising_period_resource.month_names.aug'),

                        17, 18 => __('filament/admin/advertising_period_resource.month_names.aug'),

                        19, 20 => __('filament/admin/advertising_period_resource.month_names.sep'),

                        21, 22 => __('filament/admin/advertising_period_resource.month_names.oct'),

                        23, 24 => __('filament/admin/advertising_period_resource.month_names.nov'),

                        25, 26 => __('filament/admin/advertising_period_resource.month_names.dec'),

                        default => '-',
                    }),

                TextColumn::make('damascus_daraa_sweida_start')
                    ->label(DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->label())
                    ->state(function ($record): string {
                        $range = $record->ranges->firstWhere(
                            'display_group',
                            DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA
                        );

                        if (! $range) {
                            return '-';
                        }

                        return sprintf(
                            '%02d/%02d',
                            $range->start_day,
                            $range->start_month
                        );
                    }),

                TextColumn::make('others_start')
                    ->label(DisplayGroupEnum::OTHERS->label())
                    ->state(function ($record): string {
                        $range = $record->ranges->firstWhere(
                            'display_group',
                            DisplayGroupEnum::OTHERS
                        );

                        if (! $range) {
                            return '-';
                        }

                        return sprintf(
                            '%02d/%02d',
                            $range->start_day,
                            $range->start_month
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}