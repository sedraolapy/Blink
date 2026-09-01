<?php

namespace App\Services\Electronic;

use App\Enums\BookingItemStatusEnum;
use App\Models\LedBooking;
use App\Models\LedNetwork;
use App\Models\LedScreen;
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
        return [
            'total_screens' => LedScreen::query()->whereNull('network_id')->count(),
            'networks_count' => LedNetwork::query()->count(),
            'linked_bookings' => LedBooking::query()->count(),
        ];
    }

    public function showScreen(int $id): array
    {
        $screen = LedScreen::query()
            ->with([
                'area.governorate',
                'bookingItems' => function ($query) {
                    $query->with([
                        'period.ledBooking.booking.customer',
                    ]);
                },
            ])
            ->findOrFail($id);

        $confirmedItems = $screen->bookingItems
            ->filter(
                fn ($item) =>
                    $item->status === BookingItemStatusEnum::BOOKED
            );

        $unconfirmedItems = $screen->bookingItems
            ->filter(
                fn ($item) =>
                    $item->status === BookingItemStatusEnum::UNCONFIRMED
            );

        return [
            'screen' => $screen,
            'confirmed_bookings' =>$this->formatLedBookings($confirmedItems),
            'unconfirmed_bookings' =>$this->formatLedBookings($unconfirmedItems),
        ];
    }

    private function formatLedBookings($items): array
    {
        return $items
            ->map(function ($item) {
                $booking = $item
                    ->period
                    ->ledBooking
                    ->booking;

                return [
                    'id' => $booking->id,
                    'client_name' =>$booking->customer?->name,
                    'start_date' =>$item->period->start_date?->format('Y-m-d'),
                    'end_date' =>$item->period->end_date?->format('Y-m-d'),
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
                        'period.ledBooking.booking.customer',
                    ]);
                },
            ])
            ->withCount('screens')
            ->findOrFail($id);

        $confirmedItems = $network->bookingItems
            ->filter(
                fn ($item) =>$item->status === BookingItemStatusEnum::BOOKED);

        $unconfirmedItems = $network->bookingItems
            ->filter(fn ($item) =>$item->status === BookingItemStatusEnum::UNCONFIRMED);

        return [
            'network' => $network,
            'confirmed_bookings' =>$this->formatLedBookings($confirmedItems),
            'unconfirmed_bookings' =>$this->formatLedBookings($unconfirmedItems),
        ];
    }
}