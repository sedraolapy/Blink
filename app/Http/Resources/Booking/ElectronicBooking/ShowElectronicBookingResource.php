<?php

namespace App\Http\Resources\Booking\ElectronicBooking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowElectronicBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->booking_id,

            'designs' => $this->designs
                ->pluck('name')
                ->values(),

            'periods' => $this->periods
                ->map(function ($bookingPeriod) {
                    $standaloneItems = $bookingPeriod->items
                        ->filter(
                            fn ($item) => $item->led_network_id === null
                        );

                    $networkGroups = $bookingPeriod->items
                        ->filter(
                            fn ($item) => $item->led_network_id !== null
                        )
                        ->groupBy('led_network_id');

                    return [
                        'start_date' => $bookingPeriod->start_date?->toDateString(),
                        'end_date' => $bookingPeriod->end_date?->toDateString(),

                        'screens' => $standaloneItems
                            ->map(function ($item) {
                                $screen = $item->screen;
                                $area = $screen?->area;
                                $governorate = $area?->governorate;

                                return [
                                    'screen' => [
                                        'id' => $screen?->id,
                                        'code' => $screen?->code,
                                        'name' => $screen?->location_name,

                                        'governorate' => [
                                            'id' => $governorate?->id,
                                            'name' => $governorate?->name,
                                        ],

                                        'area' => $area?->name,

                                        'width' => (float) $screen?->width,
                                        'height' => (float) $screen?->height,
                                    ],

                                    'is_gift' => (bool) $item->is_gift,

                                    'slides' => $item->slides
                                        ->map(function ($slide) {
                                            return [
                                                'slide_number' => $slide->slide_number,
                                                'design_name' => $slide->design?->name,
                                            ];
                                        })
                                        ->values(),
                                ];
                            })
                            ->values(),

                        'networks' => $networkGroups
                            ->map(function ($items) {
                                $firstItem = $items->first();
                                $network = $firstItem?->network;

                                $firstNetworkScreen = $network?->screens->first();
                                $area = $firstNetworkScreen?->area;
                                $governorate = $area?->governorate;

                                return [
                                    'network' => [
                                        'id' => $network?->id,
                                        'name' => $network?->location_name,

                                        'governorate' => [
                                            'id' => $governorate?->id,
                                            'name' => $governorate?->name,
                                        ],

                                        'screens_count' => $network?->screens->count() ?? 0,
                                    ],

                                    'is_gift' => (bool) $firstItem?->is_gift,

                                    'screens' => $items
                                        ->map(function ($item) {
                                            $screen = $item->screen;

                                            return [
                                                'screen' => [
                                                    'id' => $screen?->id,
                                                    'code' => $screen?->code,
                                                    'name' => $screen?->location_name,
                                                ],

                                                'slides' => $item->slides
                                                    ->map(function ($slide) {
                                                        return [
                                                            'slide_number' => $slide->slide_number,
                                                            'design_name' => $slide->design?->name,
                                                        ];
                                                    })
                                                    ->values(),
                                            ];
                                        })
                                        ->values(),
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values(),
        ];
    }
}
