<?php

namespace App\Http\Resources\Booking\ElectronicBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreElectronicBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $booking = $this['booking'];
        $electronicBooking = $this['electronic_booking'];

        $types = [];

        if ($booking->flexBooking) {
            $types[] = 'flex';
        }

        if ($booking->ledBooking) {
            $types[] = 'electronic';
        }

        if ($booking->externalBooking) {
            $externalTypes = $booking->externalBooking->types
                ->pluck('type')
                ->map(fn ($type) => $type->value ?? $type)
                ->toArray();

            $types = [
                ...$types,
                ...$externalTypes,
            ];
        }

        $items = $electronicBooking->periods
            ->flatMap(fn ($period) => $period->items);

        return [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status->value,
                'advertiser_type' =>$booking->booking_type->value,
                'types' => $types,
            ],

            'electronic_booking' => [
                'id' => $electronicBooking->id,
                'periods_count' => $electronicBooking->periods->count(),
                'networks_count' => $items
                        ->whereNotNull('led_network_id')
                        ->pluck('led_network_id')
                        ->unique()
                        ->count(),

                'screens_count' => $items->count(),
                'slides_count' => $items->sum(fn ($item) =>$item->slides->count()),
                'designs_count' => $electronicBooking->designs->count(),
            ],
        ];
    }
}
