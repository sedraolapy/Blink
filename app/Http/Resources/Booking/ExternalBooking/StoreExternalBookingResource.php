<?php

namespace App\Http\Resources\Booking\ExternalBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreExternalBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this->booking;
        $requestedType = $request->input('type');
        $externalType = $this->types
            ->first(fn ($type) => ($type->type->value ?? $type->type) === $requestedType);

        return [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status->value,
                'advertiser_type' => $booking->booking_type->value,

                'types' => collect([
                    $booking->flexBooking ? 'flex' : null,
                    $booking->ledBooking ? 'electronic' : null,

                    ...(
                        $booking->externalBooking
                            ? $booking->externalBooking->types
                                ->pluck('type')
                                ->map(
                                    fn ($type) =>
                                        $type->value ?? $type
                                )
                                ->toArray()
                            : []
                    ),
                ])
                    ->filter()
                    ->values(),
            ],

            'outdoor_booking' => [
                'id' => $booking->id,
                'type' => $externalType?->type?->value,
                'periods_count' => $externalType?->periods->count() ?? 0,
                'items_count' => $externalType
                    ?->periods
                    ->sum(fn ($period) => $period->items->count()) ?? 0,
                'designs_count' => $externalType?->designs->count() ?? 0,
            ],
        ];
    }
}