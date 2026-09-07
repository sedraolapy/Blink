<?php

namespace App\Services\Outdoor;

use App\Enums\AssetAvailabilityStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\ExternalAssetTypeEnum;
use App\Models\ExternalAsset;
use Illuminate\Database\Eloquent\Builder;

class OutdoorService
{
    public function index(ExternalAssetTypeEnum $type,array $filters)
    {
        $today = now()->toDateString();

        $baseQuery = ExternalAsset::query()
            ->type($type)
            ->search($filters['search'] ?? null)
            ->governorate(
                isset($filters['governorate_id'])
                    ? (int) $filters['governorate_id']
                    : null
            );

        $summary = $this->getSummary(
            $baseQuery,
            $today
        );

        $query = (clone $baseQuery)
            ->with('area.governorate');

        $this->applyStatusFilter(
            $query,
            $filters['status'] ?? null,
            $today
        );

        $query->withExists([
            'bookingItems as has_booked_item' =>
                fn (Builder $query) =>
                    $this->applyItemBookingStatusConstraint(
                        $query,
                        $today,
                        BookingStatusEnum::CONFIRMED
                    ),

            'bookingItems as has_unconfirmed_item' =>
                fn (Builder $query) =>
                    $this->applyItemBookingStatusConstraint(
                        $query,
                        $today,
                        BookingStatusEnum::UNCONFIRMED
                    ),
        ]);

        $assets = $query
            ->orderBy('id')
            ->paginate(24);

        $assets->getCollection()->transform(
            function (ExternalAsset $asset) {
                $asset->availability_status = match (true) {
                    (bool) $asset->has_booked_item => AssetAvailabilityStatusEnum::BOOKED->value,
                    (bool) $asset->has_unconfirmed_item => AssetAvailabilityStatusEnum::UNCONFIRMED->value,
                    default => AssetAvailabilityStatusEnum::AVAILABLE->value,
                };

                return $asset;
            }
        );

        return [
            'summary' => $summary,
            'assets' => $assets,
        ];
    }

    private function getSummary(Builder $baseQuery,string $today)
    {
        $total = (clone $baseQuery)->count();

        $bookedQuery = clone $baseQuery;

        $this->applyBookingStatusFilter(
            $bookedQuery,
            $today,
            BookingStatusEnum::CONFIRMED
        );

        $booked = $bookedQuery->count();

        $unconfirmedQuery = clone $baseQuery;

        $this->applyBookingStatusFilter(
            $unconfirmedQuery,
            $today,
            BookingStatusEnum::UNCONFIRMED
        );

        $this->excludeBookingStatus(
            $unconfirmedQuery,
            $today,
            BookingStatusEnum::CONFIRMED
        );

        $unconfirmed = $unconfirmedQuery->count();

        return [
            'total' => $total,
            'booked' => $booked,
            'available' => $total - $booked - $unconfirmed,
            'unconfirmed' => $unconfirmed,
        ];
    }

    private function applyStatusFilter(Builder $query,?string $status,string $today)
    {
        match ($status) {
            AssetAvailabilityStatusEnum::BOOKED->value =>
                $this->applyBookingStatusFilter(
                    $query,
                    $today,
                    BookingStatusEnum::CONFIRMED
                ),

            AssetAvailabilityStatusEnum::UNCONFIRMED->value =>
                $this->applyUnconfirmedFilter(
                    $query,
                    $today
                ),

            AssetAvailabilityStatusEnum::AVAILABLE->value =>
                $this->applyAvailableFilter(
                    $query,
                    $today
                ),

            default => null,
        };
    }

    private function applyBookingStatusFilter(Builder $query,string $today,BookingStatusEnum $status)
    {
        return $query->whereHas(
            'bookingItems',
            fn (Builder $query) =>
                $this->applyItemBookingStatusConstraint(
                    $query,
                    $today,
                    $status
                )
        );
    }

    private function applyUnconfirmedFilter(Builder $query,string $today)
    {
        $this->applyBookingStatusFilter(
            $query,
            $today,
            BookingStatusEnum::UNCONFIRMED
        );

        $this->excludeBookingStatus(
            $query,
            $today,
            BookingStatusEnum::CONFIRMED
        );
    }

    private function applyAvailableFilter(Builder $query,string $today)
    {
        $query->whereDoesntHave(
            'bookingItems',
            function (Builder $query) use ($today) {
                $query->whereHas(
                    'period',
                    function (Builder $query) use ($today) {
                        $query
                            ->whereDate(
                                'start_date',
                                '<=',
                                $today
                            )
                            ->whereDate(
                                'end_date',
                                '>=',
                                $today
                            )
                            ->whereHas(
                                'externalBookingType.externalBooking.booking',
                                fn (Builder $query) =>
                                    $query->whereIn(
                                        'status',
                                        [
                                            BookingStatusEnum::CONFIRMED->value,
                                            BookingStatusEnum::UNCONFIRMED->value,
                                        ]
                                    )
                            );
                    }
                );
            }
        );
    }

    private function excludeBookingStatus(Builder $query,string $today,BookingStatusEnum $status)
    {
        return $query->whereDoesntHave(
            'bookingItems',
            fn (Builder $query) =>
                $this->applyItemBookingStatusConstraint(
                    $query,
                    $today,
                    $status
                )
        );
    }

    private function applyItemBookingStatusConstraint(Builder $query,string $today,BookingStatusEnum $status)
    {
        return $query->whereHas(
            'period',
            function (Builder $query) use (
                $today,
                $status
            ) {
                $query
                    ->whereDate(
                        'start_date',
                        '<=',
                        $today
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $today
                    )
                    ->whereHas(
                        'externalBookingType.externalBooking.booking',
                        fn (Builder $query) =>
                            $query->where(
                                'status',
                                $status->value
                            )
                    );
            }
        );
    }

    public function show(int $id)
    {
        $asset = ExternalAsset::query()
            ->with([
                'area.governorate',

                'bookingItems' => function ($query) {
                    $query->with([
                        'design',
                        'period.externalBookingType.externalBooking.booking.customer',
                    ]);
                },
            ])
            ->findOrFail($id);

        $confirmedItems = $asset
            ->bookingItems
            ->filter(
                fn ($item) =>
                    $item
                        ->period
                        ->externalBookingType
                        ->externalBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::CONFIRMED
            );

        $unconfirmedItems = $asset
            ->bookingItems
            ->filter(
                fn ($item) =>
                    $item
                        ->period
                        ->externalBookingType
                        ->externalBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::UNCONFIRMED
            );

        return [
            'asset' => $asset,

            'confirmed_bookings' =>
                $this->formatBookings(
                    $confirmedItems
                ),

            'unconfirmed_bookings' =>
                $this->formatBookings(
                    $unconfirmedItems
                ),

            'unavailable_ranges' =>
                $this->formatUnavailableRanges(
                    $confirmedItems
                ),
        ];
    }

    private function formatBookings($items): array
    {
        return $items
            ->groupBy(
                fn ($item) =>
                    $item
                        ->period
                        ->externalBookingType
                        ->externalBooking
                        ->booking_id
            )
            ->map(function ($items) {
                $firstItem = $items->first();

                $booking = $firstItem
                    ->period
                    ->externalBookingType
                    ->externalBooking
                    ->booking;

                return [
                    'id' => $booking->id,

                    'client_name' =>
                        $booking->customer?->name,

                    'periods' => $items
                        ->map(
                            fn ($item) => [
                                'start_date' =>
                                    $item
                                        ->period
                                        ->start_date
                                        ?->format('Y-m-d'),

                                'end_date' =>
                                    $item
                                        ->period
                                        ->end_date
                                        ?->format('Y-m-d'),

                                'design_name' =>
                                    $item
                                        ->design
                                        ?->name,
                            ]
                        )
                        ->sortBy('start_date')
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function formatUnavailableRanges($items)
    {
        return $items
            ->map(
                fn ($item) => [
                    'start_date' =>
                        $item
                            ->period
                            ->start_date
                            ?->format('Y-m-d'),

                    'end_date' =>
                        $item
                            ->period
                            ->end_date
                            ?->format('Y-m-d'),
                ]
            )
            ->unique(
                fn ($range) =>
                    $range['start_date']
                    . '-'
                    . $range['end_date']
            )
            ->sortBy('start_date')
            ->values()
            ->toArray();
    }
}