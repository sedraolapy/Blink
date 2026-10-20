<?php

namespace App\Services\Orders\Outdoor;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;

class OutdoorOrderCandidatesService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function getCandidates(string $outdoorType,int $customerId,int $periodId)
    {
        $year = $this->workingYearContext->get();

        $period = ExternalBookingPeriod::query()
            ->whereKey($periodId)
            ->whereHas(
                'externalBookingType',
                fn (Builder $query) =>
                    $query
                        ->where('type', $outdoorType)
                        ->whereHas(
                            'externalBooking.booking',
                            fn (Builder $query) =>
                                $query
                                    ->where('customer_id', $customerId)
                                    ->where('year', $year)
                                    ->where('status',BookingStatusEnum::CONFIRMED->value)
                        )
            )
            ->with([
                'externalBookingType.externalBooking.booking.customer',

                'items' => fn ($query) =>
                    $query
                        ->where('status',BookingItemStatusEnum::BOOKED->value)
                        ->whereNull('order_status')
                        ->with([
                            'asset.area.governorate',
                            'design',
                        ])
                        ->orderBy('external_asset_id'),
            ])
            ->firstOrFail();

        $customer = $period
            ->externalBookingType
            ->externalBooking
            ->booking
            ->customer;

        return [
            'customer' => [
                'id' => (int) $customer->id,
                'name' => $customer->name,
            ],

            'period' => [
                'id' => (int) $period->id,
                'start_date' => $period->start_date->toDateString(),
                'end_date' => $period->end_date->toDateString(),
            ],

            'items' => $period->items
                ->map(
                    fn (ExternalBookingItem $item) => $this->formatItem($item)
                )
                ->values()
                ->all(),
        ];
    }

    private function formatItem(ExternalBookingItem $item)
    {
        $asset = $item->asset;
        $governorate = $asset->area?->governorate;

        return [
            'asset' => [
                'id' => (int) $asset->id,
                'code' => $asset->code,
                'name' => $asset->location_name,
                'area' => $asset->area?->name,
                'width' => $asset->width !== null
                    ? (float) $asset->width
                    : null,
                'height' => $asset->height !== null
                    ? (float) $asset->height
                    : null,
                'governorate' => $governorate
                    ? [
                        'id' => (int) $governorate->id,
                        'name' => $governorate->name,
                    ]
                    : null,
            ],

            'design_name' => $item->design?->name,
        ];
    }
}
