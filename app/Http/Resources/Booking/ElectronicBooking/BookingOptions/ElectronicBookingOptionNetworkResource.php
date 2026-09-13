<?php

namespace App\Http\Resources\Booking\ElectronicBooking\BookingOptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicBookingOptionNetworkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->location_name,
            'screens_count' => $this->screens->count(),
            'screens' => ElectronicBookingOptionScreenResource::collection($this->screens),
        ];
    }
}
