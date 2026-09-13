<?php

namespace App\Http\Resources\Booking\ElectronicBooking\BookingOptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicBookingOptionScreenResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->location_name,
            'area' => $this->area?->name,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'resolution' => "{$this->width_px}x{$this->height_px}",
        ];
    }
}
