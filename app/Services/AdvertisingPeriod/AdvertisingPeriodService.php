<?php

namespace App\Services\AdvertisingPeriod;

use App\Models\AdvertisingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use App\Enums\DisplayGroupEnum;
use Carbon\CarbonImmutable;

class AdvertisingPeriodService
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {}


    public function getAllWithCurrent(): Collection
    {
        $workingYear = $this->workingYearContext->get();

        $currentPeriodNumber =
            $workingYear === now()->year
                ? $this->getCurrentPeriodNumber()
                : null;

        $periods = AdvertisingPeriod::query()
            ->with([
                'ranges' => fn ($query) => $query
                    ->where('display_group',DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->value),
            ])
            ->orderBy('number')
            ->get();

        $periods->each(function (AdvertisingPeriod $period) use (
            $currentPeriodNumber,
            $workingYear
        ) {
            $period->is_current =
                $currentPeriodNumber !== null
                && $period->number === $currentPeriodNumber;

            $range = $period->ranges->first();

            if ($range === null) {
                $period->damascus_start_date = null;
                $period->damascus_end_date = null;
                return;
            }

            $startDate = CarbonImmutable::create(
                $workingYear,
                $range->start_month,
                $range->start_day,
                0, 0, 0,
                'Asia/Damascus'
            );

            $endDate = CarbonImmutable::create(
                $workingYear,
                $range->end_month,
                $range->end_day,
                0, 0, 0,
                'Asia/Damascus'
            );

            if ($endDate->lessThan($startDate)) {
                $endDate = $endDate->addYear();
            }

            $period->damascus_start_date = $startDate->toDateString();
            $period->damascus_end_date = $endDate->toDateString();
        });

        return $periods;
    }


    public function getCurrentPeriodId(): int
    {
        return AdvertisingPeriod::query()
            ->where('number',$this->getCurrentPeriodNumber())
            ->valueOrFail('id');
    }

    private function getCurrentPeriodNumber(): int
    {
        return $this->getPeriodNumber(now());
    }

    public function getCurrentPeriodForYear(int $year): AdvertisingPeriod
    {
        $today = now();

        $referenceDate = Carbon::create($year,$today->month,1)->startOfDay();
        $referenceDate->day(min($today->day,$referenceDate->daysInMonth));

        return AdvertisingPeriod::query()
            ->where('number',$this->getPeriodNumber($referenceDate))
            ->firstOrFail();
    }

    private function getPeriodNumber(Carbon $date): int
    {
        return min(
            (int) floor(
                ($date->dayOfYear - 1) / 14
            ) + 1,
            26
        );
    }
}