<?php

namespace Database\Seeders;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\ContractStatusEnum;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ContractNotificationTestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException(
                'This seeder is allowed only in local/testing.'
            );
        }

        $today = CarbonImmutable::today('Asia/Damascus');

        $cases = [
            'contract_start_reminder' => [
                'name' => [
                    'ar' => 'اختبار تذكير بداية العقد',
                    'en' => 'Contract Start Reminder Test',
                ],
                'start' => $today->addDays(7),
                'end' => $today->addDays(21),
            ],

            'contract_started' => [
                'name' => [
                    'ar' => 'اختبار بداية العقد',
                    'en' => 'Contract Started Test',
                ],
                'start' => $today,
                'end' => $today->addDays(14),
            ],

            'contract_end_reminder' => [
                'name' => [
                    'ar' => 'اختبار تذكير انتهاء العقد',
                    'en' => 'Contract End Reminder Test',
                ],
                'start' => $today->subDays(7),
                'end' => $today->addDays(7),
            ],

            'contract_ended' => [
                'name' => [
                    'ar' => 'اختبار انتهاء العقد',
                    'en' => 'Contract Ended Test',
                ],
                'start' => $today->subDays(14),
                'end' => $today,
            ],
        ];

        DB::transaction(function () use ($cases, $today) {
            foreach ($cases as $index => $case) {
                $customer = Customer::query()->updateOrCreate(
                    [
                        'phone' => sprintf(
                            '09998821%02d',
                            array_search(
                                $index,
                                array_keys($cases),
                                true
                            ) + 1
                        ),
                    ],
                    [
                        'name' => $case['name'],
                    ]
                );

                $booking = Booking::query()->firstOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'year' => $today->year,
                    ],
                    [
                        'booking_type' => BookingTypeEnum::LOCAL->value,
                    ]
                );

                $booking->forceFill([
                    'status' => BookingStatusEnum::CONFIRMED->value,
                ])->save();

                $status = match (true) {
                    $today->lt($case['start']) =>
                        ContractStatusEnum::WAITING_START->value,

                    $today->gt($case['end']) =>
                        ContractStatusEnum::FINISHED->value,

                    default =>
                        ContractStatusEnum::IN_PROGRESS->value,
                };

                $contract = Contract::query()->updateOrCreate(
                    [
                        'booking_id' => $booking->id,
                    ],
                    [
                        'contract_number' =>
                            "TEST-NOTIF-{$today->year}-{$index}",

                        'start_date' =>
                            $case['start']->toDateString(),

                        'end_date' =>
                            $case['end']->toDateString(),

                        'status' => $status,
                    ]
                );
            }
        });
    }
}
