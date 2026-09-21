<?php

namespace App\Http\Resources\Booking\ExternalBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowExternalBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->externalBooking->booking_id,
            'type' => $this->type->value,
            'designs' => $this->designs
                ->pluck('name')
                ->values(),

            'periods' => $this->periods
                ->map(function ($period) {
                    return [
                        'start_date' => $period->start_date->format('Y-m-d'),
                        'end_date' => $period->end_date->format('Y-m-d'),
                        'items' => $period->items
                            ->map(function ($item) {
                                $asset = $item->asset;

                                return [
                                    'asset' => [
                                        'id' => $asset->id,
                                        'type' => $asset->type->value,
                                        'code' => $asset->code,
                                        'name' => $asset->location_name,
                                        'governorate' => [
                                            'id' => $asset->area?->governorate?->id,
                                            'name' => $asset->area?->governorate?->name,
                                        ],

                                        'area' => $asset->area?->name,
                                        'width' => (float) $asset->width,
                                        'height' => (float) $asset->height,
                                    ],

                                    'is_gift' => (bool) $item->is_gift,
                                    'design_name' => $item->design?->name,
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values(),
        ];
    }
}