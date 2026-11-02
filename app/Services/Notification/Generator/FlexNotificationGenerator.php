<?php

namespace App\Services\Notification\Generator;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\DisplayGroupEnum;
use App\Models\Booking;
use App\Services\Notification\NotificationDispatchService;
use App\Services\Reports\CampaignReportPeriodService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class FlexNotificationGenerator
{
    private const TIMEZONE = 'Asia/Damascus';
    private const DAYS_BEFORE_START = 3;

    public function __construct(
        private readonly NotificationDispatchService $dispatchService,
        private readonly CampaignReportPeriodService $periodService
    ) {}

    public function generate(?CarbonImmutable $date = null): int
    {
        $today = ($date ?? CarbonImmutable::now(self::TIMEZONE))
            ->setTimezone(self::TIMEZONE)
            ->startOfDay();

        $period = $this->periodService->getAvailablePeriod(
            $today->year,
            DisplayGroupEnum::DAMASCUS_DARAA_SWEIDA,
            $today
        );

        if (! $period || ! $this->isReminderDate($period, $today)) {
            return 0;
        }

        $customerIds = $this->getBookedCustomerIds($period);

        $sentCount = 0;

        foreach ($customerIds as $customerId) {
            $eventId = implode(':', [
                'flex',
                $period['year'],
                $period['id'],
                $customerId,
            ]);

            $sentCount += $this->dispatchService
                ->sendToAllUsers(
                    'flex_orders_reminder',
                    $eventId,
                    [
                        'year' => $period['year'],
                        'period_id' => $period['id'],
                        'period_number' => $period['number'],
                        'customer_id' => $customerId,
                    ]
                );
        }

        return $sentCount;
    }

    private function isReminderDate(array $period,CarbonImmutable $today)
    {
        $reminderDate = CarbonImmutable::parse(
            $period['start_date'],
            self::TIMEZONE
        )->subDays(self::DAYS_BEFORE_START);

        return $today->isSameDay($reminderDate);
    }

    private function getBookedCustomerIds(array $period)
    {
        return Booking::query()
            ->where('year', $period['year'])
            ->where('status',BookingStatusEnum::CONFIRMED->value)
            ->whereHas(
                'flexBooking.periods',
                function (Builder $query) use ($period) {
                    $query
                        ->where(
                            'advertising_period_id',
                            $period['id']
                        )
                        ->whereHas(
                            'bookingItems',
                            fn (Builder $query) =>
                                $query->where(
                                    'status',
                                    BookingItemStatusEnum::BOOKED->value
                                )
                        );
                }
            )
            ->distinct()
            ->pluck('customer_id');
    }
}
