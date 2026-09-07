<?php

namespace App\Services\FlexBillboard;

use App\Enums\AssetAvailabilityStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBillboard;
use App\Services\AdvertisingPeriod\AdvertisingPeriodService;
use Illuminate\Database\Eloquent\Builder;

class FlexAvailabilityService
{
    public function __construct(private readonly AdvertisingPeriodService $advertisingPeriodService) {}

    public function index(array $filters): array
    {
        $periodId = isset($filters['period_id'])
            ? (int) $filters['period_id']
            : $this->advertisingPeriodService->getCurrentPeriodId();

        $year = now()->year;

        $baseQuery = FlexBillboard::query()
            ->search($filters['search'] ?? null)
            ->governorate(
                isset($filters['governorate_id'])
                    ? (int) $filters['governorate_id']
                    : null
            );

        $summary = $this->getSummary(
            $baseQuery,
            $periodId,
            $year
        );

        $query = (clone $baseQuery)
            ->with('area.governorate');

        $this->applyStatusFilter(
            $query,
            $filters['status'] ?? null,
            $periodId,
            $year
        );

        $query->withExists([
            'bookingItems as has_booked_item' =>
                fn (Builder $query) =>
                    $this->applyItemBookingStatusConstraint(
                        $query,
                        $periodId,
                        $year,
                        BookingStatusEnum::CONFIRMED
                    ),

            'bookingItems as has_unconfirmed_item' =>
                fn (Builder $query) =>
                    $this->applyItemBookingStatusConstraint(
                        $query,
                        $periodId,
                        $year,
                        BookingStatusEnum::UNCONFIRMED
                    ),
        ]);

        $billboards = $query
            ->orderBy('id')
            ->paginate(24);

        $billboards->getCollection()->transform(
            function (FlexBillboard $billboard) {
                $billboard->flex_status = match (true) {
                    (bool) $billboard->has_booked_item =>
                        AssetAvailabilityStatusEnum::BOOKED->value,

                    (bool) $billboard->has_unconfirmed_item =>
                        AssetAvailabilityStatusEnum::UNCONFIRMED->value,

                    default =>
                        AssetAvailabilityStatusEnum::AVAILABLE->value,
                };

                return $billboard;
            }
        );

        return [
            'summary' => $summary,
            'billboards' => $billboards,
        ];
    }

    private function getSummary(Builder $baseQuery,int $periodId,int $year)
    {
        $total = (clone $baseQuery)->count();

        $bookedQuery = clone $baseQuery;

        $this->applyBookingStatusFilter(
            $bookedQuery,
            $periodId,
            $year,
            BookingStatusEnum::CONFIRMED
        );

        $booked = $bookedQuery->count();

        $unconfirmedQuery = clone $baseQuery;

        $this->applyBookingStatusFilter(
            $unconfirmedQuery,
            $periodId,
            $year,
            BookingStatusEnum::UNCONFIRMED
        );

        $this->excludeBookingStatus(
            $unconfirmedQuery,
            $periodId,
            $year,
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

    private function applyStatusFilter(Builder $query, ?string $status,int $periodId,int $year)
    {
        match ($status) {
            AssetAvailabilityStatusEnum::BOOKED->value =>
                $this->applyBookingStatusFilter(
                    $query,
                    $periodId,
                    $year,
                    BookingStatusEnum::CONFIRMED
                ),

            AssetAvailabilityStatusEnum::UNCONFIRMED->value =>
                $this->applyUnconfirmedFilter(
                    $query,
                    $periodId,
                    $year
                ),

            AssetAvailabilityStatusEnum::AVAILABLE->value =>
                $this->applyAvailableFilter(
                    $query,
                    $periodId,
                    $year
                ),

            default => null,
        };
    }

    private function applyBookingStatusFilter(Builder $query,int $periodId,int $year,BookingStatusEnum $status)
    {
        return $query->whereHas(
            'bookingItems',
            fn (Builder $query) =>
                $this->applyItemBookingStatusConstraint(
                    $query,
                    $periodId,
                    $year,
                    $status
                )
        );
    }

    private function applyUnconfirmedFilter(Builder $query,int $periodId,int $year)
    {
        $this->applyBookingStatusFilter(
            $query,
            $periodId,
            $year,
            BookingStatusEnum::UNCONFIRMED
        );

        $this->excludeBookingStatus(
            $query,
            $periodId,
            $year,
            BookingStatusEnum::CONFIRMED
        );
    }

    private function applyAvailableFilter(Builder $query,int $periodId,int $year)
    {
        $query->whereDoesntHave(
            'bookingItems',
            function (Builder $query) use (
                $periodId,
                $year
            ) {
                $query->whereHas(
                    'period',
                    function (Builder $query) use (
                        $periodId,
                        $year
                    ) {
                        $query
                            ->where(
                                'advertising_period_id',
                                $periodId
                            )
                            ->where('year', $year)
                            ->whereHas(
                                'flexBooking.booking',
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

    private function excludeBookingStatus(Builder $query,int $periodId,int $year,BookingStatusEnum $status)
    {
        return $query->whereDoesntHave(
            'bookingItems',
            fn (Builder $query) =>
                $this->applyItemBookingStatusConstraint(
                    $query,
                    $periodId,
                    $year,
                    $status
                )
        );
    }

    private function applyItemBookingStatusConstraint(Builder $query,int $periodId,int $year,BookingStatusEnum $status)
    {
        return $query->whereHas(
            'period',
            function (Builder $query) use (
                $periodId,
                $year,
                $status
            ) {
                $query
                    ->where(
                        'advertising_period_id',
                        $periodId
                    )
                    ->where('year', $year)
                    ->whereHas(
                        'flexBooking.booking',
                        fn (Builder $query) =>
                            $query->where(
                                'status',
                                $status->value
                            )
                    );
            }
        );
    }

    public function show(int $id): array
    {
        $year = now()->year;

        $currentPeriodId =
            $this->advertisingPeriodService
                ->getCurrentPeriodId();

        $billboard = FlexBillboard::query()
            ->with([
                'area.governorate',

                'bookingItems' => function ($query) use ($year) {
                    $query
                        ->whereHas(
                            'period',
                            fn (Builder $query) =>
                                $query->where(
                                    'year',
                                    $year
                                )
                        )
                        ->with([
                            'design',
                            'period.advertisingPeriod',
                            'period.flexBooking.booking.customer',
                        ]);
                },
            ])
            ->findOrFail($id);

        $currentPeriodItems = $billboard
            ->bookingItems
            ->filter(
                fn ($item) =>
                    (int) $item
                        ->period
                        ->advertising_period_id
                    === $currentPeriodId
            );

        $hasConfirmedBooking = $currentPeriodItems
            ->contains(
                fn ($item) =>
                    $item
                        ->period
                        ->flexBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::CONFIRMED
            );

        $hasUnconfirmedBooking = $currentPeriodItems
            ->contains(
                fn ($item) =>
                    $item
                        ->period
                        ->flexBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::UNCONFIRMED
            );

        $billboard->flex_status = match (true) {
            $hasConfirmedBooking =>
                AssetAvailabilityStatusEnum::BOOKED->value,

            $hasUnconfirmedBooking =>
                AssetAvailabilityStatusEnum::UNCONFIRMED->value,

            default =>
                AssetAvailabilityStatusEnum::AVAILABLE->value,
        };

        $confirmedItems = $billboard
            ->bookingItems
            ->filter(
                fn ($item) =>
                    $item
                        ->period
                        ->flexBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::CONFIRMED
            );

        $unconfirmedItems = $billboard
            ->bookingItems
            ->filter(
                fn ($item) =>
                    $item
                        ->period
                        ->flexBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::UNCONFIRMED
            );

        $reservedPeriodIds = $billboard
            ->bookingItems
            ->pluck('period.advertising_period_id')
            ->filter()
            ->unique();

        $periods = AdvertisingPeriod::query()
            ->orderBy('number')
            ->get([
                'id',
                'number',
            ])
            ->map(function ($period) use ($reservedPeriodIds) {
                $period->available = ! $reservedPeriodIds->contains(
                    $period->id
                );

                return $period;
            });

        return [
            'billboard' => $billboard,
            'confirmed_bookings' =>$this->formatBookings($confirmedItems),
            'unconfirmed_bookings' => $this->formatBookings($unconfirmedItems),
            'periods' => $periods,
        ];
    }

    private function formatBookings($items): array
    {
        return $items
            ->groupBy(
                fn ($item) =>
                    $item
                        ->period
                        ->flexBooking
                        ->booking_id
            )
            ->map(function ($items) {
                $firstItem = $items->first();

                $booking = $firstItem
                    ->period
                    ->flexBooking
                    ->booking;

                return [
                    'id' => $booking->id,

                    'client_name' =>
                        $booking
                            ->customer
                            ?->name,

                    'periods' => $items
                        ->map(
                            fn ($item) => [
                                'id' => $item
                                    ->period
                                    ->advertisingPeriod
                                    ->id,

                                'number' => $item
                                    ->period
                                    ->advertisingPeriod
                                    ->number,

                                'design_name' =>
                                    $item
                                        ->design
                                        ?->name,
                            ]
                        )
                        ->unique('id')
                        ->sortBy('number')
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }
}