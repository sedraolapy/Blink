<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
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
         * Cleanup previous test data
         * ========================================
         */

        $this->cleanupPreviousTestData();

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

        Customer::query()->updateOrCreate(
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
         * Pagination Test
         *
         * 25 historical confirmed bookings
         * ========================================
         */

         for ($i = 25; $i >= 1; $i--) {
            $startDate = now()
                ->subMonths($i + 2)
                ->startOfMonth();

            $endDate = $startDate
                ->copy()
                ->addDays(20);

            $status = $i % 2 === 0
                ? BookingStatusEnum::CONFIRMED
                : BookingStatusEnum::UNCONFIRMED;

            Booking::factory()
                ->for($fullCustomer)
                ->state([
                    'booking_type' => 'internal',

                    'status' => $status->value,

                    'start_date' =>
                        $startDate->toDateString(),

                    'end_date' =>
                        $endDate->toDateString(),
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
         * Booking = CONFIRMED
         * Latest customer contract = PENDING
         * ========================================
         */

        Booking::factory()
            ->for($fullCustomer)
            ->state([
                'booking_type' => 'internal',

                'status' =>
                    BookingStatusEnum::CONFIRMED->value,

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
         * Booking = UNCONFIRMED
         *
         * price_offer = true
         * contract = false
         * installation = true
         * extension = false
         * operation لا يظهر
         * ========================================
         */

        Booking::factory()
            ->for($flexCustomer)
            ->state([
                'booking_type' => 'internal',

                'status' =>
                    BookingStatusEnum::UNCONFIRMED->value,

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
         * Booking = UNCONFIRMED
         *
         * price_offer = false
         * contract = false
         * operation = false
         *
         * installation / extension لا يظهروا
         * ========================================
         */

        Booking::factory()
            ->for($electronicCustomer)
            ->state([
                'booking_type' => 'internal',

                'status' =>
                    BookingStatusEnum::UNCONFIRMED->value,

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
         * Booking = UNCONFIRMED
         *
         * price_offer = true
         * contract = true
         * installation = false
         * extension = true
         * operation لا يظهر
         * ========================================
         */

        Booking::factory()
            ->for($externalCustomer)
            ->state([
                'booking_type' => 'external',

                'status' =>
                    BookingStatusEnum::UNCONFIRMED->value,

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

    private function cleanupPreviousTestData(): void
    {
        $phones = [
            '0999000001',
            '0999000002',
            '0999000003',
            '0999000004',
            '0999000005',
        ];

        $customers = Customer::query()
            ->whereIn('phone', $phones)
            ->get();

        foreach ($customers as $customer) {
            $bookings = Booking::query()
                ->where('customer_id', $customer->id)
                ->with('contract')
                ->get();

            foreach ($bookings as $booking) {
                $booking->contract?->clearMediaCollection(
                    'contract_images'
                );

                $booking->delete();
            }
        }
    }
}