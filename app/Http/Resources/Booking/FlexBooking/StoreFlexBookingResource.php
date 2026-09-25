<?php

namespace App\Http\Resources\Booking\FlexBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreFlexBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this->resource['booking'];
        $flexBooking = $this->resource['flex_booking'];

        return [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'advertiser_type' => $booking->booking_type,

                'types' => collect([
                    $booking->flexBooking ? 'flex' : null,
                    $booking->ledBooking ? 'electronic' : null,

                    ...(
                        $booking->externalBooking
                            ? $booking->externalBooking->types
                                ->pluck('type')
                                ->map(
                                    fn ($type) => $type->value ?? $type
                                )
                                ->toArray()
                            : []
                    ),
                ])
                    ->filter()
                    ->values(),
            ],

            'flex_booking' => [
                'id' => $flexBooking->id,
                'periods_count' => $this->resource['periods_count'],
                'items_count' => $this->resource['items_count'],
                'designs_count' => $this->resource['designs_count'],
            ],
        ];
    }

}