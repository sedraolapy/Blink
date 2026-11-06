<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstitutionFlexGroupsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'region' => $this->resource['region'],

            'items' => collect($this->resource['items'])
                ->map(fn (array $item): array => [
                    'id' => (int) $item['id'],
                    'label' => $item['label'],
                ])
                ->values()
                ->all(),
        ];
    }
}
