<?php

namespace App\Http\Resources\Booking\FlexBooking\BookingOptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlexAvailableAssetsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $governorate = $this->resource['governorate'];

        return [
            'governorate' => [
                'id' => $governorate->id,
                'name' => $governorate->name,
            ],

            'periods' => $this->resource['periods']
                ->map(
                    fn (array $periodData) => [
                        'period' => [
                            'id' => $periodData['period']->id,
                            'number' => $periodData['period']->number,
                        ],

                        'items' => $periodData['items']
                            ->map(
                                fn ($billboard) => [
                                    'id' => $billboard->id,
                                    'code' => $billboard->code,
                                    'name' => $billboard->location_name,
                                    'area' => $billboard->area?->name,
                                    'width' => (float) $billboard->width,
                                    'height' => (float) $billboard->height,
                                ]
                            )
                            ->values()
                            ->toArray(),
                    ]
                )
                ->values()
                ->toArray(),
        ];
    }
}