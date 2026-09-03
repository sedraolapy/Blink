<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\ContractStatusEnum;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class BookingApiTestSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * ========================================
         * Test Customers
         * ========================================
         */

        $fullCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000001'],
            [
                'name' => [
                    'ar' => 'شركة اختبار شاملة',
                    'en' => 'Full Test Company',
                ],
                'subscription_type' => 'gold',
            ]
        );

        $flexCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000002'],
            [
                'name' => [
                    'ar' => 'شركة اختبار فليكس',
                    'en' => 'Flex Test Company',
                ],
                'subscription_type' => 'silver',
            ]
        );

        $electronicCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000003'],
            [
                'name' => [
                    'ar' => 'شركة اختبار إلكتروني',
                    'en' => 'Electronic Test Company',
                ],
                'subscription_type' => 'bronze',
            ]
        );

        $externalCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000004'],
            [
                'name' => [
                    'ar' => 'شركة اختبار خارجي',
                    'en' => 'Outdoor Test Company',
                ],
                'subscription_type' => 'gold',
            ]
        );

        $emptyCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000005'],
            [
                'name' => [
                    'ar' => 'زبون بدون حجوزات',
                    'en' => 'Customer Without Bookings',
                ],
                'subscription_type' => 'bronze',
            ]
        );

        /*
         * ========================================
         * Pagination test
         *
         * 25 historical bookings.
         * آخر عقد للزبون رح نعمله بعدهم حتى
         * يضل latest status = pending.
         * ========================================
         */

        for ($i = 25; $i >= 1; $i--) {
            $start = now()
                ->subMonths($i + 2)
                ->startOfMonth();

            Booking::factory()
                ->for($fullCustomer)
                ->state([
                    'booking_type' => 'internal',
                    'start_date' => $start->toDateString(),
                    'end_date' => $start
                        ->copy()
                        ->addDays(20)
                        ->toDateString(),
                ])
                ->withContract(
                    ContractStatusEnum::FINISHED,
                    false
                )
                ->create();
        }

        /*
         * ========================================
         * FULL BOOKING
         *
         * Flex + Electronic + External
         *
         * كل requirements = true
         * Current asset status = BOOKED
         * Latest customer contract = PENDING
         * ========================================
         */

        $fullBooking = Booking::factory()
            ->for($fullCustomer)
            ->state([
                'booking_type' => 'internal',
                'start_date' => now()
                    ->subDays(5)
                    ->toDateString(),
                'end_date' => now()
                    ->addDays(30)
                    ->toDateString(),
            ])
            ->withOrders(
                installation: true,
                extension: true,
                operation: true
            )
            ->withQuotation()
            ->withContract(
                ContractStatusEnum::PENDING,
                true
            )
            ->withFlex(
                BookingItemStatusEnum::BOOKED,
                0
            )
            ->withElectronic(
                BookingItemStatusEnum::BOOKED,
                0,
                0
            )
            ->withExternal(
                BookingItemStatusEnum::BOOKED,
                0
            )
            ->create();

        /*
         * ========================================
         * FLEX ONLY
         *
         * price_offer = true
         * contract = false
         * installation = true
         * extension = false
         * operation مش لازم يظهر
         *
         * Asset = UNCONFIRMED
         * ========================================
         */

        $flexBooking = Booking::factory()
            ->for($flexCustomer)
            ->state([
                'booking_type' => 'internal',
                'start_date' => now()
                    ->subDays(2)
                    ->toDateString(),
                'end_date' => now()
                    ->addDays(20)
                    ->toDateString(),
            ])
            ->withOrders(
                installation: true,
                extension: false,
                operation: false
            )
            ->withQuotation()
            ->withContract(
                ContractStatusEnum::WAITING_START,
                false
            )
            ->withFlex(
                BookingItemStatusEnum::UNCONFIRMED,
                1
            )
            ->create();

        /*
         * ========================================
         * ELECTRONIC ONLY
         *
         * price_offer = false
         * contract = false
         * operation = false
         *
         * installation / extension
         * ما لازم يظهروا.
         * ========================================
         */

        $electronicBooking = Booking::factory()
            ->for($electronicCustomer)
            ->state([
                'booking_type' => 'internal',
                'start_date' => now()
                    ->subDays(10)
                    ->toDateString(),
                'end_date' => now()
                    ->addDays(10)
                    ->toDateString(),
            ])
            ->withOrders(
                installation: false,
                extension: false,
                operation: false
            )
            ->withContract(
                ContractStatusEnum::IN_PROGRESS,
                false
            )
            ->withElectronic(
                BookingItemStatusEnum::UNCONFIRMED,
                1,
                1
            )
            ->create();

        /*
         * ========================================
         * EXTERNAL ONLY
         *
         * كل أنواع الخارجي الخمسة
         * UNCONFIRMED
         *
         * price_offer = true
         * contract = true
         * installation = false
         * extension = true
         * operation ما لازم يظهر
         * ========================================
         */

        $externalBooking = Booking::factory()
            ->for($externalCustomer)
            ->state([
                'booking_type' => 'external',
                'start_date' => now()
                    ->subDays(3)
                    ->toDateString(),
                'end_date' => now()
                    ->addDays(25)
                    ->toDateString(),
            ])
            ->withOrders(
                installation: false,
                extension: true,
                operation: false
            )
            ->withQuotation()
            ->withContract(
                ContractStatusEnum::FINISHED,
                true
            )
            ->withExternal(
                BookingItemStatusEnum::UNCONFIRMED,
                1
            )
            ->create();

    }
}