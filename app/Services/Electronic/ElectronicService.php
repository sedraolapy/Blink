<?php

namespace App\Services\Electronic;

use App\Enums\BookingItemStatusEnum;
use App\Models\LedBooking;
use App\Models\LedBookingItem;
use App\Models\LedBookingSlide;
use App\Models\LedNetwork;
use App\Models\LedScreen;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ElectronicService
{
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
            'summary' => $this->getSummary(),
            'items' => $items,
        ];
    }

    private function getSummary(): array
    {
        $confirmedBookings = LedBooking::query()
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
                'items.status',
                BookingItemStatusEnum::BOOKED->value
            )
            ->distinct()
            ->count('led_bookings.id');

        $unconfirmedBookings = LedBooking::query()
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
                'items.status',
                BookingItemStatusEnum::UNCONFIRMED->value
            )
            ->distinct()
            ->count('led_bookings.id');

        return [
            'total_screens' => LedScreen::query()->whereNull('network_id')->count(),
            'networks_count' => LedNetwork::query()->count(),
            'confirmed_bookings' => $confirmedBookings,
            'unconfirmed_bookings' => $unconfirmedBookings,
        ];
    }

    public function showScreen(int $id): array
    {
        $screen = LedScreen::query()
            ->with([
                'area.governorate',

                'bookingItems' => function ($query) {
                    $query->with([
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
                    $item->status === BookingItemStatusEnum::BOOKED
            );

        $unconfirmedItems = $screen
            ->bookingItems
            ->filter(fn (LedBookingItem $item) => $item->status === BookingItemStatusEnum::UNCONFIRMED);

        return [
            'screen' => $screen,
            'confirmed_bookings' => $this->formatScreenBookings($confirmedItems,$screen),
            'unconfirmed_bookings' => $this->formatScreenBookings($unconfirmedItems,$screen),
        ];
    }

    private function formatScreenBookings( Collection $items, LedScreen $screen): array
    {
        return $items
            ->groupBy(
                fn (LedBookingItem $item) => $item->period->ledBooking->booking_id
            )
            ->map(function (Collection $items) use ($screen) {

                $firstItem = $items->first();

                $booking = $firstItem
                    ->period
                    ->ledBooking
                    ->booking;

                return [
                    'id' => $booking->id,
                    'client_name' => $booking->customer?->name,

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

    public function showNetwork(int $id): array
    {
        $network = LedNetwork::query()
            ->with([
                'screens.area.governorate',

                'bookingItems' => function ($query) {
                    $query->with([
                        'slides.design',
                        'period.ledBooking.booking.customer',
                    ]);
                },
            ])
            ->withCount('screens')
            ->findOrFail($id);

        $confirmedItems = $network
            ->bookingItems
            ->filter(fn (LedBookingItem $item) => $item->status === BookingItemStatusEnum::BOOKED);

        $unconfirmedItems = $network
            ->bookingItems
            ->filter(fn (LedBookingItem $item) => $item->status === BookingItemStatusEnum::UNCONFIRMED);

        return [
            'network' => $network,
            'confirmed_bookings' => $this->formatNetworkBookings($confirmedItems),
            'unconfirmed_bookings' => $this->formatNetworkBookings($unconfirmedItems),
        ];
    }


    private function formatNetworkBookings(Collection $items): array
    {
        return $items
            ->groupBy(
                fn (LedBookingItem $item) =>
                    $item->period->ledBooking->booking_id
            )
            ->map(function (Collection $items) {

                $firstItem = $items->first();

                $booking = $firstItem
                    ->period
                    ->ledBooking
                    ->booking;

                return [
                    'id' => $booking->id,
                    'client_name' => $booking->customer?->name,

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
                                    $this->formatSlides($item),
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

    private function formatSlides(LedBookingItem $item, ?int $screenId = null): array
    {
        $slides = $item->slides;

        if ($screenId !== null) {
            $slides = $slides->where(
                'led_screen_id',
                $screenId
            );
        }

        return $slides
            ->sortBy('slide_number')
            ->map(
                fn (LedBookingSlide $slide) => [
                    'slide_number' =>
                        (int) $slide->slide_number,

                    'design_name' =>
                        $slide->design?->name,
                ]
            )
            ->values()
            ->toArray();
    }
}