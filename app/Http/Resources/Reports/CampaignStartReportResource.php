<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class CampaignStartReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;
        $governorates = collect($data['governorates']);

        return [
            'period' => $this->formatPeriod($data['period']),

            'governorates' => $governorates
                ->map(fn ($governorate) => [
                    'id' => (int) $governorate->id,
                    'name' => $governorate->name,
                ])
                ->values()
                ->all(),

            'items' => $this->formatItems(
                $data['items'],
                $governorates
            ),

            'summary' => $this->formatSummary(
                $data['summary'],
                $governorates
            ),
        ];
    }

    private function formatPeriod(array $period): array
    {
        return [
            'id' => (int) $period['id'],
            'number' => (int) $period['number'],
            'year' => (int) $period['year'],
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
        ];
    }

    private function formatItems(array|Collection $items,Collection $governorates): array
    {
        return collect($items)
            ->map(function ($item) use ($governorates) {

                $counts = $this->formatGovernorateCounts(
                    $item['counts'],
                    $governorates
                );

                return [
                    'customer' => [
                        'id' => (int) $item['customer']->id,
                        'name' => $item['customer']->name,
                    ],

                    'classification' => $item['classification'],
                    'governorates' => $counts,
                    'total' => $this->calculateTotal($counts),
                ];
            })
            ->values()
            ->all();
    }

    private function formatSummary(array $summary,Collection $governorates): array
    {
        $result = [];

        foreach ([
            'extension',
            'installation',
            'booked',
            'inventory',
            'remaining',
        ] as $type) {

            $counts = $this->formatGovernorateCounts(
                $summary[$type] ?? [],
                $governorates
            );

            $result[$type] = [
                'governorates' => $counts,
                'total' => $this->calculateTotal($counts),
            ];
        }

        return $result;
    }

    private function formatGovernorateCounts(array|Collection $counts,Collection $governorates): array
    {
        $counts = collect($counts);

        return $governorates
            ->map(function ($governorate) use ($counts) {

                $row = $counts->get($governorate->id, []);

                return [
                    'governorate_id' => (int) $governorate->id,
                    'boards_count' => (int) ($row['boards'] ?? 0),
                    'networks_count' => (int) ($row['networks'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function calculateTotal(array $counts): array
    {
        return [
            'boards_count' => array_sum(array_column($counts, 'boards_count')),
            'networks_count' => array_sum(array_column($counts, 'networks_count')),
        ];
    }
}
