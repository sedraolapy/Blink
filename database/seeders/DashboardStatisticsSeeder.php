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
use App\Services\AdvertisingPeriod\AdvertisingPeriodService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DashboardStatisticsSeeder extends Seeder
{
    private const YEARS = [
        2025,
        2026,
        2027,
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $customers = $this->prepareCustomers();

            $this->prepareWorkingYears();
            $this->prepareSubscriptions($customers);

            $this->cleanupPreviousData($customers);

            foreach (self::YEARS as $year) {
                $bookings = $this->createConfirmedBookings(
                    $customers,
                    $year
                );

                $this->seedFlex(
                    $bookings,
                    $year
                );

                $this->seedElectronic(
                    $bookings,
                    $year
                );

                $this->seedExternal(
                    $bookings,
                    $year
                );
            }
        });

        $this->command?->info(
            'Dashboard statistics test data seeded successfully.'
        );
    }

    private function prepareCustomers(): Collection
    {
        return collect([
            [
                'phone' => '0999444401',

                'name' => [
                    'ar' => 'شركة إحصائيات ألفا',
                    'en' => 'Dashboard Alpha Company',
                ],
            ],

            [
                'phone' => '0999444402',

                'name' => [
                    'ar' => 'شركة إحصائيات بيتا',
                    'en' => 'Dashboard Beta Company',
                ],
            ],

            [
                'phone' => '0999444403',

                'name' => [
                    'ar' => 'شركة إحصائيات غاما',
                    'en' => 'Dashboard Gamma Company',
                ],
            ],
        ])->map(
            fn (array $data) =>
                Customer::query()->updateOrCreate(
                    [
                        'phone' => $data['phone'],
                    ],
                    [
                        'name' => $data['name'],
                    ]
                )
        );
    }

    private function prepareWorkingYears(): void
    {
        foreach (self::YEARS as $year) {
            WorkingYear::query()->firstOrCreate([
                'year' => $year,
            ]);
        }
    }

    private function prepareSubscriptions(
        Collection $customers
    ): void {
        $subscriptionTypes = [
            SubscriptionTypeEnum::GOLD,
            SubscriptionTypeEnum::SILVER,
            SubscriptionTypeEnum::BRONZE,
        ];

        foreach (
            $customers->values()
            as $index => $customer
        ) {
            foreach (self::YEARS as $year) {
                CustomerSubscription::query()
                    ->updateOrCreate(
                        [
                            'customer_id' =>
                                $customer->id,

                            'year' =>
                                $year,
                        ],
                        [
                            'subscription_type' =>
                                $subscriptionTypes[
                                    $index
                                ]->value,
                        ]
                    );
            }
        }
    }

    private function cleanupPreviousData(
        Collection $customers
    ): void {
        Booking::query()
            ->whereIn(
                'customer_id',
                $customers->pluck('id')
            )
            ->whereIn(
                'year',
                self::YEARS
            )
            ->delete();
    }

    private function createConfirmedBookings(
        Collection $customers,
        int $year
    ): Collection {
        return $customers
            ->values()
            ->map(
                function (
                    Customer $customer,
                    int $index
                ) use ($year) {
                    return Booking::query()
                        ->forceCreate([
                            'customer_id' =>
                                $customer->id,

                            'year' =>
                                $year,

                            'booking_type' =>
                                $index % 2 === 0
                                    ? BookingTypeEnum::LOCAL->value
                                    : BookingTypeEnum::FOREIGN->value,

                            'status' =>
                                BookingStatusEnum::CONFIRMED->value,

                            'installation_order' =>
                                true,

                            'extension_order' =>
                                $index === 1,

                            'operation_order' =>
                                true,

                            'created_at' =>
                                CarbonImmutable::create(
                                    $year,
                                    1,
                                    10 + $index
                                ),

                            'updated_at' =>
                                CarbonImmutable::create(
                                    $year,
                                    1,
                                    10 + $index
                                ),
                        ]);
                }
            );
    }

    /*
     * ============================================================
     * FLEX
     * ============================================================
     *
     * 10 assets.
     *
     * Occurrences:
     * Asset 1  => 18
     * Asset 2  => 17
     * ...
     * Asset 10 => 9
     *
     * The first period is always the current period
     * for the selected working year.
     *
     * Therefore all 10 seeded flex assets are booked
     * in flex_current_period.
     */
    private function seedFlex(
        Collection $bookings,
        int $year
    ): void {
        $assets = FlexBillboard::query()
            ->orderBy('id')
            ->skip(100)
            ->take(10)
            ->get();

        $this->requireCount(
            $assets,
            10,
            'flex billboards'
        );

        $periods = $this->getFlexPeriods(
            $year,
            18
        );

        $contexts = $bookings
            ->values()
            ->map(
                function (
                    Booking $booking
                ) use ($year) {
                    $flexBooking =
                        FlexBooking::query()->create([
                            'booking_id' =>
                                $booking->id,

                            'installation_order' =>
                                true,

                            'extension_order' =>
                                false,
                        ]);

                    $design =
                        FlexDesign::query()->create([
                            'flex_booking_id' =>
                                $flexBooking->id,

                            'name' =>
                                "Dashboard Flex {$year} - {$booking->id}",
                        ]);

                    return [
                        'booking' => $flexBooking,
                        'design' => $design,
                    ];
                }
            );

        foreach (
            $periods->values()
            as $periodIndex => $advertisingPeriod
        ) {
            $context = $contexts[
                $periodIndex
                % $contexts->count()
            ];

            $period =
                FlexBookingPeriod::query()->create([
                    'flex_booking_id' =>
                        $context['booking']->id,

                    'advertising_period_id' =>
                        $advertisingPeriod->id,
                ]);

            foreach (
                $assets->values()
                as $assetIndex => $asset
            ) {
                $targetOccurrences =
                    18 - $assetIndex;

                if (
                    $periodIndex
                    >= $targetOccurrences
                ) {
                    continue;
                }

                FlexBookingItem::query()->create([
                    'flex_booking_period_id' =>
                        $period->id,

                    'flex_billboard_id' =>
                        $asset->id,

                    'design_id' =>
                        $context['design']->id,

                    'has_dykat' =>
                        $assetIndex % 2 === 0,

                    'is_gift' =>
                        false,

                    'status' =>
                        BookingItemStatusEnum::BOOKED->value,
                ]);
            }
        }
    }

    /*
     * ============================================================
     * ELECTRONIC
     * ============================================================
     *
     * Standalone screens:
     * 20, 19, 18 ... 11 occurrences.
     *
     * Networks:
     * Network 1 => 18 periods
     * Network 2 => 15 periods
     * Network 3 => 12 periods
     *
     * Every network period creates an item for every
     * physical screen, exactly like the real booking flow.
     *
     * Dashboard still counts the network once per period.
     */
    private function seedElectronic(
        Collection $bookings,
        int $year
    ): void {
        $screens = LedScreen::query()
            ->whereNull('network_id')
            ->orderBy('id')
            ->skip(100)
            ->take(10)
            ->get();

        $this->requireCount(
            $screens,
            10,
            'independent LED screens'
        );

        $networks = LedNetwork::query()
            ->with('screens')
            ->whereHas('screens')
            ->orderBy('id')
            ->take(3)
            ->get();

        $this->requireCount(
            $networks,
            3,
            'LED networks'
        );

        $contexts = $bookings
            ->values()
            ->map(
                function (
                    Booking $booking
                ) use ($year) {
                    $ledBooking =
                        LedBooking::query()->create([
                            'booking_id' =>
                                $booking->id,

                            'operation_order' =>
                                true,
                        ]);

                    $design =
                        LedDesign::query()->create([
                            'led_booking_id' =>
                                $ledBooking->id,

                            'name' =>
                                "Dashboard Electronic {$year} - {$booking->id}",
                        ]);

                    return [
                        'booking' => $ledBooking,
                        'design' => $design,
                    ];
                }
            );

        $networkOccurrences = [
            18,
            15,
            12,
        ];

        for (
            $periodIndex = 0;
            $periodIndex < 20;
            $periodIndex++
        ) {
            $context = $contexts[
                $periodIndex
                % $contexts->count()
            ];

            $dates = $this->periodDates(
                $year,
                $periodIndex,
                16,
                7
            );

            $period =
                LedBookingPeriod::query()->create([
                    'led_booking_id' =>
                        $context['booking']->id,

                    'start_date' =>
                        $dates['start'],

                    'end_date' =>
                        $dates['end'],
                ]);

            /*
             * Standalone screens.
             */
            foreach (
                $screens->values()
                as $screenIndex => $screen
            ) {
                $targetOccurrences =
                    20 - $screenIndex;

                if (
                    $periodIndex
                    >= $targetOccurrences
                ) {
                    continue;
                }

                $this->createLedItem(
                    period: $period,
                    screen: $screen,
                    design: $context['design'],
                    networkId: null
                );
            }

            /*
             * Networks.
             */
            foreach (
                $networks->values()
                as $networkIndex => $network
            ) {
                if (
                    $periodIndex
                    >= $networkOccurrences[
                        $networkIndex
                    ]
                ) {
                    continue;
                }

                foreach (
                    $network->screens
                    as $networkScreen
                ) {
                    $this->createLedItem(
                        period: $period,
                        screen: $networkScreen,
                        design: $context['design'],
                        networkId: $network->id
                    );
                }
            }
        }
    }

    private function createLedItem(
        LedBookingPeriod $period,
        LedScreen $screen,
        LedDesign $design,
        ?int $networkId
    ): void {
        $item = LedBookingItem::query()
            ->forceCreate([
                'led_booking_period_id' =>
                    $period->id,

                'led_screen_id' =>
                    $screen->id,

                'led_network_id' =>
                    $networkId,

                'is_gift' =>
                    false,

                'status' =>
                    BookingItemStatusEnum::BOOKED->value,
            ]);

        LedBookingSlide::query()->create([
            'led_booking_item_id' =>
                $item->id,

            'design_id' =>
                $design->id,

            'slide_number' =>
                1,
        ]);
    }

    /*
     * ============================================================
     * OUTDOOR
     * ============================================================
     *
     * Each type gets 10 different assets with
     * a descending request distribution.
     *
     * mural    => 24 .. 15
     * rooftop  => 22 .. 13
     * tunnel   => 20 .. 11
     * bridge   => 18 .. 9
     * unipole  => 16 .. 7
     *
     * This makes Mural the expected overall leader
     * in the dashboard data generated by this seeder.
     */
    private function seedExternal(
        Collection $bookings,
        int $year
    ): void {
        $externalBookings = $bookings
            ->values()
            ->map(
                fn (Booking $booking) =>
                    ExternalBooking::query()
                        ->create([
                            'booking_id' =>
                                $booking->id,

                            'installation_order' =>
                                true,

                            'extension_order' =>
                                false,
                        ])
            );

        $maximumOccurrences = [
            ExternalAssetTypeEnum::MURAL->value =>
                24,

            ExternalAssetTypeEnum::ROOFTOP->value =>
                22,

            ExternalAssetTypeEnum::TUNNEL->value =>
                20,

            ExternalAssetTypeEnum::BRIDGE->value =>
                18,

            ExternalAssetTypeEnum::UNIPOLE->value =>
                16,
        ];

        foreach (
            ExternalAssetTypeEnum::cases()
            as $type
        ) {
            $assets = ExternalAsset::query()
                ->type($type)
                ->orderBy('id')
                ->skip(100)
                ->take(10)
                ->get();

            $this->requireCount(
                $assets,
                10,
                "{$type->value} assets"
            );

            $contexts = $externalBookings
                ->values()
                ->map(
                    function (
                        ExternalBooking $externalBooking
                    ) use (
                        $type,
                        $year
                    ) {
                        $bookingType =
                            ExternalBookingType::query()
                                ->create([
                                    'external_booking_id' =>
                                        $externalBooking->id,

                                    'type' =>
                                        $type->value,
                                ]);

                        $design =
                            ExternalDesign::query()
                                ->create([
                                    'external_booking_type_id' =>
                                        $bookingType->id,

                                    'name' =>
                                        "Dashboard {$type->value} {$year} - {$externalBooking->id}",
                                ]);

                        return [
                            'type' =>
                                $bookingType,

                            'design' =>
                                $design,
                        ];
                    }
                );

            $maxOccurrences =
                $maximumOccurrences[
                    $type->value
                ];

            for (
                $periodIndex = 0;
                $periodIndex < $maxOccurrences;
                $periodIndex++
            ) {
                $context = $contexts[
                    $periodIndex
                    % $contexts->count()
                ];

                $dates = $this->periodDates(
                    $year,
                    $periodIndex,
                    13,
                    7
                );

                $period =
                    ExternalBookingPeriod::query()
                        ->create([
                            'external_booking_type_id' =>
                                $context['type']->id,

                            'start_date' =>
                                $dates['start'],

                            'end_date' =>
                                $dates['end'],
                        ]);

                foreach (
                    $assets->values()
                    as $assetIndex => $asset
                ) {
                    $targetOccurrences =
                        $maxOccurrences
                        - $assetIndex;

                    if (
                        $periodIndex
                        >= $targetOccurrences
                    ) {
                        continue;
                    }

                    ExternalBookingItem::query()
                        ->create([
                            'external_booking_period_id' =>
                                $period->id,

                            'external_asset_id' =>
                                $asset->id,

                            'design_id' =>
                                $context['design']->id,

                            'is_gift' =>
                                false,

                            'status' =>
                                BookingItemStatusEnum::BOOKED->value,
                        ]);
                }
            }
        }
    }

    private function getFlexPeriods(
        int $year,
        int $count
    ): Collection {
        $currentPeriod =
            app(
                AdvertisingPeriodService::class
            )->getCurrentPeriodForYear(
                $year
            );

        $periodNumbers = collect(
            range(0, $count - 1)
        )
            ->map(
                fn (int $offset) =>
                    (
                        (
                            $currentPeriod->number
                            - 1
                            + $offset
                        )
                        % 26
                    )
                    + 1
            )
            ->values();

        $positions = array_flip(
            $periodNumbers->all()
        );

        $periods = AdvertisingPeriod::query()
            ->whereIn(
                'number',
                $periodNumbers
            )
            ->get()
            ->sortBy(
                fn (
                    AdvertisingPeriod $period
                ) =>
                    $positions[
                        $period->number
                    ]
            )
            ->values();

        $this->requireCount(
            $periods,
            $count,
            'advertising periods'
        );

        return $periods;
    }

    private function periodDates(
        int $year,
        int $index,
        int $stepDays,
        int $durationDays
    ): array {
        $start = CarbonImmutable::create(
            $year,
            1,
            3
        )->addDays(
            $index * $stepDays
        );

        return [
            'start' =>
                $start->toDateString(),

            'end' =>
                $start
                    ->addDays($durationDays)
                    ->toDateString(),
        ];
    }

    private function requireCount(
        Collection $items,
        int $required,
        string $label
    ): void {
        if (
            $items->count()
            >= $required
        ) {
            return;
        }

        throw new RuntimeException(
            "DashboardStatisticsSeeder requires at least {$required} {$label}. Found {$items->count()}."
        );
    }
}