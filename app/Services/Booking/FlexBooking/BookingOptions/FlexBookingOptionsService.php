<?php

namespace App\Services\Booking\FlexBooking\BookingOptions;

use App\Enums\BookingItemStatusEnum;
use App\Models\AdvertisingPeriod;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class FlexBookingOptionsService
{
    public function getPeriods(?int $bookingId = null): Collection
    {
        $periods = AdvertisingPeriod::query()
            ->select([
                'id',
                'number',
            ])
            ->with([
                'ranges' => fn ($query) => $query->select([
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
                        $selectedPeriodIds->contains($period->id)
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
            ->where('flex_booking_id', $flexBookingId)
            ->where('year', now()->year)
            ->pluck('advertising_period_id');
    }

    private function getOccupiedCounts(SupportCollection $periodIds, ?int $bookingId): SupportCollection
    {
        return FlexBookingItem::query()
            ->whereIn(
                'status',
                [
                    BookingItemStatusEnum::BOOKED->value,
                    BookingItemStatusEnum::UNCONFIRMED->value,
                ]
            )
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
                            fn ($query) => $query->whereHas(
                                'flexBooking',
                                fn ($query) => $query->where(
                                    'booking_id',
                                    '!=',
                                    $bookingId
                                )
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
                fn (SupportCollection $items) =>
                    $items
                        ->pluck('flex_billboard_id')
                        ->unique()
                        ->count()
            );
    }

    private function formatMonthDay(?int $month, ?int $day)
    {
        if ($month === null || $day === null) {
            return null;
        }

        return sprintf('%02d-%02d',$month,$day);
    }
}