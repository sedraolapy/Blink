<?php

namespace App\Services\Notification\Generator;

use App\Models\Contract;
use App\Services\Notification\NotificationDispatchService;
use Carbon\CarbonImmutable;

class ContractNotificationGenerator
{
    public function __construct(private readonly NotificationDispatchService $dispatchService)
    {}

    public function generate(?CarbonImmutable $date = null): int
    {
        $today = ($date ?? CarbonImmutable::now('Asia/Damascus'))
            ->setTimezone('Asia/Damascus')
            ->startOfDay();

        $sentCount = 0;

        foreach ($this->events() as $event) {
            $targetDate = $today
                ->addDays($event['days_before'])
                ->toDateString();

            Contract::query()
                ->with('booking:id,customer_id,year')
                ->whereDate($event['date_column'], $targetDate)
                ->chunkById(100, function ($contracts) use (
                    $event,
                    $targetDate,
                    &$sentCount
                ) {
                    foreach ($contracts as $contract) {
                        if (! $contract->booking) {
                            continue;
                        }

                        $eventId = "contract:{$contract->id}:{$targetDate}";

                        $sentCount += $this->dispatchService
                            ->sendToAllUsers(
                                $event['type'],
                                $eventId,
                                $this->context($contract)
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
                'type' => 'contract_start_reminder',
                'date_column' => 'start_date',
                'days_before' => 7,
            ],
            [
                'type' => 'contract_started',
                'date_column' => 'start_date',
                'days_before' => 0,
            ],
            [
                'type' => 'contract_end_reminder',
                'date_column' => 'end_date',
                'days_before' => 7,
            ],
            [
                'type' => 'contract_ended',
                'date_column' => 'end_date',
                'days_before' => 0,
            ],
        ];
    }

    private function context(Contract $contract): array
    {
        return [
            'contract_id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'booking_id' => $contract->booking_id,
            'customer_id' => $contract->booking->customer_id,
            'start_date' => $contract->start_date->toDateString(),
            'end_date' => $contract->end_date->toDateString(),
            'year' => $contract->booking->year,
        ];
    }
}
