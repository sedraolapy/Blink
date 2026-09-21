<?php

namespace App\Http\Resources\Booking\ElectronicBooking\BookingOptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicBookingOptionNetworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $firstScreen = $this->screens->first();

        return [
            'id' => $this->id,
            'name' => $this->location_name,

            'screens_count' => $this->screens->count(),
            'area' => $firstScreen?->area?->name,

            'screens' => $this->screens
                ->map(function ($screen) {
                    return [
                        'id' => $screen->id,
                        'code' => $screen->code,
                        'name' => $screen->location_name,
                        'width' => (float) $screen->width,
                        'height' => (float) $screen->height,
                        'resolution' => "{$screen->width_px}x{$screen->height_px}",
                    ];
                })
                ->values(),
        ];
    }
}