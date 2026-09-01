<?php

namespace App\Http\Resources\FlexBillboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlexBillboardDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $billboard = $this->resource['billboard'];

        return [
            'id' => $billboard->id,
            'code' => $billboard->code,
            'name' => $billboard->location_name,
            'governorate' => [
                'id' => $billboard->area?->governorate?->id,
                'name' => $billboard->area?->governorate?->name,
            ],
            'area' => $billboard->area?->name,
            'width' => (float) $billboard->width,
            'height' => (float) $billboard->height,
            'status' => $billboard->flex_status,

            'confirmed_bookings' =>$this->resource['confirmed_bookings'],
            'unconfirmed_bookings' =>$this->resource['unconfirmed_bookings'],
            'available_periods' =>
                $this->resource['available_periods']
                    ->map(fn ($period) => [
                        'id' => $period->id,
                        'number' => $period->number,
                    ])
                    ->values()
                    ->toArray(),
        ];
    }
}
