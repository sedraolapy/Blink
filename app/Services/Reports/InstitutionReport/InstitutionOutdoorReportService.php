<?php

namespace App\Services\Reports\InstitutionReport;

use App\Models\ExternalBookingItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InstitutionOutdoorReportService
{
    public function buildReport(int $year,int $month,string $outdoorType)
    {
        $startDate = CarbonImmutable::create(
            $year, $month, 1, 0, 0, 0, 'Asia/Damascus'
        );

        $endDate = $startDate->endOfMonth();

        $bookingItems = ExternalBookingItem::query()
            ->confirmedForYear($year)
            ->whereHas('asset', function (Builder $query) use ($outdoorType) {
                $query
                    ->where('type', $outdoorType)
                    ->whereHas('area.governorate');
            })
            ->whereHas('period', function (Builder $query) use (
                $startDate,
                $endDate
            ) {
                $query
                    ->whereDate('start_date','<=',$endDate->toDateString())
                    ->whereDate('end_date','>=',$startDate->toDateString());
            })
            ->with([
                'asset.area.governorate',
                'period.externalBookingType.externalBooking.booking.customer',
            ])
            ->orderBy('id')
            ->get()
            ->unique(function (ExternalBookingItem $item) {
                $bookingId = $item
                    ->period
                    ->externalBookingType
                    ->externalBooking
                    ->booking_id;

                return $bookingId . ':' . $item->external_asset_id;
            })
            ->values();

        $monthData = [
            'number' => $month,
            'year' => $year,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];

        if (in_array($outdoorType, [
            'mural',
            'rooftop',
            'unipole',
        ], true)) {
            $items = $bookingItems
                ->sortBy(fn (ExternalBookingItem $item) =>
                    sprintf(
                        '%010d-%010d',
                        $item->asset->area->governorate_id,
                        $item->external_asset_id
                    )
                )
                ->map(fn (ExternalBookingItem $item) =>
                    $this->formatItem($item, $outdoorType, true)
                )
                ->values()
                ->all();

            return [
                'month' => $monthData,
                'items' => $items,
                'summary' => [
                    'total_area' => $this->sumArea($items),
                ],
            ];
        }

        $sections = $bookingItems
            ->groupBy(
                fn (ExternalBookingItem $item) =>
                    $item->asset->area->governorate_id
            )
            ->sortKeys()
            ->map(function (
                Collection $cityItems
            ) use ($outdoorType) {
                $governorate = $cityItems
                    ->first()
                    ->asset
                    ->area
                    ->governorate;

                $items = $cityItems
                    ->sortBy('external_asset_id')
                    ->map(fn (ExternalBookingItem $item) =>
                        $this->formatItem($item, $outdoorType, false)
                    )
                    ->values()
                    ->all();

                return [
                    'city' => [
                        'id' => (int) $governorate->id,
                        'name' => $governorate->name,
                    ],
                    'items' => $items,
                    'summary' => [
                        'total_area' => $this->sumArea($items),
                    ],
                ];
            })
            ->values()
            ->all();

        $report = [
            'month' => $monthData,
            'sections' => $sections,
        ];

        if ($outdoorType === 'tunnel') {
            $report['summary'] = [
                'total_area' => round(
                    collect($sections)->sum(
                        fn (array $section) =>
                            $section['summary']['total_area']
                    ),
                    2
                ),
            ];
        }

        return $report;
    }

    private function formatItem(ExternalBookingItem $item,string $outdoorType,bool $includeCity)
    {
        $asset = $item->asset;

        $booking = $item
            ->period
            ->externalBookingType
            ->externalBooking
            ->booking;

        $width = (float) $asset->width;
        $height = (float) $asset->height;
        $area = round($width * $height, 2);
        $data = [];

        if ($includeCity) {
            $data['city'] = [
                'id' => (int) $asset->area->governorate->id,
                'name' => $asset->area->governorate->name,
            ];
        }

        if ($outdoorType === 'unipole') {
            $data['connecting_road'] = $asset->area->name;
        }

        $data['location'] = $asset->location_name;

        $data['customer'] = [
            'id' => (int) $booking->customer->id,
            'name' => $booking->customer->name,
        ];

        if (in_array($outdoorType, [
            'tunnel',
            'unipole',
        ], true)) {
            $data['dimensions'] =
                $this->formatDimension($width)
                . '*'
                . $this->formatDimension($height);
        } else {
            $data['width_m'] = $width;
            $data['height_m'] = $height;
        }

        $data['total_area'] = $area;

        return $data;
    }

    private function formatDimension(float $value): string
    {
        return rtrim(
            rtrim(number_format($value, 2, '.', ''), '0'),
            '.'
        );
    }

    private function sumArea(array $items): float
    {
        return round(
            collect($items)->sum('total_area'),
            2
        );
    }
}
