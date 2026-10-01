<?php

namespace App\Services\AdvertisingPeriod;

use App\Models\AdvertisingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Collection;

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
        $dayOfYear = now()->dayOfYear;
        $periodNumber = (int) floor(($dayOfYear - 1) / 14) + 1;

        return min($periodNumber, 26);
    }
}