<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\ContractStatusEnum;
use App\Enums\SubscriptionTypeEnum;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use Illuminate\Database\Seeder;

class BookingApiTestSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

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
            ]
        );

        $flexCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000002'],
            [
                'name' => [
                    'ar' => 'شركة اختبار فليكس',
                    'en' => 'Flex Test Company',
                ],
            ]
        );

        $electronicCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000003'],
            [
                'name' => [
                    'ar' => 'شركة اختبار إلكتروني',
                    'en' => 'Electronic Test Company',
                ],
            ]
        );

        $externalCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000004'],
            [
                'name' => [
                    'ar' => 'شركة اختبار خارجي',
                    'en' => 'Outdoor Test Company',
                ],
            ]
        );

        $emptyCustomer = Customer::query()->updateOrCreate(
            ['phone' => '0999000005'],
            [
                'name' => [
                    'ar' => 'زبون بدون حجوزات',
                    'en' => 'Customer Without Bookings',
                ],
            ]
        );

        /*
         * ========================================
         * Customer Subscriptions
         * ========================================
         */

        $this->createSubscription(
            $fullCustomer,
            $year,
            SubscriptionTypeEnum::GOLD
        );

        $this->createSubscription(
            $flexCustomer,
            $year,
            SubscriptionTypeEnum::SILVER
        );

        $this->createSubscription(
            $electronicCustomer,
            $year,
            SubscriptionTypeEnum::BRONZE
        );

        $this->createSubscription(
            $externalCustomer,
            $year,
            SubscriptionTypeEnum::GOLD
        );

        $this->createSubscription(
            $emptyCustomer,
            $year,
            SubscriptionTypeEnum::BRONZE
        );

        /*
         * ========================================
         * Pagination + Date Filter Test
         *
         * 25 bookings
         * Mixed confirmed / unconfirmed
         * ========================================
         */

        for ($i = 25; $i >= 1; $i--) {
            $createdAt = now()
                ->subDays($i + 30)
                ->startOfDay();

            $status = $i % 2 === 0
                ? BookingStatusEnum::CONFIRMED
                : BookingStatusEnum::UNCONFIRMED;

            Booking::factory()
                ->for($fullCustomer)
                ->state([
                    'year' => $year,

                    'booking_type' =>
                        BookingTypeEnum::LOCAL->value,

                    'status' => $status->value,

                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
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
         * Booking = CONFIRMED
         * ========================================
         */

        Booking::factory()
            ->for($fullCustomer)
            ->state([
                'year' => $year,

                'booking_type' =>
                    BookingTypeEnum::LOCAL->value,

                'status' =>
                    BookingStatusEnum::CONFIRMED->value,

                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ])
            ->withOrders(
                installation: true,
                extension: true,
                operation: true
            )
            ->withQuotation()
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
         * ========================================
         */

        Booking::factory()
            ->for($flexCustomer)
            ->state([
                'year' => $year,

                'booking_type' =>
                    BookingTypeEnum::LOCAL->value,

                'status' =>
                    BookingStatusEnum::UNCONFIRMED->value,

                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
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
         * ========================================
         */

        Booking::factory()
            ->for($electronicCustomer)
            ->state([
                'year' => $year,

                'booking_type' =>
                    BookingTypeEnum::LOCAL->value,

                'status' =>
                    BookingStatusEnum::UNCONFIRMED->value,

                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
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
         * Booking = UNCONFIRMED
         * ========================================
         */

        Booking::factory()
            ->for($externalCustomer)
            ->state([
                'year' => $year,

                'booking_type' =>
                    BookingTypeEnum::FOREIGN->value,

                'status' =>
                    BookingStatusEnum::UNCONFIRMED->value,

                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
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

    private function createSubscription(
        Customer $customer,
        int $year,
        SubscriptionTypeEnum $subscriptionType
    ): void {
        CustomerSubscription::query()->updateOrCreate(
            [
                'customer_id' => $customer->id,
                'year' => $year,
            ],
            [
                'subscription_type' =>
                    $subscriptionType->value,
            ]
        );
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

            $customer->subscriptions()->delete();
        }
    }
}