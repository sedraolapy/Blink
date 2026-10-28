<?php

namespace App\Http\Resources\Reports;

use App\Models\FlexBookingItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignRouteReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'previous_period' => $data['previous_period'],
            'current_period' => $data['current_period'],
            'governorates' => $data['governorates']
                ->map(fn ($governorate) => [
                    'id' => $governorate->id,
                    'name' => $governorate->name,
                ])
                ->values()
                ->all(),

            'items' => $data['items']
                ->map(function (array $item) {
                    $governorate = $item['governorate'];
                    $board = $item['board'];

                    return [
                        'governorate' => $governorate
                            ? [
                                'id' => $governorate->id,
                                'name' => $governorate->name,
                            ]
                            : null,

                        'board' => [
                            'id' => $board->id,
                            'code' => $board->code,
                            'name' => $board->location_name,
                        ],

                        'previous_booking' => $this->formatBooking($item['previous_item']),
                        'current_booking' => $this->formatBooking($item['current_item']),
                        'required_action' => $item['required_action'],
                    ];
                })
                ->values()
                ->all(),

            'summary' => [
                'boards_count' => $data['summary']['boards_count'],
                'installation_count' => $data['summary']['installation_count'],
                'extension_count' => $data['summary']['extension_count'],
                'customers_count' => $data['summary']['customers_count'],
            ],
        ];
    }

    private function formatBooking(?FlexBookingItem $item): ?array
    {
        if ($item === null) {
            return null;
        }

        $customer = $item
            ->period
            ->flexBooking
            ->booking
            ->customer;

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
            ],

            'design_name' => $item->design?->name,
        ];
    }
}