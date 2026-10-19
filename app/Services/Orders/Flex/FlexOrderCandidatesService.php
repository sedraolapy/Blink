<?php

namespace App\Services\Orders\Flex;

use App\Enums\BookingItemStatusEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBookingItem;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;

class FlexOrderCandidatesService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function getCandidates(int $periodId, ?string $type = null): array
    {
        $year = $this->workingYearContext->get();

        $period = AdvertisingPeriod::query()
            ->findOrFail($periodId);

        $currentItems = FlexBookingItem::query()
            ->forAdvertisingPeriod($period->id)
            ->confirmedForYear($year)
            ->where(
                'status',
                BookingItemStatusEnum::BOOKED->value
            )
            ->where(function (Builder $query) {
                $query
                    ->whereNull('order_status')
                    ->orWhere('order_status', 'unclassified');
            })
            ->with([
                'billboard.area.governorate',
                'period.flexBooking.booking.customer',
                'design',
            ])
            ->orderBy('flex_billboard_id')
            ->get();

        $periodNumber = (int) $period->number;

        $previousNumber = $periodNumber === 1
            ? 26
            : $periodNumber - 1;

        $previousYear = $periodNumber === 1
            ? $year - 1
            : $year;

        $previousPeriodId = AdvertisingPeriod::query()
            ->where('number', $previousNumber)
            ->value('id');

        $previousItems = collect();

        if ($previousPeriodId !== null && $currentItems->isNotEmpty()) {
            $previousItems = FlexBookingItem::query()
                ->forAdvertisingPeriod((int) $previousPeriodId)
                ->confirmedForYear($previousYear)
                ->where(
                    'status',
                    BookingItemStatusEnum::BOOKED->value
                )
                ->whereIn(
                    'flex_billboard_id',
                    $currentItems->pluck('flex_billboard_id')
                )
                ->get()
                ->keyBy('flex_billboard_id');
        }

        $items = $currentItems->map(function (FlexBookingItem $item) use ($previousItems)
        {

            $billboard = $item->billboard;
            $area = $billboard->area;
            $governorate = $area?->governorate;
            $customer = $item->period->flexBooking->booking->customer;
            $previousItem = $previousItems->get($item->flex_billboard_id);

            return [
                'flex' => [
                    'id' => $billboard->id,
                    'code' => $billboard->code,
                    'name' => $billboard->location_name,
                    'area' => $area?->name,
                    'governorate' => $governorate
                        ? [
                            'id' => $governorate->id,
                            'name' => $governorate->name,
                        ]
                        : null,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ],
                'design_name' => $item->design?->name,
                'classification' => $this->classify($item,$previousItem),
            ];

        })
        ->filter(
            fn (array $item) =>
                $type === null
                || in_array(
                    $item['classification'],
                    [$type, 'unclassified', 'no_design'],
                    true
                )
        )
        ->values()
        ->all();


        return [
            'period' => [
                'id' => $period->id,
                'number' => $periodNumber,
            ],
            'items' => $items,
        ];
    }

    private function classify(FlexBookingItem $item,?FlexBookingItem $previousItem)
    {

        if ($item->design_id === null) {
            return 'no_design';
        }

        if ($item->order_status === 'unclassified') {
            return 'unclassified';
        }

        if (
            $previousItem !== null
            && $previousItem->design_id !== null
            && (int) $previousItem->design_id
                === (int) $item->design_id
        ) {
            return 'extension';
        }

        return 'installation';
    }
}
