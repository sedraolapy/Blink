<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstitutionOutdoorReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;
        $type = (string) $request->query('outdoor_type');

        $result = [
            'month' => [
                'number' => (int) $data['month']['number'],
                'year' => (int) $data['month']['year'],
                'start_date' => $data['month']['start_date'],
                'end_date' => $data['month']['end_date'],
            ],
        ];

        if (in_array($type, [
            'mural',
            'rooftop',
            'unipole',
        ], true)) {
            $result['items'] = collect($data['items'])
                ->map(fn (array $item) => $this->formatItem($item, $type, true))
                ->values()
                ->all();

            $result['summary'] = ['total_area' => (float) $data['summary']['total_area'],];
            return $result;
        }

        $result['sections'] = collect($data['sections'])
            ->map(function (array $section) use ($type): array {
                return [
                    'city' => $this->formatCity($section['city']),
                    'items' => collect($section['items'])
                        ->map(fn (array $item) => $this->formatItem($item, $type, false))
                        ->values()
                        ->all(),

                    'summary' => [ 'total_area' => (float) $section['summary']['total_area'],],
                ];
            })
            ->values()
            ->all();

        if ($type === 'tunnel') {
            $result['summary'] = [
                'total_area' => (float) $data['summary']['total_area'],
            ];
        }

        return $result;
    }

    private function formatItem(array $item,string $type,bool $includeCity): array
    {
        $result = [];

        if ($includeCity) {
            $result['city'] = $this->formatCity($item['city']);
        }

        if ($type === 'unipole') {
            $result['connecting_road'] = $item['connecting_road'];
        }

        $result['location'] = $item['location'];

        $result['customer'] = [
            'id' => (int) $item['customer']['id'],
            'name' => $item['customer']['name'],
        ];

        if (in_array($type, ['tunnel', 'unipole'], true)) {
            $result['dimensions'] = $item['dimensions'];
        } else {
            $result['width_m'] = (float) $item['width_m'];
            $result['height_m'] = (float) $item['height_m'];
        }

        $result['total_area'] = (float) $item['total_area'];

        return $result;
    }

    private function formatCity(array $city): array
    {
        return [
            'id' => (int) $city['id'],
            'name' => $city['name'],
        ];
    }
}
