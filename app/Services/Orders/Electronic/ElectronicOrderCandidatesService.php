<?php

namespace App\Services\Orders\Electronic;

use App\Enums\BookingStatusEnum;
use App\Models\LedBookingItem;
use App\Models\LedBookingPeriod;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ElectronicOrderCandidatesService
{
    public function __construct(
        private readonly WorkingYearContext $workingYearContext,
        private readonly ElectronicOrderEligibilityService $eligibilityService
    ) {}

    public function getCandidates(int $customerId,int $periodId)
    {
        $period = LedBookingPeriod::query()
            ->whereKey($periodId)
            ->whereHas(
                'ledBooking.booking',
                fn (Builder $query) => $query
                    ->where('customer_id', $customerId)
                    ->where('year', $this->workingYearContext->get())
                    ->where(
                        'status',
                        BookingStatusEnum::CONFIRMED->value
                    )
            )
            ->with([
                'ledBooking.booking.customer',
                'items' => fn ($query) => $query->orderBy('led_screen_id'),
                'items.screen.area.governorate',
                'items.network.screens',
                'items.slides.design',
            ])
            ->firstOrFail();

        $customer = $period->ledBooking->booking->customer;
        $eligibleItems = $this->eligibilityService->getEligibleItems($period->items);

        return [
            'customer' => [
                'id' => (int) $customer->id,
                'name' => $customer->name,
            ],

            'period' => [
                'id' => (int) $period->id,
                'start_date' => $period->start_date->toDateString(),
                'end_date' => $period->end_date->toDateString(),
            ],

            'screens' => $this->getIndependentScreens($eligibleItems),
            'networks' => $this->getNetworks($eligibleItems),
        ];
    }

    private function getIndependentScreens(Collection $items)
    {
        return $items
            ->filter(fn (LedBookingItem $item) => $item->led_network_id === null)
            ->map(fn (LedBookingItem $item) => $this->formatScreen($item))
            ->values()
            ->all();
    }

    private function getNetworks(Collection $items): array
    {
        return $items
            ->filter(
                fn (LedBookingItem $item) => $item->led_network_id !== null
            )
            ->groupBy('led_network_id')
            ->map(fn (Collection $networkItems) => $this->formatNetwork($networkItems))
            ->values()
            ->all();
    }

    private function formatNetwork(Collection $items): array
    {
        $network = $items->first()->network;

        return [
            'id' => (int) $network->id,
            'code' => null,
            'name' => $network->location_name,

            'screens' => $items
                ->map(
                    fn (LedBookingItem $item) => $this->formatScreen($item)
                )
                ->values()
                ->all(),
        ];
    }

    private function formatScreen(LedBookingItem $item)
    {
        $screen = $item->screen;
        $governorate = $screen->area?->governorate;

        return [
            'id' => (int) $screen->id,
            'code' => $screen->code,
            'name' => $screen->location_name,
            'area' => $screen->area?->name,

            'governorate' => $governorate
                ? [
                    'id' => (int) $governorate->id,
                    'name' => $governorate->name,
                ]
                : null,

            'width' => $screen->width,
            'height' => $screen->height,

            'resolution' =>
                $screen->width_px !== null
                && $screen->height_px !== null
                    ? "{$screen->width_px}x{$screen->height_px}"
                    : null,

            'designs' => $item->slides
                ->pluck('design.name')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
