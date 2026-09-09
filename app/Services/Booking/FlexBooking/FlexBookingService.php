<?php

namespace App\Services\Booking\FlexBooking;

use App\Enums\BookingStatusEnum;
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

            [
                'requested_items' => $requestedItems,
                'billboards' => $billboards,
            ] = $this->prepareItems(
                $data['periods'],
                $booking->id
            );

            $flexBooking = FlexBooking::query()->create([
                'booking_id' => $booking->id,
            ]);

            $designs = $this->createDesigns($flexBooking,$data['designs']);

            $this->createPeriodsAndItems(
                $flexBooking,
                $data['periods'],
                $billboards,
                $designs
            );

            return $this->buildResult(
                $booking,
                $flexBooking,
                count($data['periods']),
                $requestedItems->count(),
                $designs->count()
            );
        });
    }

    public function update(int $bookingId,array $data)
    {
        return DB::transaction(function () use ($bookingId,$data)
        {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($bookingId);

            $flexBooking = FlexBooking::query()
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            [
                'requested_items' => $requestedItems,
                'billboards' => $billboards,
            ] = $this->prepareItems($data['periods'],$booking->id);

            $designs = $this->syncDesigns($flexBooking,$data['designs']);

            $this->syncPeriodsAndItems(
                $flexBooking,
                $data['periods'],
                $billboards,
                $designs
            );

            $this->deleteUnusedDesigns($flexBooking,$data['designs']);

            return $this->buildResult(
                $booking,
                $flexBooking,
                count($data['periods']),
                $requestedItems->count(),
                count($data['designs'])
            );
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
                        __(
                            'messages.flex_booking.customer_mismatch'
                        ),
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
                    __(
                        'messages.flex_booking.already_exists'
                    ),
                ],
            ]);
        }
    }

    private function prepareItems(array $periods,int $bookingId)
    {
        $this->ensureNoDuplicateItems($periods);
        $requestedItems = $this->getRequestedItems($periods);
        $billboards = $this->lockBillboards($requestedItems);
        $this->ensureAssetsAreAvailable($periods,$bookingId);

        return [
            'requested_items' => $requestedItems,
            'billboards' => $billboards,
        ];
    }

    private function ensureNoDuplicateItems(array $periods)
    {
        foreach ($periods as $periodData) {
            $flexIds = collect(
                $periodData['items']
            )
                ->pluck('flex_id')
                ->map(fn ($id) => (int) $id);

            if (
                $flexIds->count()
                === $flexIds->unique()->count()
            ) {
                continue;
            }

            throw ValidationException::withMessages([
                'periods' => [
                    __(
                        'messages.flex_booking.duplicate_assets'
                    ),
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
                            'period_id' =>(int) $period['period_id'],
                            'flex_id' =>(int) $item['flex_id'],
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

        foreach ($periods as $periodData)
        {
            $periodId = (int) $periodData['period_id'];
            $billboardIds = collect($periodData['items'])
                ->pluck('flex_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $hasConflict = FlexBookingItem::query()
                ->whereIn('flex_billboard_id',$billboardIds)
                ->whereHas('period',fn ($query) =>
                        $query
                            ->where('advertising_period_id',$periodId)
                            ->where('year',$year)
                )
                ->whereHas(
                    'period.flexBooking.booking',
                    fn ($query) =>
                        $query
                            ->where('id','!=',$bookingId)
                            ->whereIn(
                                'status',
                                [
                                    BookingStatusEnum::CONFIRMED->value,
                                    BookingStatusEnum::UNCONFIRMED->value,
                                ]
                            )
                )
                ->exists();

            if ($hasConflict) {
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
                function (string $name) use ($flexBooking)
                {
                    $design = FlexDesign::query()->create([
                        'flex_booking_id' => $flexBooking->id,
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
                $this->createItem(
                    $period,
                    $itemData,
                    $billboards,
                    $designs
                );
            }
        }
    }

    private function syncDesigns(FlexBooking $flexBooking,array $designNames)
    {
        $existingDesigns = FlexDesign::query()
            ->where('flex_booking_id',$flexBooking->id)
            ->get()
            ->keyBy('name');

        return collect($designNames)
            ->mapWithKeys(
                function (string $name) use ($flexBooking,$existingDesigns)
                {
                    $design =
                        $existingDesigns->get($name)
                        ?? FlexDesign::query()->create([
                            'flex_booking_id' => $flexBooking->id,
                            'name' => $name,
                        ]);

                    return [
                        $name => $design,
                    ];
                }
            );
    }

    private function syncPeriodsAndItems(FlexBooking $flexBooking,array $periods,Collection $billboards,Collection $designs)
    {
        $year = now()->year;

        $requestedPeriodIds = collect($periods)
            ->pluck('period_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $existingPeriods = FlexBookingPeriod::query()
            ->where('flex_booking_id',$flexBooking->id)
            ->where('year', $year)
            ->with('bookingItems')
            ->get()
            ->keyBy('advertising_period_id');

        foreach ($periods as $periodData)
        {
            $periodId =(int) $periodData['period_id'];
            $period = $existingPeriods->get($periodId);

            if (! $period) {
                $period = FlexBookingPeriod::query()->create([
                    'flex_booking_id' => $flexBooking->id,
                    'advertising_period_id' => $periodId,
                    'year' => $year,
                ]);

                $period->setRelation('bookingItems',collect());
            }

            $this->syncPeriodItems(
                $period,
                $periodData['items'],
                $billboards,
                $designs
            );
        }

        FlexBookingPeriod::query()
            ->where('flex_booking_id',$flexBooking->id)
            ->where('year', $year)
            ->whereNotIn('advertising_period_id',$requestedPeriodIds)
            ->delete();
    }

    private function syncPeriodItems(FlexBookingPeriod $period,array $items,Collection $billboards,Collection $designs)
    {
        $existingItems = $period
            ->bookingItems
            ->keyBy('flex_billboard_id');

        $requestedFlexIds = collect($items)
            ->pluck('flex_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        foreach ($items as $itemData) {

            $flexId = (int) $itemData['flex_id'];
            $existingItem = $existingItems->get($flexId);
            $design = $this->resolveDesign($itemData,$designs);

            if ($existingItem) {
                $existingItem->update([
                    'design_id' => $design?->id,
                    'has_dykat' => $itemData['has_dykes'],
                    'is_gift' => $itemData['is_gift'],
                ]);

                continue;
            }

            $this->createItem(
                $period,
                $itemData,
                $billboards,
                $designs
            );
        }

        FlexBookingItem::query()
            ->where('flex_booking_period_id',$period->id)
            ->whereNotIn('flex_billboard_id',$requestedFlexIds)
            ->delete();
    }

    private function createItem(FlexBookingPeriod $period,array $itemData,Collection $billboards,Collection $designs)
    {
        $billboard = $billboards->get((int) $itemData['flex_id']);
        $design = $this->resolveDesign($itemData,$designs);

        return FlexBookingItem::query()->create([
            'flex_booking_period_id' => $period->id,
            'flex_billboard_id' => $billboard->id,
            'design_id' => $design?->id,
            'has_dykat' => $itemData['has_dykes'],
            'is_gift' => $itemData['is_gift'],
        ]);
    }

    private function resolveDesign(array $itemData,Collection $designs)
    {
        $designName = $itemData['design_name'] ?? null;

        return $designName
            ? $designs->get($designName)
            : null;
    }

    private function deleteUnusedDesigns(FlexBooking $flexBooking,array $designNames)
    {
        $query = FlexDesign::query()->where('flex_booking_id',$flexBooking->id);

        if (! empty($designNames)) {
            $query->whereNotIn(
                'name',
                $designNames
            );
        }

        $query->delete();
    }

    private function buildResult(Booking $booking,FlexBooking $flexBooking,int $periodsCount,int $itemsCount,int $designsCount)
    {
        return [
            'booking' => $booking->refresh(),
            'flex_booking' => $flexBooking->refresh(),
            'periods_count' => $periodsCount,
            'items_count' => $itemsCount,
            'designs_count' => $designsCount,
        ];
    }

    public function show(int $bookingId): FlexBooking
    {
        return FlexBooking::query()
            ->where('booking_id', $bookingId)
            ->with([
                'designs' => fn ($query) => $query->orderBy('id'),
                'periods.advertisingPeriod',
                'periods.bookingItems.design',
                'periods.bookingItems.billboard.area.governorate',
            ])
            ->firstOrFail();
    }
}