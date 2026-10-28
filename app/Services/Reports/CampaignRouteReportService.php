<?php

namespace App\Services\Reports;

use App\Enums\BookingItemStatusEnum;
use App\Enums\DisplayGroupEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBookingItem;
use App\Models\Governorate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LogicException;

class CampaignRouteReportService
{
    private const TIMEZONE = 'Asia/Damascus';

    public function buildReport(array $currentPeriod,?array $governorateIds = null): array
    {
        $currentYear = (int) $currentPeriod['year'];
        $currentPeriodId = (int) $currentPeriod['id'];
        $currentNumber = (int) $currentPeriod['number'];

        $previousNumber = $currentNumber === 1
            ? 26
            : $currentNumber - 1;

        $previousYear = $currentNumber === 1
            ? $currentYear - 1
            : $currentYear;

        $previousPeriod = AdvertisingPeriod::query()
            ->where('number', $previousNumber)
            ->firstOrFail();

        $previousPeriodData = $this->periodData(
            $previousPeriod,
            $previousYear
        );

        $previousItems = $this->getBookedItems(
            $previousPeriod->id,
            $previousYear,
            $governorateIds
        );

        $currentItems = $this->getBookedItems(
            $currentPeriodId,
            $currentYear,
            $governorateIds
        );

        $boardIds = $previousItems
            ->keys()
            ->merge($currentItems->keys())
            ->unique()
            ->values();

        $items = $boardIds->map(function ($boardId) use (
            $previousItems,
            $currentItems
        ) {
            $previous = $previousItems->get($boardId);
            $current = $currentItems->get($boardId);

            $board = ($current ?? $previous)->billboard;

            return [
                'governorate' => $board->area?->governorate,
                'board' => $board,
                'previous_item' => $previous,
                'current_item' => $current,
                'required_action' => $this->classify(
                    $previous,
                    $current
                ),
            ];
        })
        ->sortBy(fn (array $item) => sprintf(
            '%06d-%012d',
            $item['governorate']?->id ?? 0,
            $item['board']->id
        ))
        ->values();

        $governorates = Governorate::query()
            ->when(
                $governorateIds !== null,
                fn (Builder $query) =>
                    $query->whereIn('id', $governorateIds)
            )
            ->orderBy('id')
            ->get();

        return [
            'previous_period' => $previousPeriodData,
            'current_period' => $currentPeriod,
            'governorates' => $governorates,
            'items' => $items,
            'summary' => $this->buildSummary($items),
        ];
    }

    private function getBookedItems(int $periodId,int $year,?array $governorateIds): Collection
    {
        $items = FlexBookingItem::query()
            ->forAdvertisingPeriod($periodId)
            ->confirmedForYear($year)
            ->where('status',BookingItemStatusEnum::BOOKED->value)
            ->when(
                $governorateIds !== null,
                fn (Builder $query) =>
                    $query->whereHas(
                        'billboard.area',
                        fn (Builder $area) =>
                            $area->whereIn('governorate_id',$governorateIds)
                    )
            )
            ->with([
                'billboard.area.governorate',
                'period.flexBooking.booking.customer',
                'design',
            ])
            ->orderBy('flex_billboard_id')
            ->get();

        if (
            $items->pluck('flex_billboard_id')
                ->unique()
                ->count() !== $items->count()
        ) {
            throw new LogicException(
                "Duplicate confirmed flex bookings found "
                . "in period {$periodId}, year {$year}."
            );
        }

        return $items->keyBy('flex_billboard_id');
    }

    private function classify(?FlexBookingItem $previous,?FlexBookingItem $current): string
    {
        if (
            $current === null
            || $current->design_id === null
        ) {
            return 'no_order';
        }

        if (
            $previous === null
            || $previous->design_id === null
        ) {
            return 'installation';
        }

        $previousCustomerId = (int) $previous
            ->period
            ->flexBooking
            ->booking
            ->customer_id;

        $currentCustomerId = (int) $current
            ->period
            ->flexBooking
            ->booking
            ->customer_id;

        if (
            $previousCustomerId === $currentCustomerId
            && (int) $previous->design_id
                === (int) $current->design_id
        ) {
            return 'extension';
        }
        return 'installation';
    }

    private function buildSummary(Collection $items): array
    {
        $customerIds = $items
            ->flatMap(function (array $item) {
                return [
                    $item['previous_item']
                        ?->period
                        ?->flexBooking
                        ?->booking
                        ?->customer_id,

                    $item['current_item']
                        ?->period
                        ?->flexBooking
                        ?->booking
                        ?->customer_id,
                ];
            })
            ->filter(fn ($id) => $id !== null)
            ->unique();

        return [
            'boards_count' => $items->count(),
            'installation_count' => $items->where('required_action', 'installation')->count(),
            'extension_count' => $items->where('required_action', 'extension')->count(),
            'customers_count' => $customerIds->count(),
        ];
    }

    private function periodData(AdvertisingPeriod $period,int $year)
    {
        $range = $period->ranges()
            ->where('display_group',DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA->value)
            ->firstOrFail();

        $startDate = CarbonImmutable::create(
            $year,
            $range->start_month,
            $range->start_day,
            0, 0, 0,
            self::TIMEZONE
        );

        $endDate = CarbonImmutable::create(
            $year,
            $range->end_month,
            $range->end_day,
            0, 0, 0,
            self::TIMEZONE
        );

        if ($endDate->lessThan($startDate)) {
            $endDate = $endDate->addYear();
        }

        return [
            'id' => $period->id,
            'number' => (int) $period->number,
            'year' => $year,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }
}
