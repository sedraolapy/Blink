<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingPeriod;
use App\Models\LedBookingSlide;
use App\Models\LedDesign;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElectronicSlidesTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
             * ========================================
             * Test Customer
             * ========================================
             */

            $customer = Customer::query()->updateOrCreate(
                [
                    'phone' => '0999222222',
                ],
                [
                    'name' => [
                        'ar' => 'زبون اختبار سلايدات الإلكتروني',
                        'en' => 'Electronic Slides Test Customer',
                    ],
                    'subscription_type' => 'bronze',
                ]
            );

            /*
             * ========================================
             * Cleanup previous test bookings
             * ========================================
             */

            Booking::query()
                ->where('customer_id', $customer->id)
                ->delete();

            /*
             * ========================================
             * Assets
             * ========================================
             */

            $standaloneScreen = LedScreen::query()
                ->whereNull('network_id')
                ->firstOrFail();

            $network = LedNetwork::query()
                ->with('screens')
                ->whereHas('screens')
                ->firstOrFail();

            if ($network->screens->count() < 2) {
                throw new \RuntimeException(
                    'Test network must contain at least 2 screens.'
                );
            }

            /*
             * ========================================
             * Main Booking
             * ========================================
             */

            $booking = Booking::query()->create([
                'customer_id' => $customer->id,
                'booking_type' => 'internal',

                'start_date' => '2026-09-01',
                'end_date' => '2026-12-31',

                'installation_order' => false,
                'extension_order' => false,
                'operation_order' => false,
            ]);

            $ledBooking = LedBooking::query()->create([
                'booking_id' => $booking->id,
            ]);

            /*
             * ========================================
             * Designs
             * ========================================
             */

            $designs = [];

            foreach ([
                'Standalone Design 1',
                'Standalone Design 2',
                'Standalone Design 3',

                'Network Screen 1 Design 1',
                'Network Screen 1 Design 2',

                'Network Screen 2 Design 1',
                'Network Screen 2 Design 2',
                'Network Screen 2 Design 3',

                'Unconfirmed Standalone Design',
                'Unconfirmed Network Screen 1 Design',
                'Unconfirmed Network Screen 2 Design',
            ] as $name) {
                $designs[$name] = LedDesign::query()->create([
                    'led_booking_id' => $ledBooking->id,
                    'name' => $name,
                ]);
            }

            /*
             * ========================================
             * PERIOD 1
             * Confirmed Standalone Screen
             * ========================================
             */

            $standalonePeriod = LedBookingPeriod::query()->create([
                'led_booking_id' => $ledBooking->id,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-30',
            ]);

            $standaloneItem = LedBookingItem::query()->create([
                'led_booking_period_id' => $standalonePeriod->id,

                'led_screen_id' => $standaloneScreen->id,
                'led_network_id' => null,

                'unit_price_at_booking' =>
                    $standaloneScreen->local_price ?? 0,

                'is_gift' => false,

                'status' =>
                    BookingItemStatusEnum::BOOKED->value,
            ]);

            $this->createSlide(
                $standaloneItem->id,
                $standaloneScreen->id,
                $designs['Standalone Design 1']->id,
                1
            );

            $this->createSlide(
                $standaloneItem->id,
                $standaloneScreen->id,
                $designs['Standalone Design 2']->id,
                2
            );

            $this->createSlide(
                $standaloneItem->id,
                $standaloneScreen->id,
                $designs['Standalone Design 3']->id,
                3
            );

            /*
             * ========================================
             * PERIOD 2
             * Confirmed Network
             *
             * كل شاشة إلها Slides مختلفة
             * ========================================
             */

            $networkPeriod = LedBookingPeriod::query()->create([
                'led_booking_id' => $ledBooking->id,
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-31',
            ]);

            $networkItem = LedBookingItem::query()->create([
                'led_booking_period_id' => $networkPeriod->id,

                'led_screen_id' => null,
                'led_network_id' => $network->id,

                'unit_price_at_booking' =>
                    $network->local_price ?? 0,

                'is_gift' => false,

                'status' =>
                    BookingItemStatusEnum::BOOKED->value,
            ]);

            $screen1 = $network->screens->values()->get(0);
            $screen2 = $network->screens->values()->get(1);

            /*
             * Screen 1
             */

            $this->createSlide(
                $networkItem->id,
                $screen1->id,
                $designs['Network Screen 1 Design 1']->id,
                1
            );

            $this->createSlide(
                $networkItem->id,
                $screen1->id,
                $designs['Network Screen 1 Design 2']->id,
                2
            );

            /*
             * Screen 2
             */

            $this->createSlide(
                $networkItem->id,
                $screen2->id,
                $designs['Network Screen 2 Design 1']->id,
                1
            );

            $this->createSlide(
                $networkItem->id,
                $screen2->id,
                $designs['Network Screen 2 Design 2']->id,
                2
            );

            $this->createSlide(
                $networkItem->id,
                $screen2->id,
                $designs['Network Screen 2 Design 3']->id,
                3
            );

            /*
             * ========================================
             * PERIOD 3
             * Unconfirmed Standalone Screen
             * ========================================
             */

            $unconfirmedStandalonePeriod =
                LedBookingPeriod::query()->create([
                    'led_booking_id' => $ledBooking->id,
                    'start_date' => '2026-11-01',
                    'end_date' => '2026-11-30',
                ]);

            $unconfirmedStandaloneItem =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $unconfirmedStandalonePeriod->id,

                    'led_screen_id' => $standaloneScreen->id,
                    'led_network_id' => null,

                    'unit_price_at_booking' =>
                        $standaloneScreen->local_price ?? 0,

                    'is_gift' => false,

                    'status' =>
                        BookingItemStatusEnum::UNCONFIRMED->value,
                ]);

            $this->createSlide(
                $unconfirmedStandaloneItem->id,
                $standaloneScreen->id,
                $designs['Unconfirmed Standalone Design']->id,
                1
            );

            /*
             * ========================================
             * PERIOD 4
             * Unconfirmed Network
             * ========================================
             */

            $unconfirmedNetworkPeriod =
                LedBookingPeriod::query()->create([
                    'led_booking_id' => $ledBooking->id,
                    'start_date' => '2026-12-01',
                    'end_date' => '2026-12-31',
                ]);

            $unconfirmedNetworkItem =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $unconfirmedNetworkPeriod->id,

                    'led_screen_id' => null,
                    'led_network_id' => $network->id,

                    'unit_price_at_booking' =>
                        $network->local_price ?? 0,

                    'is_gift' => false,

                    'status' =>
                        BookingItemStatusEnum::UNCONFIRMED->value,
                ]);

            $this->createSlide(
                $unconfirmedNetworkItem->id,
                $screen1->id,
                $designs[
                    'Unconfirmed Network Screen 1 Design'
                ]->id,
                1
            );

            $this->createSlide(
                $unconfirmedNetworkItem->id,
                $screen2->id,
                $designs[
                    'Unconfirmed Network Screen 2 Design'
                ]->id,
                1
            );

           
        });
    }

    private function createSlide(
        int $itemId,
        int $screenId,
        int $designId,
        int $slideNumber
    ): void {
        LedBookingSlide::query()->create([
            'led_booking_item_id' => $itemId,
            'led_screen_id' => $screenId,
            'design_id' => $designId,
            'slide_number' => $slideNumber,
        ]);
    }
}