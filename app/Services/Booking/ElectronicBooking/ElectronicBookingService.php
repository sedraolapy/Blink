<?php

namespace App\Services\Booking\ElectronicBooking;

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingPeriod;
use App\Models\LedBookingSlide;
use App\Models\LedDesign;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ElectronicBookingService
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {}

    public function store(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $booking = $this->resolveBooking($data);

            $this->ensureElectronicBookingDoesNotExist(
                $booking
            );

            $periods = $this->preparePeriods(
                $data['periods']
            );

            $screens = $this->lockScreens(
                $periods
            );

            $this->validateScreenTypes(
                $periods,
                $screens
            );

            $this->validateCompleteNetworks(
                $periods
            );

            $ledBooking = LedBooking::create([
                'booking_id' => $booking->id,
            ]);

            $designs = $this->createDesigns(
                $ledBooking,
                $data['designs']
            );

            foreach ($periods as $periodData) {
                $period = LedBookingPeriod::create([
                    'led_booking_id' => $ledBooking->id,
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                ]);

                foreach (
                    $periodData['items']
                    as $itemData
                ) {
                    $item = LedBookingItem::create([
                        'led_booking_period_id' => $period->id,
                        'led_screen_id' => $itemData['screen_id'],
                        'led_network_id' => $itemData['network_id'],
                        'is_gift' => $itemData['is_gift'],
                    ]);

                    foreach (
                        $itemData['slides']
                        as $slideData
                    ) {
                        LedBookingSlide::create([
                            'led_booking_item_id' =>
                                $item->id,

                            'design_id' =>
                                $designs[
                                    $slideData['design_name']
                                ],

                            'slide_number' =>
                                $slideData['slide_number'],
                        ]);
                    }
                }
            }

            return $this->loadResult(
                $booking,
                $ledBooking
            );
        });
    }

    public function update(int $bookingId,array $data)
    {
        return DB::transaction(
            function () use (
                $bookingId,
                $data
            ) {
                $year = $this->workingYearContext->get();

                $booking = Booking::query()
                    ->where('year',$year)
                    ->lockForUpdate()
                    ->findOrFail($bookingId);

                $ledBooking = $booking
                    ->ledBooking()
                    ->lockForUpdate()
                    ->firstOrFail();

                $periods = $this->preparePeriods(
                    $data['periods']
                );

                $screens = $this->lockScreens(
                    $periods
                );

                $this->validateScreenTypes(
                    $periods,
                    $screens
                );

                $this->validateCompleteNetworks(
                    $periods
                );

                $designSync = $this->syncDesigns(
                    $ledBooking,
                    $data['designs']
                );

                $this->syncPeriods(
                    $ledBooking,
                    $periods,
                    $designSync['designs']
                );

                $this->deleteRemovedDesigns(
                    $ledBooking,
                    $designSync['delete_ids']
                );

                return $this->loadResult(
                    $booking,
                    $ledBooking
                );
            }
        );
    }

    private function syncDesigns(LedBooking $ledBooking,array $designNames)
    {
        $existingDesigns = $ledBooking
            ->designs()
            ->get()
            ->keyBy('name');

        $designs = [];
        $keptIds = [];

        foreach ($designNames as $designName) {
            $design = $existingDesigns->get(
                $designName
            );

            if (! $design) {
                $design = LedDesign::create([
                    'led_booking_id' =>
                        $ledBooking->id,

                    'name' =>
                        $designName,
                ]);
            }

            $designs[$designName] =
                $design->id;

            $keptIds[] =
                $design->id;
        }

        $deleteIds = $existingDesigns
            ->pluck('id')
            ->reject(
                fn ($id) =>
                    in_array(
                        $id,
                        $keptIds,
                        true
                    )
            )
            ->values()
            ->all();

        return [
            'designs' => $designs,
            'delete_ids' => $deleteIds,
        ];
    }

    private function deleteRemovedDesigns(LedBooking $ledBooking,array $deleteIds)
    {
        if ($deleteIds === []) {
            return;
        }

        LedDesign::query()
            ->where(
                'led_booking_id',
                $ledBooking->id
            )
            ->whereIn(
                'id',
                $deleteIds
            )
            ->delete();
    }

    private function syncPeriods(LedBooking $ledBooking,array $periods,array $designs)
    {
        $existingPeriods = $ledBooking
            ->periods()
            ->with([
                'items.slides',
            ])
            ->get();

        $periodPools = [];

        foreach (
            $existingPeriods
            as $existingPeriod
        ) {
            $key = $this->periodKey(
                $existingPeriod->start_date
                    ->toDateString(),
                $existingPeriod->end_date
                    ->toDateString()
            );

            $periodPools[$key][] =
                $existingPeriod;
        }

        $keptPeriodIds = [];

        foreach ($periods as $periodData) {
            $key = $this->periodKey(
                $periodData['start_date'],
                $periodData['end_date']
            );

            $period = null;

            if (
                isset($periodPools[$key])
                && $periodPools[$key] !== []
            ) {
                $period = array_shift(
                    $periodPools[$key]
                );
            }

            if (! $period) {
                $period = LedBookingPeriod::create([
                    'led_booking_id' => $ledBooking->id,
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                ]);

                $period->setRelation(
                    'items',
                    collect()
                );
            }

            $keptPeriodIds[] =
                $period->id;

            $this->syncPeriodItems(
                $period,
                $periodData['items'],
                $designs
            );
        }

        $periodsToDelete = $existingPeriods
            ->reject(
                fn (LedBookingPeriod $period) =>
                    in_array(
                        $period->id,
                        $keptPeriodIds,
                        true
                    )
            );

        foreach (
            $periodsToDelete
            as $period
        ) {
            $this->deletePeriod($period);
        }
    }

    private function syncPeriodItems(LedBookingPeriod $period,array $items,array $designs)
    {
        $existingItems = $period
            ->items
            ->keyBy(
                fn (LedBookingItem $item) =>
                    (int) $item->led_screen_id
            );

        $keptItemIds = [];

        foreach ($items as $itemData) {
            $screenId = (int)
                $itemData['screen_id'];

            $item = $existingItems->get(
                $screenId
            );

            if ($item) {
                $item->update([
                    'led_network_id' =>$itemData['network_id'],
                    'is_gift' => $itemData['is_gift'],
                ]);
            } else {
                $item = LedBookingItem::create([
                    'led_booking_period_id' =>$period->id,
                    'led_screen_id' => $screenId,
                    'led_network_id' => $itemData['network_id'],
                    'is_gift' => $itemData['is_gift'],
                ]);

                $item->setRelation(
                    'slides',
                    collect()
                );
            }

            $keptItemIds[] =
                $item->id;

            $this->syncItemSlides(
                $item,
                $itemData['slides'],
                $designs
            );
        }

        $itemsToDelete = $existingItems
            ->reject(
                fn (LedBookingItem $item) =>
                    in_array(
                        $item->id,
                        $keptItemIds,
                        true
                    )
            );

        foreach (
            $itemsToDelete
            as $item
        ) {
            $item->slides()->delete();
            $item->delete();
        }
    }

    private function syncItemSlides(LedBookingItem $item,array $slides,array $designs)
    {
        $existingSlides = $item
            ->slides
            ->keyBy(
                fn (LedBookingSlide $slide) =>
                    (int) $slide->slide_number
            );

        $keptSlideIds = [];

        foreach ($slides as $slideData) {
            $slideNumber = (int)
                $slideData['slide_number'];

            $slide = $existingSlides->get(
                $slideNumber
            );

            $designId = $designs[
                $slideData['design_name']
            ];

            if ($slide) {
                $slide->update([
                    'design_id' =>
                        $designId,
                ]);
            } else {
                $slide = LedBookingSlide::create([
                    'led_booking_item_id' => $item->id,
                    'design_id' => $designId,
                    'slide_number' => $slideNumber,
                ]);
            }

            $keptSlideIds[] =
                $slide->id;
        }

        $existingSlides
            ->reject(
                fn (LedBookingSlide $slide) =>
                    in_array(
                        $slide->id,
                        $keptSlideIds,
                        true
                    )
            )
            ->each(
                fn (LedBookingSlide $slide) =>
                    $slide->delete()
            );
    }

    private function deletePeriod(LedBookingPeriod $period)
    {
        $items = $period
            ->items()
            ->with('slides')
            ->get();

        foreach ($items as $item) {
            $item->slides()->delete();
            $item->delete();
        }

        $period->delete();
    }

    private function periodKey(
        string $startDate,
        string $endDate
    ): string {
        return "{$startDate}|{$endDate}";
    }

    private function loadResult(Booking $booking,LedBooking $ledBooking)
    {
        $booking->refresh();

        $booking->load([
            'flexBooking',
            'ledBooking',
            'externalBooking.types',
        ]);

        $ledBooking->refresh();

        $ledBooking->load([
            'designs',
            'periods.items.slides',
        ]);

        return [
            'booking' => $booking,
            'electronic_booking' =>
                $ledBooking,
        ];
    }

    private function validateCompleteNetworks(array $periods)
    {
        $networkIds = collect($periods)
            ->flatMap(
                fn (array $period) =>
                    collect($period['items'])
                        ->pluck('network_id')
                        ->filter()
            )
            ->unique()
            ->values();

        if ($networkIds->isEmpty()) {
            return;
        }

        $networks = LedNetwork::query()
            ->whereIn(
                'id',
                $networkIds
            )
            ->with([
                'screens:id,network_id',
            ])
            ->get()
            ->keyBy('id');

        foreach (
            $periods
            as $periodIndex => $period
        ) {
            $selectedNetworks = collect(
                $period['items']
            )
                ->whereNotNull('network_id')
                ->groupBy('network_id');

            foreach (
                $selectedNetworks
                as $networkId => $items
            ) {
                $network = $networks->get(
                    (int) $networkId
                );

                if (! $network) {
                    continue;
                }

                $requiredScreenIds = $network
                    ->screens
                    ->pluck('id')
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->sort()
                    ->values()
                    ->all();

                $selectedScreenIds = $items
                    ->pluck('screen_id')
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->sort()
                    ->values()
                    ->all();

                if (
                    $selectedScreenIds
                    !== $requiredScreenIds
                ) {
                    throw ValidationException::withMessages([
                        "periods.{$periodIndex}.networks" =>
                            __(
                                'validation.electronic_booking.networks.all_screens_required'
                            ),
                    ]);
                }
            }
        }
    }

    private function resolveBooking(array $data)
    {
        $year = $this->workingYearContext->get();

        if (! empty($data['booking_id'])) {
            $booking = Booking::query()
                ->where('year',$year)
                ->lockForUpdate()
                ->findOrFail(
                    $data['booking_id']
                );

            if (
                (int) $booking->customer_id
                !== (int) $data['customer_id']
            ) {
                throw ValidationException::withMessages([
                    'customer_id' => __(
                        'validation.electronic_booking.customer_id.booking_mismatch'
                    ),
                ]);
            }

            return $booking;
        }

        return Booking::create([
            'customer_id' => $data['customer_id'],
            'year' => $year,
            'booking_type' => $data['advertiser_type'],
            'status' => BookingStatusEnum::UNCONFIRMED->value,
        ]);
    }

    private function ensureElectronicBookingDoesNotExist(Booking $booking)
    {
        if (
            $booking
                ->ledBooking()
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'booking_id' => __(
                    'validation.electronic_booking.booking_id.already_exists'
                ),
            ]);
        }
    }

    private function createDesigns(LedBooking $ledBooking,array $designNames)
    {
        $designs = [];

        foreach (
            $designNames
            as $designName
        ) {
            $design = LedDesign::create([
                'led_booking_id' =>
                    $ledBooking->id,

                'name' =>
                    $designName,
            ]);

            $designs[$designName] =
                $design->id;
        }

        return $designs;
    }

    private function preparePeriods(array $periods)
    {
        $year = $this->workingYearContext->get();

        $prepared = [];

        foreach (
            $periods
            as $periodIndex => $period
        ) {
            $startDate = Carbon::parse(
                $period['start_date']
            );

            $endDate = Carbon::parse(
                $period['end_date']
            );

            if (
                $startDate->year !== $year
                || $endDate->year !== $year
            ) {
                throw ValidationException::withMessages([
                    "periods.{$periodIndex}" => __(
                        'validation.electronic_booking.periods.outside_working_year',
                        [
                            'year' => $year,
                        ]
                    ),
                ]);
            }

            $items = [];

            foreach (
                $period['screens'] ?? []
                as $screen
            ) {
                $this->validateSlides(
                    $screen['slides'],
                    "periods.{$periodIndex}.screens"
                );

                $items[] = [
                    'screen_id' =>
                        (int) $screen['screen_id'],

                    'network_id' =>
                        null,

                    'is_gift' =>
                        (bool) $screen['is_gift'],

                    'slides' =>
                        $screen['slides'],
                ];
            }

            foreach (
                $period['networks'] ?? []
                as $networkIndex => $network
            ) {
                foreach (
                    $network['screens']
                    as $screen
                ) {
                    $this->validateSlides(
                        $screen['slides'],
                        "periods.{$periodIndex}.networks.{$networkIndex}.screens"
                    );

                    $items[] = [
                        'screen_id' =>
                            (int) $screen['screen_id'],

                        'network_id' =>
                            (int) $network['network_id'],

                        'is_gift' =>
                            (bool) $network['is_gift'],

                        'slides' =>
                            $screen['slides'],
                    ];
                }
            }

            if ($items === []) {
                throw ValidationException::withMessages([
                    "periods.{$periodIndex}" => __(
                        'validation.electronic_booking.periods.items_required'
                    ),
                ]);
            }

            $screenIds = collect($items)
                ->pluck('screen_id');

            if (
                $screenIds
                    ->unique()
                    ->count()
                !== $screenIds->count()
            ) {
                throw ValidationException::withMessages([
                    "periods.{$periodIndex}" => __(
                        'validation.electronic_booking.screens.duplicate'
                    ),
                ]);
            }

            $prepared[] = [
                'start_date' =>
                    $period['start_date'],

                'end_date' =>
                    $period['end_date'],

                'items' =>
                    $items,
            ];
        }

        return $prepared;
    }

    private function validateSlides(array $slides,string $attribute)
    {
        $numbers = collect($slides)
            ->pluck('slide_number');

        if (
            $numbers
                ->unique()
                ->count()
            !== $numbers->count()
        ) {
            throw ValidationException::withMessages([
                $attribute => __(
                    'validation.electronic_booking.slides.duplicate'
                ),
            ]);
        }
    }

    private function lockScreens(array $periods)
    {
        $screenIds = collect($periods)
            ->flatMap(
                fn (array $period) =>
                    collect($period['items'])
                        ->pluck('screen_id')
            )
            ->unique()
            ->values();

        return LedScreen::query()
            ->whereIn(
                'id',
                $screenIds
            )
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function validateScreenTypes(array $periods,$screens)
    {
        foreach (
            $periods
            as $periodIndex => $period
        ) {
            foreach (
                $period['items']
                as $itemIndex => $item
            ) {
                $screen = $screens->get(
                    $item['screen_id']
                );

                if (! $screen) {
                    continue;
                }

                if (
                    $item['network_id'] === null
                    && $screen->network_id
                        !== null
                ) {
                    throw ValidationException::withMessages([
                        "periods.{$periodIndex}.items.{$itemIndex}" =>
                            __(
                                'validation.electronic_booking.screens.not_independent'
                            ),
                    ]);
                }

                if (
                    $item['network_id'] !== null
                    && (int) $screen->network_id
                        !== (int) $item['network_id']
                ) {
                    throw ValidationException::withMessages([
                        "periods.{$periodIndex}.items.{$itemIndex}" =>
                            __(
                                'validation.electronic_booking.networks.screen_mismatch'
                            ),
                    ]);
                }
            }
        }
    }

    public function show(int $bookingId)
    {
        $year = $this->workingYearContext->get();

        return LedBooking::query()
            ->where('booking_id',$bookingId)
            ->whereHas(
                'booking',
                fn ($query) =>
                    $query->where('year',$year)
            )
            ->with([
                'designs' => fn ($query) => $query->orderBy('id'),
                'periods' => fn ($query) => $query->orderBy('id'),
                'periods.items' => fn ($query) => $query->orderBy('id'),
                'periods.items.slides' => fn ($query) =>
                    $query->orderBy('slide_number'),
                'periods.items.slides.design',
                'periods.items.screen.area.governorate',
                'periods.items.network.screens.area.governorate',
            ])
            ->firstOrFail();
    }

}