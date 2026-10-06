<?php

namespace App\Services\Quotation\Calculators;

use App\Enums\BookingTypeEnum;
use App\Models\Booking;
use App\Models\FlexBooking;
use App\Models\FlexPriceGroup;
use LogicException;

class FlexQuotationCalculator
{
    public function calculate(Booking $booking): array
    {
        $flexBooking = FlexBooking::query()
            ->where('booking_id', $booking->id)
            ->with([
                'periods.advertisingPeriod',
                'periods.bookingItems.billboard.area.governorate',
            ])
            ->first();

        if (! $flexBooking) {
            return [
                'summary' => [],
                'details' => [],
                'total' => 0,
            ];
        }

        $governorateIds = $flexBooking->periods
            ->flatMap(fn ($period) => $period->bookingItems)
            ->map(
                fn ($item) =>
                    $item->billboard->area->governorate_id
            )
            ->unique()
            ->values();

        $priceGroups = FlexPriceGroup::query()
            ->whereIn('governorate_id', $governorateIds)
            ->get()
            ->keyBy('governorate_id');

        $summary = [];
        $totalCents = 0;

        foreach ($flexBooking->periods as $period) {
            $itemsByGovernorate = $period->bookingItems
                ->groupBy(
                    fn ($item) =>
                        $item->billboard->area->governorate_id
                );

            foreach ($itemsByGovernorate as $governorateId => $items) {
                $priceGroup = $priceGroups->get($governorateId);

                if (! $priceGroup) {
                    throw new LogicException(
                        "Flex price group is missing for governorate {$governorateId}."
                    );
                }

                $groupSize = (int) $priceGroup->billboards_count;

                if ($groupSize <= 0) {
                    throw new LogicException(
                        "Invalid flex price group size for governorate {$governorateId}."
                    );
                }

                $boardsCount = $items->count();

                $groupsCount = intdiv(
                    $boardsCount + $groupSize - 1,
                    $groupSize
                );

                $groupPrice = match ($booking->booking_type) {
                    BookingTypeEnum::LOCAL => $priceGroup->local_price,
                    BookingTypeEnum::FOREIGN => $priceGroup->foreign_price,
                };

                $groupPriceCents = $this->toCents($groupPrice);

                $rowTotalCents =
                    $groupsCount * $groupPriceCents;

                $totalCents += $rowTotalCents;

                $governorate = $items
                    ->first()
                    ->billboard
                    ->area
                    ->governorate;

                $summary[] = [
                    'governorate' => [
                        'id' => $governorate->id,
                        'name' => $governorate->name,
                    ],

                    'period' => [
                        'id' => $period->advertisingPeriod->id,
                        'number' => $period->advertisingPeriod->number,
                    ],

                    'boards_count' => $boardsCount,
                    'networks_count' => $groupsCount,
                    'network_price' => $this->fromCents(
                        $groupPriceCents
                    ),
                    'total_price' => $this->fromCents(
                        $rowTotalCents
                    ),
                ];
            }
        }

        return [
            'summary' => $summary,
            'details' => $this->buildDetails($flexBooking),
            'total' => $this->fromCents($totalCents),
        ];
    }

    private function toCents(string|int|float $amount): int
    {
        $amount = (string) $amount;

        [$whole, $decimal] = array_pad(
            explode('.', $amount, 2),
            2,
            ''
        );

        $decimal = str_pad(
            substr($decimal, 0, 2),
            2,
            '0'
        );

        return ((int) $whole * 100) + (int) $decimal;
    }

    private function fromCents(int $cents): float
    {
        return round($cents / 100, 2);
    }

    private function buildDetails(FlexBooking $flexBooking): array
    {
        $details = [];

        foreach ($flexBooking->periods as $period) {
            foreach ($period->bookingItems as $item) {
                $billboard = $item->billboard;
                $governorate = $billboard->area->governorate;

                if (! isset($details[$governorate->id])) {
                    $details[$governorate->id] = [
                        'governorate' => [
                            'id' => $governorate->id,
                            'name' => $governorate->name,
                        ],
                        'items' => [],
                    ];
                }

                $details[$governorate->id]['items'][] = [
                    'id' => $billboard->id,
                    'code' => $billboard->code,
                    'location' => $billboard->location_name,
                    'width' => (float) $billboard->width,
                    'height' => (float) $billboard->height,
                    'period' => [
                        'id' => $period->advertisingPeriod->id,
                        'number' => $period->advertisingPeriod->number,
                    ],
                ];
            }
        }

        return array_values($details);
    }
}