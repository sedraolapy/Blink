<?php

namespace App\Services\Booking\ExternalBooking;

use App\Enums\BookingItemStatusEnum;
use App\Enums\ExternalAssetTypeEnum;
use App\Models\Booking;
use App\Models\ExternalAsset;
use App\Models\ExternalBooking;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingType;
use App\Models\Governorate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExternalBookingService
{
    public function getAvailableAssets(array $data): array
    {
        $bookingId = isset($data['booking_id'])
            ? (int) $data['booking_id']
            : null;

        $type = ExternalAssetTypeEnum::from($data['type']);

        $governorateId = (int) $data['governorate_id'];

        $periods = collect($data['periods']);

        $governorate = Governorate::query()
            ->findOrFail($governorateId);


        $assets = ExternalAsset::query()
            ->type($type)
            ->governorate($governorateId)
            ->search($data['search'] ?? null)
            ->with([
                'area:id,governorate_id,name',
            ])
            ->orderBy('id')
            ->get([
                'id',
                'code',
                'area_id',
                'location_name',
                'width',
                'height',
            ]);


        $occupied = ExternalBookingItem::query()
            ->whereIn('status', [
                BookingItemStatusEnum::BOOKED->value,
                BookingItemStatusEnum::UNCONFIRMED->value,
            ])
            ->whereHas(
                'period.externalBookingType',
                function ($query) use ($type) {
                    $query->where(
                        'type',
                        $type->value
                    );
                }
            )
            ->whereHas(
                'period',
                function ($query) use ($periods) {
                    $query->where(function ($q) use ($periods) {

                        foreach ($periods as $period) {
                            $q->orWhere(function ($sub) use ($period) {
                                $sub
                                    ->whereDate(
                                        'start_date',
                                        '<=',
                                        $period['end_date']
                                    )
                                    ->whereDate(
                                        'end_date',
                                        '>=',
                                        $period['start_date']
                                    );
                            });
                        }

                    });
                }
            )
            ->when(
                $bookingId,
                function ($query) use ($bookingId) {
                    $query->whereHas(
                        'period.externalBookingType.externalBooking',
                        fn ($q) =>
                            $q->where(
                                'booking_id',
                                '!=',
                                $bookingId
                            )
                    );
                }
            )
            ->pluck('external_asset_id')
            ->unique();


        $resultPeriods = $periods->map(
            function ($period) use (
                $assets,
                $occupied,
                $bookingId
            ) {

                return [
                    'start_date' => $period['start_date'],
                    'end_date' => $period['end_date'],

                    'items' => $assets
                        ->reject(
                            fn ($asset) =>
                                $occupied->contains($asset->id)
                        )
                        ->map(function ($asset) use (
                            $bookingId
                        ) {

                            $item = [
                                'id' => $asset->id,
                                'code' => $asset->code,
                                'name' => $asset->location_name,
                                'area' => $asset->area?->name,
                                'width' => (float) $asset->width,
                                'height' => (float) $asset->height,
                            ];

                            if ($bookingId) {
                                $item['was_selected'] =
                                    ExternalBookingItem::query()
                                        ->where(
                                            'external_asset_id',
                                            $asset->id
                                        )
                                        ->whereHas(
                                            'period.externalBookingType.externalBooking',
                                            fn ($q) =>
                                                $q->where(
                                                    'booking_id',
                                                    $bookingId
                                                )
                                        )
                                        ->exists();
                            }

                            return $item;
                        })
                        ->values(),
                ];
            }
        );


        return [
            'type' => $type->value,
            'governorate' => $governorate,
            'periods' => $resultPeriods,
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            if (!empty($data['booking_id'])) {

                $booking = Booking::query()
                    ->findOrFail($data['booking_id']);

            } else {

                $booking = Booking::query()->create([
                    'customer_id' => $data['customer_id'],
                    'booking_type' => $data['advertiser_type'],
                ]);
            }

            $externalBooking = ExternalBooking::query()
                ->firstOrCreate([
                    'booking_id' => $booking->id,
                ]);

            if (
                $externalBooking->types()
                    ->where('type', $data['type'])
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'type' => [
                        __('validation.custom.external_type_already_exists'),
                    ],
                ]);
            }

            $type = $externalBooking->types()->create([
                'type' => $data['type'],
            ]);

            foreach ($data['designs'] ?? [] as $designName) {
                $type->designs()->create([
                    'name' => $designName,
                ]);
            }

            foreach ($data['periods'] as $periodData) {

                $period = $type->periods()->create([
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                ]);

                foreach ($periodData['items'] as $itemData) {

                    $design = null;

                    if (!empty($itemData['design_name'])) {
                        $design = $type->designs()
                            ->where(
                                'name',
                                $itemData['design_name']
                            )
                            ->first();
                    }

                    $period->items()->create([
                        'external_asset_id' => $itemData['asset_id'],
                        'design_id' => $design?->id,
                        'is_gift' => $itemData['is_gift'],
                        'status' => BookingItemStatusEnum::UNCONFIRMED->value,
                    ]);
                }
            }

            return $externalBooking->load([
                'booking.flexBooking',
                'booking.ledBooking',
                'booking.externalBooking.types',

                'types.designs',
                'types.periods.items',
            ]);
        });
    }
    
    public function show(int $bookingId,ExternalAssetTypeEnum $type)
    {
        return ExternalBookingType::query()
            ->where('type', $type->value)
            ->whereHas('externalBooking',fn ($query) =>$query->where('booking_id', $bookingId))
            ->with([
                'designs',
                'periods' => fn ($query) => $query->orderBy('id'),
                'periods.items' => fn ($query) => $query->orderBy('id'),
                'periods.items.design',
                'periods.items.asset.area.governorate',
            ])
            ->firstOrFail();
    }


    public function update(int $bookingId,ExternalAssetTypeEnum $type,array $data)
    {
        return DB::transaction(function () use ($bookingId,$type,$data)
        {
            $externalBookingType = ExternalBookingType::query()
                ->where('type', $type->value)
                ->whereHas(
                    'externalBooking',
                    fn ($query) =>
                        $query->where('booking_id', $bookingId)
                )
                ->with('externalBooking.booking')
                ->firstOrFail();

            $externalBookingType->periods()->delete();

            $externalBookingType->designs()->delete();

            foreach ($data['designs'] as $designName) {
                $externalBookingType->designs()->create([
                    'name' => $designName,
                ]);
            }

            foreach ($data['periods'] as $periodData) {

                $period = $externalBookingType->periods()->create([
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                ]);

                foreach ($periodData['items'] as $itemData) {

                    $design = null;

                    if (!empty($itemData['design_name'])) {
                        $design = $externalBookingType
                            ->designs()
                            ->where(
                                'name',
                                $itemData['design_name']
                            )
                            ->first();
                    }

                    $period->items()->create([
                        'external_asset_id' => $itemData['asset_id'],
                        'design_id' => $design?->id,
                        'is_gift' => $itemData['is_gift'],
                        'status' => BookingItemStatusEnum::UNCONFIRMED->value,
                    ]);
                }
            }

            return $externalBookingType->fresh([
                'designs',
                'periods.items.asset.area.governorate',
                'externalBooking.booking.flexBooking',
                'externalBooking.booking.ledBooking',
                'externalBooking.booking.externalBooking.types',
            ]);
        });
    }
}