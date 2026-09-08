<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\ExternalAsset;
use App\Models\ExternalBooking;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingPeriod;
use App\Models\ExternalBookingType;
use App\Models\ExternalDesign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OutdoorMultiplePeriodsTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
             * ========================================
             * Asset
             * ========================================
             */

            $asset = ExternalAsset::query()->findOrFail(1);

            $type = $asset->type instanceof \BackedEnum
                ? $asset->type->value
                : $asset->type;

            /*
             * ========================================
             * Test Customer
             * ========================================
             */

            $customer = Customer::query()->updateOrCreate(
                [
                    'phone' => '0999111111',
                ],
                [
                    'name' => [
                        'ar' => 'زبون اختبار الفترات الخارجية',
                        'en' => 'Outdoor Periods Test Customer',
                    ],
                    'subscription_type' => 'bronze',
                ]
            );

            /*
             * ========================================
             * Cleanup old test bookings
             * ========================================
             */

            Booking::query()
                ->where('customer_id', $customer->id)
                ->delete();

            /*
             * ========================================
             * CONFIRMED BOOKING
             *
             * 3 Periods
             * Different design for each period
             * ========================================
             */

            $confirmedBooking = Booking::query()->create([
                'customer_id' => $customer->id,
                'booking_type' =>  BookingTypeEnum::FOREIGN->value,

                'status' =>
                    BookingStatusEnum::CONFIRMED->value,

                'installation_order' => false,
                'extension_order' => false,
                'operation_order' => false,
            ]);

            $confirmedExternalBooking =
                ExternalBooking::query()->create([
                    'booking_id' => $confirmedBooking->id,
                ]);

            $confirmedType =
                ExternalBookingType::query()->create([
                    'external_booking_id' =>
                        $confirmedExternalBooking->id,

                    'type' => $type,
                ]);

            /*
             * Designs
             */

            $confirmedDesign1 =
                ExternalDesign::query()->create([
                    'external_booking_id' =>
                        $confirmedExternalBooking->id,

                    'name' => 'تصميم الحملة الأولى',
                ]);

            $confirmedDesign2 =
                ExternalDesign::query()->create([
                    'external_booking_id' =>
                        $confirmedExternalBooking->id,

                    'name' => 'تصميم الحملة الثانية',
                ]);

            $confirmedDesign3 =
                ExternalDesign::query()->create([
                    'external_booking_id' =>
                        $confirmedExternalBooking->id,

                    'name' => 'تصميم الحملة الثالثة',
                ]);

            /*
             * Period 1
             */

            $confirmedPeriod1 =
                ExternalBookingPeriod::query()->create([
                    'external_booking_type_id' =>
                        $confirmedType->id,

                    'start_date' => '2026-09-10',
                    'end_date' => '2026-09-25',
                ]);

            ExternalBookingItem::query()->create([
                'external_booking_period_id' =>
                    $confirmedPeriod1->id,

                'external_asset_id' =>
                    $asset->id,

                'design_id' =>
                    $confirmedDesign1->id,

                'status' =>
                    BookingItemStatusEnum::BOOKED->value,
            ]);

            /*
             * Period 2
             */

            $confirmedPeriod2 =
                ExternalBookingPeriod::query()->create([
                    'external_booking_type_id' =>
                        $confirmedType->id,

                    'start_date' => '2026-10-05',
                    'end_date' => '2026-10-20',
                ]);

            ExternalBookingItem::query()->create([
                'external_booking_period_id' =>
                    $confirmedPeriod2->id,

                'external_asset_id' =>
                    $asset->id,

                'design_id' =>
                    $confirmedDesign2->id,

                'status' =>
                    BookingItemStatusEnum::BOOKED->value,
            ]);

            /*
             * Period 3
             */

            $confirmedPeriod3 =
                ExternalBookingPeriod::query()->create([
                    'external_booking_type_id' =>
                        $confirmedType->id,

                    'start_date' => '2026-12-01',
                    'end_date' => '2026-12-15',
                ]);

            ExternalBookingItem::query()->create([
                'external_booking_period_id' =>
                    $confirmedPeriod3->id,

                'external_asset_id' =>
                    $asset->id,

                'design_id' =>
                    $confirmedDesign3->id,


                'status' =>
                    BookingItemStatusEnum::BOOKED->value,
            ]);

            /*
             * ========================================
             * UNCONFIRMED BOOKING
             *
             * 2 Periods
             * Different design for each period
             * ========================================
             */

            $unconfirmedBooking =
                Booking::query()->create([
                    'customer_id' => $customer->id,
                    'booking_type' =>  BookingTypeEnum::FOREIGN->value,

                    'status' =>
                        BookingStatusEnum::UNCONFIRMED->value,

                    'installation_order' => false,
                    'extension_order' => false,
                    'operation_order' => false,
                ]);

            $unconfirmedExternalBooking =
                ExternalBooking::query()->create([
                    'booking_id' =>
                        $unconfirmedBooking->id,
                ]);

            $unconfirmedType =
                ExternalBookingType::query()->create([
                    'external_booking_id' =>
                        $unconfirmedExternalBooking->id,

                    'type' => $type,
                ]);

            $unconfirmedDesign1 =
                ExternalDesign::query()->create([
                    'external_booking_id' =>
                        $unconfirmedExternalBooking->id,

                    'name' =>
                        'تصميم غير مؤكد الأول',
                ]);

            $unconfirmedDesign2 =
                ExternalDesign::query()->create([
                    'external_booking_id' =>
                        $unconfirmedExternalBooking->id,

                    'name' =>
                        'تصميم غير مؤكد الثاني',
                ]);

            /*
             * Period 1
             */

            $unconfirmedPeriod1 =
                ExternalBookingPeriod::query()->create([
                    'external_booking_type_id' =>
                        $unconfirmedType->id,

                    'start_date' => '2027-01-05',
                    'end_date' => '2027-01-20',
                ]);

            ExternalBookingItem::query()->create([
                'external_booking_period_id' =>
                    $unconfirmedPeriod1->id,

                'external_asset_id' =>
                    $asset->id,

                'design_id' =>
                    $unconfirmedDesign1->id,


                'status' =>
                    BookingItemStatusEnum::UNCONFIRMED->value,
            ]);

            /*
             * Period 2
             */

            $unconfirmedPeriod2 =
                ExternalBookingPeriod::query()->create([
                    'external_booking_type_id' =>
                        $unconfirmedType->id,

                    'start_date' => '2027-02-01',
                    'end_date' => '2027-02-20',
                ]);

            ExternalBookingItem::query()->create([
                'external_booking_period_id' =>
                    $unconfirmedPeriod2->id,

                'external_asset_id' =>
                    $asset->id,

                'design_id' =>
                    $unconfirmedDesign2->id,


                'status' =>
                    BookingItemStatusEnum::UNCONFIRMED->value,
            ]);
        });
    }
}