<?php

namespace App\Filament\Widgets;

use App\Models\Area;
use App\Models\Customer;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStats extends StatsOverviewWidget
{
    protected int | array | null $columns = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(
                __('filament/admin/dashboard.total_customers'),
                Customer::query()->count()
            )
                ->icon(Heroicon::OutlinedUserGroup)
                ->description(
                    __('filament/admin/dashboard.total_customers_description')
                )
                ->color('secondary')
                ->extraAttributes([
                    'style' => self::cardStyle('136, 1, 254'),
                ]),

            Stat::make(
                __('filament/admin/dashboard.new_customers_month'),
                Customer::query()
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count()
            )
                ->icon(Heroicon::OutlinedUserPlus)
                ->description(
                    __('filament/admin/dashboard.new_customers_description')
                )
                ->color('secondary')
                ->extraAttributes([
                    'style' => self::cardStyle('136, 1, 254'),
                ]),

            Stat::make(
                __('filament/admin/dashboard.total_users'),
                User::query()->count()
            )
                ->icon(Heroicon::OutlinedUsers)
                ->description(
                    __('filament/admin/dashboard.total_users_description')
                )
                ->color('secondary')
                ->extraAttributes([
                    'style' => self::cardStyle('136, 1, 254'),
                ]),

            Stat::make(
                __('filament/admin/dashboard.total_areas'),
                Area::query()->count()
            )
                ->icon(Heroicon::OutlinedMapPin)
                ->description(
                    __('filament/admin/dashboard.total_areas_description')
                )
                ->color('secondary')
                ->extraAttributes([
                    'style' => self::cardStyle('136, 1, 254'),
                ]),
        ];
    }

    private static function cardStyle(string $rgb): string
    {
        return "
            min-height: 130px;
            padding: 28px 32px;
            border: 1px solid rgba(136, 1, 254, 0.12);
            border-inline-start: 4px solid #8801FE;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.025);
        ";
    }
}
