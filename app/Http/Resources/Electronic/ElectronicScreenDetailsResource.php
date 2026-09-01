<?php

namespace App\Http\Resources\Electronic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicScreenDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $screen = $this->resource['screen'];

        return [
            'id' => $screen->id,
            'type' => 'screen',
            'name' => $screen->location_name,
            'code' => $screen->code,

            'governorate' => [
                'id' => $screen->area?->governorate?->id,
                'name' => $screen->area?->governorate?->name,
            ],
            'area' => $screen->area?->name,

            'width' => (float) $screen->width,
            'height' => (float) $screen->height,

            'pixel_width' => (int) $screen->width_px,
            'pixel_height' => (int) $screen->height_px,

            'confirmed_bookings' =>$this->resource['confirmed_bookings'],
            'unconfirmed_bookings' =>$this->resource['unconfirmed_bookings'],
        ];
    }
}
