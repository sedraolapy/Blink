<?php

namespace App\Http\Resources\Booking\ExternalBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateExternalBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this->externalBooking->booking;

        $governoratesCount = $this->periods
            ->flatMap(
                fn ($period) =>
                    $period->items
                        ->map(
                            fn ($item) =>
                                $item->asset?->area?->governorate?->id
                        )
            )
            ->filter()
            ->unique()
            ->count();

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
                'type' => $this->type->value,
                'periods_count' => $this->periods->count(),
                'governorates_count' => $governoratesCount,
                'items_count' => $this->periods->sum( fn ($period) => $period->items->count()),
                'designs_count' => $this->designs->count(),
            ],
        ];
    }
}