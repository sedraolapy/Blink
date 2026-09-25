<?php

namespace App\Http\Resources\Booking\FlexBooking;

use App\Enums\BookingTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateFlexBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this['booking'];
        $bookingType = $booking->booking_type instanceof BookingTypeEnum
                ? $booking->booking_type
                : BookingTypeEnum::from($booking->booking_type);

        return [
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'advertiser_type' => $bookingType,
                'types' => collect([
                    $booking->flexBooking ? 'flex' : null,
                    $booking->ledBooking ? 'electronic' : null,

                    ...(
                        $booking->externalBooking
                            ? $booking->externalBooking->types
                                ->pluck('type')
                                ->map(fn ($type) => $type->value ?? $type)
                                ->toArray()
                            : []
                    ),
                ])
                    ->filter()
                    ->values(),
            ],

            'flex_booking' => [
                'id' => $this['flex_booking']->id,
                'periods_count' => $this['periods_count'],
                'items_count' => $this['items_count'],
                'designs_count' => $this['designs_count'],
            ],
        ];
    }
}