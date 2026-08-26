<?php

namespace Database\Seeders;

use App\Enums\DisplayGroupEnum;
use App\Models\AdvertisingPeriod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdvertisingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        // Non-leap reference year because we only store month/day.
        $referenceYear = 2025;

        $groups = [
            DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->value => Carbon::create(
                $referenceYear,
                1,
                1
            ),

            DisplayGroupEnum::OTHERS->value => Carbon::create(
                $referenceYear,
                1,
                3
            ),
        ];

        for ($number = 1; $number <= 26; $number++) {

            $period = AdvertisingPeriod::updateOrCreate(
                ['number' => $number]
            );

            foreach ($groups as $displayGroup => $firstStartDate) {

                $startDate = $firstStartDate
                    ->copy()
                    ->addDays(($number - 1) * 15);

                $endDate = $startDate
                    ->copy()
                    ->addDays(13);

                // Last range must not pass December 31.
                $lastDayOfYear = Carbon::create(
                    $referenceYear,
                    12,
                    31
                );

                if ($endDate->greaterThan($lastDayOfYear)) {
                    $endDate = $lastDayOfYear;
                }

                $period->ranges()->updateOrCreate(
                    [
                        'display_group' => $displayGroup,
                    ],
                    [
                        'start_month' => $startDate->month,
                        'start_day' => $startDate->day,
                        'end_month' => $endDate->month,
                        'end_day' => $endDate->day,
                    ]
                );
            }
        }
    }
}