<?php

namespace App\Services\Notification\Generator;

use App\Enums\DisplayGroupEnum;
use App\Services\Notification\NotificationDispatchService;
use App\Services\Reports\CampaignReportPeriodService;
use Carbon\CarbonImmutable;

class FlexInstitutionReportNotificationGenerator
{
    private const TIMEZONE = 'Asia/Damascus';

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

        if (! $period) {
            return 0;
        }

        $startDate = CarbonImmutable::parse(
            $period['start_date'],
            self::TIMEZONE
        )->startOfDay();

        $reminderDay = match (true) {
            $today->isSameDay($startDate->addDay()) => 2,
            $today->isSameDay($startDate->addDays(2)) => 3,
            default => null,
        };

        if ($reminderDay === null) {
            return 0;
        }

        $eventId = implode(':', [
            'institution-flex',
            $period['year'],
            $period['id'],
            $reminderDay,
        ]);

        return $this->dispatchService->sendToAllUsers('flex_institution_report_reminder',$eventId,
            [
                'year' => $period['year'],
                'period_id' => $period['id'],
                'period_number' => $period['number'],
                'reminder_day' => $reminderDay,
            ]
        );
    }
}
