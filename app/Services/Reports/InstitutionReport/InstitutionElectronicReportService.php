<?php

namespace App\Services\Reports\InstitutionReport;

use App\Models\LedScreen;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class InstitutionElectronicReportService
{
    public function buildReport(int $year, int $month): array
    {
        $startDate = CarbonImmutable::create(
            $year,
            $month,
            1,
            0,
            0,
            0,
            'Asia/Damascus'
        );

        $endDate = $startDate->endOfMonth();

        $screens = LedScreen::query()
            ->whereHas('area.governorate')
            ->whereHas(
                'bookingItems',
                function (Builder $query) use (
                    $year,
                    $startDate,
                    $endDate
                ) {
                    $query
                        ->confirmedForYear($year)
                        ->whereHas(
                            'period',
                            function (Builder $periodQuery) use ($startDate,$endDate)
                            {
                                $periodQuery
                                    ->whereDate('start_date','<=',$endDate->toDateString())
                                    ->whereDate('end_date','>=',$startDate->toDateString());
                            }
                        );
                }
            )
            ->with('area.governorate')
            ->orderBy('id')
            ->get();

        $sections = $screens
            ->groupBy(
                fn (LedScreen $screen) => $screen->area->governorate_id
            )
            ->sortKeys()
            ->map(function ($cityScreens) {

                $governorate = $cityScreens
                    ->first()
                    ->area
                    ->governorate;

                $items = $cityScreens
                    ->map(function (LedScreen $screen) {

                        $width = (float) $screen->width;
                        $height = (float) $screen->height;

                        return [
                            'id' => (int) $screen->id,
                            'location' => $screen->location_name,
                            'width_m' => $width,
                            'width_px' => (int) $screen->width_px,
                            'height_m' => $height,
                            'height_px' => (int) $screen->height_px,
                            'area' => round(
                                $width * $height,
                                2
                            ),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'city' => [
                        'id' => (int) $governorate->id,
                        'name' => $governorate->name,
                    ],
                    'items' => $items,
                    'summary' => [
                        'total_area' => round(
                            collect($items)->sum('area'),
                            2
                        ),
                    ],
                ];
            })
            ->values()
            ->all();

        return [
            'month' => [
                'number' => $month,
                'year' => $year,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],

            'sections' => $sections,
        ];
    }
}
