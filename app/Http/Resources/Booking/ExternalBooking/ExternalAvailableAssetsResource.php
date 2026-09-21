<?php

namespace App\Http\Resources\Booking\ExternalBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalAvailableAssetsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $governorate = $this->resource['governorate'];

        return [
            'type' => $this->resource['type'],

            'governorate' => [
                'id' => $governorate->id,
                'name' => $governorate->name,
            ],

            'periods' => collect($this->resource['periods'])
                ->map(function ($period) {

                    return [
                        'start_date' => $period['start_date'],
                        'end_date' => $period['end_date'],

                        'items' => collect($period['items'])
                            ->map(function ($asset) {

                                return $asset;

                            })
                            ->values()
                            ->toArray(),
                    ];
                })
                ->values()
                ->toArray(),
        ];
    }
}