<?php

namespace App\Services\Reports;

use App\Enums\DisplayGroupEnum;
use App\Models\AdvertisingPeriod;
use Carbon\CarbonImmutable;

class CampaignReportPeriodService
{
    private const TIMEZONE = 'Asia/Damascus';

    private const DAYS_BEFORE_START = 3;
    private const DAYS_BEFORE_END = 3;

    public function getAvailablePeriod(int $workingYear,DisplayGroupEnum $displayGroup = DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA,?CarbonImmutable $referenceDate = null): ?array
    {
        $today = ($referenceDate ?? CarbonImmutable::now(self::TIMEZONE))
            ->setTimezone(self::TIMEZONE)
            ->startOfDay();

        if ($workingYear !== $today->year) {
            return null;
        }

        $periods = AdvertisingPeriod::query()
            ->with([
                'ranges' => fn ($query) => $query->where('display_group',$displayGroup->value),
            ])
            ->orderBy('number')
            ->get();

        foreach ([$workingYear, $workingYear + 1] as $year) {
            foreach ($periods as $period) {
                $range = $period->ranges->first();

                if (! $range) {
                    continue;
                }

                $startDate = CarbonImmutable::create(
                    $year,
                    $range->start_month,
                    $range->start_day,
                    0,
                    0,
                    0,
                    self::TIMEZONE
                );

                $endDate = CarbonImmutable::create(
                    $year,
                    $range->end_month,
                    $range->end_day,
                    0,
                    0,
                    0,
                    self::TIMEZONE
                );

                if ($endDate->lessThan($startDate)) {
                    $endDate = $endDate->addYear();
                }

                $openDate = $startDate->subDays(self::DAYS_BEFORE_START);
                $closeDate = $endDate->subDays(self::DAYS_BEFORE_END);

                if (
                    $today->greaterThanOrEqualTo($openDate)
                    && $today->lessThanOrEqualTo($closeDate)
                ) {
                    return [
                        'id' => $period->id,
                        'number' => (int) $period->number,
                        'year' => $year,
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                    ];
                }
            }
        }

        return null;
    }
}
