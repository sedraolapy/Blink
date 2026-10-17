<?php

namespace App\Services\Orders\Electronic;

use App\Enums\BookingStatusEnum;
use App\Models\Customer;
use App\Models\LedBookingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;

class ElectronicOrderPeriodsService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext,
        private readonly ElectronicOrderEligibilityService $eligibilityService
    ) {}

    public function getPeriods(int $customerId): array
    {
        $year = $this->workingYearContext->get();

        $customer = Customer::query()
            ->whereHas(
                'bookings',
                fn (Builder $query) => $query
                    ->where('year', $year)
                    ->where(
                        'status',
                        BookingStatusEnum::CONFIRMED->value
                    )
                    ->whereHas('ledBooking')
            )
            ->findOrFail($customerId);

        $periods = LedBookingPeriod::query()
            ->whereHas(
                'ledBooking.booking',
                fn (Builder $query) => $query
                    ->where('customer_id', $customerId)
                    ->where('year', $year)
                    ->where(
                        'status',
                        BookingStatusEnum::CONFIRMED->value
                    )
            )
            ->with([
                'items.network.screens',
            ])
            ->orderBy('start_date')
            ->orderBy('end_date')
            ->orderBy('id')
            ->get();

        return [
            'customer' => [
                'id' => (int) $customer->id,
                'name' => $customer->name,
            ],

            'items' => $periods
                ->map(fn (LedBookingPeriod $period) => [
                    'id' => (int) $period->id,
                    'start_date' => $period->start_date->toDateString(),
                    'end_date' => $period->end_date->toDateString(),
                    'pending_screens_count' => $this->eligibilityService->countEligibleScreens($period->items),
                ])
                ->values()
                ->all(),
        ];
    }
}
