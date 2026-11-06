<?php

namespace App\Services\Notification\Generator;

use App\Services\Notification\NotificationDispatchService;
use Carbon\CarbonImmutable;

class MonthlyInstitutionReportNotificationGenerator
{
    private const TIMEZONE = 'Asia/Damascus';

    private const REMINDER_DAYS = [26, 27];

    public function __construct(
        private readonly NotificationDispatchService $dispatchService
    ) {}

    public function generate(?CarbonImmutable $date = null): int
    {
        $today = ($date ?? CarbonImmutable::now(self::TIMEZONE))
            ->setTimezone(self::TIMEZONE)
            ->startOfDay();

        $reminderDay = $today->day;

        if (! in_array($reminderDay, self::REMINDER_DAYS, true)) {
            return 0;
        }

        $eventId = implode(':', [
            'institution-monthly',
            $today->year,
            $today->month,
            $reminderDay,
        ]);

        return $this->dispatchService->sendToAllUsers(
            'monthly_institution_report_reminder',
            $eventId,
            [
                'year' => $today->year,
                'month' => $today->month,
                'reminder_day' => $reminderDay,
            ]
        );
    }
}
