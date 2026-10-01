<?php

namespace Database\Factories;

use App\Enums\BookingItemStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Enums\ContractStatusEnum;
use App\Enums\ExternalAssetTypeEnum;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\ExternalAsset;
use App\Models\ExternalBooking;
use App\Models\ExternalBookingItem;
use App\Models\ExternalBookingPeriod;
use App\Models\ExternalBookingType;
use App\Models\FlexBillboard;
use App\Models\FlexBooking;
use App\Models\FlexBookingItem;
use App\Models\FlexBookingPeriod;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingPeriod;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use App\Models\Quotation;
use App\Services\AdvertisingPeriod\AdvertisingPeriodService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::query()
                ->inRandomOrder()
                ->value('id'),

            'booking_type' => fake()->randomElement([
                BookingTypeEnum::LOCAL->value,
                BookingTypeEnum::FOREIGN->value,
            ]),

            'installation_order' => false,
            'extension_order' => false,
            'operation_order' => false,
        ];
    }

    public function withOrders(
        bool $installation = false,
        bool $extension = false,
        bool $operation = false
    ): static {
        return $this->state([
            'installation_order' => $installation,
            'extension_order' => $extension,
            'operation_order' => $operation,
        ]);
    }

    public function withQuotation(): static
    {
        return $this->afterCreating(function (Booking $booking) {
            Quotation::query()->create([
                'booking_id' => $booking->id,
                'calculated_total' => 10_000_000,
                'final_total' => 9_500_000,
            ]);
        });
    }

    public function withContract(
        ContractStatusEnum $status,
        bool $withImage = false
    ): static {
        return $this->afterCreating(
            function (Booking $booking) use (
                $status,
                $withImage
            ) {
                $contractStartDate = $booking->created_at
                    ?->copy()
                    ?? now();

                $contractEndDate = $contractStartDate
                    ->copy()
                    ->addDays(30);

                $contract = Contract::query()->create([
                    'booking_id' => $booking->id,

                    'start_date' =>
                        $contractStartDate->toDateString(),

                    'end_date' =>
                        $contractEndDate->toDateString(),

                    'status' => $status->value,
                ]);

                if ($withImage) {
                    $this->attachFakeContractImage(
                        $contract
                    );
                }
            }
        );
    }

    public function withFlex(
        BookingItemStatusEnum $status,
        int $assetOffset = 0
    ): static {
        return $this->afterCreating(
            function (Booking $booking) use (
                $status,
                $assetOffset
            ) {
                $billboard = FlexBillboard::query()
                    ->orderBy('id')
                    ->skip($assetOffset)
                    ->firstOrFail();

                $flexBooking = FlexBooking::query()->create([
                    'booking_id' => $booking->id,
                ]);

                $advertisingPeriodId = app(
                    AdvertisingPeriodService::class
                )->getCurrentPeriodId();

                $period = FlexBookingPeriod::query()->create([
                    'flex_booking_id' =>
                        $flexBooking->id,

                    'advertising_period_id' =>
                        $advertisingPeriodId,
                ]);

                FlexBookingItem::query()->create([
                    'flex_booking_period_id' =>
                        $period->id,

                    'flex_billboard_id' =>
                        $billboard->id,

                    'design_id' => null,

                    'has_dykat' => false,
                    'is_gift' => false,

                    'status' => $status->value,
                ]);
            }
        );
    }

    public function withElectronic(
        BookingItemStatusEnum $status,
        int $screenOffset = 0,
        int $networkOffset = 0
    ): static {
        return $this->afterCreating(
            function (Booking $booking) use (
                $status,
                $screenOffset,
                $networkOffset
            ) {
                $ledBooking = LedBooking::query()->create([
                    'booking_id' => $booking->id,
                ]);

                $period = LedBookingPeriod::query()->create([
                    'led_booking_id' =>
                        $ledBooking->id,

                    'start_date' => now()
                        ->subDays(7)
                        ->toDateString(),

                    'end_date' => now()
                        ->addDays(7)
                        ->toDateString(),
                ]);

                /*
                 * Standalone Screen
                 */

                $screen = LedScreen::query()
                    ->whereNull('network_id')
                    ->orderBy('id')
                    ->skip($screenOffset)
                    ->firstOrFail();

                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $period->id,

                    'led_screen_id' =>
                        $screen->id,

                    'led_network_id' => null,


                    'is_gift' => false,

                    'status' => $status->value,
                ]);

                /*
                 * Network
                 */

                $network = LedNetwork::query()
                    ->orderBy('id')
                    ->skip($networkOffset)
                    ->firstOrFail();

                LedBookingItem::query()->create([
                    'led_booking_period_id' =>
                        $period->id,

                    'led_screen_id' => null,

                    'led_network_id' =>
                        $network->id,

                    'is_gift' => false,

                    'status' => $status->value,
                ]);
            }
        );
    }

    public function withExternal(
        BookingItemStatusEnum $status,
        int $assetOffset = 0
    ): static {
        return $this->afterCreating(
            function (Booking $booking) use (
                $status,
                $assetOffset
            ) {
                $externalBooking =
                    ExternalBooking::query()->create([
                        'booking_id' => $booking->id,
                    ]);

                foreach (
                    ExternalAssetTypeEnum::cases() as $type
                ) {
                    $bookingType =
                        ExternalBookingType::query()->create([
                            'external_booking_id' =>
                                $externalBooking->id,

                            'type' => $type->value,
                        ]);

                    $period =
                        ExternalBookingPeriod::query()->create([
                            'external_booking_type_id' =>
                                $bookingType->id,

                            'start_date' => now()
                                ->subDays(7)
                                ->toDateString(),

                            'end_date' => now()
                                ->addDays(7)
                                ->toDateString(),
                        ]);

                    $asset = ExternalAsset::query()
                        ->where(
                            'type',
                            $type->value
                        )
                        ->orderBy('id')
                        ->skip($assetOffset)
                        ->firstOrFail();

                    ExternalBookingItem::query()->create([
                        'external_booking_period_id' =>
                            $period->id,

                        'external_asset_id' =>
                            $asset->id,

                        'design_id' => null,


                        'status' => $status->value,
                    ]);
                }
            }
        );
    }

    private function attachFakeContractImage(
        Contract $contract
    ): void {
        $directory = storage_path(
            'app/testing'
        );

        File::ensureDirectoryExists(
            $directory
        );

        $path = $directory
            . '/'
            . Str::uuid()
            . '.png';

        $image = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlK7XcAAAAASUVORK5CYII='
        );

        File::put(
            $path,
            $image
        );

        $contract
            ->addMedia($path)
            ->usingFileName(
                'contract-'
                . $contract->id
                . '.png'
            )
            ->toMediaCollection(
                'contract_images'
            );
    }
}