<?php

namespace App\Services\FlexBillboard;

use App\Enums\FlexBookingItemStatusEnum;
use App\Enums\FlexStatusEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBillboard;
use App\Models\FlexBookingItem;
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

        $summary = $this->getSummary($baseQuery,$periodId,$year);

        $query = (clone $baseQuery)
            ->with('area.governorate')
            ->status(
                $filters['status'] ?? null,
                $periodId,
                $year
            )
            ->withExists([
                'bookingItems as has_booked_item' =>
                    fn (Builder $query) =>
                        $query
                            ->where('status',FlexBookingItemStatusEnum::BOOKED->value)
                            ->whereHas(
                                'period',
                                fn (Builder $query) =>
                                    $query
                                        ->where('advertising_period_id',$periodId)
                                        ->where('year', $year)
                            ),

                'bookingItems as has_unconfirmed_item' =>
                    fn (Builder $query) =>
                        $query
                            ->where('status',FlexBookingItemStatusEnum::UNCONFIRMED->value)
                            ->whereHas('period',
                                fn (Builder $query) =>
                                    $query
                                        ->where('advertising_period_id',$periodId)
                                        ->where('year', $year)
                            ),
            ]);

        $billboards = $query
            ->orderBy('id')
            ->paginate(24);

        $billboards->getCollection()->transform(
            function (FlexBillboard $billboard) {
                $billboard->flex_status = match (true) {
                    (bool) $billboard->has_booked_item =>FlexStatusEnum::BOOKED->value,
                    (bool) $billboard->has_unconfirmed_item =>FlexStatusEnum::UNCONFIRMED->value,
                    default =>FlexStatusEnum::AVAILABLE->value,
                };
                return $billboard;
            }
        );

        return [
            'summary' => $summary,
            'billboards' => $billboards,
        ];
    }

    private function getSummary(Builder $baseQuery,int $periodId,int $year): array
    {
        $total = (clone $baseQuery)->count();

        $statusCounts = FlexBookingItem::query()
            ->selectRaw('status, COUNT(DISTINCT flex_billboard_id) as total')
            ->whereIn('flex_billboard_id',(clone $baseQuery)->select('flex_billboards.id'))
            ->whereHas('period',
                fn (Builder $query) =>
                    $query
                        ->where('advertising_period_id',$periodId)
                        ->where('year', $year)
            )
            ->whereIn('status', [
                FlexBookingItemStatusEnum::BOOKED->value,
                FlexBookingItemStatusEnum::UNCONFIRMED->value,
            ])
            ->groupBy('status')
            ->pluck('total', 'status');

        $booked = (int) (
            $statusCounts[FlexBookingItemStatusEnum::BOOKED->value] ?? 0
        );

        $unconfirmed = (int) (
            $statusCounts[FlexBookingItemStatusEnum::UNCONFIRMED->value] ?? 0
        );

        return [
            'total' => $total,
            'booked' => $booked,
            'available' => $total - $booked - $unconfirmed,
            'unconfirmed' => $unconfirmed,
        ];
    }


    public function show(int $id): array
    {
        $year = now()->year;

        $billboard = FlexBillboard::query()
            ->with([
                'area.governorate',

                'bookingItems' => function ($query) use ($year) {
                    $query
                        ->whereHas(
                            'period',
                            fn (Builder $query) =>
                                $query->where('year', $year)
                        )
                        ->with([
                            'period.advertisingPeriod',
                            'period.flexBooking.booking.customer',
                        ]);
                },
            ])
            ->findOrFail($id);

        $bookedItems = $billboard->bookingItems
            ->filter(fn ($item) =>$item->status === FlexBookingItemStatusEnum::BOOKED);

        $unconfirmedItems = $billboard->bookingItems
            ->filter(fn ($item) =>$item->status === FlexBookingItemStatusEnum::UNCONFIRMED);

        $reservedPeriodIds = $billboard->bookingItems
            ->pluck('period.advertising_period_id')
            ->filter()
            ->unique();

        $availablePeriods = AdvertisingPeriod::query()
            ->whereNotIn('id', $reservedPeriodIds)
            ->orderBy('number')
            ->get([
                'id',
                'number',
            ]);

        return [
            'billboard' => $billboard,
            'confirmed_bookings' =>$this->formatBookings($bookedItems),
            'unconfirmed_bookings' =>$this->formatBookings($unconfirmedItems),
            'available_periods' => $availablePeriods,
        ];
    }

    private function formatBookings($items): array
    {
        return $items
            ->groupBy(
                fn ($item) =>
                    $item->period->flexBooking->booking_id
            )
            ->map(function ($items) {
                $firstItem = $items->first();
                $booking = $firstItem
                    ->period
                    ->flexBooking
                    ->booking;

                return [
                    'id' => $booking->id,
                    'client_name' => $booking->customer?->name,
                    'periods' => $items
                        ->map(fn ($item) => [
                            'id' => $item
                                ->period
                                ->advertisingPeriod
                                ->id,
                            'number' => $item
                                ->period
                                ->advertisingPeriod
                                ->number,
                        ])
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