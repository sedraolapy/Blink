<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignSalesReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'period' => [
                'id' => (int) $data['period']['id'],
                'number' => (int) $data['period']['number'],
                'year' => (int) $data['period']['year'],
                'start_date' => $data['period']['start_date'],
                'end_date' => $data['period']['end_date'],
            ],

            'items' => $data['items']
                ->map(function (array $item) {
                    return [
                        'customer' => [
                            'id' => (int) $item['customer']->id,
                            'name' => $item['customer']->name,
                        ],

                        'booking_status' =>$item['booking_status'],
                        'damascus_boards_count' => (int)$item['damascus_boards_count'],
                        'other_governorates_boards_count' => (int)$item['other_governorates_boards_count'],
                        'total_boards_count' => (int)$item['total_boards_count'],
                    ];
                })
                ->values()
                ->all(),

            'summary' => [
                'damascus' => [
                    'inventory_boards_count' => (int) $data['summary']['damascus']['inventory_boards_count'],
                    'booked_boards_count' => (int) $data['summary']['damascus']['booked_boards_count'],
                    'confirmed_boards_count' => (int) $data['summary']['damascus']['confirmed_boards_count'],
                    'unconfirmed_boards_count' => (int)  $data['summary']['damascus']['unconfirmed_boards_count'],
                    'remaining_boards_count' => (int) $data['summary']['damascus']['remaining_boards_count'],
                ],

                'other_governorates' => [
                    'inventory_boards_count' => (int)  $data['summary']['other_governorates']['inventory_boards_count'],
                    'booked_boards_count' => (int) $data['summary']['other_governorates']['booked_boards_count'],
                    'confirmed_boards_count' => (int) $data['summary']['other_governorates']['confirmed_boards_count'],
                    'unconfirmed_boards_count' => (int) $data['summary']['other_governorates']['unconfirmed_boards_count'],
                    'remaining_boards_count' => (int) $data['summary']['other_governorates']['remaining_boards_count'],
                ],

                'total' => [
                    'booked_boards_count' => (int) $data['summary']['total'] ['booked_boards_count'],
                    'confirmed_boards_count' => (int)$data['summary']['total']['confirmed_boards_count'],
                    'unconfirmed_boards_count' => (int)$data['summary']['total']['unconfirmed_boards_count'],
                    'remaining_boards_count' => (int) $data['summary']['total']['remaining_boards_count'],
                ],
            ],
        ];
    }
}
