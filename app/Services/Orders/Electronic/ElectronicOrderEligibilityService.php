<?php

namespace App\Services\Orders\Electronic;

use App\Models\LedBookingItem;
use Illuminate\Support\Collection;

class ElectronicOrderEligibilityService
{
    public function getEligibleItems(Collection $items): Collection
    {
        $independentItems = $items->filter(
            fn (LedBookingItem $item) =>
                $item->led_network_id === null
                && ! $item->operation_order
        );

        $networkItems = $items
            ->filter(fn (LedBookingItem $item) => $item->led_network_id !== null)
            ->groupBy('led_network_id')
            ->filter(fn (Collection $items) => $this->isEligibleNetwork($items))
            ->flatMap(fn (Collection $items) => $items);

        return $independentItems
            ->concat($networkItems)
            ->values();
    }

    public function countEligibleScreens(Collection $items): int
    {
        return $this->getEligibleItems($items)->count();
    }

    private function isEligibleNetwork(Collection $items): bool
    {
        $network = $items->first()?->network;

        if ($network === null) {
            return false;
        }

        if ($items->contains(
            fn (LedBookingItem $item) =>
                $item->operation_order
        )) {
            return false;
        }

        $bookedScreenIds = $items
            ->pluck('led_screen_id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $networkScreenIds = $network->screens
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        return $bookedScreenIds === $networkScreenIds;
    }
}
