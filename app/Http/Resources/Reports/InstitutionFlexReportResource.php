<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstitutionFlexReportResource extends JsonResource
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

            'sections' => collect($data['sections'])
                ->map(fn (array $section): array => [
                    'governorate' => [
                        'id' => (int) $section['governorate']['id'],
                        'name' => $section['governorate']['name'],
                    ],

                    'label' => $section['label'],

                    'customers' => collect($section['customers'])
                        ->map(fn (array $customer): array => [
                            'customer' => [
                                'id' => (int) $customer['customer']['id'],
                                'name' => $customer['customer']['name'],
                            ],

                            'items' => collect($customer['items'])
                                ->map(fn (array $item): array => [
                                    'dimensions' => $item['dimensions'],
                                    'boards_count' => (int) $item['boards_count'],
                                    'total_area' => (float) $item['total_area'],
                                ])
                                ->values()
                                ->all(),
                        ])
                        ->values()
                        ->all(),

                    'summary' => [
                        'booked_boards_count' => (int) $section['summary']['booked_boards_count'],
                        'remaining_boards_count' => (int) $section['summary']['remaining_boards_count'],
                        'total_area' => (float) $section['summary']['total_area'],
                    ],
                ])
                ->values()
                ->all(),
        ];
    }
}
