<?php

namespace App\Services\Booking\ElectronicBooking\BookingOptions;

use App\Models\Governorate;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use Illuminate\Database\Eloquent\Builder;

class ElectronicBookingOptionsService
{
    public function assets(array $filters)
    {
        $governorateId = (int) $filters['governorate_id'];
        $search = $filters['search'] ?? null;

        $governorate = Governorate::query()
            ->findOrFail($governorateId);

        $screens = LedScreen::query()
            ->whereNull('network_id')
            ->whereHas(
                'area',
                fn (Builder $query) =>
                    $query->where(
                        'governorate_id',
                        $governorateId
                    )
            )
            ->when(
                $search,
                fn (Builder $query) =>
                    $this->applyScreenSearch(
                        $query,
                        $search
                    )
            )
            ->with('area')
            ->orderBy('id')
            ->get();

        $networks = LedNetwork::query()
            ->whereHas(
                'screens.area',
                fn (Builder $query) =>
                    $query->where(
                        'governorate_id',
                        $governorateId
                    )
            )
            ->when(
                $search,
                function (Builder $query) use ($search) {
                    $query->where(
                        function (Builder $query) use ($search) {
                            $query
                                ->where(
                                    'location_name->ar',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'location_name->en',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'screens',
                                    fn (Builder $query) =>
                                        $this->applyScreenSearch(
                                            $query,
                                            $search
                                        )
                                );
                        }
                    );
                }
            )
            ->with([
                'screens' => function ($query) use ($governorateId) {
                    $query
                        ->whereHas(
                            'area',
                            fn (Builder $query) =>
                                $query->where(
                                    'governorate_id',
                                    $governorateId
                                )
                        )
                        ->with('area')
                        ->orderBy('id');
                },
            ])
            ->orderBy('id')
            ->get();

        return [
            'governorate' => $governorate,
            'screens' => $screens,
            'networks' => $networks,
        ];
    }

    private function applyScreenSearch(Builder $query,string $search)
    {
        return $query->where(
            function (Builder $query) use ($search) {
                $query
                    ->where(
                        'code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'location_name->ar',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'location_name->en',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'area',
                        function (Builder $query) use ($search) {
                            $query
                                ->where(
                                    'name->ar',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'name->en',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            }
        );
    }
}