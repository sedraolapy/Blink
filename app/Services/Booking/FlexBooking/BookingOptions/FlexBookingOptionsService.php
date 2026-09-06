<?php

namespace App\Services\Booking\FlexBooking\BookingOptions;

use App\Models\AdvertisingPeriod;
use Illuminate\Database\Eloquent\Collection;

class FlexBookingOptionsService
{
    public function getPeriods(?int $bookingId = null): Collection
    {
        $query = AdvertisingPeriod::query()
            ->leftJoin(
                'advertising_period_ranges as damascus_ranges',
                function ($join) {
                    $join
                        ->on(
                            'damascus_ranges.advertising_period_id',
                            '=',
                            'advertising_periods.id'
                        )
                        ->where(
                            'damascus_ranges.display_group',
                            'damascus_daraa_sweida'
                        );
                }
            )
            ->leftJoin(
                'advertising_period_ranges as other_ranges',
                function ($join) {
                    $join
                        ->on(
                            'other_ranges.advertising_period_id',
                            '=',
                            'advertising_periods.id'
                        )
                        ->where(
                            'other_ranges.display_group',
                            'others'
                        );
                }
            )
            ->select([
                'advertising_periods.id',
                'advertising_periods.number',

                'damascus_ranges.start_day as damascus_daraa_sweida_start_day',

                'other_ranges.start_day as other_governorates_start_day',
            ]);

        if ($bookingId === null) {
            $query->selectRaw('1 as is_available');
        } else {
            $query->selectRaw(
                '
                NOT EXISTS (
                    SELECT 1
                    FROM flex_booking_periods AS fbp
                    INNER JOIN flex_bookings AS fb
                        ON fb.id = fbp.flex_booking_id
                    WHERE
                        fbp.advertising_period_id = advertising_periods.id
                        AND fbp.year = ?
                        AND fb.booking_id = ?
                ) AS is_available
                ',
                [
                    now()->year,
                    $bookingId,
                ]
            );
        }

        return $query
            ->orderBy('advertising_periods.number')
            ->get();
    }
}