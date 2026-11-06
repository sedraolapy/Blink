<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\ExternalAssetTypeEnum;
use App\Models\Area;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\ExternalAsset;
use App\Models\ExternalBooking;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingPeriod;
use App\Models\ExternalBookingType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OutdoorNotificationTestSeeder extends Seeder
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
            'reminder' => [
                'type' => ExternalAssetTypeEnum::MURAL,
                'start' => $today->addDays(3),
                'end' => $today->addDays(13),
            ],

            'started' => [
                'type' => ExternalAssetTypeEnum::BRIDGE,
                'start' => $today,
                'end' => $today->addDays(10),
            ],

            'ended' => [
                'type' => ExternalAssetTypeEnum::UNIPOLE,
                'start' => $today->subDays(10),
                'end' => $today,
            ],
        ];

        DB::transaction(function () use ($today, $cases) {

            $area = Area::query()->firstOrFail();

            $customer = Customer::query()->firstOrCreate(
                [
                    'phone' => '0999888812',
                ],
                [
                    'name' => [
                        'ar' => 'زبون اختبار إشعارات الخارجي',
                        'en' => 'Outdoor Notifications Test Customer',
                    ],
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

            $externalBooking = ExternalBooking::query()
                ->firstOrCreate([
                    'booking_id' => $booking->id,
                ]);

            foreach ($cases as $key => $case) {

                $outdoorType = $case['type'];

                $bookingType = ExternalBookingType::query()
                    ->firstOrCreate([
                        'external_booking_id' => $externalBooking->id,
                        'type' => $outdoorType->value,
                    ]);

                $period = ExternalBookingPeriod::query()
                    ->updateOrCreate(
                        [
                            'external_booking_type_id' => $bookingType->id,
                        ],
                        [
                            'start_date' => $case['start']->toDateString(),
                            'end_date' => $case['end']->toDateString(),
                        ]
                    );

                $asset = ExternalAsset::query()
                    ->firstOrCreate(
                        [
                            'code' => "OUT-NOTIF-{$key}-{$today->year}",
                        ],
                        [
                            'type' => $outdoorType->value,
                            'area_id' => $area->id,

                            'location_name' => [
                                'ar' => "لوحة اختبار إشعارات الخارجي {$key}",
                                'en' => "Outdoor Notification Test {$key}",
                            ],

                            'width' => 5,
                            'height' => 3,

                            'local_price' => 1000,
                            'foreign_price' => 1500,
                        ]
                    );

                ExternalBookingItem::query()->updateOrCreate(
                    [
                        'external_booking_period_id' => $period->id,
                        'external_asset_id' => $asset->id,
                    ],
                    [
                        'status' => BookingItemStatusEnum::BOOKED->value,
                        'is_gift' => false,
                    ]
                );
            }
        });
    }
}
