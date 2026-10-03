<?php

namespace App\Services\Quotation\Calculators;

use App\Enums\BookingTypeEnum;
use App\Models\Booking;
use App\Models\LedBooking;
use Carbon\Carbon;
use LogicException;

class ElectronicQuotationCalculator
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

    private function buildStandalone(Booking $booking, LedBooking $ledBooking)
    {
        $standalone = [];

        foreach ($ledBooking->periods as $period) {
            $billableMonths = $this->calculateBillableMonths(
                $period->start_date,
                $period->end_date
            );

            $items = $period->items
                ->whereNull('led_network_id');

            foreach ($items as $item) {
                $screen = $item->screen;
                $governorate = $screen->area->governorate;

                $slidesCount = $item->slides->count();

                $monthlySlidePrice = match ($booking->booking_type) {
                    BookingTypeEnum::LOCAL => $screen->local_price,
                    BookingTypeEnum::FOREIGN => $screen->foreign_price,
                };

                if ($monthlySlidePrice === null) {
                    throw new LogicException(
                        "Price is missing for LED screen {$screen->id}."
                    );
                }

                $price = round(
                    (float) $monthlySlidePrice
                    * $slidesCount
                    * $billableMonths,
                    2
                );

                if (! isset($standalone[$governorate->id])) {
                    $standalone[$governorate->id] = [
                        'governorate' => [
                            'id' => $governorate->id,
                            'name' => $governorate->name,
                        ],
                        'items' => [],
                    ];
                }

                $standalone[$governorate->id]['items'][] = [
                    'id' => $screen->id,
                    'location' => $screen->location_name,
                    'width' => $screen->width,
                    'height' => $screen->height,
                    'resolution' =>  "{$screen->width_px}x{$screen->height_px}",

                    'period' => [
                        'start_date' => $period->start_date->toDateString(),
                        'end_date' => $period->end_date->toDateString(),
                    ],

                    'slides_count' => $slidesCount,
                    'price' => $price,
                ];
            }
        }

        return array_values($standalone);
    }

    public function calculate(Booking $booking)
    {
        $ledBooking = LedBooking::query()
            ->where('booking_id', $booking->id)
            ->with([
                'periods.items.slides',
                'periods.items.screen.area.governorate',
                'periods.items.network',
            ])
            ->first();

        if (! $ledBooking) {
            return [
                'summary' => [],
                'standalone' => [],
                'networks' => [],
                'total' => 0,
            ];
        }

        $standalone = $this->buildStandalone( $booking,$ledBooking);
        $networks = $this->buildNetworks($booking,$ledBooking);
        $summary = $this->buildSummary($standalone, $networks);

        $standaloneTotal = collect($standalone)
            ->flatMap(fn ($group) => $group['items'])
            ->sum('price');

        $networksTotal = collect($networks)
            ->sum('total_price');

        return [
            'summary' => $summary,
            'standalone' => $standalone,
            'networks' => $networks,
            'total' => round($standaloneTotal + $networksTotal, 2),
        ];
    }

    private function buildNetworks(Booking $booking,LedBooking $ledBooking)
    {
        $networks = [];

        foreach ($ledBooking->periods as $period) {
            $billableMonths = $this->calculateBillableMonths(
                $period->start_date,
                $period->end_date
            );

            $networkItems = $period->items
                ->whereNotNull('led_network_id')
                ->groupBy('led_network_id');

            foreach ($networkItems as $networkId => $items) {
                $network = $items->first()->network;

                if (! $network) {
                    throw new LogicException("LED network {$networkId} not found.");
                }

                $governorates = $items
                    ->map(
                        fn ($item) => $item->screen->area->governorate
                    )
                    ->unique('id')
                    ->values();

                if ($governorates->count() !== 1) {
                    throw new LogicException("LED network {$networkId} contains screens from multiple governorates.");
                }

                $governorate = $governorates->first();
                $maxSlidesCount = $items
                    ->map(
                        fn ($item) => $item->slides->count()
                    )
                    ->max();

                $monthlySlidePrice = match ($booking->booking_type) {
                    BookingTypeEnum::LOCAL => $network->local_price,

                    BookingTypeEnum::FOREIGN => $network->foreign_price,
                };

                if ($monthlySlidePrice === null) {
                    throw new LogicException("Price is missing for LED network {$network->id}.");
                }

                $totalPrice = round(
                    (float) $monthlySlidePrice
                    * $maxSlidesCount
                    * $billableMonths,
                    2
                );

                $screens = $items
                    ->map(function ($item) {
                        $screen = $item->screen;

                        return [
                            'id' => $screen->id,
                            'location' => $screen->location_name,
                            'width' => $screen->width,
                            'height' => $screen->height,
                            'resolution' => "{$screen->width_px}x{$screen->height_px}",
                            'slides_count' => $item->slides->count(),
                        ];
                    })
                    ->values()
                    ->all();

                $networks[] = [
                    'id' => $network->id,
                    'name' => $network->location_name,

                    'governorate' => [
                        'id' => $governorate->id,
                        'name' => $governorate->name,
                    ],

                    'period' => [
                        'start_date' =>  $period->start_date->toDateString(),
                        'end_date' => $period->end_date->toDateString(),
                    ],

                    'screens' => $screens,
                    'total_price' => $totalPrice,
                ];
            }
        }

        return $networks;
    }

    private function buildSummary(array $standalone,array $networks)
    {
        $summary = [];

        foreach ($standalone as $group) {
            foreach ($group['items'] as $item) {
                $governorate = $group['governorate'];
                $period = $item['period'];

                $key = implode('|', [
                    $governorate['id'],
                    $period['start_date'],
                    $period['end_date'],
                ]);

                if (! isset($summary[$key])) {
                    $summary[$key] = [
                        'governorate' => $governorate,
                        'screens_count' => 0,
                        'networks_count' => 0,
                        'period' => $period,
                        'total_price' => 0,
                    ];
                }

                $summary[$key]['screens_count']++;

                $summary[$key]['total_price'] +=
                    $item['price'];
            }
        }

        foreach ($networks as $network) {
            $governorate = $network['governorate'];
            $period = $network['period'];

            $key = implode('|', [
                $governorate['id'],
                $period['start_date'],
                $period['end_date'],
            ]);

            if (! isset($summary[$key])) {
                $summary[$key] = [
                    'governorate' => $governorate,
                    'screens_count' => 0,
                    'networks_count' => 0,
                    'period' => $period,
                    'total_price' => 0,
                ];
            }

            $summary[$key]['networks_count']++;

            $summary[$key]['total_price'] +=
                $network['total_price'];
        }

        return array_values($summary);
    }

}
