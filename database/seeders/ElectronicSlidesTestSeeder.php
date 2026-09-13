<?php

namespace Database\Seeders;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
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

            Booking::query()
                ->where('customer_id', $customer->id)
                ->delete();

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

            $screen1 = $network->screens
                ->values()
                ->get(0);

            $screen2 = $network->screens
                ->values()
                ->get(1);

            /*
             * Confirmed booking
             */
            $confirmedBooking = Booking::query()->create([
                'customer_id' => $customer->id,
                'booking_type' => BookingTypeEnum::LOCAL->value,
                'status' => BookingStatusEnum::CONFIRMED->value,

                'installation_order' => false,
                'extension_order' => false,
                'operation_order' => false,
            ]);

            $confirmedLedBooking = LedBooking::query()->create([
                'booking_id' => $confirmedBooking->id,
            ]);

            $confirmedDesigns = [];

            foreach ([
                'Standalone Design 1',
                'Standalone Design 2',
                'Standalone Design 3',

                'Network Screen 1 Design 1',
                'Network Screen 1 Design 2',

                'Network Screen 2 Design 1',
                'Network Screen 2 Design 2',
                'Network Screen 2 Design 3',
            ] as $name) {
                $confirmedDesigns[$name] =
                    LedDesign::query()->create([
                        'led_booking_id' =>
                            $confirmedLedBooking->id,

                        'name' => $name,
                    ]);
            }

            /*
             * Confirmed standalone screen
             */
            $standalonePeriod =
                LedBookingPeriod::query()->create([
                    'led_booking_id' =>
                        $confirmedLedBooking->id,

                    'start_date' => '2026-09-01',
                    'end_date' => '2026-09-30',
                ]);

            $standaloneItem =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $standalonePeriod->id,

                    'led_screen_id' =>
                        $standaloneScreen->id,

                    'led_network_id' => null,

                    'is_gift' => false,
                ]);

            $this->createSlide(
                $standaloneItem->id,
                $confirmedDesigns[
                    'Standalone Design 1'
                ]->id,
                1
            );

            $this->createSlide(
                $standaloneItem->id,
                $confirmedDesigns[
                    'Standalone Design 2'
                ]->id,
                2
            );

            $this->createSlide(
                $standaloneItem->id,
                $confirmedDesigns[
                    'Standalone Design 3'
                ]->id,
                3
            );

            /*
             * Confirmed network
             */
            $networkPeriod =
                LedBookingPeriod::query()->create([
                    'led_booking_id' =>
                        $confirmedLedBooking->id,

                    'start_date' => '2026-10-01',
                    'end_date' => '2026-10-31',
                ]);

            $networkScreen1Item =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $networkPeriod->id,

                    'led_screen_id' =>
                        $screen1->id,

                    'led_network_id' =>
                        $network->id,

                    'is_gift' => false,
                ]);

            $networkScreen2Item =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $networkPeriod->id,

                    'led_screen_id' =>
                        $screen2->id,

                    'led_network_id' =>
                        $network->id,

                    'is_gift' => false,
                ]);

            $this->createSlide(
                $networkScreen1Item->id,
                $confirmedDesigns[
                    'Network Screen 1 Design 1'
                ]->id,
                1
            );

            $this->createSlide(
                $networkScreen1Item->id,
                $confirmedDesigns[
                    'Network Screen 1 Design 2'
                ]->id,
                2
            );

            $this->createSlide(
                $networkScreen2Item->id,
                $confirmedDesigns[
                    'Network Screen 2 Design 1'
                ]->id,
                1
            );

            $this->createSlide(
                $networkScreen2Item->id,
                $confirmedDesigns[
                    'Network Screen 2 Design 2'
                ]->id,
                2
            );

            $this->createSlide(
                $networkScreen2Item->id,
                $confirmedDesigns[
                    'Network Screen 2 Design 3'
                ]->id,
                3
            );

            /*
             * Unconfirmed booking
             */
            $unconfirmedBooking =
                Booking::query()->create([
                    'customer_id' => $customer->id,
                    'booking_type' =>
                        BookingTypeEnum::LOCAL->value,

                    'status' =>
                        BookingStatusEnum::UNCONFIRMED->value,

                    'installation_order' => false,
                    'extension_order' => false,
                    'operation_order' => false,
                ]);

            $unconfirmedLedBooking =
                LedBooking::query()->create([
                    'booking_id' =>
                        $unconfirmedBooking->id,
                ]);

            $unconfirmedDesigns = [];

            foreach ([
                'Unconfirmed Standalone Design',
                'Unconfirmed Network Screen 1 Design',
                'Unconfirmed Network Screen 2 Design',
            ] as $name) {
                $unconfirmedDesigns[$name] =
                    LedDesign::query()->create([
                        'led_booking_id' =>
                            $unconfirmedLedBooking->id,

                        'name' => $name,
                    ]);
            }

            /*
             * Unconfirmed standalone
             */
            $unconfirmedStandalonePeriod =
                LedBookingPeriod::query()->create([
                    'led_booking_id' =>
                        $unconfirmedLedBooking->id,

                    'start_date' => '2026-11-01',
                    'end_date' => '2026-11-30',
                ]);

            $unconfirmedStandaloneItem =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $unconfirmedStandalonePeriod->id,

                    'led_screen_id' =>
                        $standaloneScreen->id,

                    'led_network_id' => null,

                    'is_gift' => false,
                ]);

            $this->createSlide(
                $unconfirmedStandaloneItem->id,
                $unconfirmedDesigns[
                    'Unconfirmed Standalone Design'
                ]->id,
                1
            );

            /*
             * Unconfirmed network
             */
            $unconfirmedNetworkPeriod =
                LedBookingPeriod::query()->create([
                    'led_booking_id' =>
                        $unconfirmedLedBooking->id,

                    'start_date' => '2026-12-01',
                    'end_date' => '2026-12-31',
                ]);

            $unconfirmedNetworkScreen1Item =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $unconfirmedNetworkPeriod->id,

                    'led_screen_id' =>
                        $screen1->id,

                    'led_network_id' =>
                        $network->id,

                    'is_gift' => false,
                ]);

            $unconfirmedNetworkScreen2Item =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $unconfirmedNetworkPeriod->id,

                    'led_screen_id' =>
                        $screen2->id,

                    'led_network_id' =>
                        $network->id,

                    'is_gift' => false,
                ]);

            $this->createSlide(
                $unconfirmedNetworkScreen1Item->id,
                $unconfirmedDesigns[
                    'Unconfirmed Network Screen 1 Design'
                ]->id,
                1
            );

            $this->createSlide(
                $unconfirmedNetworkScreen2Item->id,
                $unconfirmedDesigns[
                    'Unconfirmed Network Screen 2 Design'
                ]->id,
                1
            );
        });
    }

    private function createSlide(
        int $itemId,
        int $designId,
        int $slideNumber
    ): void {
        LedBookingSlide::query()->create([
            'led_booking_item_id' =>
                $itemId,

            'design_id' =>
                $designId,

            'slide_number' =>
                $slideNumber,
        ]);
    }
}