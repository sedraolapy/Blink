<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\AdvertisingPeriod;
use App\Models\Area;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\FlexDesign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlexOrdersTestSeeder extends Seeder
{
    private const YEAR = 2026;

    public function run(): void
    {
        DB::transaction(function () {
            $customer = Customer::query()->updateOrCreate(
                ['phone' => '0999777744'],
                [
                    'name' => [
                        'ar' => 'زبون اختبار أوامر الفليكس',
                        'en' => 'Flex Orders Test Customer',
                    ],
                ]
            );

            $area = Area::query()
                ->orderBy('id')
                ->firstOrFail();

            $previous = AdvertisingPeriod::query()
                ->where('number', 20)
                ->firstOrFail();

            $current = AdvertisingPeriod::query()
                ->where('number', 21)
                ->firstOrFail();

            $booking = Booking::query()
                ->where('customer_id', $customer->id)
                ->where('year', self::YEAR)
                ->first();

            if (! $booking) {
                $booking = Booking::query()->forceCreate([
                    'customer_id' => $customer->id,
                    'year' => self::YEAR,
                    'booking_type' => BookingTypeEnum::LOCAL->value,
                    'status' => BookingStatusEnum::CONFIRMED->value,
                ]);
            }

            $flexBooking = FlexBooking::query()->firstOrCreate([
                'booking_id' => $booking->id,
            ]);

            $previousPeriod = FlexBookingPeriod::query()
                ->firstOrCreate([
                    'flex_booking_id' => $flexBooking->id,
                    'advertising_period_id' => $previous->id,
                ]);

            $currentPeriod = FlexBookingPeriod::query()
                ->firstOrCreate([
                    'flex_booking_id' => $flexBooking->id,
                    'advertising_period_id' => $current->id,
                ]);

            $designNames = [
                'old' => 'Flex Test - Old Design',
                'new' => 'Flex Test - New Design',
                'same' => 'Flex Test - Same Design',
                'unclassified' => 'Flex Test - Unclassified',
            ];

            $designs = [];

            foreach ($designNames as $key => $name) {
                $designs[$key] = FlexDesign::query()
                    ->firstOrCreate([
                        'flex_booking_id' => $flexBooking->id,
                        'name' => $name,
                    ]);
            }

            $billboardNames = [
                'installation' => 'لوحة اختبار التركيب',
                'extension' => 'لوحة اختبار التمديد',
                'unclassified' => 'لوحة اختبار غير محدد',
                'no_design' => 'لوحة اختبار بدون تصميم',
            ];

            $billboards = [];

            foreach ($billboardNames as $type => $name) {
                $billboards[$type] = FlexBillboard::query()
                    ->firstOrCreate(
                        [
                            'code' => 'FLX-ORDER-TEST-' . strtoupper($type),
                        ],
                        [
                            'area_id' => $area->id,
                            'location_name' => [
                                'ar' => $name,
                                'en' => "Flex Order Test {$type}",
                            ],
                            'width' => 6,
                            'height' => 3,
                        ]
                    );
            }

            // Installation: design changed from previous period.
            $this->createItem(
                $previousPeriod,
                $billboards['installation'],
                $designs['old']
            );

            $this->createItem(
                $currentPeriod,
                $billboards['installation'],
                $designs['new']
            );

            // Extension: same design in both periods.
            $this->createItem(
                $previousPeriod,
                $billboards['extension'],
                $designs['same']
            );

            $this->createItem(
                $currentPeriod,
                $billboards['extension'],
                $designs['same']
            );

            // Unclassified: excluded from a previous order.
            $this->createItem(
                $currentPeriod,
                $billboards['unclassified'],
                $designs['unclassified'],
                'unclassified'
            );

            // No design: confirmed booking without a design.
            $this->createItem(
                $currentPeriod,
                $billboards['no_design'],
                null
            );
        });
    }

    private function createItem(
        FlexBookingPeriod $period,
        FlexBillboard $billboard,
        ?FlexDesign $design,
        ?string $orderStatus = null
    ): void {
        FlexBookingItem::query()->firstOrCreate(
            [
                'flex_booking_period_id' => $period->id,
                'flex_billboard_id' => $billboard->id,
            ],
            [
                'design_id' => $design?->id,
                'has_dykat' => false,
                'is_gift' => false,
                'status' => BookingItemStatusEnum::BOOKED->value,
                'order_status' => $orderStatus,
            ]
        );
    }
}
