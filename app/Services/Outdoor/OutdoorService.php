<?php

namespace App\Services\Outdoor;

use App\Enums\AssetAvailabilityStatusEnum;
use App\Enums\BookingItemStatusEnum;
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

        $summary = $this->getSummary($baseQuery);

        $query = (clone $baseQuery)
            ->with('area.governorate')
            ->statusToday(
                $filters['status'] ?? null
            )
            ->withExists([
                'bookingItems as has_booked_item' =>
                    fn (Builder $query) =>
                        $query
                            ->where('status',BookingItemStatusEnum::BOOKED->value)
                            ->whereHas('period',fn (Builder $query) =>
                                    $query
                                        ->whereDate('start_date','<=',$today)
                                        ->whereDate('end_date','>=',$today)
                            ),

                'bookingItems as has_unconfirmed_item' =>
                    fn (Builder $query) =>
                        $query
                            ->where('status',BookingItemStatusEnum::UNCONFIRMED->value)
                            ->whereHas('period',fn (Builder $query) =>
                                    $query
                                        ->whereDate('start_date','<=',$today)
                                        ->whereDate('end_date','>=',$today)
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

    private function getSummary(Builder $baseQuery): array
    {
        $total = (clone $baseQuery)->count();

        $booked = (clone $baseQuery)
            ->bookedToday()
            ->count();

        $unconfirmed = (clone $baseQuery)
            ->unconfirmedToday()
            ->count();

        $available = (clone $baseQuery)
            ->availableToday()
            ->count();

        return [
            'total' => $total,
            'booked' => $booked,
            'available' => $available,
            'unconfirmed' => $unconfirmed,
        ];
    }

    public function show(int $id): array
    {
        $asset = ExternalAsset::query()
            ->with([
                'area.governorate',

                'bookingItems' => function ($query) {
                    $query->with([
                        'period.externalBookingType.externalBooking.booking.customer',
                    ]);
                },
            ])
            ->findOrFail($id);

        $confirmedItems = $asset->bookingItems
            ->filter(fn ($item) =>$item->status === BookingItemStatusEnum::BOOKED);

        $unconfirmedItems = $asset->bookingItems
            ->filter(fn ($item) =>$item->status === BookingItemStatusEnum::UNCONFIRMED);

        return [
            'asset' => $asset,
            'confirmed_bookings' => $this->formatBookings($confirmedItems),
            'unconfirmed_bookings' => $this->formatBookings($unconfirmedItems),
            'unavailable_ranges' => $this->formatUnavailableRanges($confirmedItems),
        ];
    }

    private function formatBookings($items): array
    {
        return $items
            ->map(function ($item) {
                $booking = $item
                    ->period
                    ->externalBookingType
                    ->externalBooking
                    ->booking;

                return [
                    'id' => $booking->id,
                    'client_name' =>$booking->customer?->name,
                    'start_date' =>$item->period->start_date?->format('Y-m-d'),
                    'end_date' =>$item->period->end_date?->format('Y-m-d'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function formatUnavailableRanges($items): array
    {
        return $items
            ->map(fn ($item) => [
                'start_date' =>$item->period->start_date?->format('Y-m-d'),
                'end_date' =>$item->period->end_date?->format('Y-m-d'),
            ])
            ->unique(fn ($range) =>$range['start_date'] . '-' . $range['end_date'])
            ->sortBy('start_date')
            ->values()
            ->toArray();
    }
}