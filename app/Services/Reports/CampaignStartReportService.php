<?php

namespace App\Services\Reports;

use App\Enums\BookingItemStatusEnum;
use App\Enums\DisplayGroupEnum;
use App\Models\AdvertisingPeriodRange;
use App\Models\FlexBookingItem;
use App\Models\Governorate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CampaignStartReportService
{
    public function getIssuedItems(int $year,int $periodId): Collection
    {
        return FlexBookingItem::query()
            ->forAdvertisingPeriod($periodId)
            ->confirmedForYear($year)
            ->where('status',BookingItemStatusEnum::BOOKED->value)
            ->whereIn('order_status', [
                'installation',
                'extension',
            ])
            ->with([
                'period.flexBooking.booking.customer',
                'billboard.area.governorate',
            ])
            ->get();
    }

    public function buildItems(Collection $issuedItems): array
    {
        $governorates = Governorate::query()
            ->with('flexPriceGroup')
            ->get()
            ->keyBy('id');

        return $issuedItems
            ->groupBy(function ($item) {
                $customerId = $item->period
                    ->flexBooking
                    ->booking
                    ->customer_id;

                return $customerId . ':' . $item->order_status;
            })
            ->map(function ($items) use ($governorates) {
                $first = $items->first();

                $customer = $first->period
                    ->flexBooking
                    ->booking
                    ->customer;

                $counts = $items
                    ->groupBy(
                        fn ($item) => $item->billboard->area->governorate_id
                    )
                    ->map(function ($rows, $governorateId) use ($governorates) {

                        $groupSize = (int) (
                            $governorates
                                ->get($governorateId)
                                ?->flexPriceGroup
                                ?->billboards_count ?? 0
                        );

                        if ($groupSize <= 0) {
                            throw new \RuntimeException(
                                "Missing flex price group for governorate {$governorateId}"
                            );
                        }

                        $boards = $rows->count();

                        return [
                            'boards' => $boards,
                            'networks' => (int) ceil(
                                $boards / $groupSize
                            ),
                        ];
                    })
                    ->all();

                return [
                    'customer' => $customer,
                    'classification' => $first->order_status,
                    'counts' => $counts,
                ];
            })
            ->sortBy(
                fn ($row) => sprintf(
                    '%010d-%s',
                    $row['customer']->id,
                    $row['classification']
                )
            )
            ->values()
            ->all();
    }


    public function buildSummary(Collection $issuedItems): array
    {
        $governorates = Governorate::query()
            ->with('flexPriceGroup')
            ->orderBy('id')
            ->get();

        $inventoryCounts = DB::table('flex_billboards as fb')
            ->join('areas as a', 'a.id', '=', 'fb.area_id')
            ->select('a.governorate_id')
            ->selectRaw('COUNT(*) as boards_count')
            ->groupBy('a.governorate_id')
            ->pluck('boards_count', 'governorate_id');

        $items = collect($this->buildItems($issuedItems))
            ->groupBy('classification');

        $summary = [
            'extension' => [],
            'installation' => [],
            'booked' => [],
            'inventory' => [],
            'remaining' => [],
        ];

        foreach ($governorates as $governorate) {
            $id = (int) $governorate->id;

            $groupSize = (int) (
                $governorate->flexPriceGroup?->billboards_count ?? 0
            );

            if ($groupSize <= 0) {
                throw new \RuntimeException(
                    "Missing flex price group for governorate {$id}"
                );
            }

            $getCounts = function (string $type) use ($items, $id) {
                $rows = $items->get($type, collect());

                return [
                    'boards' => (int) $rows->sum(
                        fn ($row) =>
                            $row['counts'][$id]['boards'] ?? 0
                    ),
                    'networks' => (int) $rows->sum(
                        fn ($row) =>
                            $row['counts'][$id]['networks'] ?? 0
                    ),
                ];
            };

            $extension = $getCounts('extension');
            $installation = $getCounts('installation');

            $booked = [
                'boards' => $extension['boards'] + $installation['boards'],
                'networks' => $extension['networks'] + $installation['networks'],
            ];

            $inventoryBoards = (int) $inventoryCounts->get($id, 0);

            $inventory = [
                'boards' => $inventoryBoards,
                'networks' => (int) ceil($inventoryBoards / $groupSize),
            ];

            if ($booked['boards'] > $inventoryBoards) {
                throw new \RuntimeException(
                    "Booked boards exceed inventory in governorate {$id}"
                );
            }

            $remainingBoards = $inventoryBoards - $booked['boards'];
            $remaining = [
                'boards' => $remainingBoards,
                'networks' => (int) ceil($remainingBoards / $groupSize),
            ];

            $summary['extension'][$id] = $extension;
            $summary['installation'][$id] = $installation;
            $summary['booked'][$id] = $booked;
            $summary['inventory'][$id] = $inventory;
            $summary['remaining'][$id] = $remaining;
        }

        return $summary;
    }

    public function getGovernorates(): Collection
    {
        return Governorate::query()
            ->orderBy('id')
            ->get();
    }

}
