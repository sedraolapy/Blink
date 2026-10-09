<?php

namespace App\Services\AdvertisingPeriod;

use App\Models\AdvertisingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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
            ->orderBy('number')
            ->get();

        $periods->each(
            function (AdvertisingPeriod $period) use ($currentPeriodNumber)
            {
                $period->is_current =
                    $currentPeriodNumber !== null
                    && $period->number === $currentPeriodNumber;
            }
        );

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

        $referenceDate = Carbon::create(
            $year,
            $today->month,
            1
        )->startOfDay();

        $referenceDate->day(
            min(
                $today->day,
                $referenceDate->daysInMonth
            )
        );

        return AdvertisingPeriod::query()
            ->where(
                'number',
                $this->getPeriodNumber($referenceDate)
            )
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