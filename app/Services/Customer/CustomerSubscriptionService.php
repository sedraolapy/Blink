<?php

namespace App\Services\Customer;

use App\Enums\BookingStatusEnum;
use App\Enums\SubscriptionTypeEnum;
use App\Models\Customer;

class CustomerSubscriptionService
{
    public function getCustomersRanking(int $year)
    {
        $customers = Customer::query()
            ->with([
                'bookings' => function ($query) use ($year) {
                    $query
                        ->where('year', $year)
                        ->where(
                            'status',
                            BookingStatusEnum::CONFIRMED->value
                        )
                        ->with([
                            'flexBooking.periods.bookingItems',
                            'ledBooking.periods.items',
                            'externalBooking.types.periods.items',
                        ]);
                },
            ])
            ->get([
                'id',
                'name',
            ]);

        return $customers
            ->map(
                fn (Customer $customer) => [
                    'customer' => $customer,

                    'booking_items_count' =>
                        $this->getBookingItemsCount(
                            $customer
                        ),
                ]
            )
            ->sort(
                function (
                    array $first,
                    array $second
                ) {
                    $countComparison =
                        $second['booking_items_count']
                        <=>
                        $first['booking_items_count'];

                    if ($countComparison !== 0) {
                        return $countComparison;
                    }

                    return $first['customer']->id
                        <=>
                        $second['customer']->id;
                }
            )
            ->values();
    }

    public function assignSubscriptionTypes(int $year): void
    {
        $ranking = $this->getCustomersRanking($year);

        $total = $ranking->count();

        if ($total === 0) {
            return;
        }

        $goldCount = (int) ceil(
            $total * 0.30
        );

        $silverCount = (int) ceil(
            $total * 0.30
        );

        $ranking->each(
            function (
                array $rankedCustomer,
                int $index
            ) use (
                $goldCount,
                $silverCount,
                $year
            ) {
                $subscriptionType = match (true) {
                    $index < $goldCount =>
                        SubscriptionTypeEnum::GOLD,

                    $index < (
                        $goldCount + $silverCount
                    ) =>
                        SubscriptionTypeEnum::SILVER,

                    default =>
                        SubscriptionTypeEnum::BRONZE,
                };

                $rankedCustomer['customer']
                    ->subscriptions()
                    ->updateOrCreate(
                        [
                            'year' => $year,
                        ],
                        [
                            'subscription_type' =>
                                $subscriptionType->value,
                        ]
                    );
            }
        );
    }

    private function getBookingItemsCount(Customer $customer)
    {
        return $customer->bookings->sum(
            fn ($booking) =>
                $this->getFlexItemsCount($booking)
                + $this->getLedItemsCount($booking)
                + $this->getExternalItemsCount($booking)
        );
    }

    private function getFlexItemsCount($booking)
    {
        if (! $booking->flexBooking) {
            return 0;
        }

        return $booking
            ->flexBooking
            ->periods
            ->sum(
                fn ($period) =>
                    $period
                        ->bookingItems
                        ->where('is_gift', false)
                        ->count()
            );
    }

    private function getLedItemsCount($booking)
    {
        if (! $booking->ledBooking) {
            return 0;
        }

        return $booking
            ->ledBooking
            ->periods
            ->sum(
                fn ($period) =>
                    $period
                        ->items
                        ->where('is_gift', false)
                        ->count()
            );
    }

    private function getExternalItemsCount($booking)
    {
        if (! $booking->externalBooking) {
            return 0;
        }

        return $booking
            ->externalBooking
            ->types
            ->sum(
                fn ($type) =>
                    $type
                        ->periods
                        ->sum(
                            fn ($period) =>
                                $period
                                    ->items
                                    ->count()
                        )
            );
    }
}