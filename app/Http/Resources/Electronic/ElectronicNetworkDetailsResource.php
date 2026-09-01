<?php

namespace App\Http\Resources\Electronic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicNetworkDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $network = $this->resource['network'];

        $firstScreen = $network->screens->first();

        $area = $firstScreen?->area;
        $governorate = $area?->governorate;

        return [
            'id' => $network->id,
            'type' => 'network',
            'name' => $network->location_name,

            'governorate' => [
                'id' => $governorate?->id,
                'name' => $governorate?->name,
            ],
            'area' => $area?->name,

            'screens_count' => (int) $network->screens_count,
            'confirmed_bookings' =>$this->resource['confirmed_bookings'],
            'unconfirmed_bookings' =>$this->resource['unconfirmed_bookings'],
        ];
    }
}
