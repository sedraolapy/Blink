<?php

namespace App\Http\Resources\Booking\ElectronicBooking\BookingOptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicBookingOptionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'governorate' => [
                'id' => $this['governorate']->id,
                'name' => $this['governorate']->name,
            ],

            'screens' => ElectronicBookingOptionScreenResource::collection($this['screens']),
            'networks' => ElectronicBookingOptionNetworkResource::collection($this['networks']),
        ];
    }
}
