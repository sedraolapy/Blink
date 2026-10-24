<?php

namespace App\Services\Orders\Electronic;

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Models\LedBookingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Support\Collection;

class ElectronicOrderCustomersService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext,
        private readonly ElectronicOrderEligibilityService $eligibilityService
    ) {}


    public function getCustomers(): array
    {
        $bookings = Booking::query()
            ->where('year', $this->workingYearContext->get())
            ->where('status', BookingStatusEnum::CONFIRMED->value)
            ->whereHas('ledBooking')
            ->with([
                'customer',
                'ledBooking.periods.items.network.screens',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

            return [
                'items' => $bookings
                    ->groupBy('customer_id')
                    ->map(
                        fn (Collection $bookings) =>  $this->formatCustomer($bookings)
                    )
                    ->filter(
                        fn (array $customer) =>  $customer['bookings_count'] > 0
                    )
                    ->sortBy(
                        fn (array $customer) =>  $customer['nearest_start_date'] ?? '9999-12-31'
                    )
                    ->values()
                    ->all(),
            ];

    }


    private function formatCustomer(Collection $bookings): array
    {
        $latestBooking = $bookings->first();

        $periods = $bookings->flatMap(
            fn (Booking $booking) => $booking->ledBooking->periods
        );

        $pendingCounts = $bookings->map(
            fn (Booking $booking) =>
                $this->countPendingScreens($booking->ledBooking->periods)
        );

        return [
            'id' => (int) $latestBooking->customer_id,
            'name' => $latestBooking->customer->name,

            'bookings_count' => $pendingCounts
                ->filter(fn (int $count) => $count > 0)
                ->count(),

            'pending_screens_count' => $pendingCounts->sum(),
            'nearest_start_date' => $this->getNearestStartDate($periods),
        ];
    }

    private function countPendingScreens(Collection $periods): int
    {
        return $periods->sum(
            fn (LedBookingPeriod $period) =>
                $this->eligibilityService->countEligibleScreens($period->items)
        );
    }

    private function getNearestStartDate(Collection $periods)
    {
        return $periods->min(
            fn (LedBookingPeriod $period) => $period->start_date?->toDateString()
        );
    }
}
