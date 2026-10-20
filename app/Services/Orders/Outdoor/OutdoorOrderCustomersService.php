<?php

namespace App\Services\Orders\Outdoor;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OutdoorOrderCustomersService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function getCustomers(string $outdoorType): array
    {
        $bookings = Booking::query()
            ->where('year', $this->workingYearContext->get())
            ->where('status',BookingStatusEnum::CONFIRMED->value)
            ->whereHas(
                'externalBooking.types',
                fn (Builder $query) =>
                    $query->where('type', $outdoorType)
            )
            ->with([
                'customer',
                'externalBooking.types' =>
                    fn ($query) =>
                        $query->where('type', $outdoorType),

                'externalBooking.types.periods.items',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return [
            'items' => $bookings
                ->groupBy('customer_id')
                ->map(
                    fn (Collection $customerBookings) =>
                        $this->formatCustomer($customerBookings)
                )
                ->values()
                ->all(),
        ];
    }

    private function formatCustomer(Collection $bookings)
    {
        $latestBooking = $bookings->first();

        $periods = $bookings->flatMap(
            fn (Booking $booking) =>
                $booking->externalBooking->types->flatMap(
                    fn ($type) => $type->periods
                )
        );

        return [
            'id' => (int) $latestBooking->customer_id,
            'name' => $latestBooking->customer->name,
            'periods_count' => $periods->count(),
            'ready_assets_count' => $periods->sum(
                fn (ExternalBookingPeriod $period) =>
                    $this->countReadyAssets($period)
            ),
            'nearest_start_date' => $periods->min(
                fn (ExternalBookingPeriod $period) =>
                    $period->start_date?->toDateString()
            ),
        ];
    }


    private function countReadyAssets(ExternalBookingPeriod $period)
    {
        return $period->items
            ->filter(
                fn (ExternalBookingItem $item) =>
                    $item->status === BookingItemStatusEnum::BOOKED
                    && $item->order_status === null
            )
            ->count();
    }

}
