<?php

namespace App\Services\Reports\InstitutionReport;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\DisplayGroupEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBillboard;
use App\Models\FlexBookingItem;
use App\Models\Governorate;
use App\Models\InstitutionFlexGroup;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class InstitutionFlexReportService
{
    public function getGroups(string $region): array
    {
        $damascusId = Governorate::query()
            ->where('name->en', 'Damascus')
            ->firstOrFail()
            ->id;

        $groups = InstitutionFlexGroup::query()
            ->whereHas(
                'billboards.area',
                function (Builder $query) use ($region, $damascusId) {
                    $query->where('governorate_id',$region === 'damascus' ? '=' : '!=',$damascusId);
                }
            )
            ->get()
            ->sortBy(
                fn (InstitutionFlexGroup $group) =>
                    $group->getTranslation('name', app()->getLocale())
            )
            ->values()
            ->map(function (InstitutionFlexGroup $group) use ($region) {
                return [
                    'id' => $group->id,
                    'label' => __(
                        "reports.institution_flex.{$region}_group",
                        ['name' => $group->name]
                    ),
                ];
            })
            ->all();

        return [
            'region' => $region,
            'items' => array_merge(
                [[
                    'id' => 0,
                    'label' => __("reports.institution_flex.{$region}_default"),
                ]],
                $groups
            ),
        ];
    }


    public function getIssuedItems(int $year,int $periodId,string $region,int $groupId)
    {
        $damascusId = Governorate::query()
            ->where('name->en', 'Damascus')
            ->firstOrFail()
            ->id;

        return FlexBookingItem::query()
            ->forAdvertisingPeriod($periodId)
            ->confirmedForYear($year)
            ->where('status',BookingItemStatusEnum::BOOKED->value)
            ->whereIn('order_status', [
                'installation',
                'extension',
            ])
            ->whereHas(
                'billboard',
                function (Builder $query) use (
                    $region,
                    $groupId,
                    $damascusId
                ) {
                    $query->whereHas(
                        'area',
                        fn (Builder $query) => $query->where(
                            'governorate_id',
                            $region === 'damascus' ? '=' : '!=',
                            $damascusId
                        )
                    );

                    if ($groupId === 0) {
                        $query->whereNull('institution_flex_group_id');
                    } else {
                        $query->where('institution_flex_group_id',$groupId);
                    }
                }
            )
            ->with([
                'period.flexBooking.booking.customer',
                'billboard.area.governorate',
            ])
            ->get();
    }


    public function buildBookedSections(Collection $issuedItems): array
    {
        return $issuedItems
            ->groupBy(
                fn (FlexBookingItem $item) => $item->billboard->area->governorate_id
            )
            ->sortKeys()
            ->map(function ($governorateItems) {

                $governorate = $governorateItems
                    ->first()
                    ->billboard
                    ->area
                    ->governorate;

                $customers = $governorateItems
                    ->groupBy(
                        fn (FlexBookingItem $item) =>
                            $item->period
                                ->flexBooking
                                ->booking
                                ->customer_id
                    )
                    ->sortKeys()
                    ->map(function ($customerItems) {

                        $customer = $customerItems
                            ->first()
                            ->period
                            ->flexBooking
                            ->booking
                            ->customer;

                        $items = $customerItems
                            ->groupBy(function (FlexBookingItem $item) {
                                $billboard = $item->billboard;

                                return $this->formatDimension($billboard->width)
                                    . '*'
                                    . $this->formatDimension($billboard->height);
                            })
                            ->map(function ($dimensionItems, $dimensions) {
                                return [
                                    'dimensions' => $dimensions,
                                    'boards_count' => $dimensionItems->count(),
                                    'total_area' => round(
                                        $dimensionItems->sum(
                                            fn (FlexBookingItem $item) =>
                                                (float) $item->billboard->width
                                                * (float) $item->billboard->height
                                        ),
                                        2
                                    ),
                                ];
                            })
                            ->sortKeys()
                            ->values()
                            ->all();

                        return [
                            'customer' => [
                                'id' => (int) $customer->id,
                                'name' => $customer->name,
                            ],
                            'items' => $items,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'governorate' => [
                        'id' => (int) $governorate->id,
                        'name' => $governorate->name,
                    ],
                    'customers' => $customers,
                    'summary' => [
                        'booked_boards_count' => $governorateItems->count(),
                        'total_area' => round(
                            $governorateItems->sum(
                                fn (FlexBookingItem $item) =>
                                    (float) $item->billboard->width
                                    * (float) $item->billboard->height
                            ),
                            2
                        ),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function formatDimension(float $value): string
    {
        return rtrim(
            rtrim(number_format($value, 2, '.', ''), '0'),
            '.'
        );
    }


    public function addRemainingCounts(array $sections,int $year,int $periodId,int $groupId): array
    {
        if (empty($sections)) {
            return [];
        }

        $remainingCounts = FlexBillboard::query()
            ->join(
                'areas',
                'areas.id',
                '=',
                'flex_billboards.area_id'
            )
            ->when(
                $groupId === 0,
                fn (Builder $query) => $query
                    ->whereNull('flex_billboards.institution_flex_group_id'),
                fn (Builder $query) => $query
                    ->where('flex_billboards.institution_flex_group_id', $groupId)
            )
            ->whereDoesntHave('bookingItems',
                fn (Builder $query) => $query
                    ->forAdvertisingPeriod($periodId)
                    ->whereIn('status', [
                        BookingItemStatusEnum::BOOKED->value,
                        BookingItemStatusEnum::UNCONFIRMED->value,
                    ])
                    ->whereHas(
                        'period.flexBooking.booking',
                        fn (Builder $query) => $query
                            ->where('year', $year)
                            ->whereIn('status', [
                                BookingStatusEnum::CONFIRMED->value,
                                BookingStatusEnum::UNCONFIRMED->value,
                            ])
                    )
            )
            ->select('areas.governorate_id')
            ->selectRaw('COUNT(*) as remaining_boards_count')
            ->groupBy('areas.governorate_id')
            ->pluck('remaining_boards_count', 'governorate_id');

        return array_map(
            function (array $section) use ($remainingCounts): array {
                $governorateId = $section['governorate']['id'];

                $section['summary']['remaining_boards_count'] =
                    (int) $remainingCounts->get($governorateId, 0);

                return $section;
            },
            $sections
        );
    }


    public function buildReport(int $year,int $periodId, string $region,int $groupId)
    {
        $selectedGroup = collect($this->getGroups($region)['items'])->firstWhere('id', $groupId);

        if ($selectedGroup === null) {
            throw ValidationException::withMessages([
                'group_id' => __(
                    'messages.institution_flex_report.invalid_group_region'
                ),
            ]);
        }

        $displayGroup = $region === 'damascus'
            ? DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA
            : DisplayGroupEnum::OTHERS;

        $period = AdvertisingPeriod::query()
            ->with([
                'ranges' => fn ($query) => $query->where(
                    'display_group',
                    $displayGroup->value
                ),
            ])
            ->findOrFail($periodId);

        $range = $period->ranges->firstOrFail();

        $startDate = CarbonImmutable::create(
            $year,
            $range->start_month,
            $range->start_day,
            0, 0, 0,
            'Asia/Damascus'
        );

        $endDate = CarbonImmutable::create(
            $year,
            $range->end_month,
            $range->end_day,
            0, 0, 0,
            'Asia/Damascus'
        );

        if ($endDate->lessThan($startDate)) {
            $endDate = $endDate->addYear();
        }

        $issuedItems = $this->getIssuedItems(
            $year,
            $periodId,
            $region,
            $groupId
        );

        $sections = $this->buildBookedSections($issuedItems);

        $sections = $this->addRemainingCounts(
            $sections,
            $year,
            $periodId,
            $groupId
        );

        $sections = array_map(
            function (array $section) use ($selectedGroup): array {
                $section['label'] = $selectedGroup['label'];

                return $section;
            },
            $sections
        );

        return [
            'period' => [
                'id' => (int) $period->id,
                'number' => (int) $period->number,
                'year' => $year,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'sections' => $sections,
        ];
    }


}
