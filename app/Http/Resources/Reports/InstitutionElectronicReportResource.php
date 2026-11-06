<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstitutionElectronicReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'month' => [
                'number' => (int) $data['month']['number'],
                'year' => (int) $data['month']['year'],
                'start_date' => $data['month']['start_date'],
                'end_date' => $data['month']['end_date'],
            ],

            'sections' => collect($data['sections'])
                ->map(fn (array $section): array => [
                    'city' => [
                        'id' => (int) $section['city']['id'],
                        'name' => $section['city']['name'],
                    ],

                    'items' => collect($section['items'])
                        ->map(fn (array $item): array => [
                            'id' => (int) $item['id'],
                            'location' => $item['location'],
                            'width_m' => (float) $item['width_m'],
                            'width_px' => (int) $item['width_px'],
                            'height_m' => (float) $item['height_m'],
                            'height_px' => (int) $item['height_px'],
                            'area' => (float) $item['area'],
                        ])
                        ->values()
                        ->all(),

                    'summary' => [
                        'total_area' => (float) $section['summary']['total_area'],
                    ],
                ])
                ->values()
                ->all(),
        ];
    }
}
