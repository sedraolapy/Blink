<?php

namespace App\Services\Quotation\Calculators;
use Carbon\Carbon;
use App\Enums\BookingTypeEnum;
use App\Enums\ExternalAssetTypeEnum;
use App\Models\Booking;
use App\Models\ExternalBooking;
use LogicException;

class OutdoorQuotationCalculator
{
    private function calculateBillableMonths(Carbon $startDate,Carbon $endDate)
    {
        $startDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->startOfDay();

        if ($startDate->equalTo($endDate)) {
            return 0.5;
        }

        $fullMonths = 0;

        while (true) {
            $nextAnchor = $startDate
                ->copy()
                ->addMonthsNoOverflow($fullMonths + 1);

            if ($nextAnchor->gt($endDate)) {
                break;
            }

            $fullMonths++;
        }

        $currentAnchor = $startDate
            ->copy()
            ->addMonthsNoOverflow($fullMonths);

        if ($currentAnchor->equalTo($endDate)) {
            return (float) $fullMonths;
        }

        $nextAnchor = $startDate
            ->copy()
            ->addMonthsNoOverflow($fullMonths + 1);

        $cycleSeconds = $currentAnchor->diffInSeconds($nextAnchor);
        $usedSeconds = $currentAnchor->diffInSeconds($endDate);

        $remainder = $usedSeconds <= ($cycleSeconds / 2)
            ? 0.5
            : 1.0;

        return $fullMonths + $remainder;
    }

    private function calculateType(Booking $booking,ExternalBooking $externalBooking,ExternalAssetTypeEnum $type)
    {
        $summary = [];
        $details = [];
        $total = 0;

        $bookingType = $externalBooking->types
            ->first(
                fn ($bookingType) =>
                    $bookingType->type === $type
            );

        if (! $bookingType) {
            return [
                'summary' => [],
                'details' => [],
                'total' => 0,
            ];
        }

        foreach ($bookingType->periods as $period) {
            $billableMonths = $this->calculateBillableMonths(
                $period->start_date,
                $period->end_date
            );

            foreach ($period->items as $item) {
                $asset = $item->asset;
                $governorate = $asset->area->governorate;

                $monthlyPrice = match ($booking->booking_type) {
                    BookingTypeEnum::LOCAL => $asset->local_price,
                    BookingTypeEnum::FOREIGN => $asset->foreign_price,
                };

                if ($monthlyPrice === null) {
                    throw new LogicException( "Price is missing for external asset {$asset->id}.");
                }

                $price = round(
                    (float) $monthlyPrice
                    * $billableMonths,
                    2
                );

                $total += $price;

                $summaryKey = implode('|', [
                    $governorate->id,
                    $period->start_date->toDateString(),
                    $period->end_date->toDateString(),
                ]);

                if (! isset($summary[$summaryKey])) {
                    $summary[$summaryKey] = [
                        'governorate' => [
                            'id' => $governorate->id,
                            'name' => $governorate->name,
                        ],
                        'assets_count' => 0,
                        'period' => [
                            'start_date' => $period->start_date->toDateString(),
                            'end_date' => $period->end_date->toDateString(),
                        ],
                        'total_price' => 0,
                    ];
                }

                $summary[$summaryKey]['assets_count']++;

                $summary[$summaryKey]['total_price'] +=
                    $price;

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
                    'id' => $asset->id,
                    'location' => $asset->location_name,
                    'width' => $asset->width,
                    'height' => $asset->height,

                    'period' => [
                        'start_date' =>
                            $period->start_date->toDateString(),

                        'end_date' =>
                            $period->end_date->toDateString(),
                    ],

                    'price' => $price,
                ];
            }
        }

        return [
            'summary' => array_values($summary),
            'details' => array_values($details),
            'total' => round($total, 2),
        ];
    }

    public function calculate(Booking $booking): array
    {
        $externalBooking = ExternalBooking::query()
            ->where('booking_id', $booking->id)
            ->with([
                'types.periods.items.asset.area.governorate',
            ])
            ->first();

        if (! $externalBooking) {
            return [
                'mural' => [
                    'summary' => [],
                    'details' => [],
                    'total' => 0,
                ],
                'rooftop' => [
                    'summary' => [],
                    'details' => [],
                    'total' => 0,
                ],
                'tunnel' => [
                    'summary' => [],
                    'details' => [],
                    'total' => 0,
                ],
                'bridge' => [
                    'summary' => [],
                    'details' => [],
                    'total' => 0,
                ],
                'unipole' => [
                    'summary' => [],
                    'details' => [],
                    'total' => 0,
                ],
                'total' => 0,
            ];
        }

        $mural = $this->calculateType(
            $booking,
            $externalBooking,
            ExternalAssetTypeEnum::MURAL
        );

        $rooftop = $this->calculateType(
            $booking,
            $externalBooking,
            ExternalAssetTypeEnum::ROOFTOP
        );

        $tunnel = $this->calculateType(
            $booking,
            $externalBooking,
            ExternalAssetTypeEnum::TUNNEL
        );

        $bridge = $this->calculateType(
            $booking,
            $externalBooking,
            ExternalAssetTypeEnum::BRIDGE
        );

        $unipole = $this->calculateType(
            $booking,
            $externalBooking,
            ExternalAssetTypeEnum::UNIPOLE
        );

        $total = round(
            $mural['total']
            + $rooftop['total']
            + $tunnel['total']
            + $bridge['total']
            + $unipole['total'],
            2
        );

        return [
            'mural' => $mural,
            'rooftop' => $rooftop,
            'tunnel' => $tunnel,
            'bridge' => $bridge,
            'unipole' => $unipole,
            'total' => $total,
        ];
    }
}
