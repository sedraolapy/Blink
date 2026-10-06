<?php

namespace Database\Seeders;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\ExternalAssetTypeEnum;
use App\Enums\SubscriptionTypeEnum;
use App\Models\AdvertisingPeriod;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\ExternalAsset;
use App\Models\ExternalBooking;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingPeriod;
use App\Models\ExternalBookingType;
use App\Models\ExternalDesign;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\FlexDesign;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingPeriod;
use App\Models\LedBookingSlide;
use App\Models\LedDesign;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use App\Models\WorkingYear;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MixedWorkingYearsBookingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $years = [
                2025,
                2027,
            ];

            foreach ($years as $year) {
                WorkingYear::query()->firstOrCreate([
                    'year' => $year,
                ]);
            }

            $customer = Customer::query()->updateOrCreate(
                [
                    'phone' => '0999333333',
                ],
                [
                    'name' => [
                        'ar' => 'زبون اختبار سنوات العمل',
                        'en' => 'Working Years Test Customer',
                    ],
                ]
            );

            foreach ($years as $year) {
                CustomerSubscription::query()->updateOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'year' => $year,
                    ],
                    [
                        'subscription_type' =>
                            $year === 2025
                                ? SubscriptionTypeEnum::SILVER->value
                                : SubscriptionTypeEnum::GOLD->value,
                    ]
                );
            }

            CustomerSubscription::query()->firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'year' => now()->year,
                ],
                [
                    'subscription_type' =>
                        SubscriptionTypeEnum::BRONZE->value,
                ]
            );

            /*
             * Cleanup only this seeder's old bookings.
             */
            Booking::query()
                ->where('customer_id', $customer->id)
                ->whereIn('year', $years)
                ->delete();

            $bookingTypes = [
                BookingTypeEnum::LOCAL,
                BookingTypeEnum::FOREIGN,
            ];

            $bookingStatuses = [
                BookingStatusEnum::CONFIRMED,
                BookingStatusEnum::UNCONFIRMED,
            ];

            /*
             * 4 scenarios per year:
             *
             * 0 => local   + confirmed
             * 1 => local   + unconfirmed
             * 2 => foreign + confirmed
             * 3 => foreign + unconfirmed
             */
            foreach ($years as $year) {
                $scenarioIndex = 0;

                foreach ($bookingTypes as $bookingType) {
                    foreach ($bookingStatuses as $bookingStatus) {
                        $this->createScenario(
                            customer: $customer,
                            year: $year,
                            bookingType: $bookingType,
                            bookingStatus: $bookingStatus,
                            scenarioIndex: $scenarioIndex,
                        );

                        $scenarioIndex++;
                    }
                }
            }
        });
    }

    private function createScenario(
        Customer $customer,
        int $year,
        BookingTypeEnum $bookingType,
        BookingStatusEnum $bookingStatus,
        int $scenarioIndex
    ): void {
        $months = [
            1,
            4,
            7,
            10,
        ];

        $month = $months[$scenarioIndex];

        $createdAt = CarbonImmutable::create(
            $year,
            $month,
            1,
            10
        );

        $booking = Booking::query()->forceCreate([
            'customer_id' => $customer->id,

            'year' => $year,

            'booking_type' =>
                $bookingType->value,

            'status' =>
                $bookingStatus->value,

            'installation_order' =>
                $scenarioIndex % 2 === 0,

            'extension_order' =>
                $scenarioIndex % 2 !== 0,

            'operation_order' =>
                $scenarioIndex >= 2,

            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $itemStatus =
            $bookingStatus === BookingStatusEnum::CONFIRMED
                ? BookingItemStatusEnum::BOOKED
                : BookingItemStatusEnum::UNCONFIRMED;

        $this->createFlex(
            booking: $booking,
            status: $itemStatus,
            scenarioIndex: $scenarioIndex,
        );

        $this->createElectronic(
            booking: $booking,
            year: $year,
            month: $month,
            scenarioIndex: $scenarioIndex,
        );

        $this->createOutdoor(
            booking: $booking,
            year: $year,
            month: $month,
            status: $itemStatus,
            scenarioIndex: $scenarioIndex,
        );
    }

    private function createFlex(
        Booking $booking,
        BookingItemStatusEnum $status,
        int $scenarioIndex
    ): void {
        $periodNumbers = [
            2,
            8,
            14,
            20,
        ];

        $advertisingPeriod =
            AdvertisingPeriod::query()
                ->where(
                    'number',
                    $periodNumbers[$scenarioIndex]
                )
                ->firstOrFail();

        /*
         * Start from offset 20 so we do not collide
         * with the older API test seeders.
         */
        $billboard = FlexBillboard::query()
            ->orderBy('id')
            ->skip(20 + $scenarioIndex)
            ->firstOrFail();

        $flexBooking = FlexBooking::query()->create([
            'booking_id' => $booking->id,

            'installation_order' =>
                (bool) $booking->installation_order,

            'extension_order' =>
                (bool) $booking->extension_order,
        ]);

        $design = FlexDesign::query()->create([
            'flex_booking_id' =>
                $flexBooking->id,

            'name' =>
                "Flex {$booking->year} {$booking->booking_type->value} {$booking->status->value}",
        ]);

        $period = FlexBookingPeriod::query()->create([
            'flex_booking_id' =>
                $flexBooking->id,

            'advertising_period_id' =>
                $advertisingPeriod->id,
        ]);

        FlexBookingItem::query()->create([
            'flex_booking_period_id' =>
                $period->id,

            'flex_billboard_id' =>
                $billboard->id,

            'design_id' =>
                $design->id,

            'has_dykat' =>
                $scenarioIndex % 2 === 0,

            'is_gift' =>
                $scenarioIndex % 2 !== 0,

            'status' =>
                $status->value,
        ]);
    }

    private function createElectronic(
        Booking $booking,
        int $year,
        int $month,
        int $scenarioIndex
    ): void {
        $ledBooking = LedBooking::query()->create([
            'booking_id' =>
                $booking->id,

            'operation_order' =>
                (bool) $booking->operation_order,
        ]);

        $design = LedDesign::query()->create([
            'led_booking_id' =>
                $ledBooking->id,

            'name' =>
                "Electronic {$year} {$booking->booking_type->value} {$booking->status->value}",
        ]);

        $period = LedBookingPeriod::query()->create([
            'led_booking_id' =>
                $ledBooking->id,

            'start_date' =>
                CarbonImmutable::create(
                    $year,
                    $month,
                    5
                )->toDateString(),

            'end_date' =>
                CarbonImmutable::create(
                    $year,
                    $month,
                    15
                )->toDateString(),
        ]);

        /*
         * Standalone screen.
         */
        $standaloneScreen =
            LedScreen::query()
                ->whereNull('network_id')
                ->orderBy('id')
                ->skip(10 + $scenarioIndex)
                ->firstOrFail();

        $standaloneItem =
            LedBookingItem::query()->create([
                'led_booking_period_id' =>
                    $period->id,

                'led_screen_id' =>
                    $standaloneScreen->id,

                'led_network_id' =>
                    null,

                'is_gift' =>
                    $scenarioIndex % 2 !== 0,
            ]);

        LedBookingSlide::query()->create([
            'led_booking_item_id' =>
                $standaloneItem->id,

            'design_id' =>
                $design->id,

            'slide_number' => 1,
        ]);

        /*
         * Network + every physical screen inside it.
         */
        $network = LedNetwork::query()
            ->with('screens')
            ->whereHas('screens')
            ->orderBy('id')
            ->firstOrFail();

        foreach ($network->screens as $networkScreen) {
            $networkItem =
                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $period->id,

                    'led_screen_id' =>
                        $networkScreen->id,

                    'led_network_id' =>
                        $network->id,

                    'is_gift' =>
                        $scenarioIndex % 2 === 0,
                ]);

            LedBookingSlide::query()->create([
                'led_booking_item_id' =>
                    $networkItem->id,

                'design_id' =>
                    $design->id,

                'slide_number' => 1,
            ]);
        }
    }

    private function createOutdoor(
        Booking $booking,
        int $year,
        int $month,
        BookingItemStatusEnum $status,
        int $scenarioIndex
    ): void {
        $externalBooking =
            ExternalBooking::query()->create([
                'booking_id' =>
                    $booking->id,
            ]);

        foreach (
            ExternalAssetTypeEnum::cases()
            as $externalType
        ) {
            $type =
                ExternalBookingType::query()->create([
                    'external_booking_id' =>
                        $externalBooking->id,

                    'type' =>
                        $externalType->value,
                ]);

            $design =
                ExternalDesign::query()->create([
                    'external_booking_type_id' =>
                        $type->id,

                    'name' =>
                        ucfirst($externalType->value)
                        . " {$year} "
                        . $booking->booking_type->value
                        . ' '
                        . $booking->status->value,
                ]);

            $period =
                ExternalBookingPeriod::query()->create([
                    'external_booking_type_id' =>
                        $type->id,

                    'start_date' =>
                        CarbonImmutable::create(
                            $year,
                            $month,
                            5
                        )->toDateString(),

                    'end_date' =>
                        CarbonImmutable::create(
                            $year,
                            $month,
                            15
                        )->toDateString(),
                ]);

            /*
             * Different asset per scenario inside
             * the same year.
             *
             * Same offsets are intentionally reused
             * between 2025 and 2027 to test year isolation.
             */
            $asset = ExternalAsset::query()
                ->where(
                    'type',
                    $externalType->value
                )
                ->orderBy('id')
                ->skip(20 + $scenarioIndex)
                ->firstOrFail();

            ExternalBookingItem::query()->create([
                'external_booking_period_id' =>
                    $period->id,

                'external_asset_id' =>
                    $asset->id,

                'design_id' =>
                    $design->id,

                'is_gift' =>
                    $scenarioIndex % 2 !== 0,

                'status' =>
                    $status->value,
            ]);
        }
    }
}