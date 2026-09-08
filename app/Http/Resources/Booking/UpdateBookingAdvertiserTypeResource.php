<?php

namespace App\Http\Resources\Booking;

use App\Enums\BookingTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateBookingAdvertiserTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bookingType = $this->booking_type instanceof BookingTypeEnum
            ? $this->booking_type
            : BookingTypeEnum::from($this->booking_type);

        return [
            'id' => $this->id,
            'advertiser_type' => $bookingType->value,
        ];
    }
}
