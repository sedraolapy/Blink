<?php

namespace App\Http\Resources\Outdoor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutdoorDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $asset = $this->resource['asset'];

        return [
            'id' => $asset->id,
            'type' => $asset->type?->value ?? $asset->type,
            'name' => $asset->location_name,
            'code' => $asset->code,

            'governorate' => [
                'id' => $asset->area?->governorate?->id,
                'name' => $asset->area?->governorate?->name,
            ],

            'area' => $asset->area?->name,

            'width' => (float) $asset->width,
            'height' => (float) $asset->height,

            'confirmed_bookings' => $this->resource['confirmed_bookings'],
            'unconfirmed_bookings' => $this->resource['unconfirmed_bookings'],
            'unavailable_ranges' => $this->resource['unavailable_ranges'],
        ];
    }
}
