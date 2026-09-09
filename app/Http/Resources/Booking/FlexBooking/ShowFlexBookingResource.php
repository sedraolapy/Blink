<?php

namespace App\Http\Resources\Booking\FlexBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowFlexBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->booking_id,

            'designs' => $this->designs
                ->pluck('name')
                ->values(),

            'periods' => $this->periods
                ->map(function ($bookingPeriod) {
                    return [
                        'period' => [
                            'id' => $bookingPeriod->advertisingPeriod->id,
                            'number' => $bookingPeriod->advertisingPeriod->number,
                        ],

                        'items' => $bookingPeriod->bookingItems
                            ->map(function ($item) {
                                $flex = $item->billboard;
                                $area = $flex->area;
                                $governorate = $area?->governorate;

                                return [
                                    'flex' => [
                                        'id' => $flex->id,
                                        'code' => $flex->code,
                                        'name' => $flex->location_name,

                                        'governorate' => [
                                            'id' => $governorate?->id,
                                            'name' => $governorate?->name,
                                        ],

                                        'area' => $area?->name,

                                        'width' => (float) $flex->width,
                                        'height' => (float) $flex->height,
                                    ],

                                    'created_at' => $item->created_at?->toDateString(),

                                    'is_gift' => (bool) $item->is_gift,
                                    'has_dykes' => (bool) $item->has_dykat,
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