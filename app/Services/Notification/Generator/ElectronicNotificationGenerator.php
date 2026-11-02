<?php

namespace App\Services\Notification\Generator;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Models\LedBookingPeriod;
use App\Services\Notification\NotificationDispatchService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class ElectronicNotificationGenerator
{
    private const TIMEZONE = 'Asia/Damascus';

    public function __construct(private readonly NotificationDispatchService $dispatchService)
    {}

    public function generate(?CarbonImmutable $date = null): int
    {
        $today = ($date ?? CarbonImmutable::now(self::TIMEZONE))
            ->setTimezone(self::TIMEZONE)
            ->startOfDay();

        $sentCount = 0;

        foreach ($this->events() as $event) {
            $targetDate = $today
                ->addDays($event['days_before'])
                ->toDateString();

            LedBookingPeriod::query()
                ->whereDate($event['date_column'], $targetDate)
                ->whereHas(
                    'ledBooking.booking',
                    fn (Builder $query) => $query->where('status',BookingStatusEnum::CONFIRMED->value)
                )
                ->whereHas(
                    'items',
                    fn (Builder $query) => $query->where('status',BookingItemStatusEnum::BOOKED->value)
                )
                ->with('ledBooking.booking')
                ->chunkById(100, function ($periods) use (
                    $event,
                    &$sentCount
                ) {
                    foreach ($periods as $period) {
                        $booking = $period->ledBooking->booking;

                        $eventId = implode(':', [
                            'electronic',
                            $period->id,
                            $period->start_date->toDateString(),
                            $period->end_date->toDateString(),
                        ]);

                        $sentCount += $this->dispatchService
                            ->sendToAllUsers(
                                $event['type'],
                                $eventId,
                                [
                                    'year' => $booking->year,
                                    'customer_id' => $booking->customer_id,
                                    'booking_id' => $booking->id,
                                    'start_date' => $period->start_date->toDateString(),
                                    'end_date' => $period->end_date->toDateString(),
                                ]
                            );
                    }
                });
        }

        return $sentCount;
    }

    private function events(): array
    {
        return [
            [
                'type' => 'electronic_orders_reminder',
                'date_column' => 'start_date',
                'days_before' => 3,
            ],
            [
                'type' => 'electronic_period_started',
                'date_column' => 'start_date',
                'days_before' => 0,
            ],
            [
                'type' => 'electronic_period_ended',
                'date_column' => 'end_date',
                'days_before' => 0,
            ],
        ];
    }
}
