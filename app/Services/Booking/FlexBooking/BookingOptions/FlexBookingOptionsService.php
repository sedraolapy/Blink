<?php

namespace App\Services\Booking\FlexBooking\BookingOptions;

use App\Enums\BookingStatusEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\Governorate;
use Illuminate\Support\Collection;

class FlexBookingOptionsService
{
    public function getPeriods(?int $bookingId = null)
    {
        $periods = AdvertisingPeriod::query()
            ->select([
                'id',
                'number',
            ])
            ->with([
                'ranges' => fn ($query) =>
                    $query->select([
                        'id',
                        'advertising_period_id',
                        'display_group',
                        'start_month',
                        'start_day',
                    ]),
            ])
            ->orderBy('number')
            ->get();

        $selectedPeriodIds = $bookingId !== null
            ? $this->getSelectedPeriodIds($bookingId)
            : collect();

        $totalBillboards = FlexBillboard::query()->count();

        $occupiedCounts = $this->getOccupiedCounts(
            $periods->pluck('id'),
            $bookingId
        );

        $periods->each(
            function (AdvertisingPeriod $period) use (
                $bookingId,
                $selectedPeriodIds,
                $occupiedCounts,
                $totalBillboards
            ) {
                $damascusRange = $period
                    ->ranges
                    ->firstWhere(
                        'display_group',
                        'damascus_daraa_sweida'
                    );

                $otherRange = $period
                    ->ranges
                    ->firstWhere(
                        'display_group',
                        'others'
                    );

                $period->setAttribute(
                    'damascus_daraa_sweida_start_day',
                    $this->formatMonthDay(
                        $damascusRange?->start_month,
                        $damascusRange?->start_day
                    )
                );

                $period->setAttribute(
                    'other_governorates_start_day',
                    $this->formatMonthDay(
                        $otherRange?->start_month,
                        $otherRange?->start_day
                    )
                );

                $occupiedCount = $occupiedCounts->get(
                    $period->id,
                    0
                );

                $period->setAttribute(
                    'is_available',
                    $occupiedCount < $totalBillboards
                );

                if ($bookingId !== null) {
                    $period->setAttribute(
                        'was_selected',
                        $selectedPeriodIds->contains(
                            $period->id
                        )
                    );
                }
            }
        );

        return $periods;
    }

    private function getSelectedPeriodIds(int $bookingId)
    {
        $flexBookingId = FlexBooking::query()
            ->where('booking_id', $bookingId)
            ->value('id');

        if ($flexBookingId === null) {
            return collect();
        }

        return FlexBookingPeriod::query()
            ->where(
                'flex_booking_id',
                $flexBookingId
            )
            ->where('year', now()->year)
            ->pluck('advertising_period_id');
    }

    private function getOccupiedCounts(Collection $periodIds,?int $bookingId)
    {
        return FlexBookingItem::query()
            ->whereHas(
                'period',
                function ($query) use (
                    $periodIds,
                    $bookingId
                ) {
                    $query
                        ->where('year', now()->year)
                        ->whereIn(
                            'advertising_period_id',
                            $periodIds
                        )
                        ->when(
                            $bookingId !== null,
                            fn ($query) =>
                                $query->whereHas(
                                    'flexBooking',
                                    fn ($query) =>
                                        $query->where(
                                            'booking_id',
                                            '!=',
                                            $bookingId
                                        )
                                )
                        )
                        ->whereHas(
                            'flexBooking.booking',
                            fn ($query) =>
                                $query->whereIn(
                                    'status',
                                    [
                                        BookingStatusEnum::CONFIRMED->value,
                                        BookingStatusEnum::UNCONFIRMED->value,
                                    ]
                                )
                        );
                }
            )
            ->with([
                'period:id,advertising_period_id',
            ])
            ->get([
                'id',
                'flex_booking_period_id',
                'flex_billboard_id',
            ])
            ->groupBy(
                fn (FlexBookingItem $item) =>
                    $item
                        ->period
                        ->advertising_period_id
            )
            ->map(
                fn (Collection $items) =>
                    $items
                        ->pluck('flex_billboard_id')
                        ->unique()
                        ->count()
            );
    }

    private function formatMonthDay(?int $month,?int $day)
    {
        if ($month === null || $day === null) {
            return null;
        }

        return sprintf('%02d-%02d',$month,$day);
    }

    public function getAvailableAssets(array $data): array
    {
        $bookingId = isset($data['booking_id'])
            ? (int) $data['booking_id']
            : null;

        $governorateId = (int) $data['governorate_id'];

        $periodIds = collect($data['period_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $year = now()->year;

        $governorate = Governorate::query()
            ->findOrFail($governorateId);

        $periods = AdvertisingPeriod::query()
            ->whereIn('id', $periodIds)
            ->orderBy('number')
            ->get([
                'id',
                'number',
            ]);

        $billboards = FlexBillboard::query()
            ->governorate($governorateId)
            ->search($data['search'] ?? null)
            ->with([
                'area:id,governorate_id,name',
            ])
            ->orderBy('id')
            ->get([
                'id',
                'code',
                'area_id',
                'location_name',
                'width',
                'height',
            ]);

        $occupiedByPeriod = FlexBookingItem::query()
            ->whereHas(
                'period',
                fn ($query) =>
                    $query
                        ->where('year', $year)
                        ->whereIn(
                            'advertising_period_id',
                            $periodIds
                        )
            )
            ->whereHas(
                'period.flexBooking.booking',
                function ($query) use ($bookingId) {
                    $query
                        ->whereIn(
                            'status',
                            [
                                BookingStatusEnum::CONFIRMED->value,
                                BookingStatusEnum::UNCONFIRMED->value,
                            ]
                        )
                        ->when(
                            $bookingId !== null,
                            fn ($query) =>
                                $query->where(
                                    'id',
                                    '!=',
                                    $bookingId
                                )
                        );
                }
            )
            ->with([
                'period:id,advertising_period_id',
            ])
            ->get([
                'id',
                'flex_booking_period_id',
                'flex_billboard_id',
            ])
            ->groupBy(
                fn (FlexBookingItem $item) =>
                    $item->period->advertising_period_id
            )
            ->map(
                fn ($items) =>
                    $items
                        ->pluck('flex_billboard_id')
                        ->unique()
            );

        $periodsData = $periods->map(
            function (AdvertisingPeriod $period) use (
                $billboards,
                $occupiedByPeriod
            ) {
                $occupiedBillboardIds = $occupiedByPeriod->get(
                    $period->id,
                    collect()
                );

                return [
                    'period' => $period,

                    'items' => $billboards
                        ->reject(
                            fn (FlexBillboard $billboard) =>
                                $occupiedBillboardIds->contains(
                                    $billboard->id
                                )
                        )
                        ->values(),
                ];
            }
        );

        return [
            'governorate' => $governorate,
            'periods' => $periodsData,
        ];
    }
}