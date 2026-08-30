<?php

namespace App\Http\Resources\FlexBillboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlexBillboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->location_name,
            'governorate' => [
                'id' => $this->area?->governorate?->id,
                'name' => $this->area?->governorate?->name,
            ],
            'area' =>  $this->area?->name,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'status' => $this->flex_status,
        ];
    }
}
