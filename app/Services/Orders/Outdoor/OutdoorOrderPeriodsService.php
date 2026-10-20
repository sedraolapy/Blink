<?php

namespace App\Services\Orders\Outdoor;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\Customer;
use App\Models\ExternalBookingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;

class OutdoorOrderPeriodsService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext
    ) {}

    public function getPeriods(int $customerId,string $outdoorType)
    {
        $year = $this->workingYearContext->get();

        $customer = Customer::query()
            ->whereHas(
                'bookings',
                fn (Builder $query) =>
                    $query
                        ->where('year', $year)
                        ->where('status',BookingStatusEnum::CONFIRMED->value)
                        ->whereHas(
                            'externalBooking.types',
                            fn (Builder $query) =>
                                $query->where('type', $outdoorType)
                        )
            )
            ->findOrFail($customerId);

        $periods = ExternalBookingPeriod::query()
            ->whereHas(
                'externalBookingType',
                fn (Builder $query) =>
                    $query
                        ->where('type', $outdoorType)
                        ->whereHas(
                            'externalBooking.booking',
                            fn (Builder $query) =>
                                $query
                                    ->where('customer_id', $customerId)
                                    ->where('year', $year)
                                    ->where('status',BookingStatusEnum::CONFIRMED->value)
                        )
            )
            ->withCount([
                'items as ready_assets_count' =>
                    fn ($query) =>
                        $query
                            ->where('status',BookingItemStatusEnum::BOOKED->value)
                            ->whereNull('order_status'),
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
                ->map(
                    fn (ExternalBookingPeriod $period) => [
                        'id' => (int) $period->id,
                        'start_date' => $period->start_date->toDateString(),
                        'end_date' => $period->end_date->toDateString(),
                        'ready_assets_count' =>(int) $period->ready_assets_count,
                    ]
                )
                ->values()
                ->all(),
        ];
    }
}
