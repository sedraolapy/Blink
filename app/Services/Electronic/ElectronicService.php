<?php

namespace App\Services\Electronic;

use App\Enums\BookingStatusEnum;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingSlide;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ElectronicService
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {}

    public function index(array $filters): array
    {
        $search = $filters['search'] ?? null;

        $governorateId = isset($filters['governorate_id'])
            ? (int) $filters['governorate_id']
            : null;

        $screensQuery = LedScreen::query()
            ->whereNull('network_id')
            ->search($search)
            ->governorate($governorateId)
            ->select('id')
            ->selectRaw("'screen' as type");

        $networksQuery = LedNetwork::query()
            ->search($search)
            ->governorate($governorateId)
            ->select('id')
            ->selectRaw("'network' as type");

        $unionQuery = $screensQuery
            ->unionAll($networksQuery);

        $items = DB::query()
            ->fromSub($unionQuery, 'electronic_items')
            ->orderBy('id')
            ->orderBy('type')
            ->paginate(24);

        $pageItems = $items->getCollection();

        $screenIds = $pageItems
            ->where('type', 'screen')
            ->pluck('id');

        $networkIds = $pageItems
            ->where('type', 'network')
            ->pluck('id');

        $screens = LedScreen::query()
            ->whereIn('id', $screenIds)
            ->with('area.governorate')
            ->get()
            ->keyBy('id');

        $networks = LedNetwork::query()
            ->whereIn('id', $networkIds)
            ->with('screens.area.governorate')
            ->withCount('screens')
            ->get()
            ->keyBy('id');

        $items->setCollection(
            $pageItems
                ->map(function ($item) use ($screens, $networks) {
                    if ($item->type === 'screen') {
                        $screen = $screens->get($item->id);

                        if ($screen) {
                            $screen->electronic_type = 'screen';
                        }

                        return $screen;
                    }

                    $network = $networks->get($item->id);

                    if ($network) {
                        $network->electronic_type = 'network';
                    }

                    return $network;
                })
                ->filter()
                ->values()
        );

        return [
            'summary' => $this->getSummary($filters),
            'items' => $items,
        ];
    }

    private function getSummary(array $filters): array
    {
        $year = $this->workingYearContext->get();

        $search = $filters['search'] ?? null;

        $governorateId = isset($filters['governorate_id'])
            ? (int) $filters['governorate_id']
            : null;

        $screensQuery = LedScreen::query()
            ->whereNull('network_id')
            ->search($search)
            ->governorate($governorateId);

        $networksQuery = LedNetwork::query()
            ->search($search)
            ->governorate($governorateId);

        $hasAssetFilters = filled($search) || $governorateId !== null;

        $confirmedBookingsQuery = LedBooking::query()
            ->join(
                'bookings',
                'bookings.id',
                '=',
                'led_bookings.booking_id'
            )
            ->join(
                'led_booking_periods as periods',
                'periods.led_booking_id',
                '=',
                'led_bookings.id'
            )
            ->join(
                'led_booking_items as items',
                'items.led_booking_period_id',
                '=',
                'periods.id'
            )
            ->where(
                'bookings.status',
                BookingStatusEnum::CONFIRMED->value
            )
            ->where('bookings.year',$year);

        $unconfirmedBookingsQuery = LedBooking::query()
            ->join(
                'bookings',
                'bookings.id',
                '=',
                'led_bookings.booking_id'
            )
            ->join(
                'led_booking_periods as periods',
                'periods.led_booking_id',
                '=',
                'led_bookings.id'
            )
            ->join(
                'led_booking_items as items',
                'items.led_booking_period_id',
                '=',
                'periods.id'
            )
            ->where(
                'bookings.status',
                BookingStatusEnum::UNCONFIRMED->value
            )
            ->where('bookings.year',$year);

        if ($hasAssetFilters) {
            $screenIdsQuery = LedScreen::query()
                ->whereNull('network_id')
                ->search($search)
                ->governorate($governorateId)
                ->select('led_screens.id');

            $networkIdsQuery = LedNetwork::query()
                ->search($search)
                ->governorate($governorateId)
                ->select('led_networks.id');

            $applyAssetFilter = function ($query) use (
                $screenIdsQuery,
                $networkIdsQuery
            ) {
                $query->where(function ($query) use (
                    $screenIdsQuery,
                    $networkIdsQuery
                ) {
                    $query
                        ->whereIn(
                            'items.led_screen_id',
                            clone $screenIdsQuery
                        )
                        ->orWhereIn(
                            'items.led_network_id',
                            clone $networkIdsQuery
                        );
                });
            };

            $applyAssetFilter($confirmedBookingsQuery);
            $applyAssetFilter($unconfirmedBookingsQuery);
        }

        return [
            'total_screens' => (clone $screensQuery)->count(),
            'networks_count' => (clone $networksQuery)->count(),
            'confirmed_bookings' => $confirmedBookingsQuery->distinct()->count('led_bookings.id'),
            'unconfirmed_bookings' => $unconfirmedBookingsQuery->distinct()->count('led_bookings.id'),
        ];
    }

    public function showScreen(int $id): array
    {
        $year = $this->workingYearContext->get();

        $screen = LedScreen::query()
            ->with([
                'area.governorate',

                'bookingItems' => function ($query) use ($year) {
                    $query
                        ->whereHas(
                            'period.ledBooking.booking',
                            fn ($query) =>
                                $query->where('year',$year)
                        )
                        ->with([
                            'slides.design',
                            'period.ledBooking.booking.customer',
                        ]);
                },
            ])
            ->findOrFail($id);

        $confirmedItems = $screen
            ->bookingItems
            ->filter(
                fn (LedBookingItem $item) =>
                    $item
                        ->period
                        ->ledBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::CONFIRMED
            );

        $unconfirmedItems = $screen
            ->bookingItems
            ->filter(
                fn (LedBookingItem $item) =>
                    $item
                        ->period
                        ->ledBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::UNCONFIRMED
            );

        return [
            'screen' => $screen,
            'confirmed_bookings' => $this->formatScreenBookings($confirmedItems,$screen),
            'unconfirmed_bookings' => $this->formatScreenBookings($unconfirmedItems,$screen),
        ];
    }

    private function formatScreenBookings(Collection $items,LedScreen $screen)
    {
        return $items
            ->groupBy(
                fn (LedBookingItem $item) =>
                    $item
                        ->period
                        ->ledBooking
                        ->booking_id
            )
            ->map(function (Collection $items) use ($screen) {
                $firstItem = $items->first();

                $booking = $firstItem
                    ->period
                    ->ledBooking
                    ->booking;

                return [
                    'id' => $booking->id,

                    'client_name' =>
                        $booking->customer?->name,

                    'periods' => $items
                        ->map(
                            fn (LedBookingItem $item) => [
                                'start_date' =>
                                    $item
                                        ->period
                                        ->start_date
                                        ?->format('Y-m-d'),

                                'end_date' =>
                                    $item
                                        ->period
                                        ->end_date
                                        ?->format('Y-m-d'),

                                'slides' =>
                                    $this->formatSlides(
                                        $item,
                                        $screen->id
                                    ),
                            ]
                        )
                        ->sortBy('start_date')
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function showNetwork(int $id)
    {
        $year = $this->workingYearContext->get();

        $network = LedNetwork::query()
            ->with([
                'screens.area.governorate',

                'bookingItems' => function ($query) use ($year) {
                    $query
                        ->whereHas(
                            'period.ledBooking.booking',
                            fn ($query) =>
                                $query->where('year',$year)
                        )
                        ->with([
                            'slides.design',
                            'period.ledBooking.booking.customer',
                        ]);
                },
            ])
            ->withCount('screens')
            ->findOrFail($id);

        $confirmedItems = $network
            ->bookingItems
            ->filter(
                fn (LedBookingItem $item) =>
                    $item
                        ->period
                        ->ledBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::CONFIRMED
            );

        $unconfirmedItems = $network
            ->bookingItems
            ->filter(
                fn (LedBookingItem $item) =>
                    $item
                        ->period
                        ->ledBooking
                        ->booking
                        ->status
                    === BookingStatusEnum::UNCONFIRMED
            );

        return [
            'network' => $network,

            'confirmed_bookings' =>
                $this->formatNetworkBookings(
                    $confirmedItems
                ),

            'unconfirmed_bookings' =>
                $this->formatNetworkBookings(
                    $unconfirmedItems
                ),
        ];
    }

    private function formatNetworkBookings(Collection $items)
    {
        return $items
            ->groupBy(
                fn (LedBookingItem $item) =>
                    $item
                        ->period
                        ->ledBooking
                        ->booking_id
            )
            ->map(function (Collection $items) {
                $firstItem = $items->first();

                $booking = $firstItem
                    ->period
                    ->ledBooking
                    ->booking;

                return [
                    'id' => $booking->id,

                    'client_name' =>
                        $booking->customer?->name,

                    'periods' => $items
                        ->map(
                            fn (LedBookingItem $item) => [
                                'start_date' =>
                                    $item
                                        ->period
                                        ->start_date
                                        ?->format('Y-m-d'),

                                'end_date' =>
                                    $item
                                        ->period
                                        ->end_date
                                        ?->format('Y-m-d'),

                                'designs' =>
                                    $this->formatDesigns($item),
                            ]
                        )
                        ->sortBy('start_date')
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function formatDesigns(LedBookingItem $item,?int $screenId = null): array
    {
        $slides = $item->slides;

        if (
            $screenId !== null
            && $item->led_network_id !== null
        ) {
            $slides = $slides->where(
                'led_screen_id',
                $screenId
            );
        }

        return $slides
            ->map(
                fn (LedBookingSlide $slide) =>
                    $slide->design?->name
            )
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function formatSlides(LedBookingItem $item, ?int $screenId = null)
    {
        $slides = $item->slides;
        if (
            $screenId !== null
            && $item->led_network_id !== null
        ) {
            $slides = $slides->where(
                'led_screen_id',
                $screenId
            );
        }

        return $slides
            ->sortBy('slide_number')
            ->map(
                fn (LedBookingSlide $slide) => [
                    'slide_number' => (int) $slide->slide_number,
                    'design_name' => $slide->design?->name,
                ]
            )
            ->values()
            ->toArray();
    }
}