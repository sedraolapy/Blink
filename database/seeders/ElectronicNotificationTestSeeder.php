<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\Area;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingPeriod;
use App\Models\LedScreen;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ElectronicNotificationTestSeeder extends Seeder
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
                'name_ar' => 'اختبار تذكير أوامر الإلكتروني',
                'name_en' => 'Electronic Orders Reminder Test',
                'start' => $today->addDays(3),
                'end' => $today->addDays(13),
            ],

            'started' => [
                'name_ar' => 'اختبار بداية الحجز الإلكتروني',
                'name_en' => 'Electronic Booking Started Test',
                'start' => $today,
                'end' => $today->addDays(10),
            ],

            'ended' => [
                'name_ar' => 'اختبار نهاية الحجز الإلكتروني',
                'name_en' => 'Electronic Booking Ended Test',
                'start' => $today->subDays(10),
                'end' => $today,
            ],
        ];

        DB::transaction(function () use ($cases, $today) {
            $area = Area::query()->firstOrFail();

            foreach ($cases as $key => $case) {

                $customer = Customer::query()->updateOrCreate(
                    [
                        'phone' => "TEST-LED-NOTIF-{$key}",
                    ],
                    [
                        'name' => [
                            'ar' => $case['name_ar'],
                            'en' => $case['name_en'],
                        ],
                    ]
                );

                $booking = Booking::query()->firstOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'year' => $today->year,
                    ],
                    [
                        'booking_type' =>
                            BookingTypeEnum::LOCAL->value,
                    ]
                );

                $booking->forceFill([
                    'status' =>
                        BookingStatusEnum::CONFIRMED->value,
                ])->save();

                $ledBooking = LedBooking::query()->firstOrCreate([
                    'booking_id' => $booking->id,
                ]);

                $period = LedBookingPeriod::query()->updateOrCreate(
                    [
                        'led_booking_id' => $ledBooking->id,
                    ],
                    [
                        'start_date' =>
                            $case['start']->toDateString(),

                        'end_date' =>
                            $case['end']->toDateString(),
                    ]
                );

                $screen = LedScreen::query()->firstOrCreate(
                    [
                        'code' => "LED-NOTIF-{$key}-{$today->year}",
                    ],
                    [
                        'area_id' => $area->id,
                        'network_id' => null,

                        'location_name' => [
                            'ar' => "شاشة اختبار إشعارات {$key}",
                            'en' => "Notification Test Screen {$key}",
                        ],

                        'width' => 5,
                        'height' => 3,
                        'width_px' => 1920,
                        'height_px' => 1080,
                    ]
                );

                $item = LedBookingItem::query()->firstOrCreate(
                    [
                        'led_booking_period_id' => $period->id,
                        'led_screen_id' => $screen->id,
                    ],
                    [
                        'led_network_id' => null,
                        'is_gift' => false,
                    ]
                );

                $item->forceFill([
                    'status' =>
                        BookingItemStatusEnum::BOOKED->value,
                ])->save();

            }
        });
    }
}
