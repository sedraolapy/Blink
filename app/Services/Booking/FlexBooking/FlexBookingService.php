<?php

namespace App\Services\Booking\FlexBooking;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\Booking;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\FlexDesign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FlexBookingService
{
    public function store(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $booking = $this->resolveBooking($data);
            $this->ensureFlexBookingDoesNotExist($booking);
            $requestedItems = $this->getRequestedItems($data['periods']);
            $billboards = $this->lockBillboards($requestedItems);
            $this->ensureAssetsAreAvailable($data['periods'], $booking->id);
            $flexBooking = FlexBooking::query()->create(['booking_id' => $booking->id]);
            $designs = $this->createDesigns($flexBooking, $data['designs']);

            $this->createPeriodsAndItems(
                $flexBooking,
                $data['periods'],
                $billboards,
                $designs
            );

            return [
                'booking' => $booking->refresh(),
                'flex_booking' => $flexBooking,
                'periods_count' => count($data['periods']),
                'items_count' => $requestedItems->count(),
                'designs_count' => $designs->count(),
            ];
        });
    }

    private function resolveBooking(array $data): Booking
    {
        if (! empty($data['booking_id'])) {
            $booking = Booking::query()
                ->findOrFail($data['booking_id']);

            if (
                (int) $booking->customer_id
                !== (int) $data['customer_id']
            ) {
                throw ValidationException::withMessages([
                    'customer_id' => [
                        __('messages.flex_booking.customer_mismatch'),
                    ],
                ]);
            }

            return $booking;
        }

        return Booking::query()->create([
            'customer_id' => $data['customer_id'],
            'booking_type' => $data['advertiser_type'],
            'status' => BookingStatusEnum::UNCONFIRMED->value,
            'installation_order' => false,
            'extension_order' => false,
            'operation_order' => false,
        ]);
    }


    private function ensureFlexBookingDoesNotExist(Booking $booking)
    {
        $exists = FlexBooking::query()
            ->where('booking_id', $booking->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'booking_id' => [
                    __('messages.flex_booking.already_exists'),
                ],
            ]);
        }
    }

    private function getRequestedItems(array $periods)
    {
        return collect($periods)
            ->flatMap(
                fn (array $period) =>
                    collect($period['items'])->map(
                        fn (array $item) => [
                            'period_id' =>
                                (int) $period['period_id'],

                            'flex_id' =>
                                (int) $item['flex_id'],
                        ]
                    )
            )
            ->values();
    }

    private function lockBillboards(Collection $requestedItems) 
    {
        $billboardIds = $requestedItems
            ->pluck('flex_id')
            ->unique()
            ->sort()
            ->values();

        return FlexBillboard::query()
            ->whereIn('id', $billboardIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function ensureAssetsAreAvailable(array $periods,int $bookingId) 
    {
        $year = now()->year;

        foreach ($periods as $periodData) {
            $periodId = (int) $periodData['period_id'];

            $billboardIds = collect(
                $periodData['items']
            )
                ->pluck('flex_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $occupiedBillboardIds =
                FlexBookingItem::query()
                    ->whereIn(
                        'flex_billboard_id',
                        $billboardIds
                    )
                    ->whereHas(
                        'period',
                        fn ($query) =>
                            $query
                                ->where(
                                    'advertising_period_id',
                                    $periodId
                                )
                                ->where(
                                    'year',
                                    $year
                                )
                    )
                    ->whereHas(
                        'period.flexBooking.booking',
                        fn ($query) =>
                            $query
                                ->where(
                                    'id',
                                    '!=',
                                    $bookingId
                                )
                                ->whereIn(
                                    'status',
                                    [
                                        BookingStatusEnum::CONFIRMED->value,
                                        BookingStatusEnum::UNCONFIRMED->value,
                                    ]
                                )
                    )
                    ->pluck('flex_billboard_id')
                    ->unique();

            if ($occupiedBillboardIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'periods' => [
                        __(
                            'messages.flex_booking.assets_not_available'
                        ),
                    ],
                ]);
            }
        }
    }

    private function createDesigns(FlexBooking $flexBooking,array $designNames)
    {
        return collect($designNames)
            ->mapWithKeys(
                function (
                    string $name
                ) use ($flexBooking) {
                    $design = FlexDesign::query()->create([
                        'flex_booking_id' =>
                            $flexBooking->id,

                        'name' => $name,
                    ]);

                    return [
                        $name => $design,
                    ];
                }
            );
    }

    private function createPeriodsAndItems(FlexBooking $flexBooking,array $periods,Collection $billboards,Collection $designs)
    {
        $year = now()->year;

        foreach ($periods as $periodData) {
            $period = FlexBookingPeriod::query()->create([
                'flex_booking_id' => $flexBooking->id,
                'advertising_period_id' => $periodData['period_id'],
                'year' => $year,
            ]);

            foreach ($periodData['items'] as $itemData) {
                $billboard = $billboards->get((int) $itemData['flex_id']);

                $design = isset($itemData['design_name'])
                    ? $designs->get(
                        $itemData['design_name']
                    )
                    : null;

                FlexBookingItem::query()->create([
                    'flex_booking_period_id' => $period->id,
                    'flex_billboard_id' => $billboard->id,
                    'design_id' => $design?->id,
                    'unit_price_at_booking' => $this->getPrice($billboard,$flexBooking->booking),
                    'has_dykat' => $itemData['has_dykes'],
                    'is_gift' => $itemData['is_gift'],
                ]);
            }
        }
    }

    private function getPrice(FlexBillboard $billboard,Booking $booking)
    {
        return (float) match ($booking->booking_type) {
            BookingTypeEnum::FOREIGN => $billboard->foreign_price,
            BookingTypeEnum::LOCAL => $billboard->local_price,
        };
    }
}